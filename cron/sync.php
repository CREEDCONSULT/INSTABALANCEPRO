<?php

/**
 * Instagram Sync Cron Job
 * 
 * This script is run periodically (via cron) to process pending sync jobs
 * Command: php cron/sync.php
 * 
 * Cron schedule (example):
 * * * * * * php /path/to/cron/sync.php >> /var/log/instagram-sync.log 2>&1
 * 
 * Process:
 * 1. Find all pending sync jobs
 * 2. For each job, create InstagramApiService and SyncService
 * 3. Execute full sync
 * 4. Update job status and progress
 * 5. Log any errors
 */

// Load application
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Model.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/SyncJob.php';
require_once __DIR__ . '/../src/Models/ActivityLog.php';
require_once __DIR__ . '/../src/Services/EncryptionService.php';
require_once __DIR__ . '/../src/Services/InstagramApiService.php';
require_once __DIR__ . '/../src/Services/SyncService.php';

use App\Database;
use App\Models\SyncJob;
use App\Models\ActivityLog;
use App\Services\InstagramApiService;
use App\Services\SyncService;
use App\Services\EncryptionService;

// Prevent HTTP access
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be run from the command line');
}

// Configuration
$config = require __DIR__ . '/../config/app.php';

// Initialize database
try {
    $db = new Database(
        $config['database']['host'],
        $config['database']['name'],
        $config['database']['user'],
        $config['database']['password']
    );
} catch (Exception $e) {
    error_log('Failed to connect to database: ' . $e->getMessage());
    exit(1);
}

// Encryption service
$encryption = new EncryptionService($config['encryption']['key'], $config['encryption']['iv']);

// Initialize sync counter
$syncedCount = 0;
$errorCount = 0;

echo "[" . date('Y-m-d H:i:s') . "] Starting Instagram sync job processor...\n";

try {
    // Get all pending sync jobs (limit to 5 concurrent to avoid overload)
    $pendingJobs = getPendingSyncJobs($db, 5);

    if (empty($pendingJobs)) {
        echo "[" . date('Y-m-d H:i:s') . "] No pending sync jobs found.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Found " . count($pendingJobs) . " pending sync jobs.\n";

    // Process each sync job
    foreach ($pendingJobs as $syncJob) {
        try {
            echo "[" . date('Y-m-d H:i:s') . "] Processing sync job #" . $syncJob['id'] . " for user #" . $syncJob['user_id'] . "\n";

            // Get user and verify Instagram token
            $user = getUserWithToken($db, $syncJob['user_id'], $encryption);

            if (!$user || !$user['instagram_access_token']) {
                throw new Exception('Instagram account not connected');
            }

            // Create API and Sync services
            $apiService = new InstagramApiService(
                $config['instagram']['app_id'],
                $config['instagram']['app_secret'],
                $config['instagram']['redirect_uri'],
                $user['instagram_access_token']
            );

            $syncService = new SyncService($db, $apiService);

            // Load sync job object
            $jobObject = new SyncJob();
            $jobObject->id = $syncJob['id'];
            $jobObject->user_id = $syncJob['user_id'];
            $jobObject->status = $syncJob['status'];
            $jobObject->created_at = $syncJob['created_at'];

            // Execute sync
            echo "  - Starting full sync...\n";
            $result = $syncService->syncFull($syncJob['user_id'], $jobObject);

            if ($result['success']) {
                echo "  ✓ Sync completed successfully\n";
                echo "    - Following: " . $result['stats']['following'] . "\n";
                echo "    - Followers: " . $result['stats']['followers'] . "\n";
                echo "    - New unfollowers: " . $result['stats']['new_unfollowers'] . "\n";
                echo "    - New followers: " . $result['stats']['new_followers'] . "\n";

                $syncedCount++;

            } else {
                throw new Exception($result['error'] ?? 'Unknown error');
            }

        } catch (Exception $e) {
            echo "  ✗ Sync failed: " . $e->getMessage() . "\n";

            // Log error in sync job
            try {
                $updateStmt = $db->prepare("
                    UPDATE sync_jobs SET status = 'failed', error_message = ? WHERE id = ?
                ");
                $updateStmt->execute([substr($e->getMessage(), 0, 500), $syncJob['id']]);
            } catch (Exception $logError) {
                error_log("Failed to log sync job error: " . $logError->getMessage());
            }

            $errorCount++;
        }

        // Small delay between syncs to avoid rate limiting
        sleep(2);
    }

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Fatal error: " . $e->getMessage() . "\n";
    error_log('Instagram sync cron error: ' . $e->getMessage());
    exit(1);
}

echo "[" . date('Y-m-d H:i:s') . "] Sync job processor completed.\n";
echo "  - Synced: " . $syncedCount . "\n";
echo "  - Errors: " . $errorCount . "\n";
exit(0);

/**
 * Get pending sync jobs from database
 * 
 * @param Database $db
 * @param int $limit Max jobs to return
 * @return array Array of sync job records
 */
function getPendingSyncJobs($db, $limit = 5)
{
    $stmt = $db->prepare("
        SELECT id, user_id, status, following_count, followers_count, created_at
        FROM sync_jobs
        WHERE status IN ('pending', 'in_progress')
        ORDER BY created_at ASC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get user with decrypted Instagram token
 * 
 * @param Database $db
 * @param int $userId User ID
 * @param EncryptionService $encryption
 * @return array|false User record with decrypted token
 */
function getUserWithToken($db, $userId, $encryption)
{
    $stmt = $db->prepare("
        SELECT u.id, u.instagram_account_id, ic.access_token, ic.expires_at
        FROM users u
        LEFT JOIN instagram_connections ic ON u.id = ic.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    // Check if token is expired and needs refresh
    if ($user['expires_at'] && strtotime($user['expires_at']) < time()) {
        // Token expired - would need refresh logic here
        // For now, mark as expired
        return false;
    }

    // Decrypt token
    if ($user['access_token']) {
        try {
            $user['instagram_access_token'] = $encryption->decrypt($user['access_token']);
        } catch (Exception $e) {
            error_log("Failed to decrypt token for user " . $userId . ": " . $e->getMessage());
            return false;
        }
    }

    return $user;
}
