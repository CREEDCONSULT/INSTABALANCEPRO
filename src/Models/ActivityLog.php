<?php

namespace App\Models;

use App\Database;
use App\Model;

/**
 * ActivityLog Model - Track user actions and synchronization events
 * 
 * Records all important activities for audit trail and feed display
 */
class ActivityLog extends Model
{
    protected string $table = 'activity_log';
    protected array $fillable = ['user_id', 'action', 'description', 'data', 'ip_address', 'user_agent'];

    /**
     * Get recent activities for a user
     */
    public static function getRecentForUser(Database $db, int $userId, int $limit = 20): array
    {
        $stmt = $db->prepare(
            "SELECT * FROM activity_log 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get activities for a specific type
     */
    public static function getByAction(Database $db, int $userId, string $action, int $limit = 20): array
    {
        $stmt = $db->prepare(
            "SELECT * FROM activity_log 
             WHERE user_id = ? AND action = ? 
             ORDER BY created_at DESC 
             LIMIT ?"
        );
        $stmt->execute([$userId, $action, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Log a user action
     */
    public static function log(
        Database $db,
        int $userId,
        string $action,
        string $description,
        ?array $data = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        $ipAddress = $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $db->insert('activity_log', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'data' => $data ? json_encode($data) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Count activities by action
     */
    public static function countByAction(Database $db, int $userId, string $action, string $period = '30 days'): int
    {
        $stmt = $db->prepare(
            "SELECT COUNT(*) as count FROM activity_log 
             WHERE user_id = ? AND action = ? AND created_at >= DATE_SUB(NOW(), INTERVAL $period)"
        );
        $stmt->execute([$userId, $action]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * Get activity summary
     */
    public static function getSummary(Database $db, int $userId): array
    {
        $stmt = $db->prepare(
            "SELECT action, COUNT(*) as count 
             FROM activity_log 
             WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY action"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
