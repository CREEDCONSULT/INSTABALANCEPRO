<?php

namespace App\Controllers;

use App\Controller;

/**
 * WebhookController — Handles external webhooks from Stripe and Instagram
 */
class WebhookController extends Controller
{
    /**
     * Handle Stripe webhook events
     */
    public function stripe()
    {
        // TODO: Verify Stripe signature, handle payment events
        $this->json(['received' => true]);
    }

    /**
     * Validate Instagram webhook (GET handshake)
     */
    public function instagramValidation()
    {
        // TODO: Verify challenge token from Instagram
        $challenge = $this->get('hub_challenge');
        echo $challenge;
    }
}
