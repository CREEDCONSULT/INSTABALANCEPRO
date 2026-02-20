<?php

namespace App\Models;

use App\Database;
use App\Model;

/**
 * SyncJob Model - Track Instagram synchronization jobs
 * 
 * Used to monitor sync progress and history
 */
class SyncJob extends Model
{
    protected string $table = 'sync_jobs';
    protected array $fillable = ['user_id', 'status', 'following_count', 'followers_count', 'processed_count', 'error_message', 'started_at', 'completed_at'];

    /**
     * Get the latest sync job for a user
     */
    public static function getLatestForUser(Database $db, int $userId): ?self
    {
        return self::where($db, 'user_id = ?', [$userId])
            ->orderBy('created_at DESC')
            ->first();
    }

    /**
     * Get sync jobs for a user, ordered by newest first
     */
    public static function getForUser(Database $db, int $userId, int $limit = 10): array
    {
        return self::where($db, 'user_id = ?', [$userId])
            ->orderBy('created_at DESC')
            ->limit($limit)
            ->all();
    }

    /**
     * Create a new sync job
     */
    public static function createForUser(Database $db, int $userId): self
    {
        $job = new self();
        $job->user_id = $userId;
        $job->status = 'pending';
        $job->processed_count = 0;
        $job->created_at = date('Y-m-d H:i:s');
        
        $db->insert('sync_jobs', [
            'user_id' => $userId,
            'status' => 'pending',
            'processed_count' => 0,
            'created_at' => $job->created_at,
        ]);
        
        $job->id = $db->lastInsertId();
        return $job;
    }

    /**
     * Update sync job status
     */
    public function updateStatus(Database $db, string $status, ?string $errorMessage = null): void
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        if ($errorMessage) {
            $data['error_message'] = $errorMessage;
        }

        $db->update('sync_jobs', $data, 'id = ?', [$this->id]);
    }

    /**
     * Update sync progress
     */
    public function updateProgress(Database $db, int $followingCount, int $followersCount, int $processedCount): void
    {
        $db->update('sync_jobs', [
            'following_count' => $followingCount,
            'followers_count' => $followersCount,
            'processed_count' => $processedCount,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$this->id]);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): int
    {
        if ($this->following_count && $this->following_count > 0) {
            return min(100, (int)(($this->processed_count / $this->following_count) * 100));
        }
        return 0;
    }
}
