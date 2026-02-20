<?php

namespace App\Controllers;

use App\Controller;

/**
 * BillingController — Stripe subscription management
 */
class BillingController extends Controller
{
    public function index()
    {
        echo '<h1>Billing</h1>';
    }

    public function showUpgrade()
    {
        echo '<h1>Upgrade Subscription</h1>';
    }

    public function checkout()
    {
        if ($this->isPost()) {
            $this->json(['checkout_url' => 'https://checkout.stripe.com/...']);
        }
    }

    public function success()
    {
        echo '<h1>Subscription Activated!</h1>';
    }

    public function canceled()
    {
        echo '<h1>Checkout Canceled</h1>';
    }

    public function portal()
    {
        // Redirect to Stripe billing portal
        $this->redirect('https://billing.stripe.com/...');
    }

    public function cancelSubscription()
    {
        if ($this->isPost()) {
            $this->json(['success' => 'Subscription canceled']);
        }
    }
}
