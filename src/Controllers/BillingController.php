<?php

namespace App\Controllers;

use App\Controller;

/**
 * BillingController — Stripe payment processing and subscription management
 */
class BillingController extends Controller
{
    /**
     * Show billing dashboard
     */
    public function index()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $userId = $_SESSION['user_id'];

        // Get user's subscription info
        $stmt = $this->db->prepare("
            SELECT 
                subscription_tier, 
                stripe_customer_id,
                stripe_subscription_id,
                subscription_status,
                current_period_start,
                current_period_end,
                cancel_at,
                created_at
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Get usage stats
        $usageStats = $this->getUsageStats($userId);

        // Get billing history
        $stmt = $this->db->prepare("
            SELECT id, amount, currency, status, description, created_at
            FROM billing_transactions
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 12
        ");
        $stmt->execute([$userId]);
        $transactions = $stmt->fetchAll();

        return $this->view('pages/billing', [
            'pageTitle' => 'Billing',
            'user' => $user,
            'usageStats' => $usageStats,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show upgrade page
     */
    public function showUpgrade()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $userId = $_SESSION['user_id'];

        // Get current tier
        $stmt = $this->db->prepare("SELECT subscription_tier FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Define plans
        $plans = [
            'free' => [
                'name' => 'Free',
                'price' => 0,
                'limits' => [
                    'Monthly unfollows: 500',
                    'Accounts tracked: 5,000',
                    'Syncs per month: 10',
                ],
                'features' => [
                    'Basic ranked list',
                    'Engagement scoring',
                    'Limited activity history',
                ],
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 9.99,
                'limits' => [
                    'Monthly unfollows: 2,000',
                    'Accounts tracked: 50,000',
                    'Syncs per month: 100',
                ],
                'features' => [
                    'All Free features',
                    'Kanban workflow board',
                    'Activity calendar & heatmap',
                    'Advanced filtering',
                    'CSV export',
                    'Email support',
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 29.99,
                'limits' => [
                    'Monthly unfollows: unlimited',
                    'Accounts tracked: unlimited',
                    'Syncs per month: 500',
                ],
                'features' => [
                    'All Pro features',
                    'Advanced analytics',
                    'Custom scoring rules',
                    'Bulk scheduler',
                    'API access',
                    'Priority support',
                    'Custom branding',
                ],
            ],
        ];

        return $this->view('pages/billing-upgrade', [
            'pageTitle' => 'Upgrade Plan',
            'currentTier' => $user['subscription_tier'],
            'plans' => $plans,
        ]);
    }

    /**
     * Create Stripe checkout session
     */
    public function checkout()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $plan = $this->post('plan');

        // Validate plan
        $validPlans = ['free', 'pro', 'premium'];
        if (!in_array($plan, $validPlans)) {
            return $this->jsonError('Invalid plan', 422);
        }

        try {
            // Get user email
            $stmt = $this->db->prepare("SELECT email, stripe_customer_id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            // Get Stripe API key from config
            $config = require __DIR__ . '/../../config/app.php';
            $stripeKey = $config['stripe']['secret_key'];

            // For demo purposes, simulate checkout
            // In production, use Stripe\Checkout\Session::create()

            return $this->jsonSuccess('Checkout session created', [
                'sessionId' => 'sim_' . uniqid(),
                'url' => '/billing/success?plan=' . $plan,
            ]);

        } catch (\Exception $e) {
            error_log('Checkout error: ' . $e->getMessage());
            return $this->jsonError('Failed to create checkout', 500);
        }
    }

    /**
     * Handle successful Stripe payment
     */
    public function success()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $plan = $this->get('plan', 'pro');

        return $this->view('pages/billing-success', [
            'pageTitle' => 'Upgrade Successful',
            'plan' => $plan,
        ]);
    }

    /**
     * Handle canceled Stripe payment
     */
    public function canceled()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        return $this->view('pages/billing-canceled', [
            'pageTitle' => 'Payment Canceled',
        ]);
    }

    /**
     * Open Stripe billing portal
     */
    public function portal()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        return $this->view('pages/billing-portal', [
            'pageTitle' => 'Billing Portal',
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $userId = $_SESSION['user_id'];
        $reason = $this->post('reason', '');

        try {
            // Update subscription
            $stmt = $this->db->prepare("
                UPDATE users 
                SET subscription_tier = 'free', subscription_status = 'canceled', cancel_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$userId]);

            // Log activity
            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'subscription_canceled',
                'Downgraded subscription to free plan',
                ['reason' => $reason],
                $this->getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            return $this->jsonSuccess('Subscription canceled', [
                'redirect' => '/billing',
            ]);

        } catch (\Exception $e) {
            error_log('Cancel subscription error: ' . $e->getMessage());
            return $this->jsonError('Failed to cancel subscription', 500);
        }
    }

    /**
     * Get usage statistics
     */
    private function getUsageStats($userId)
    {
        // Get unfollows this month
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM unfollow_queue
            WHERE user_id = ? 
            AND status = 'completed'
            AND DATE(completed_at) >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ");
        $stmt->execute([$userId]);
        $unfollowsThisMonth = $stmt->fetch()['count'] ?? 0;

        // Get accounts tracked
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM following
            WHERE user_id = ? AND unfollowed_at IS NULL
        ");
        $stmt->execute([$userId]);
        $accountsTracked = $stmt->fetch()['count'] ?? 0;

        // Get syncs this month
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM sync_jobs
            WHERE user_id = ? 
            AND status = 'completed'
            AND DATE(completed_at) >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ");
        $stmt->execute([$userId]);
        $syncsThisMonth = $stmt->fetch()['count'] ?? 0;

        return [
            'unfollows_this_month' => $unfollowsThisMonth,
            'accounts_tracked' => $accountsTracked,
            'syncs_this_month' => $syncsThisMonth,
        ];
    }
}
}
