<?php

namespace App\Controllers;

use App\Controller;
use App\Services\EngagementService;
use App\Services\ScoringService;
use App\Services\UnfollowQueueService;
use App\Services\InstagramApiService;

/**
 * UnfollowController — Manage unfollow operations and ranked list UI
 */
class UnfollowController extends Controller
{
    /**
     * Show ranked list of all following accounts
     */
    public function index()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $userId = $_SESSION['user_id'];

        // Get filter and sort parameters
        $category = $this->get('category', null);
        $scoreMin = (int)$this->get('score_min', 0);
        $scoreMax = (int)$this->get('score_max', 100);
        $followerMin = (int)$this->get('follower_min', 0);
        $followerMax = (int)$this->get('follower_max', 100000000);
        $search = $this->get('search', '');
        $sort = $this->get('sort', 'score_desc');
        $page = max(1, (int)$this->get('page', 1));
        $perPage = 50;

        // Build query
        $query = "
            SELECT 
                f.id, f.instagram_user_id, f.username, f.name, f.followers_count, f.profile_picture_url,
                ai.engagement_score, ai.engagement_gap_days, ai.category, ai.explanation, ai.account_type
            FROM following f
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE f.user_id = ? AND f.unfollowed_at IS NULL
        ";

        $params = [$userId];

        // Apply filters
        if ($category) {
            $query .= " AND ai.category = ?";
            $params[] = $category;
        }

        if ($scoreMin > 0 || $scoreMax < 100) {
            $query .= " AND ai.engagement_score BETWEEN ? AND ?";
            $params[] = $scoreMin;
            $params[] = $scoreMax;
        }

        if ($followerMin > 0 || $followerMax < 100000000) {
            $query .= " AND f.followers_count BETWEEN ? AND ?";
            $params[] = $followerMin;
            $params[] = $followerMax;
        }

