<?php
/**
 * Billing Canceled Page - Payment cancellation message
 * @var array $user
 */
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-warning">
                <div class="card-body text-center py-5">
                    <!-- Warning Icon -->
                    <div class="mb-4">
                        <i class="fas fa-times-circle" style="font-size: 80px; color: #ffc107;"></i>
                    </div>

                    <h1 class="card-title mb-2">Payment Cancelled</h1>
                    <p class="text-muted mb-4">
                        Your payment was not processed. Your subscription remains unchanged, 
                        and you continue with your current plan.
                    </p>

                    <!-- Current Plan Status -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="mb-3">Your Current Plan</h6>
                            <div class="badge bg-<?php echo match($user['subscription_tier']) {
                                'pro' => 'primary',
                                'premium' => 'danger',
                                default => 'secondary'
                            } ?> p-2 mb-3">
                                <?php echo ucfirst($user['subscription_tier']); ?>
                            </div>
                            <p class="text-muted">
                                No changes have been made to your account. Your current plan continues as normal.
                            </p>
                        </div>
                    </div>

                    <!-- Common Reasons & Solutions -->
                    <div class="text-start mb-4">
                        <h6 class="mb-3">Why was my payment cancelled?</h6>
                        <div class="accordion accordion-sm" id="reasonAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reason1">
                                        Insufficient Funds
                                    </button>
                                </h2>
                                <div id="reason1" class="accordion-collapse collapse" data-bs-parent="#reasonAccordion">
                                    <div class="accordion-body">
                                        Please ensure your payment method has sufficient funds and try again.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reason2">
                                        Card Declined
                                    </button>
                                </h2>
                                <div id="reason2" class="accordion-collapse collapse" data-bs-parent="#reasonAccordion">
                                    <div class="accordion-body">
                                        Try using a different payment method or contact your bank to verify your card information.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reason3">
                                        Expired Card
                                    </button>
                                </h2>
                                <div id="reason3" class="accordion-collapse collapse" data-bs-parent="#reasonAccordion">
                                    <div class="accordion-body">
                                        Update your payment method with valid card information and try again.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reason4">
                                        User Cancelled
                                    </button>
                                </h2>
                                <div id="reason4" class="accordion-collapse collapse" data-bs-parent="#reasonAccordion">
                                    <div class="accordion-body">
                                        If you cancelled the payment intentionally, that's fine! You can upgrade whenever you're ready.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="alert alert-light border mb-4">
                        <h6 class="mb-2">What happens now?</h6>
                        <ul class="text-start small mb-0">
                            <li>Your subscription remains active with your current plan</li>
                            <li>You continue to have access to all current features</li>
                            <li>You can retry your upgrade at any time</li>
                            <li>No charges have been made to your account</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/billing" class="btn btn-primary btn-lg">
                            <i class="fas fa-retry"></i> Try Again
                        </a>
                        <a href="/dashboard" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-home"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">Still having trouble?</h6>
                    <p class="card-text text-muted small">
                        If you continue to experience payment issues, our support team is here to help. 
                        We can assist with troubleshooting, alternative payment methods, or billing inquiries.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="mailto:support@instabalancepro.com" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-envelope"></i> Email Support
                        </a>
                        <a href="/settings" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-cog"></i> Update Payment Method
                        </a>
                    </div>
                </div>
            </div>

            <!-- FAQ -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">FAQ</h6>
                    <div class="accordion accordion-sm" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Will my account be downgraded?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    No, your account remains on your current plan. Only when you manually request a downgrade or let your subscription expire will your plan change.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Can I try a different payment method?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes! When you retry the upgrade, you'll be able to choose a different payment method.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How long to retry the payment?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can retry at any time! There's no expiration or deadline for upgrading.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
