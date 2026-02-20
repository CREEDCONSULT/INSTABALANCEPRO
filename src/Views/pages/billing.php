<?php
/**
 * Billing Page - Show subscription status and usage
 * @var array $user
 * @var array $usageStats
 * @var array $transactions
 */
?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">
        <i class="fas fa-credit-card"></i> Billing & Subscription
    </h1>

    <!-- Current Plan Status -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Current Plan</h5>
                </div>
                <div class="card-body">
                    <h3 class="mb-3">
                        <span class="badge bg-<?php echo match($user['subscription_tier']) {
                            'pro' => 'primary',
                            'premium' => 'danger',
                            default => 'secondary'
                        } ?>">
                            <?php echo ucfirst($user['subscription_tier']); ?>
                        </span>
                    </h3>
                    
                    <?php if ($user['subscription_status'] && $user['subscription_status'] !== 'canceled'): ?>
                    <div class="mb-3">
                        <small class="text-muted">Billing Period</small>
                        <div><?php echo date('M d, Y', strtotime($user['current_period_start'])); ?> - <?php echo date('M d, Y', strtotime($user['current_period_end'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <a href="/billing/upgrade" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-up"></i> Upgrade Plan
                    </a>

                    <?php if ($user['subscription_tier'] !== 'free'): ?>
                    <button class="btn btn-danger btn-sm" onclick="showCancelModal()">
                        <i class="fas fa-times"></i> Cancel Subscription
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Usage Stats -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Usage This Month</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col">
                            <div class="h4 text-primary"><?php echo $usageStats['unfollows_this_month']; ?></div>
                            <small class="text-muted">Unfollows</small>
                        </div>
                        <div class="col">
                            <div class="h4 text-success"><?php echo $usageStats['accounts_tracked']; ?></div>
                            <small class="text-muted">Accounts Tracked</small>
                        </div>
                        <div class="col">
                            <div class="h4 text-info"><?php echo $usageStats['syncs_this_month']; ?></div>
                            <small class="text-muted">Syncs</small>
                        </div>
                    </div>

                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" 
                             style="width: <?php echo min(100, ($usageStats['unfollows_this_month'] / 2000) * 100); ?>%"></div>
                    </div>
                    <small class="text-muted">Unfollows: <?php echo $usageStats['unfollows_this_month']; ?> / 2000</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing History -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Billing History</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox"></i> No transactions yet
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($tx['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($tx['description']); ?></td>
                            <td><strong><?php echo $tx['currency']; ?> <?php echo number_format($tx['amount'] / 100, 2); ?></strong></td>
                            <td>
                                <span class="badge bg-<?php echo $tx['status'] === 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($tx['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Back Link -->
    <a href="/dashboard" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<!-- Cancel Subscription Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> Canceling your subscription will downgrade you to the Free plan after this billing period ends.
                </div>

                <form id="cancelForm">
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label">Reason for Cancellation (optional)</label>
                        <select class="form-select" id="cancelReason" name="reason">
                            <option value="">Select a reason...</option>
                            <option value="too_expensive">Too expensive</option>
                            <option value="not_using">Not using the features</option>
                            <option value="found_alternative">Found a better alternative</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmCancel" required>
                        <label class="form-check-label" for="confirmCancel">
                            I understand my subscription will be canceled at the end of this period
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Subscription</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancelSubscription()">Cancel Subscription</button>
            </div>
        </div>
    </div>
</div>

<script>
function showCancelModal() {
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

async function confirmCancelSubscription() {
    if (!document.getElementById('confirmCancel').checked) {
        alert('Please confirm cancellation');
        return;
    }

    const reason = document.getElementById('cancelReason').value;

    try {
        const response = await fetch('/billing/cancel-subscription', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reason: reason,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            alert('Error: ' + (data.message || 'Failed to cancel subscription'));
            return;
        }

        alert('Subscription canceled. You will be downgraded to Free plan.');
        window.location.href = data.data.redirect;

    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>
