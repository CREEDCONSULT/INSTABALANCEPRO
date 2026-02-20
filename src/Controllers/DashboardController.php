<?php

namespace App\Controllers;

use App\Controller;

/**
 * DashboardController — Main user dashboard with KPI cards and activity feed
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        // TODO: Load user's KPI data, sync status, activity feed
        echo '<h1>Dashboard</h1>';
        echo '<p>Dashboard content coming in PROMPT 6.</p>';
    }

    /**
     * Start sync operation
     */
    public function startSync()
    {
        // TODO: Trigger background sync, return JSON status
        if ($this->isAjax()) {
            $this->json(['status' => 'sync_started']);
        }
    }

    /**
     * Get sync status via AJAX/htmx
     */
    public function syncStatus()
    {
        // TODO: Poll sync job status and return updated progress
        if ($this->isAjax()) {
            $this->partial('partials/sync-status', [
                'status' => 'in_progress',
                'percent' => 50,
            ]);
        }
    }
}
