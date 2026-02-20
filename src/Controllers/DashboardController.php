<?php

namespace App\Controllers;

use App\Controller;
use App\Models\ActivityLog;
use App\Models\SyncJob;

/**
 * DashboardController — Main user dashboard with KPI cards and activity feed
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard with KPIs and activity feed
     */
    public function index()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $userId = $_SESSION['user_id'];

        // Get KPI metrics
        $kpis = $this->db->getDashboardKPIs($userId);

        // Get last sync timestamp
        $lastSyncTime = $this->db->getLastSyncTime($userId);

        // Get current/recent sync status
        $currentSync = $this->db->getCurrentSyncStatus($userId);

        // Get recent activity feed
        $activities = $this->db->getActivityFeed($userId, 15);

        // Format activities with readable descriptions
        $formattedActivities = array_map(function ($activity) {
            return [
                'id' => $activity['id'],
                'action' => $activity['action'],
                'description' => $activity['description'],
                'data' => $activity['data'] ? json_decode($activity['data'], true) : null,
                'createdAt' => $activity['created_at'],
                'timeAgo' => $this->getTimeAgo($activity['created_at']),
            ];
        }, $activities);

        // Get subscription tier
        $user = $this->getUser();
        $subscriptionTier = $_SESSION['tier'] ?? 'free';

        // Prepare dashboard data
        $dashboardData = [
            'pageTitle' => 'Dashboard',
            'kpis' => $kpis,
            'lastSyncTime' => $lastSyncTime,
            'lastSyncTimeAgo' => $lastSyncTime ? $this->getTimeAgo($lastSyncTime) : 'Never',
            'currentSync' => $currentSync,
            'syncInProgress' => $currentSync && $currentSync['status'] === 'in_progress',
            'syncProgress' => $currentSync ? $this->getSyncProgress($currentSync) : null,
            'activities' => $formattedActivities,
            'subscriptionTier' => $subscriptionTier,
            'userId' => $userId,
        ];

        return $this->view('pages/dashboard', $dashboardData);
    }

    /**
     * Start sync operation via AJAX
     */
    public function startSync()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401, 'Not authenticated');
        }

        if (!$this->isPost()) {
            $this->abort(405, 'Method not allowed');
        }

        $userId = $_SESSION['user_id'];

        // Check if sync already in progress
        $currentSync = $this->db->getCurrentSyncStatus($userId);
        if ($currentSync && $currentSync['status'] === 'in_progress') {
            return $this->jsonError('Sync already in progress', 409);
        }

        // Create new sync job
        $syncJob = SyncJob::createForUser($this->db, $userId);

        // Log activity
        ActivityLog::log($this->db, $userId, 'sync_started', 'Initiated follower synchronization');

        // Queue background sync job
        // On production servers, this should be triggered via cron job or message queue
        // For now, we attempt to trigger it asynchronously
        try {
            $this->queueSyncJob($syncJob->id);
        } catch (\Exception $e) {
            // Log error but don't fail the request - sync job is created
            error_log('Failed to queue background sync: ' . $e->getMessage());
        }

        return $this->json([
            'success' => true,
            'message' => 'Sync started',
            'jobId' => $syncJob->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Get sync status via AJAX/htmx
     */
    public function syncStatus()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401, 'Not authenticated');
        }

        $userId = $_SESSION['user_id'];
        $currentSync = $this->db->getCurrentSyncStatus($userId);

        if (!$currentSync) {
            return $this->json([
                'status' => 'no_sync',
                'message' => 'No sync job found',
            ]);
        }

        $progress = $this->getSyncProgress($currentSync);

        return $this->json([
            'jobId' => $currentSync['id'],
            'status' => $currentSync['status'],
            'progress' => $progress,
            'followingCount' => $currentSync['following_count'],
            'followersCount' => $currentSync['followers_count'],
            'processedCount' => $currentSync['processed_count'],
            'errorMessage' => $currentSync['error_message'],
            'createdAt' => $currentSync['created_at'],
            'completedAt' => $currentSync['completed_at'],
        ]);
    }

    /**
     * Cancel sync job via AJAX
     */
    public function cancelSync()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401, 'Not authenticated');
        }

        if (!$this->isPost()) {
            $this->abort(405, 'Method not allowed');
        }

        $userId = $_SESSION['user_id'];
        $currentSync = $this->db->getCurrentSyncStatus($userId);

        if (!$currentSync || $currentSync['status'] !== 'in_progress') {
            return $this->jsonError('No sync in progress to cancel', 422);
        }

        // Update sync job status to cancelled
        $syncJob = new SyncJob();
        $syncJob->id = $currentSync['id'];
        $syncJob->updateStatus($this->db, 'cancelled', 'User cancelled sync');

        // Log activity
        ActivityLog::log($this->db, $userId, 'sync_cancelled', 'Cancelled follower synchronization');

        return $this->jsonSuccess('Sync cancelled');
    }

    /**
     * Queue background sync job
     * Attempts to trigger the cron job asynchronously
     */
    private function queueSyncJob(int $syncJobId): void
    {
        // Determine PHP executable path
        $php = PHP_EXECUTABLE ?: 'php';
        $cronScript = dirname(__DIR__, 2) . '/cron/sync.php';

        // Build command - background execution varies by OS
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: Use start command to run in background
            $command = "start /B $php \"$cronScript\" > nul 2>&1";
            pclose(popen($command, 'r'));
        } else {
            // Linux/Mac: Use nohup or & for background execution
            $command = "nohup $php \"$cronScript\" > /dev/null 2>&1 &";
            shell_exec($command);
        }

        // Note: In production, use proper job queue (Beanstalkd, Redis, RabbitMQ, etc.)
    }

    /**
     * Calculate sync progress percentage
     */
    private function getSyncProgress(array $syncJob): array
    {
        $followingCount = (int)($syncJob['following_count'] ?? 0);
        $processedCount = (int)($syncJob['processed_count'] ?? 0);

        $percent = $followingCount > 0 ? min(100, (int)(($processedCount / $followingCount) * 100)) : 0;

        return [
            'percent' => $percent,
            'processed' => $processedCount,
            'total' => $followingCount,
        ];
    }

    /**
     * Format timestamp as "time ago" (e.g., "2 hours ago")
     */
    private function getTimeAgo(string $dateTime): string
    {
        $time = strtotime($dateTime);
        $now = time();
        $secondsAgo = $now - $time;

        if ($secondsAgo < 60) {
            return 'Just now';
        }

        $minutesAgo = floor($secondsAgo / 60);
        if ($minutesAgo < 60) {
            return $minutesAgo === 1 ? '1 minute ago' : "$minutesAgo minutes ago";
        }

        $hoursAgo = floor($secondsAgo / 3600);
        if ($hoursAgo < 24) {
            return $hoursAgo === 1 ? '1 hour ago' : "$hoursAgo hours ago";
        }

        $daysAgo = floor($secondsAgo / 86400);
        if ($daysAgo < 7) {
            return $daysAgo === 1 ? '1 day ago' : "$daysAgo days ago";
        }

        $weeksAgo = floor($secondsAgo / 604800);
        if ($weeksAgo < 4) {
            return $weeksAgo === 1 ? '1 week ago' : "$weeksAgo weeks ago";
        }

        return date('M d, Y', $time);
    }

    /**
     * Get activity icon by action type
     */
    public function getActivityIcon(string $action): string
    {
        return match ($action) {
            'sync_started' => 'bi-arrow-clockwise text-primary',
            'sync_completed' => 'bi-check-circle text-success',
            'sync_failed' => 'bi-exclamation-circle text-danger',
            'sync_cancelled' => 'bi-x-circle text-warning',
            'unfollow_executed' => 'bi-person-x text-danger',
            'unfollow_queued' => 'bi-hourglass-bottom text-info',
            'whitelist_added' => 'bi-check2-square text-success',
            'whitelist_removed' => 'bi-x-square text-warning',
            'login' => 'bi-box-arrow-in-right text-primary',
            'logout' => 'bi-box-arrow-right text-secondary',
            'settings_updated' => 'bi-gear text-info',
            default => 'bi-info-circle text-secondary',
        };
    }
}
