<?php

namespace App\Services;

use App\Database;

/**
 * Unfollow Queue Service
 * 
 * Manages the unfollow queue with rate limiting
 * - Queue accounts for unfollowing
 * - Execute unfollows respecting Instagram rate limits
 * - Track progress and outcomes
 * - Handle retries and errors
 */
class UnfollowQueueService
{
    private $db;
    
    // Instagram rate limits
    private const MAX_UNFOLLOWS_PER_SESSION = 100;     // 100 unfollows per 24-hour session
    private const UNFOLLOW_DELAY = 2;                   // Wait 2 seconds between unfollows (to avoid detection)
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Queue accounts for unfollowing
     * 
     * @param int $userId User ID
     * @param array $accountIds Array of following record IDs to unfollow
     * @param string $reason Reason for unfollowing (for logging)
     * @return array ['queued' => N, 'errors' => []]
     */
    public function queueForUnfollow($userId, $accountIds, $reason = 'Manual unfollow'): array
    {
        if (empty($accountIds)) {
            return ['queued' => 0, 'errors' => ['No accounts provided']];
        }

        $queued = 0;
        $errors = [];

        foreach ($accountIds as $accountId) {
            try {
                // Verify account belongs to user and hasn't been unfollowed
                $stmt = $this->db->prepare("
                    SELECT id FROM following WHERE id = ? AND user_id = ? AND unfollowed_at IS NULL
                ");
                $stmt->execute([$accountId, $userId]);
                
                if (!$stmt->fetch()) {
                    $errors[] = "Account {$accountId} not found or already unfollowed";
                    continue;
                }

                // Insert into unfollow_queue
                $stmt = $this->db->prepare("
                    INSERT INTO unfollow_queue (user_id, following_id, reason, status, created_at)
                    VALUES (?, ?, ?, 'pending', NOW())
                    ON DUPLICATE KEY UPDATE
                        status = 'pending',
                        updated_at = NOW()
                ");
                $stmt->execute([$userId, $accountId, substr($reason, 0, 255)]);
                $queued++;

            } catch (\Exception $e) {
                $errors[] = "Error queueing account {$accountId}: " . $e->getMessage();
            }
        }

        return [
            'queued' => $queued,
            'errors' => $errors,
        ];
    }

    /**
     * Get current unfollow queue for user
     * 
     * @param int $userId User ID
     * @param string $status Filter by status (pending, processing, completed, failed)
     * @return array Queue entries with account details
     */
    public function getQueue($userId, $status = 'pending'): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                uq.*, 
                f.instagram_user_id, f.username, f.name, f.followers_count, f.profile_picture_url,
                ai.engagement_score, ai.category
            FROM unfollow_queue uq
            JOIN following f ON uq.following_id = f.id
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE uq.user_id = ? AND uq.status = ?
            ORDER BY uq.created_at ASC
        ");
        $stmt->execute([$userId, $status]);
        return $stmt->fetchAll();
    }

    /**
     * Get queue statistics for user
     * 
     * @param int $userId User ID
     * @return array Queue stats
     */
    public function getQueueStats($userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM unfollow_queue
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Execute queued unfollows for user
     * Respects rate limits and returns control immediately
     * 
     * @param int $userId User ID
     * @param InstagramApiService $apiService Instagram API service with valid token
     * @return array Execution result
     */
    public function executeQueue($userId, $apiService): array
    {
        // Check rate limit
        $canUnfollow = $this->checkRateLimit($userId);
        if (!$canUnfollow['allowed']) {
            return [
                'success' => false,
                'executed' => 0,
                'failed' => 0,
                'message' => $canUnfollow['reason'],
            ];
        }

        // Get pending unfollows (limit by rate limit)
        $remaining = $canUnfollow['remaining'];
        $stmt = $this->db->prepare("
            SELECT id, following_id, instagram_user_id, username
            FROM unfollow_queue uq
            WHERE uq.user_id = ? AND uq.status = 'pending'
            LIMIT ?
        ");
        $stmt->execute([$userId, $remaining]);
        $queue = $stmt->fetchAll();

        if (empty($queue)) {
            return [
                'success' => true,
                'executed' => 0,
                'failed' => 0,
                'message' => 'No pending unfollows in queue',
            ];
        }

        $executed = 0;
        $failed = 0;

        foreach ($queue as $entry) {
            try {
                // Update status to processing
                $stmt = $this->db->prepare("UPDATE unfollow_queue SET status = 'processing' WHERE id = ?");
                $stmt->execute([$entry['id']]);

                // Execute unfollow via Instagram API
                $apiService->unfollow($entry['instagram_user_id']);

                // Mark following account as unfollowed
                $stmt = $this->db->prepare("
                    UPDATE following SET unfollowed_at = NOW() WHERE id = ?
                ");
                $stmt->execute([$entry['following_id']]);

                // Mark queue entry as completed
                $stmt = $this->db->prepare("
                    UPDATE unfollow_queue SET status = 'completed', completed_at = NOW() WHERE id = ?
                ");
                $stmt->execute([$entry['id']]);

                // Log activity
                \App\Models\ActivityLog::log(
                    $this->db,
                    $userId,
                    'unfollow_executed',
                    'Unfollowed @' . $entry['username'],
                    ['instagram_user_id' => $entry['instagram_user_id'], 'username' => $entry['username']],
                    $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                );

                $executed++;

                // Delay to avoid Instagram detection
                sleep(self::UNFOLLOW_DELAY);

            } catch (\Exception $e) {
                // Mark as failed
                $stmt = $this->db->prepare("
                    UPDATE unfollow_queue SET status = 'failed', error_message = ? WHERE id = ?
                ");
                $stmt->execute([substr($e->getMessage(), 0, 500), $entry['id']]);

                $failed++;
            }
        }

        return [
            'success' => true,
            'executed' => $executed,
            'failed' => $failed,
            'message' => "Executed $executed unfollows, $failed failed",
        ];
    }

    /**
     * Check if user can unfollow more accounts today
     * Returns remaining quota and whether unfollowing is allowed
     * 
     * @param int $userId User ID
     * @return array ['allowed' => bool, 'remaining' => N, 'reason' => '']
     */
    public function checkRateLimit($userId): array
    {
        // Get unfollows completed in last 24 hours
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM unfollow_queue
            WHERE user_id = ? AND status = 'completed'
            AND completed_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        $completedToday = (int)$result['count'];
        $remaining = max(0, self::MAX_UNFOLLOWS_PER_SESSION - $completedToday);

        if ($remaining <= 0) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reason' => 'Daily unfollow limit reached (100 per 24 hours). Try again tomorrow.',
            ];
        }

        return [
            'allowed' => true,
            'remaining' => $remaining,
            'reason' => '',
        ];
    }

    /**
     * Remove accounts from queue without unfollowing
     * 
     * @param int $userId User ID
     * @param array $queueIds Array of unfollow_queue record IDs
     * @return array Result
     */
    public function removeFromQueue($userId, $queueIds): array
    {
        if (empty($queueIds)) {
            return ['removed' => 0, 'errors' => []];
        }

        $placeholders = implode(',', array_fill(0, count($queueIds), '?'));
        
        $stmt = $this->db->prepare("
            DELETE FROM unfollow_queue
            WHERE user_id = ? AND id IN ($placeholders) AND status IN ('pending', 'failed')
        ");

        $params = array_merge([$userId], $queueIds);
        $stmt->execute($params);

        return [
            'removed' => $stmt->rowCount(),
            'errors' => [],
        ];
    }

    /**
     * Clear entire queue for user (pending and failed only)
     * 
     * @param int $userId User ID
     * @return int Number of records cleared
     */
    public function clearQueue($userId): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM unfollow_queue
            WHERE user_id = ? AND status IN ('pending', 'failed')
        ");
        $stmt->execute([$userId]);
        return $stmt->rowCount();
    }

    /**
     * Get unfollow history for user
     * 
     * @param int $userId User ID
     * @param int $limit Number of records
     * @return array Completed unfollows
     */
    public function getHistory($userId, $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                uq.*, 
                f.username, f.name, f.profile_picture_url, f.followers_count,
                ai.engagement_score, ai.category
            FROM unfollow_queue uq
            JOIN following f ON uq.following_id = f.id
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE uq.user_id = ? AND uq.status = 'completed'
            ORDER BY uq.completed_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get unfollow statistics
     * 
     * @param int $userId User ID
     * @return array Statistics
     */
    public function getStatistics($userId): array
    {
        // Total unfollows
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_unfollowed FROM following WHERE user_id = ? AND unfollowed_at IS NOT NULL
        ");
        $stmt->execute([$userId]);
        $unfollowed = $stmt->fetch()['total_unfollowed'];

        // Unfollows by category
        $stmt = $this->db->prepare("
            SELECT ai.category, COUNT(*) as count
            FROM following f
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE f.user_id = ? AND f.unfollowed_at IS NOT NULL
            GROUP BY ai.category
        ");
        $stmt->execute([$userId]);
        $byCategory = $stmt->fetchAll();

        // Unfollows this week/month
        $stmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN unfollowed_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week,
                SUM(CASE WHEN unfollowed_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month
            FROM following
            WHERE user_id = ? AND unfollowed_at IS NOT NULL
        ");
        $stmt->execute([$userId]);
        $timeline = $stmt->fetch();

        return [
            'total_unfollowed' => $unfollowed,
            'by_category' => $byCategory,
            'this_week' => $timeline['week'] ?? 0,
            'this_month' => $timeline['month'] ?? 0,
        ];
    }
}
