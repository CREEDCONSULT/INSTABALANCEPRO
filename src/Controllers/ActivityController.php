<?php

namespace App\Controllers;

use App\Controller;

/**
 * ActivityController — Activity calendar and heatmap visualization
 */
class ActivityController extends Controller
{
    /**
     * Show activity calendar
     */
    public function index()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $userId = $_SESSION['user_id'];
        $year = (int)$this->get('year', date('Y'));
        $month = (int)$this->get('month', date('m'));

        // Get activity data for calendar
        $activityData = $this->getActivityData($userId, $year, $month);
        $monthActivity = $this->getMonthActivity($userId, $year, $month);
        $yearActivity = $this->getYearActivity($userId, $year);

        return $this->view('pages/activity', [
            'pageTitle' => 'Activity Calendar',
            'year' => $year,
            'month' => $month,
            'monthName' => date('F', mktime(0, 0, 0, $month, 1)),
            'activityData' => $activityData,
            'monthActivity' => $monthActivity,
            'yearActivity' => $yearActivity,
        ]);
    }

    /**
     * Get calendar data for specific month via AJAX
     */
    public function getCalendar($year, $month)
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        $userId = $_SESSION['user_id'];
        $year = (int)$year;
        $month = (int)$month;

        $activityData = $this->getActivityData($userId, $year, $month);

        return $this->json([
            'success' => true,
            'year' => $year,
            'month' => $month,
            'data' => $activityData,
        ]);
    }

    /**
     * Get activity data for a month
     * Returns array of date => activity_count
     */
    private function getActivityData($userId, $year, $month)
    {
        $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Get all activity logs for the month
        $stmt = $this->db->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count,
                GROUP_CONCAT(DISTINCT action SEPARATOR ',') as actions
            FROM activity_log
            WHERE user_id = ? 
            AND created_at BETWEEN ? AND ?
            AND action IN ('unfollow_queued', 'unfollow_executed', 'kanban_move', 'sync_completed')
            GROUP BY DATE(created_at)
        ");
        $stmt->execute([$userId, "$startDate 00:00:00", "$endDate 23:59:59"]);
        $results = $stmt->fetchAll();

        // Convert to associative array
        $activityMap = [];
        foreach ($results as $row) {
            $day = (int)date('j', strtotime($row['date']));
            $activityMap[$day] = [
                'count' => $row['count'],
                'actions' => explode(',', $row['actions']),
            ];
        }

        return $activityMap;
    }

    /**
     * Get activity summary for the month
     */
    private function getMonthActivity($userId, $year, $month)
    {
        $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN action = 'unfollow_executed' THEN 1 ELSE 0 END) as unfollows,
                SUM(CASE WHEN action = 'unfollow_queued' THEN 1 ELSE 0 END) as queued,
                SUM(CASE WHEN action = 'kanban_move' THEN 1 ELSE 0 END) as moves,
                SUM(CASE WHEN action = 'sync_completed' THEN 1 ELSE 0 END) as syncs,
                COUNT(DISTINCT DATE(created_at)) as active_days
            FROM activity_log
            WHERE user_id = ? 
            AND created_at BETWEEN ? AND ?
            AND action IN ('unfollow_queued', 'unfollow_executed', 'kanban_move', 'sync_completed')
        ");
        $stmt->execute([$userId, "$startDate 00:00:00", "$endDate 23:59:59"]);
        return $stmt->fetch();
    }

    /**
     * Get heatmap data for the entire year
     */
    private function getYearActivity($userId, $year)
    {
        $heatmap = [];

        // Get activity for each month
        for ($month = 1; $month <= 12; $month++) {
            $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $endDate = date('Y-m-t', strtotime($startDate));

            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM activity_log
                WHERE user_id = ? 
                AND created_at BETWEEN ? AND ?
                AND action IN ('unfollow_queued', 'unfollow_executed', 'kanban_move', 'sync_completed')
            ");
            $stmt->execute([$userId, "$startDate 00:00:00", "$endDate 23:59:59"]);
            $result = $stmt->fetch();

            $monthName = date('M', mktime(0, 0, 0, $month, 1));
            $heatmap[$monthName] = $result['count'] ?? 0;
        }

        return $heatmap;
    }

    /**
     * Get activity events for pagination/listing
     */
    public function getEvents()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        $userId = $_SESSION['user_id'];
        $date = $this->get('date');
        $limit = min(50, (int)$this->get('limit', 50));

        if (!$date) {
            return $this->jsonError('Date parameter required', 422);
        }

        // Parse date
        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj) {
            return $this->jsonError('Invalid date format', 422);
        }

        $stmt = $this->db->prepare("
            SELECT 
                id, action, description, metadata, created_at
            FROM activity_log
            WHERE user_id = ? 
            AND DATE(created_at) = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $date, $limit]);
        $events = $stmt->fetchAll();

        return $this->json([
            'success' => true,
            'date' => $date,
            'events' => $events,
        ]);
    }

    /**
     * Get insights/recommendations based on activity
     */
    public function getInsights()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        $userId = $_SESSION['user_id'];

        // Get last 7 days activity
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN action = 'unfollow_executed' THEN 1 ELSE 0 END) as unfollows
            FROM activity_log
            WHERE user_id = ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND action IN ('unfollow_queued', 'unfollow_executed')
        ");
        $stmt->execute([$userId]);
        $sevenDayStats = $stmt->fetch();

        // Get pending unfollows
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM unfollow_queue
            WHERE user_id = ? AND status = 'pending'
        ");
        $stmt->execute([$userId]);
        $pendingCount = $stmt->fetch()['count'];

        $insights = [];

        // Generate insights
        if ($sevenDayStats['unfollows'] > 20) {
            $insights[] = [
                'type' => 'success',
                'message' => "Great pace! You've unfollowed {$sevenDayStats['unfollows']} accounts in the last 7 days.",
            ];
        }

        if ($pendingCount > 50) {
            $insights[] = [
                'type' => 'info',
                'message' => "You have {$pendingCount} pending unfollows. Consider executing your queue.",
            ];
        }

        if ($sevenDayStats['total_events'] === 0) {
            $insights[] = [
                'type' => 'warning',
                'message' => 'No activity in the last 7 days. Start reviewing accounts to get recommendations.',
            ];
        }

        return $this->json([
            'success' => true,
            'insights' => $insights,
            'stats' => $sevenDayStats,
        ]);
    }
}
