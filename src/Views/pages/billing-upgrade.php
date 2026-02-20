<?php
/**
 * Billing Upgrade Page - Pricing plans and features
 * @var array $user
 */
?>

<div class="container-fluid mt-4">
    <div class="text-center mb-5">
        <h1>Upgrade Your Plan</h1>
        <p class="text-muted">Choose the perfect plan for your Instagram management needs</p>
    </div>

    <div class="row mb-5">
        <!-- Free Plan -->
        <div class="col-md-4 mb-3">
            <div class="card <?php echo $user['subscription_tier'] === 'free' ? 'border-primary border-2' : ''; ?>">
                <div class="card-body">
                    <h5 class="card-title">Free</h5>
                    <div class="mb-3">
                        <span class="display-4">$0</span>
                        <span class="text-muted">/month</span>
                    </div>

                    <?php if ($user['subscription_tier'] === 'free'): ?>
                    <div class="alert alert-info alert-sm">
                        <i class="fas fa-check-circle"></i> Your Current Plan
                    </div>
                    <?php endif; ?>

                    <ul class="list-unstyled mb-4">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>500</strong> unfollows/month
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>5,000</strong> accounts tracked
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>10</strong> syncs/month
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Basic ranked list
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Engagement scoring
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times text-danger"></i>
                            <span class="text-muted">Kanban board</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times text-danger"></i>
                            <span class="text-muted">Activity calendar</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times text-danger"></i>
                            <span class="text-muted">CSV export</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times text-danger"></i>
                            <span class="text-muted">API access</span>
                        </li>
                    </ul>

                    <?php if ($user['subscription_tier'] === 'free'): ?>
                    <button class="btn btn-secondary btn-block w-100" disabled>
                        Current Plan
                    </button>
                    <?php else: ?>
                    <button class="btn btn-outline-secondary btn-block w-100" onclick="downgradePlan('free')">
                        Downgrade
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pro Plan -->
        <div class="col-md-4 mb-3">
            <div class="card <?php echo $user['subscription_tier'] === 'pro' ? 'border-primary border-2' : ''; ?>" style="transform: scale(1.05); z-index: 10;">
                <div class="position-absolute" style="top: -10px; right: 20px;">
                    <span class="badge bg-success">POPULAR</span>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Pro</h5>
                    <div class="mb-3">
                        <span class="display-4">$9.99</span>
                        <span class="text-muted">/month</span>
                    </div>

                    <?php if ($user['subscription_tier'] === 'pro'): ?>
                    <div class="alert alert-info alert-sm">
                        <i class="fas fa-check-circle"></i> Your Current Plan
                    </div>
                    <?php endif; ?>

                    <ul class="list-unstyled mb-4">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>2,000</strong> unfollows/month
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>50,000</strong> accounts tracked
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>100</strong> syncs/month
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Advanced ranked list
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Engagement scoring
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Kanban board
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Activity calendar
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            CSV export
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times text-danger"></i>
                            <span class="text-muted">API access</span>
                        </li>
                    </ul>

                    <?php if ($user['subscription_tier'] === 'pro'): ?>
                    <button class="btn btn-secondary btn-block w-100" disabled>
                        Current Plan
                    </button>
                    <?php elseif ($user['subscription_tier'] === 'free'): ?>
                    <button class="btn btn-primary btn-block w-100" onclick="upgradePlan('pro')">
                        Upgrade Now
                    </button>
                    <?php else: ?>
                    <button class="btn btn-warning btn-block w-100" onclick="downgradePlan('pro')">
                        Downgrade to Pro
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Premium Plan -->
        <div class="col-md-4 mb-3">
            <div class="card <?php echo $user['subscription_tier'] === 'premium' ? 'border-primary border-2' : ''; ?>">
                <div class="card-body">
                    <h5 class="card-title">Premium</h5>
                    <div class="mb-3">
                        <span class="display-4">$29.99</span>
                        <span class="text-muted">/month</span>
                    </div>

                    <?php if ($user['subscription_tier'] === 'premium'): ?>
                    <div class="alert alert-info alert-sm">
                        <i class="fas fa-check-circle"></i> Your Current Plan
                    </div>
                    <?php endif; ?>

                    <ul class="list-unstyled mb-4">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>Unlimited</strong> unfollows/month
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>Unlimited</strong> accounts tracked
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>500</strong> syncs/month
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Advanced ranked list
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Smart engagement scoring
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Kanban board
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Activity calendar
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            CSV/JSON export
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Full API access
                        </li>
                    </ul>

                    <?php if ($user['subscription_tier'] === 'premium'): ?>
                    <button class="btn btn-secondary btn-block w-100" disabled>
                        Current Plan
                    </button>
                    <?php else: ?>
                    <button class="btn btn-danger btn-block w-100" onclick="upgradePlan('premium')">
                        Upgrade to Premium
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Comparison Table -->
    <div class="row mt-5">
        <div class="col-md-12">
            <h3 class="mb-3">Feature Comparison</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Feature</th>
                            <th class="text-center">Free</th>
                            <th class="text-center">Pro</th>
                            <th class="text-center">Premium</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Monthly Unfollows</strong></td>
                            <td class="text-center">500</td>
                            <td class="text-center">2,000</td>
                            <td class="text-center">∞ Unlimited</td>
                        </tr>
                        <tr>
                            <td><strong>Accounts Tracked</strong></td>
                            <td class="text-center">5,000</td>
                            <td class="text-center">50,000</td>
                            <td class="text-center">∞ Unlimited</td>
                        </tr>
                        <tr>
                            <td><strong>Monthly Syncs</strong></td>
                            <td class="text-center">10</td>
                            <td class="text-center">100</td>
                            <td class="text-center">500</td>
                        </tr>
                        <tr>
                            <td><strong>Ranked List</strong></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Kanban Board</strong></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Activity Calendar</strong></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>CSV Export</strong></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>JSON Export</strong></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>API Access</strong></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Email Support</strong></td>
                            <td class="text-center">Community</td>
                            <td class="text-center">Standard</td>
                            <td class="text-center">Priority</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="row mt-5">
        <div class="col-md-8 offset-md-2">
            <h3 class="mb-3">Frequently Asked Questions</h3>

            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Can I change my plan anytime?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            What happens to my unused unfollows?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Unused unfollows do not carry over to the next month. Your quota resets on the first day of each month.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Is there a free trial?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            The Free plan is our forever free tier! There's no trial needed - just start using it immediately.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Do you offer refunds?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, we offer a 30-day money-back guarantee if you're not satisfied with your purchase.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            Can I contact support for help?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Absolutely! Pro users get email support, and Premium users get priority support with guaranteed response within 24 hours.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-5 text-center">
        <a href="/billing" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Billing
        </a>
    </div>
</div>

<script>
function upgradePlan(tier) {
    if (confirm('Proceed to upgrade to ' + tier.toUpperCase() + ' plan?')) {
        window.location.href = '/billing/checkout?plan=' + tier;
    }
}

function downgradePlan(tier) {
    if (confirm('Are you sure you want to downgrade to ' + tier.toUpperCase() + ' plan?')) {
        fetch('/billing/cancel-subscription', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ new_tier: tier }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Plan changed successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>
