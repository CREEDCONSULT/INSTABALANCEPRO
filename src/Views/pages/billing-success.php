<?php
/**
 * Billing Success Page - Payment confirmation
 * @var array $user
 * @var string $plan_name
 * @var float $amount
 * @var string $next_billing_date
 */
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-body text-center py-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <i class="fas fa-check-circle" style="font-size: 80px; color: #28a745;"></i>
                    </div>

                    <h1 class="card-title mb-2">Payment Successful!</h1>
                    <p class="text-muted mb-4">Your upgrade has been processed and your account has been updated.</p>

                    <!-- Plan Details -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Plan Name</strong>
                                    <p class="h5"><?php echo ucfirst($plan_name ?? 'Pro'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Amount Paid</strong>
                                    <p class="h5">$<?php echo number_format($amount / 100, 2); ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Next Billing Date</strong>
                                    <p><?php echo date('F d, Y', strtotime($next_billing_date)); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Billing Frequency</strong>
                                    <p>Monthly</p>
                                </div>
                            </div>

                            <div class="alert alert-info alert-sm mb-0">
                                <i class="fas fa-info-circle"></i>
                                A confirmation email has been sent to your email address.
                            </div>
                        </div>
                    </div>

                    <!-- Features Available -->
                    <div class="text-start mb-4">
                        <h5 class="mb-3">You now have access to:</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Advanced ranked list with filtering and sorting
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Kanban board for workflow management
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Activity calendar for tracking your progress
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                CSV export for data analysis
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Priority email support
                            </li>
                        </ul>
                    </div>

                    <!-- Next Steps -->
                    <div class="alert alert-light border mb-4">
                        <h6 class="mb-2">What's next?</h6>
                        <ol class="text-start">
                            <li>Customize your scoring preferences in settings</li>
                            <li>Start building your first unfollow queue</li>
                            <li>Use the kanban board to organize your workflow</li>
                            <li>Monitor your activity with the calendar heatmap</li>
                        </ol>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/dashboard" class="btn btn-primary btn-lg">
                            <i class="fas fa-home"></i> Go to Dashboard
                        </a>
                        <a href="/billing" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-receipt"></i> View Billing
                        </a>
                    </div>
                </div>
            </div>

            <!-- Support Widget -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">Need Help?</h6>
                    <p class="card-text text-muted">
                        If you have any questions about your new plan or how to maximize its features, 
                        please don't hesitate to reach out to our support team.
                    </p>
                    <a href="mailto:support@instabalancepro.com" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-envelope"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
