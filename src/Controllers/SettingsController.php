<?php

namespace App\Controllers;

use App\Controller;

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
        // TODO: Show settings tabs (profile, email, password, 2FA, scoring, billing, export)
        echo '<h1>Settings</h1>';
        echo '<p>Settings page coming in PROMPT 10.</p>';
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        if ($this->isPost()) {
            // TODO: Update display_name, profile_picture
            $this->json(['success' => 'Profile updated']);
        }
    }

    /**
     * Update email address
     */
    public function updateEmail()
    {
        if ($this->isPost()) {
            // TODO: Generate verification token, send email, mark as unverified
            $this->json(['success' => 'Verification email sent']);
        }
    }

    /**
     * Update password
     */
    public function updatePassword()
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
