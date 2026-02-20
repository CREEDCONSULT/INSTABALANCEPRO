<?php
/**
 * Home Page - src/Views/pages/home.php
 * 
 * Landing page for public users
 */
?>

<div class="container py-5">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h1 class="display-5 fw-bold mb-4">
                Manage Your Instagram Followers <span class="text-gradient">Intelligently</span>
            </h1>
            
            <p class="lead mb-4">
                UnfollowIQ helps you identify and remove inactive, ghost followers. 
                Use our advanced scoring algorithm to prioritize who to unfollow based on 
                engagement, inactivity, and account quality.
            </p>
            
            <ul class="list-unstyled mb-4">
                <li class="mb-3">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <strong>Smart Scoring:</strong> Multi-factor analysis to identify best unfollows
                </li>
                <li class="mb-3">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <strong>Verified Badge Protection:</strong> Never unfollow influencers by accident
                </li>
                <li class="mb-3">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <strong>Whitelist Management:</strong> Protect important accounts from removal
                </li>
                <li class="mb-3">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <strong>Activity Tracking:</strong> Monitor unfollower trends over time
                </li>
            </ul>
            
            <?php if (!$isAuthenticated): ?>
                <div class="d-flex gap-3">
                    <a href="/auth/register" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Get Started Free
                    </a>
                    <a href="#features" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-down"></i> Learn More
                    </a>
                </div>
            <?php else: ?>
                <a href="/dashboard" class="btn btn-primary btn-lg">
                    <i class="bi bi-grid-3x3-gap"></i> Go to Dashboard
                </a>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow-lg" style="border-radius: 1rem; overflow: hidden;">
                <div class="bg-gradient p-5 text-white d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 400px;">
                    <div class="text-center">
                        <i class="bi bi-graph-up" style="font-size: 4rem; opacity: 0.2;"></i>
                        <h3 class="mt-3 mb-0">Dashboard Screenshot</h3>
                        <p style="opacity: 0.8;">Coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Features Section -->
    <div id="features" class="row mt-5">
        <div class="col-12 mb-5">
            <h2 class="text-center mb-4">Why Choose UnfollowIQ?</h2>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-bar-chart text-primary"></i> Advanced Analytics
                    </h5>
                    <p class="card-text">
                        Get detailed insights into your follower composition with engagement metrics 
                        and trends.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-shield-lock text-success"></i> Safe & Secure
                    </h5>
                    <p class="card-text">
                        Your data is encrypted and never shared. We follow Instagram's best practices 
                        and respect user privacy.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-lightning text-warning"></i> Lightning Fast
                    </h5>
                    <p class="card-text">
                        Sync your follower data in minutes. Our algorithm scores thousands of accounts 
                        instantly.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-kanban text-info"></i> Kanban Board
                    </h5>
                    <p class="card-text">
                        Organize your unfollowing strategy with our intuitive kanban board and 
                        calendar views.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-gear text-secondary"></i> Customizable
                    </h5>
                    <p class="card-text">
                        Adjust scoring weights to match your strategy. Whitelist important accounts 
                        and set your preferences.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-credit-card text-danger"></i> Affordable Plans
                    </h5>
                    <p class="card-text">
                        Start free, upgrade to Pro or Premium for more frequent syncs and advanced 
                        features.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-top: 5rem;">
    <div class="container py-5 text-center">
        <h2 class="mb-4">Ready to Clean Up Your Followers?</h2>
        <p class="lead mb-4">Join thousands of Instagram users who trust UnfollowIQ</p>
        
        <?php if (!$isAuthenticated): ?>
            <a href="/auth/register" class="btn btn-light btn-lg">
                <i class="bi bi-person-plus"></i> Start for Free
            </a>
        <?php endif; ?>
    </div>
</div>
