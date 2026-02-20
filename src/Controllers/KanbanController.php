<?php

namespace App\Controllers;

use App\Controller;

/**
 * KanbanController — Kanban board for unfollow workflow management
 * 
 * States:
 * - To Review: Accounts pending user review
 * - Ready to Unfollow: User-approved, queued for execution
 * - Unfollowed: Successfully unfollowed
 * - Not Now: Accounts marked to review later
 */
class KanbanController extends Controller
{
    /**
     * Show Kanban board
     */
    public function index()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $userId = $_SESSION['user_id'];

        // Get accounts by status/column
        $toReview = $this->getAccountsByStatus($userId, 'to_review');
        $readyToUnfollow = $this->getAccountsByStatus($userId, 'ready_to_unfollow');
        $unfollowed = $this->getAccountsByStatus($userId, 'unfollowed');
        $notNow = $this->getAccountsByStatus($userId, 'not_now');

        return $this->view('pages/kanban', [
            'pageTitle' => 'Kanban Board',
            'toReview' => $toReview,
            'readyToUnfollow' => $readyToUnfollow,
            'unfollowed' => $unfollowed,
            'notNow' => $notNow,
        ]);
    }

    /**
     * Get cards for a specific column via AJAX
     */
    public function getCards()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        $userId = $_SESSION['user_id'];
        $status = $this->get('status', 'to_review');

        $cards = $this->getAccountsByStatus($userId, $status);

        return $this->json([
            'success' => true,
            'status' => $status,
            'cards' => $cards,
        ]);
    }

    /**
     * Move account to different column
     */
    public function moveCard($cardId)
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $newStatus = $this->post('status');

        // Validate status
        $validStatuses = ['to_review', 'ready_to_unfollow', 'unfollowed', 'not_now'];
        if (!in_array($newStatus, $validStatuses)) {
            return $this->jsonError('Invalid status', 422);
        }

        try {
            // Update following account status
            $stmt = $this->db->prepare("
                UPDATE following 
                SET kanban_status = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$newStatus, $cardId, $userId]);

            if ($stmt->rowCount() === 0) {
                return $this->jsonError('Account not found', 404);
            }

            // Log activity
            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'kanban_move',
                'Moved account to ' . str_replace('_', ' ', $newStatus),
                ['account_id' => $cardId, 'new_status' => $newStatus],
                $this->getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            return $this->jsonSuccess('Card moved', [
                'cardId' => $cardId,
                'status' => $newStatus,
            ]);

        } catch (\Exception $e) {
            error_log('Kanban move error: ' . $e->getMessage());
            return $this->jsonError('Failed to move card', 500);
        }
    }

    /**
     * Update account notes or metadata
     */
    public function updateCard($cardId)
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $notes = $this->post('notes', '');

        try {
            $stmt = $this->db->prepare("
                UPDATE following 
                SET kanban_notes = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$notes, $cardId, $userId]);

            if ($stmt->rowCount() === 0) {
                return $this->jsonError('Account not found', 404);
            }

            return $this->jsonSuccess('Card updated', [
                'cardId' => $cardId,
                'notes' => $notes,
            ]);

        } catch (\Exception $e) {
            error_log('Kanban update error: ' . $e->getMessage());
            return $this->jsonError('Failed to update card', 500);
        }
    }

    /**
     * Get accounts grouped by kanban status
     */
    private function getAccountsByStatus($userId, $status)
    {
        $stmt = $this->db->prepare("
            SELECT 
                f.id, f.instagram_user_id, f.username, f.name, f.followers_count, f.profile_picture_url,
                ai.engagement_score, ai.engagement_gap_days, ai.category, ai.explanation,
                f.kanban_status, f.kanban_notes
            FROM following f
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE f.user_id = ? AND f.kanban_status = ? AND f.unfollowed_at IS NULL
            ORDER BY ai.engagement_score DESC, f.created_at DESC
            LIMIT 500
        ");
        $stmt->execute([$userId, $status]);
        return $stmt->fetchAll();
    }

    /**
     * Bulk action on selected cards
     */
    public function bulkAction()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $cardIds = array_map('intval', (array)$this->post('card_ids', []));
        $action = $this->post('action');

        if (empty($cardIds)) {
            return $this->jsonError('No cards selected', 422);
        }

        try {
            switch ($action) {
                case 'move_to_ready':
                    $this->db->prepare("
                        UPDATE following 
                        SET kanban_status = 'ready_to_unfollow'
                        WHERE id IN (" . implode(',', array_fill(0, count($cardIds), '?')) . ") 
                        AND user_id = ?
                    ")->execute(array_merge($cardIds, [$userId]));

                    \App\Models\ActivityLog::log(
                        $this->db,
                        $userId,
                        'kanban_bulk_action',
                        'Moved ' . count($cardIds) . ' accounts to ready_to_unfollow',
                        ['count' => count($cardIds)],
                        $this->getClientIp(),
                        $_SERVER['HTTP_USER_AGENT'] ?? ''
                    );

                    return $this->jsonSuccess('Accounts moved to Ready', ['count' => count($cardIds)]);

                case 'move_to_not_now':
                    $this->db->prepare("
                        UPDATE following 
                        SET kanban_status = 'not_now'
                        WHERE id IN (" . implode(',', array_fill(0, count($cardIds), '?')) . ") 
                        AND user_id = ?
                    ")->execute(array_merge($cardIds, [$userId]));

                    return $this->jsonSuccess('Accounts moved to Not Now', ['count' => count($cardIds)]);

                default:
                    return $this->jsonError('Invalid action', 422);
            }

        } catch (\Exception $e) {
            error_log('Kanban bulk action error: ' . $e->getMessage());
            return $this->jsonError('Failed to perform action', 500);
        }
    }

    /**
     * Get kanban column statistics
     */
    public function getStats()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        $userId = $_SESSION['user_id'];

        $stmt = $this->db->prepare("
            SELECT 
                kanban_status,
                COUNT(*) as count,
                ROUND(AVG(ai.engagement_score), 2) as avg_score
            FROM following f
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE f.user_id = ? AND f.unfollowed_at IS NULL
            GROUP BY kanban_status
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetchAll();

        return $this->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
