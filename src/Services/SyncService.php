<?php

namespace App\Services;

use App\Database;
use App\Models\SyncJob;
use Exception;

/**
 * Sync Service
 * 
 * Orchestrates Instagram synchronization operations
 * - Fetches all following and followers from Instagram API
 * - Stores data in database
 * - Updates SyncJob progress
 * - Handles errors and rollback
 */
class SyncService
{
    private $db;
    private $apiService;
    private $syncJob;
    private $userId;

    public function __construct(Database $db, InstagramApiService $apiService)
    {
        $this->db = $db;
        $this->apiService = $apiService;
    }

    /**
     * Execute full synchronization
     * Fetches following and followers lists, stores in database
     * 
     * @param int $userId User ID
     * @param SyncJob|null $syncJob Optional SyncJob instance for progress tracking
     * @return array Sync result: ['success' => true, 'stats' => [counts], 'error' => null]
     */
    public function syncFull($userId, $syncJob = null)
    {
        $this->userId = $userId;
        $this->syncJob = $syncJob;

        try {
            // Get user's Instagram token from database
            $user = $this->getUser($userId);
            if (!$user || !$user['instagram_access_token']) {
                throw new Exception('Instagram account not connected');
            }

            // Set API token
            $this->apiService->setUserToken($user['instagram_access_token']);

            // Verify API connection
            if (!$this->apiService->testConnection()) {
                throw new Exception('Failed to connect to Instagram API');
            }

            // Start transaction
            $this->db->beginTransaction();

            // Get current account info
            $this->updateStatus('in_progress', 'Fetching account information');
            $accountInfo = $this->apiService->getBusinessAccount();
            $this->storeAccountInfo($userId, $accountInfo);

            // Sync following list
            $this->updateStatus('in_progress', 'Syncing following list');
            $followingStats = $this->syncFollowing($userId, $accountInfo['id']);

            // Sync followers list
            $this->updateStatus('in_progress', 'Syncing followers list');
            $followersStats = $this->syncFollowers($userId, $accountInfo['id']);

            // Calculate engagement metrics
            $this->updateStatus('in_progress', 'Calculating engagement metrics');
            $this->calculateEngagementMetrics($userId);

            // Calculate account scores and categories
            $this->updateStatus('in_progress', 'Scoring accounts');
            $this->scoreAccounts($userId);

            // Commit transaction
            $this->db->commit();

            // Mark sync as complete
            $this->updateStatus('completed');
            if ($this->syncJob) {
                $this->syncJob->updateProgress($this->db, $followingStats['total'], $followersStats['total'], $followingStats['total']);
            }

            return [
                'success' => true,
                'stats' => [
                    'following' => $followingStats['total'],
                    'followers' => $followersStats['total'],
                    'following_processed' => $followingStats['processed'],
                    'followers_processed' => $followersStats['processed'],
                    'new_unfollowers' => $followingStats['new_unfollowers'],
                    'new_followers' => $followersStats['new_followers'],
                ],
                'error' => null,
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            try {
                $this->db->rollBack();
            } catch (Exception $rollbackError) {
                // Log rollback error but don't fail
            }

            // Mark sync as failed
            $this->updateStatus('failed', $e->getMessage());

            return [
                'success' => false,
                'stats' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sync following list from Instagram API
     * Paginates through all accounts the user is following
     * 
     * @param int $userId User ID
     * @param string $instagramAccountId Instagram account ID
     * @return array ['total' => 2847, 'processed' => 2847, 'new_unfollowers' => 5]
     */
    private function syncFollowing($userId, $instagramAccountId)
    {
        $total = 0;
        $processed = 0;
        $newUnfollowers = 0;
        $afterCursor = null;
        $maxPages = 500; // Safety limit: 500 pages * 100 per page = 50k accounts
        $pageCount = 0;

        // Get current following from database (to detect unfollowers)
        $previousFollowing = $this->getPreviousFollowing($userId);

        while ($pageCount < $maxPages) {
            try {
                // Fetch page from API
                $response = $this->apiService->getFollowing($afterCursor);
                $accounts = $response['data'] ?? [];

                if (empty($accounts)) {
                    break;
                }

                $total = count($accounts);

                // Store each account in database
                foreach ($accounts as $account) {
                    $this->storeFollowingAccount($userId, $account);
                    $processed++;

                    // Update progress every 100 accounts
                    if ($processed % 100 === 0) {
                        $this->updateProgress($processed, $total);
                    }

                    // Check if this account was previously following but isn't now
                    if (!isset($previousFollowing[$account['id']])) {
                        $newUnfollowers++;
                    }
                }

                // Check for pagination cursor
                if (!isset($response['paging']['cursors']['after'])) {
                    break;
                }

                $afterCursor = $response['paging']['cursors']['after'];
                $pageCount++;

            } catch (Exception $e) {
                // Log but continue - partial sync is better than no sync
                error_log('Error fetching following page: ' . $e->getMessage());
                break;
            }
        }

        // Mark accounts as unfollowed if they were in previous but not in current
        $this->markUnfollowed($userId, $previousFollowing, $processed);

        return [
            'total' => $total,
            'processed' => $processed,
            'new_unfollowers' => $newUnfollowers,
        ];
    }

    /**
     * Sync followers list from Instagram API
     * Paginates through all accounts following the user
     * 
     * @param int $userId User ID
     * @param string $instagramAccountId Instagram account ID
     * @return array ['total' => 1234, 'processed' => 1234, 'new_followers' => 12]
     */
    private function syncFollowers($userId, $instagramAccountId)
    {
        $total = 0;
        $processed = 0;
        $newFollowers = 0;
        $afterCursor = null;
        $maxPages = 500; // Safety limit
        $pageCount = 0;

        // Get previous followers (to detect new followers)
        $previousFollowers = $this->getPreviousFollowers($userId);

        while ($pageCount < $maxPages) {
            try {
                // Fetch page from API
                $response = $this->apiService->getFollowers($afterCursor);
                $accounts = $response['data'] ?? [];

                if (empty($accounts)) {
                    break;
                }

                $total = count($accounts);

                // Store each account in database
                foreach ($accounts as $account) {
                    $this->storeFollowerAccount($userId, $account);
                    $processed++;

                    // Count as new if not in previous
                    if (!isset($previousFollowers[$account['id']])) {
                        $newFollowers++;
                    }

                    // Update progress every 100 accounts
                    if ($processed % 100 === 0) {
                        $this->updateProgress($processed, $total);
                    }
                }

                // Check for pagination cursor
                if (!isset($response['paging']['cursors']['after'])) {
                    break;
                }

                $afterCursor = $response['paging']['cursors']['after'];
                $pageCount++;

            } catch (Exception $e) {
                // Log but continue
                error_log('Error fetching followers page: ' . $e->getMessage());
                break;
            }
        }

        return [
            'total' => $total,
            'processed' => $processed,
            'new_followers' => $newFollowers,
        ];
    }

    /**
     * Store following account in database
     * Updates if exists, creates if new
     * 
     * @param int $userId User ID
     * @param array $account Account data from API
     */
    private function storeFollowingAccount($userId, $account)
    {
        $stmt = $this->db->prepare("
            INSERT INTO following (user_id, instagram_user_id, username, name, followers_count, follows_count, profile_picture_url, biography, verified, unfollowed_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)
            ON DUPLICATE KEY UPDATE
                username = VALUES(username),
                name = VALUES(name),
                followers_count = VALUES(followers_count),
                follows_count = VALUES(follows_count),
                profile_picture_url = VALUES(profile_picture_url),
                biography = VALUES(biography),
                verified = VALUES(verified),
                unfollowed_at = NULL,
                updated_at = NOW()
        ");

        $verified = isset($account['verified']) && $account['verified'] ? 1 : 0;

        $stmt->execute([
            $userId,
            $account['id'],
            $account['username'] ?? '',
            $account['name'] ?? '',
            $account['followers_count'] ?? 0,
            $account['follows_count'] ?? 0,
            $account['profile_picture_url'] ?? '',
            $account['biography'] ?? '',
            $verified,
        ]);
    }

    /**
     * Store follower account in database
     * Updates if exists, creates if new
     * 
     * @param int $userId User ID
     * @param array $account Account data from API
     */
    private function storeFollowerAccount($userId, $account)
    {
        $stmt = $this->db->prepare("
            INSERT INTO followers (user_id, instagram_user_id, username, name, followers_count, follows_count, profile_picture_url, biography, verified, followed_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                username = VALUES(username),
                name = VALUES(name),
                followers_count = VALUES(followers_count),
                follows_count = VALUES(follows_count),
                profile_picture_url = VALUES(profile_picture_url),
                biography = VALUES(biography),
                verified = VALUES(verified),
                updated_at = NOW()
        ");

        $verified = isset($account['verified']) && $account['verified'] ? 1 : 0;

        $stmt->execute([
            $userId,
            $account['id'],
            $account['username'] ?? '',
            $account['name'] ?? '',
            $account['followers_count'] ?? 0,
            $account['follows_count'] ?? 0,
            $account['profile_picture_url'] ?? '',
            $account['biography'] ?? '',
            $verified,
        ]);
    }

    /**
     * Store account profile information
     * Updates user's Instagram profile data
     * 
     * @param int $userId User ID
     * @param array $accountInfo Account info from API
     */
    private function storeAccountInfo($userId, $accountInfo)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET instagram_username = ?,
                instagram_followers_count = ?,
                instagram_follows_count = ?,
                instagram_profile_picture = ?,
                instagram_biography = ?,
                instagram_verified = ?,
                last_instagram_sync = NOW()
            WHERE id = ?
        ");

        $verified = isset($accountInfo['verified']) && $accountInfo['verified'] ? 1 : 0;

        $stmt->execute([
            $accountInfo['username'] ?? '',
            $accountInfo['followers_count'] ?? 0,
            $accountInfo['follows_count'] ?? 0,
            $accountInfo['profile_picture_url'] ?? '',
            $accountInfo['biography'] ?? '',
            $verified,
            $userId,
        ]);
    }

    /**
     * Get previous following list for comparison
     * Returns map of instagram_user_id => account_id
     * 
     * @param int $userId User ID
     * @return array
     */
    private function getPreviousFollowing($userId)
    {
        $stmt = $this->db->prepare("
            SELECT instagram_user_id, id FROM following WHERE user_id = ? AND unfollowed_at IS NULL
        ");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();

        $map = [];
        foreach ($results as $row) {
            $map[$row['instagram_user_id']] = $row['id'];
        }

        return $map;
    }

    /**
     * Get previous followers list for comparison
     * 
     * @param int $userId User ID
     * @return array
     */
    private function getPreviousFollowers($userId)
    {
        $stmt = $this->db->prepare("
            SELECT instagram_user_id, id FROM followers WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();

        $map = [];
        foreach ($results as $row) {
            $map[$row['instagram_user_id']] = $row['id'];
        }

        return $map;
    }

    /**
     * Mark accounts as unfollowed if they were previously following
     * Sets unfollowed_at timestamp
     * 
     * @param int $userId User ID
     * @param array $previousFollowing Previous following map
     * @param int $currentCount Current following count
     */
    private function markUnfollowed($userId, $previousFollowing, $currentCount)
    {
        // Find accounts that were following but aren't anymore
        foreach ($previousFollowing as $instagramUserId => $accountId) {
            // Check if still in current following
            $stmt = $this->db->prepare("
                SELECT id FROM following WHERE user_id = ? AND instagram_user_id = ? AND unfollowed_at IS NULL
            ");
            $stmt->execute([$userId, $instagramUserId]);

            if (!$stmt->fetch()) {
                // Not in current list - mark as unfollowed
                $stmt = $this->db->prepare("
                    UPDATE following SET unfollowed_at = NOW() WHERE user_id = ? AND instagram_user_id = ?
                ");
                $stmt->execute([$userId, $instagramUserId]);
            }
        }
    }

    /**
     * Calculate engagement metrics for all following accounts
     * Populates account_insights table
     * 
     * @param int $userId User ID
     */
    private function calculateEngagementMetrics($userId)
    {
        // Use EngagementService to calculate real engagement metrics
        $engagementService = new EngagementService($this->db);
        $result = $engagementService->calculateForAllFollowing($userId);

        // Log any errors that occurred
        if (!empty($result['errors'])) {
            error_log('Engagement calculation errors for user ' . $userId . ': ' . json_encode($result['errors']));
        }
    }

    /**
     * Score all accounts based on engagement and account characteristics
     * 
     * @param int $userId User ID
     */
    private function scoreAccounts($userId)
    {
        // Use ScoringService to calculate comprehensive scores
        $scoringService = new ScoringService($this->db);
        $result = $scoringService->scoreAllFollowing($userId);

        // Log any errors that occurred
        if (!empty($result['errors'])) {
            error_log('Scoring calculation errors for user ' . $userId . ': ' . json_encode($result['errors']));
        }
    }

    /**
     * Get user from database
     * 
     * @param int $userId User ID
     * @return array User record
     */
    private function getUser($userId)
    {
        $stmt = $this->db->prepare("
            SELECT id, instagram_access_token, instagram_account_id FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Update sync status
     * 
     * @param string $status Status: pending, in_progress, completed, failed, cancelled
     * @param string $errorMessage Optional error message
     */
    private function updateStatus($status, $errorMessage = null)
    {
        if ($this->syncJob) {
            $this->syncJob->updateStatus($this->db, $status, $errorMessage);
        }
    }

    /**
     * Update sync progress
     * 
     * @param int $processed Number of accounts processed
     * @param int $total Total number of accounts to process
     */
    private function updateProgress($processed, $total)
    {
        if ($this->syncJob) {
            $this->syncJob->updateProgress($this->db, $total, $total, $processed);
        }
    }
}
