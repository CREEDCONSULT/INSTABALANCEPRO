<?php
/**
 * Dashboard Page - src/Views/pages/dashboard.php
 * 
 * Main dashboard with KPIs and activity feed for authenticated users
 * Data provided by DashboardController
 */
$pageTitle = 'Dashboard';

// Use default values if not provided by controller
$kpis = $kpis ?? [
    'following' => 0,
    'followers' => 0,
    'newUnfollowers' => 0,
    'notFollowingBack' => 0,
    'whitelisted' => 0,
];

$lastSyncTimeAgo = $lastSyncTimeAgo ?? 'Never';
$syncInProgress = $syncInProgress ?? false;
$syncProgress = $syncProgress ?? null;
$activities = $activities ?? [];
$subscriptionTier = $subscriptionTier ?? 'free';
?>

<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="mb-5">
        <h1>Welcome Back! 👋</h1>
        <p class="text-muted">
            Here's your Instagram follower analytics and management dashboard
        </p>
    </div>
    
    <!-- KPI Cards Row 1 -->
    <div class="row mb-4">
        <div class="col-12 col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Following</p>
                            <h3 class="mb-0"><?php echo number_format($kpis['following']); ?></h3>
                        </div>
                        <i class="bi bi-people text-primary" style="font-size: 1.5rem; opacity: 0.3;"></i>
                    </div>
                    <small class="text-muted">Instagram accounts</small>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">New Unfollowers</p>
                            <h3 class="mb-0 text-danger"><?php echo number_format($kpis['newUnfollowers']); ?></h3>
                        </div>
                        <i class="bi bi-graph-down text-danger" style="font-size: 1.5rem; opacity: 0.3;"></i>
                    </div>
                    <small class="text-muted">Last 30 days</small>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Not Following Back</p>
                            <h3 class="mb-0 text-warning"><?php echo number_format($kpis['notFollowingBack']); ?></h3>
                        </div>
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 1.5rem; opacity: 0.3;"></i>
                    </div>
                    <small class="text-muted">Prime unfollow candidates</small>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Whitelisted</p>
                            <h3 class="mb-0 text-success"><?php echo number_format($kpis['whitelisted']); ?></h3>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 1.5rem; opacity: 0.3;"></i>
                    </div>
                    <small class="text-muted">Protected accounts</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sync Status Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sync Status</h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-primary" id="syncBtn" hx-post="/dashboard/sync" hx-swap="none">
                            <i class="bi bi-arrow-clockwise"></i> Sync Now
                        </button>
                        <?php if ($syncInProgress): ?>
                            <button type="button" class="btn btn-sm btn-danger" id="cancelSyncBtn" hx-post="/dashboard/cancel-sync" hx-swap="none">
                                <i class="bi bi-x-circle"></i> Cancel
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="mb-1 text-muted">Last Synced</p>
                            <p class="mb-0 fw-bold"><?php echo htmlspecialchars($lastSyncTimeAgo); ?></p>
                        </div>
                        <div class="text-end">
                            <?php if ($syncInProgress): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-arrow-clockwise"></i> Syncing...
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">Ready</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($syncInProgress && $syncProgress): ?>
                        <div id="syncProgress">
                            <div class="mb-2 d-flex justify-content-between">
                                <small class="text-muted">Progress</small>
                                <small class="text-muted"><?php echo $syncProgress['percent']; ?>%</small>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?php echo $syncProgress['percent']; ?>%"></div>
                            </div>
                            <small class="text-muted">
                                Processing <?php echo number_format($syncProgress['processed']); ?> of <?php echo number_format($syncProgress['total']); ?> accounts...
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Activity Feed -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($activities)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($activities as $activity): ?>
                                <div class="list-group-item d-flex gap-3 py-3">
                                    <i class="bi <?php echo $this->getActivityIcon($activity['action']) ?? 'bi-info-circle'; ?>" style="font-size: 1.5rem;"></i>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-500"><?php echo htmlspecialchars($activity['description']); ?></p>
                                        <small class="text-muted"><?php echo htmlspecialchars($activity['timeAgo']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3">No activity yet. Start by syncing your follower data.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/ranked-list" class="btn btn-outline-primary">
                            <i class="bi bi-list-ol"></i> View Ranked List
                        </a>
                        <a href="/unfollowers" class="btn btn-outline-primary">
                            <i class="bi bi-graph-down"></i> New Unfollowers
                        </a>
                        <a href="/whitelist" class="btn btn-outline-primary">
                            <i class="bi bi-check-circle"></i> Manage Whitelist
                        </a>
                        <a href="/kanban" class="btn btn-outline-primary">
                            <i class="bi bi-kanban"></i> Kanban Board
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Subscription Card -->
            <div class="card bg-light border-0">
                <div class="card-body">
                    <p class="mb-2 small text-muted">Plan</p>
                    <h6 class="mb-3">
                        <span class="badge bg-<?php 
                            echo match($subscriptionTier) {
                                'premium' => 'danger',
                                'pro' => 'primary',
                                default => 'secondary'
                            };
                        ?>">
                            <?php echo ucfirst($subscriptionTier); ?>
                        </span>
                    </h6>
                    <p class="mb-3 small">
                        Get more sync frequency and advanced features by upgrading your plan.
                    </p>
                    <a href="/billing" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-credit-card"></i> View Plans
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Sync button handler
    document.getElementById('syncBtn')?.addEventListener('htmx:afterSwap', function() {
        Notification.success('Sync started successfully');
        
        // Poll for updates every 2 seconds
        let pollCount = 0;
        let pollInterval = setInterval(() => {
            fetch('/dashboard/sync-status')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'completed') {
                        clearInterval(pollInterval);
                        location.reload();
                    }
                })
                .catch(() => {
                    pollCount++;
                    if (pollCount > 30) clearInterval(pollInterval); // Stop after 60 seconds
                });
        }, 2000);
    });
    
    document.getElementById('syncBtn')?.addEventListener('htmx:responseError', function() {
        Notification.error('Failed to start sync');
    });
    
    document.getElementById('cancelSyncBtn')?.addEventListener('htmx:afterSwap', function() {
        Notification.success('Sync cancelled');
        setTimeout(() => location.reload(), 1000);
    });
</script>
