<?php

namespace App\Controllers;

use App\Controller;

/**
 * UnfollowController — Ranked list of unfollower accounts with bulk operations
 */
class UnfollowController extends Controller
{
    /**
     * Show unfollowers list page
     */
    public function index()
    {
        // TODO: Load following list, compute scores, show ranked table
        echo '<h1>Unfollowers List</h1>';
        echo '<p>Ranked unfollowers list with scoring coming in PROMPT 8.</p>';
    }

    /**
     * Get list of following accounts (htmx endpoint)
     */
    public function listFollowing()
    {
        // TODO: Return paginated, filtered, sorted list as HTML partial
        if ($this->isAjax()) {
            $this->partial('partials/ranked-table', [
                'accounts' => [],
                'total' => 0,
            ]);
        }
    }

    /**
     * Unfollow a single account
     */
    public function unfollowSingle($id)
    {
        // TODO: Queue unfollow operation, return JSON success/error
        if ($this->isPost()) {
            $this->json(['success' => 'Account queued for unfollow']);
        }
    }

    /**
     * Preview bulk unfollow operation
     */
    public function bulkUnfollowPreview()
    {
        // TODO: Parse selection, show modal with breakdown
        if ($this->isPost()) {
            $this->json([
                'selected_count' => 10,
                'accounts' => [],
                'estimated_time' => '5 minutes',
            ]);
        }
    }

    /**
     * Execute bulk unfollow (after approval)
     */
    public function bulkUnfollowExecute()
    {
        // TODO: Queue all unfollows with approval confirmation
        if ($this->isPost()) {
            $this->json(['success' => '10 accounts queued for unfollow']);
        }
    }
}
