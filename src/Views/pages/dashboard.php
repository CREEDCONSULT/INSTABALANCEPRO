<?php
/**
 * Dashboard Page - src/Views/pages/dashboard.php
 * 
 * Main dashboard with KPIs and activity feed for authenticated users
 */
$pageTitle = 'Dashboard';
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
                            <h3 class="mb-0">2,847</h3>
                        </div>
                        <i class="bi bi-people text-primary" style="font-size: 1.5rem; opacity: 0.3;"></i>
                    </div>
                    <small class="text-muted">+23 since last sync</small>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">New Unfollowers</p>
                            <h3 class="mb-0 text-danger">142</h3>
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
                            <h3 class="mb-0 text-warning">456</h3>
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
                            <h3 class="mb-0 text-success">78</h3>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 1.5rem; opacity: 0.3;"></i>
                    </div>
                    <small class="text-muted">Protected accounts</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sync Status</h5>
                    <button class="btn btn-sm btn-primary" id="syncBtn" hx-post="/api/sync" hx-swap="innerHTML">
                        <i class="bi bi-arrow-clockwise"></i> Sync Now
                    </button>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="mb-1 text-muted">Last Synced</p>
                            <p class="mb-0 fw-bold">2 hours ago</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success">Synced</span>
                        </div>
                    </div>
                    <div id="syncProgress" style="display: none;">
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="syncBar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">Syncing follower data...</small>
                    </div>
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
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-person-x text-danger" style="font-size: 1.5rem;"></i>
                            <div class="flex-grow-1">
                                <p class="mb-0">You unfollowed <strong>@_profile_name</strong></p>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-person-plus text-success" style="font-size: 1.5rem;"></i>
                            <div class="flex-grow-1">
                                <p class="mb-0"><strong>@new_follower</strong> started following you</p>
                                <small class="text-muted">4 hours ago</small>
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-sync text-primary" style="font-size: 1.5rem;"></i>
                            <div class="flex-grow-1">
                                <p class="mb-0">Instagram sync completed (2,847 following, 1,234 followers)</p>
                                <small class="text-muted">Yesterday</small>
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-check2-square text-info" style="font-size: 1.5rem;"></i>
                            <div class="flex-grow-1">
                                <p class="mb-0">Whitelisted <strong>@important_account</strong></p>
                                <small class="text-muted">2 days ago</small>
                            </div>
                        </div>
                    </div>
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
                        <span class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['tier'] ?? 'Free'); ?></span>
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
    document.getElementById('syncBtn').addEventListener('click', function() {
        const syncProgress = document.getElementById('syncProgress');
        const syncBar = document.getElementById('syncBar');
        
        syncProgress.style.display = 'block';
        
        // Simulate progress
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            
            syncBar.style.width = progress + '%';
        }, 300);
        
        // Complete after 3 seconds
        setTimeout(() => {
            clearInterval(interval);
            syncBar.style.width = '100%';
            setTimeout(() => {
                syncProgress.style.display = 'none';
                Notification.success('Sync completed successfully!');
            }, 500);
        }, 3000);
    });
</script>