        if ($search) {
            $query .= " AND (f.username LIKE ? OR f.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        // Count total results
        $countQuery = "SELECT COUNT(*) as count FROM ($query) as filtered";
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($params);
        $totalResults = $countStmt->fetch()['count'];
        $totalPages = ceil($totalResults / $perPage);

        // Apply sorting
        $sortMap = [
            'score_desc' => 'ai.engagement_score DESC',
            'score_asc' => 'ai.engagement_score ASC',
            'followers_desc' => 'f.followers_count DESC',
            'followers_asc' => 'f.followers_count ASC',
            'username_asc' => 'f.username ASC',
            'inactive_asc' => 'ai.engagement_gap_days DESC',
        ];
        $orderBy = $sortMap[$sort] ?? $sortMap['score_desc'];
        $query .= " ORDER BY $orderBy";

        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        // Execute query
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $accounts = $stmt->fetchAll();

        // Get unfollow queue stats
        $queueService = new UnfollowQueueService($this->db);
        $queueStats = $queueService->getQueueStats($userId);
        $rateLimit = $queueService->checkRateLimit($userId);

        // Get category distribution
        $categoryStmt = $this->db->prepare("
            SELECT ai.category, COUNT(*) as count
            FROM following f
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE f.user_id = ? AND f.unfollowed_at IS NULL
            GROUP BY ai.category
        ");
        $categoryStmt->execute([$userId]);
        $categories = $categoryStmt->fetchAll();

        // Get engagement summary
        $engagementService = new EngagementService($this->db);
        $summary = $engagementService->getSummaryStats($userId);

        // Format accounts for display
        $formattedAccounts = array_map(function ($account) {
            return [
                'id' => $account['id'],
                'instagram_user_id' => $account['instagram_user_id'],
                'username' => $account['username'],
                'name' => $account['name'],
                'followers' => $account['followers_count'],
                'profile_picture' => $account['profile_picture_url'],
                'score' => $account['engagement_score'] ?? 0,
                'gap_days' => $account['engagement_gap_days'] ?? 0,
                'category' => $account['category'] ?? 'Unscored',
                'explanation' => $account['explanation'] ?? 'No data',
                'account_type' => $account['account_type'] ?? 'Regular',
                'score_color' => ScoringService::getCategoryColor($account['category'] ?? 'Safe'),
            ];
        }, $accounts);

        return $this->view('pages/unfollow-list', [
            'pageTitle' => 'Ranked List',
            'accounts' => $formattedAccounts,
            'totalResults' => $totalResults,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage,
            'categories' => $categories,
            'summary' => $summary,
            'queueStats' => $queueStats,
            'rateLimit' => $rateLimit,
            'filters' => [
                'category' => $category,
                'score_min' => $scoreMin,
                'score_max' => $scoreMax,
                'follower_min' => $followerMin,
                'follower_max' => $followerMax,
                'search' => $search,
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Get accounts via AJAX (for infinite scroll or dynamic loading)
     */
    public function getAccounts()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        // Similar filtering logic as index but return JSON
        $userId = $_SESSION['user_id'];
        $category = $this->get('category');
        $sort = $this->get('sort', 'score_desc');
        $page = max(1, (int)$this->get('page', 1));
        $perPage = 50;

        // Build query (same as index)
        $query = "
            SELECT 
                f.id, f.instagram_user_id, f.username, f.name, f.followers_count, f.profile_picture_url,
                ai.engagement_score, ai.engagement_gap_days, ai.category, ai.explanation, ai.account_type
            FROM following f
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE f.user_id = ? AND f.unfollowed_at IS NULL
        ";

        $params = [$userId];

        if ($category) {
            $query .= " AND ai.category = ?";
            $params[] = $category;
        }

        // Sorting
        $sortMap = [
            'score_desc' => 'ai.engagement_score DESC',
            'score_asc' => 'ai.engagement_score ASC',
            'followers_desc' => 'f.followers_count DESC',
            'followers_asc' => 'f.followers_count ASC',
            'username_asc' => 'f.username ASC',
        ];
        $orderBy = $sortMap[$sort] ?? 'ai.engagement_score DESC';
        $query .= " ORDER BY $orderBy";

        // Pagination
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $accounts = $stmt->fetchAll();

        return $this->json([
            'success' => true,
            'accounts' => $accounts,
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Queue accounts for unfollowing
     */
    public function queueUnfollow()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $accountIds = $this->post('account_ids', []);

        if (!is_array($accountIds) || empty($accountIds)) {
            return $this->jsonError('No accounts provided', 422);
        }

        // Limit to reasonable batch size
        $accountIds = array_slice(array_map('intval', $accountIds), 0, 500);

        $queueService = new UnfollowQueueService($this->db);
        $result = $queueService->queueForUnfollow(
            $userId,
            $accountIds,
            'Queued from ranked list'
        );

        if ($result['queued'] === 0) {
            return $this->jsonError('Failed to queue accounts', 422, ['errors' => $result['errors']]);
        }

        // Log activity
        \App\Models\ActivityLog::log(
            $this->db,
            $userId,
            'unfollow_queued',
            'Queued ' . $result['queued'] . ' accounts for unfollowing',
            ['count' => $result['queued'], 'errors' => count($result['errors'])],
            $this->getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        return $this->jsonSuccess('Accounts queued for unfollowing', [
            'queued' => $result['queued'],
            'errors' => count($result['errors']),
            'redirect' => '/unfollow/queue',
        ]);
    }

    /**
     * Show unfollow queue
     */
    public function showQueue()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $userId = $_SESSION['user_id'];
        $status = $this->get('status', 'pending');

        $queueService = new UnfollowQueueService($this->db);
        $queue = $queueService->getQueue($userId, $status);
        $stats = $queueService->getQueueStats($userId);
        $rateLimit = $queueService->checkRateLimit($userId);

        return $this->view('pages/unfollow-queue', [
            'pageTitle' => 'Unfollow Queue',
            'queue' => $queue,
            'status' => $status,
            'stats' => $stats,
            'rateLimit' => $rateLimit,
        ]);
    }

    /**
     * Execute pending unfollows via AJAX
     */
    public function executeUnfollows()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];

        try {
            // Get user's Instagram token
            $stmt = $this->db->prepare("
                SELECT instagram_account_id, instagram_access_token FROM users WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !$user['instagram_access_token']) {
                return $this->jsonError('Instagram account not connected', 422);
            }

            // Create API service
            $config = require __DIR__ . '/../../config/app.php';
            $apiService = new InstagramApiService(
                $config['instagram']['app_id'],
                $config['instagram']['app_secret'],
                $config['instagram']['redirect_uri'],
                $user['instagram_access_token']
            );

            // Execute queue
            $queueService = new UnfollowQueueService($this->db);
            $result = $queueService->executeQueue($userId, $apiService);

            if (!$result['success']) {
                return $this->jsonError($result['message'], 429);
            }

            return $this->jsonSuccess($result['message'], $result);

        } catch (\Exception $e) {
            error_log('Unfollow execution error: ' . $e->getMessage());
            return $this->jsonError('Failed to execute unfollows: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove accounts from queue
     */
    public function removeFromQueue()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $queueIds = array_map('intval', (array)$this->post('queue_ids', []));

        if (empty($queueIds)) {
            return $this->jsonError('No queue items provided', 422);
        }

        $queueService = new UnfollowQueueService($this->db);
        $result = $queueService->removeFromQueue($userId, $queueIds);

        return $this->jsonSuccess('Removed from queue', ['removed' => $result['removed']]);
    }

    /**
     * Clear entire queue
     */
    public function clearQueue()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];

        $queueService = new UnfollowQueueService($this->db);
        $cleared = $queueService->clearQueue($userId);

        \App\Models\ActivityLog::log(
            $this->db,
            $userId,
            'unfollow_queue_cleared',
            'Cleared unfollow queue (' . $cleared . ' items)'
        );

        return $this->jsonSuccess('Queue cleared', ['cleared' => $cleared]);
    }

    /**
     * Show unfollow statistics and history
     */
    public function statistics()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $userId = $_SESSION['user_id'];
        $queueService = new UnfollowQueueService($this->db);

        $stats = $queueService->getStatistics($userId);
        $history = $queueService->getHistory($userId, 100);

        return $this->view('pages/unfollow-stats', [
            'pageTitle' => 'Unfollow Statistics',
            'stats' => $stats,
            'history' => $history,
        ]);
    }
}
