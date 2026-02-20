<?php

namespace App\Controllers;

use App\Controller;
use App\Services\EncryptionService;

/**
 * SettingsController — User account settings and preferences
 */
class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $userId = $_SESSION['user_id'];

        // Get user details
        $stmt = $this->db->prepare("
            SELECT 
                email, display_name, profile_picture_url, subscription_tier,
                is_active, email_verified_at, two_fa_enabled, created_at
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Get scoring preferences
        $stmt = $this->db->prepare("
            SELECT inactivity_weight, engagement_weight, ratio_weight, age_weight
            FROM user_scoring_preferences
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $preferences = $stmt->fetch();

        // Get session info
        $sessions = $this->getActiveSessions($userId);

        return $this->view('pages/settings', [
            'pageTitle' => 'Settings',
            'user' => $user,
            'preferences' => $preferences,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $displayName = trim($this->post('display_name', ''));

        if (empty($displayName)) {
            return $this->jsonError('Display name cannot be empty', 422);
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET display_name = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$displayName, $userId]);

            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'profile_updated',
                'Updated profile display name',
                ['display_name' => $displayName]
            );

            return $this->jsonSuccess('Profile updated', ['display_name' => $displayName]);

        } catch (\Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            return $this->jsonError('Failed to update profile', 500);
        }
    }

    /**
     * Update email address
     */
    public function updateEmail()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $newEmail = filter_var($this->post('email'), FILTER_VALIDATE_EMAIL);

        if (!$newEmail) {
            return $this->jsonError('Invalid email address', 422);
        }

        try {
            // Check if email is already in use
            $stmt = $this->db->prepare("
                SELECT id FROM users WHERE email = ? AND id != ?
            ");
            $stmt->execute([$newEmail, $userId]);
            if ($stmt->fetch()) {
                return $this->jsonError('Email already in use', 409);
            }

            // Update email
            $stmt = $this->db->prepare("
                UPDATE users 
                SET email = ?, email_verified_at = NULL, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$newEmail, $userId]);

            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'email_updated',
                'Changed email address',
                ['new_email' => $newEmail]
            );

            return $this->jsonSuccess('Email updated. Please verify your new email.');

        } catch (\Exception $e) {
            error_log('Email update error: ' . $e->getMessage());
            return $this->jsonError('Failed to update email', 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $currentPassword = $this->post('current_password');
        $newPassword = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');

        if (!$currentPassword || !$newPassword || !$confirmPassword) {
            return $this->jsonError('All password fields are required', 422);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->jsonError('New passwords do not match', 422);
        }

        if (strlen($newPassword) < 8) {
            return $this->jsonError('Password must be at least 8 characters', 422);
        }

        try {
            // Verify current password
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!password_verify($currentPassword, $user['password_hash'])) {
                return $this->jsonError('Current password is incorrect', 401);
            }

            // Update password
            $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password_hash = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$passwordHash, $userId]);

            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'password_changed',
                'Changed account password',
                [],
                $this->getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            return $this->jsonSuccess('Password changed successfully');

        } catch (\Exception $e) {
            error_log('Password update error: ' . $e->getMessage());
            return $this->jsonError('Failed to update password', 500);
        }
    }

    /**
     * Update scoring preferences
     */
    public function updateScoringPreferences()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $inactivityWeight = (int)$this->post('inactivity_weight', 40);
        $engagementWeight = (int)$this->post('engagement_weight', 35);
        $ratioWeight = (int)$this->post('ratio_weight', 15);
        $ageWeight = (int)$this->post('age_weight', 10);

        // Validate weights sum to 100
        $total = $inactivityWeight + $engagementWeight + $ratioWeight + $ageWeight;
        if ($total !== 100) {
            return $this->jsonError('Weights must sum to 100 (current: ' . $total . ')', 422);
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_scoring_preferences (user_id, inactivity_weight, engagement_weight, ratio_weight, age_weight)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                inactivity_weight = ?, engagement_weight = ?, ratio_weight = ?, age_weight = ?
            ");
            $stmt->execute([
                $userId, $inactivityWeight, $engagementWeight, $ratioWeight, $ageWeight,
                $inactivityWeight, $engagementWeight, $ratioWeight, $ageWeight
            ]);

            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'scoring_preferences_updated',
                'Updated scoring algorithm weights',
                ['weights' => compact('inactivityWeight', 'engagementWeight', 'ratioWeight', 'ageWeight')]
            );

            return $this->jsonSuccess('Scoring preferences updated');

        } catch (\Exception $e) {
            error_log('Scoring preferences update error: ' . $e->getMessage());
            return $this->jsonError('Failed to update preferences', 500);
        }
    }

    /**
     * Disconnect Instagram account
     */
    public function disconnectInstagram()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];

        try {
            // Delete Instagram connection and associated data
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM instagram_connections WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Delete all following/followers data
            $stmt = $this->db->prepare("DELETE FROM following WHERE user_id = ?");
            $stmt->execute([$userId]);

            $stmt = $this->db->prepare("DELETE FROM followers WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Update user to remove Instagram account ID
            $stmt = $this->db->prepare("
                UPDATE users 
                SET instagram_account_id = NULL, instagram_access_token = NULL
                WHERE id = ?
            ");
            $stmt->execute([$userId]);

            $this->db->commit();

            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'instagram_disconnected',
                'Disconnected Instagram account',
                []
            );

            return $this->jsonSuccess('Instagram account disconnected');

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Disconnect Instagram error: ' . $e->getMessage());
            return $this->jsonError('Failed to disconnect Instagram', 500);
        }
    }

    /**
     * Export user data
     */
    public function exportData()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $format = $this->post('format', 'json');

        try {
            // Collect all user data
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();

            $stmt = $this->db->prepare("SELECT * FROM following WHERE user_id = ?");
            $stmt->execute([$userId]);
            $following = $stmt->fetchAll();

            $stmt = $this->db->prepare("SELECT * FROM activity_log WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $activities = $stmt->fetchAll();

            $exportData = [
                'user' => $userData,
                'following' => $following,
                'activities' => $activities,
                'exported_at' => date('Y-m-d H:i:s'),
            ];

            if ($format === 'json') {
                $filename = 'instabalancepro-export-' . date('Y-m-d') . '.json';
                $content = json_encode($exportData, JSON_PRETTY_PRINT);
                $contentType = 'application/json';
            } else {
                // CSV format
                $filename = 'instabalancepro-export-' . date('Y-m-d') . '.csv';
                $content = $this->arrayToCsv($following);
                $contentType = 'text/csv';
            }

            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'data_exported',
                'Exported account data',
                ['format' => $format]
            );

            // Send file as download
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
            exit;

        } catch (\Exception $e) {
            error_log('Export data error: ' . $e->getMessage());
            return $this->jsonError('Failed to export data', 500);
        }
    }

    /**
     * Delete account
     */
    public function deleteAccount()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $password = $this->post('password');

        try {
            // Verify password
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!password_verify($password, $user['password_hash'])) {
                return $this->jsonError('Password is incorrect', 401);
            }

            // Soft delete user
            $stmt = $this->db->prepare("
                UPDATE users 
                SET deleted_at = NOW(), is_active = FALSE
                WHERE id = ?
            ");
            $stmt->execute([$userId]);

            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'account_deleted',
                'Deleted account',
                [],
                $this->getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            // Logout
            session_destroy();

            return $this->jsonSuccess('Account deleted', ['redirect' => '/']);

        } catch (\Exception $e) {
            error_log('Delete account error: ' . $e->getMessage());
            return $this->jsonError('Failed to delete account', 500);
        }
    }

    /**
     * Get active sessions
     */
    private function getActiveSessions($userId)
    {
        // This would require tracking session IDs in a sessions table
        // For now, mock data
        return [
            [
                'device' => 'Chrome on Windows',
                'ip' => '192.168.1.1',
                'last_activity' => 'Just now',
                'is_current' => true,
            ],
        ];
    }

    /**
     * Convert array to CSV
     */
    private function arrayToCsv($data)
    {
        if (empty($data)) {
            return '';
        }

        $csv = '';
        $headers = array_keys($data[0]);
        $csv .= implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $csv .= implode(',', array_map(function($v) {
                return '"' . str_replace('"', '""', $v) . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }
}
    {
        if ($this->isPost()) {
            // TODO: Verify current password, hash new password, update
            $this->json(['success' => 'Password updated']);
        }
    }

    /**
     * Update scoring preferences
     */
    public function updateScoringPreferences()
    {
        if ($this->isPost()) {
            // TODO: Update weights, thresholds in user_scoring_preferences
            $this->json(['success' => 'Scoring preferences updated']);
        }
    }

    /**
     * Disconnect Instagram account
     */
    public function disconnectInstagram()
    {
        if ($this->isPost()) {
            // TODO: Revoke token, delete connection, clear cached data
            $this->json(['success' => 'Instagram account disconnected']);
        }
    }

    /**
     * Export user data
     */
    public function exportData()
    {
        if ($this->isPost()) {
            // TODO: Generate JSON/CSV export of all user data
            $this->json(['download_url' => '/tmp/export_' . time() . '.json']);
        }
    }

    /**
     * Delete account
     */
    public function deleteAccount()
    {
        if ($this->isPost()) {
            // TODO: Soft-delete user, clear sensitive data, log action
            session_destroy();
            $this->json(['success' => 'Account deleted']);
        }
    }
}
