<?php
/**
 * Navigation Partial - src/Views/partials/navigation.php
 * 
 * Sidebar navigation with active state detection
 * Used by main.php layout
 */

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';

// Helper to mark active link
function isActive($path, $currentPath) {
    return strpos($currentPath, $path) === 0 ? 'active' : '';
}
?>

<nav class="sidebar-nav">
    <?php if ($isAuthenticated && !$isAdmin): ?>
        <!-- User Dashboard Section -->
        <li class="sidebar-nav-item">
            <a href="/dashboard" class="sidebar-nav-link <?php echo isActive('/dashboard', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-grid-3x3-gap"></i></span>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/unfollowers" class="sidebar-nav-link <?php echo isActive('/unfollowers', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-graph-down"></i></span>
                <span>Unfollowers</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/ranked-list" class="sidebar-nav-link <?php echo isActive('/ranked-list', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-list-ol"></i></span>
                <span>Ranked List</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/kanban" class="sidebar-nav-link <?php echo isActive('/kanban', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-kanban"></i></span>
                <span>Kanban Board</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/activity" class="sidebar-nav-link <?php echo isActive('/activity', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-calendar-week"></i></span>
                <span>Activity</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/whitelist" class="sidebar-nav-link <?php echo isActive('/whitelist', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-check-circle"></i></span>
                <span>Whitelist</span>
            </a>
        </li>
        
        <!-- Divider -->
        <div style="border-top: 1px solid rgba(255,255,255,0.2); margin: 1rem 0;"></div>
        
        <li class="sidebar-nav-item">
            <a href="/billing" class="sidebar-nav-link <?php echo isActive('/billing', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-credit-card"></i></span>
                <span>Billing</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/settings" class="sidebar-nav-link <?php echo isActive('/settings', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-gear"></i></span>
                <span>Settings</span>
            </a>
        </li>
    <?php endif; ?>
    
    <?php if ($isAuthenticated && $isAdmin): ?>
        <!-- Admin Dashboard Section -->
        <li class="sidebar-nav-item">
            <a href="/admin/dashboard" class="sidebar-nav-link <?php echo isActive('/admin/dashboard', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-shield-lock"></i></span>
                <span>Admin Dashboard</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/admin/users" class="sidebar-nav-link <?php echo isActive('/admin/users', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-people"></i></span>
                <span>Users</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/admin/monitoring" class="sidebar-nav-link <?php echo isActive('/admin/monitoring', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-activity"></i></span>
                <span>Monitoring</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/admin/reports" class="sidebar-nav-link <?php echo isActive('/admin/reports', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-bar-chart"></i></span>
                <span>Reports</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/admin/settings" class="sidebar-nav-link <?php echo isActive('/admin/settings', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-gear"></i></span>
                <span>Settings</span>
            </a>
        </li>
    <?php endif; ?>
    
    <?php if (!$isAuthenticated): ?>
        <!-- Public Navigation -->
        <li class="sidebar-nav-item">
            <a href="/" class="sidebar-nav-link <?php echo isActive('/', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-house"></i></span>
                <span>Home</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/features" class="sidebar-nav-link <?php echo isActive('/features', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-star"></i></span>
                <span>Features</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/pricing" class="sidebar-nav-link <?php echo isActive('/pricing', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-tag"></i></span>
                <span>Pricing</span>
            </a>
        </li>
        
        <li class="sidebar-nav-item">
            <a href="/about" class="sidebar-nav-link <?php echo isActive('/about', $currentPath); ?>">
                <span class="sidebar-nav-icon"><i class="bi bi-info-circle"></i></span>
                <span>About</span>
            </a>
        </li>
    <?php endif; ?>
</nav>
