<?php
/**
 * User Menu Partial - src/Views/partials/user-menu.php
 * 
 * User dropdown menu in topbar
 * Shows: user name, profile, theme toggle, logout
 */
?>

<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle"></i>
        <?php echo htmlspecialchars($_SESSION['email'] ?? 'User'); ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
        <li>
            <h6 class="dropdown-header">
                <span class="badge bg-<?php echo match($_SESSION['tier'] ?? 'free') {
                    'premium' => 'danger',
                    'pro' => 'primary',
                    default => 'secondary'
                }; ?>">
                    <?php echo ucfirst($_SESSION['tier'] ?? 'Free'); ?>
                </span>
            </h6>
        </li>
        <li><hr class="dropdown-divider"></li>
        
        <?php if ($isAuthenticated): ?>
            <li>
                <a class="dropdown-item" href="/settings?tab=profile">
                    <i class="bi bi-person"></i> Profile
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="/settings?tab=password">
                    <i class="bi bi-lock"></i> Security
                </a>
            </li>
        <?php endif; ?>
        
        <li>
            <button class="dropdown-item" onclick="window.toggleTheme()">
                <i class="bi bi-moon-stars"></i> Toggle Theme
            </button>
        </li>
        
        <li><hr class="dropdown-divider"></li>
        
        <?php if ($isAuthenticated): ?>
            <li>
                <form method="POST" action="/auth/logout" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </li>
        <?php else: ?>
            <li>
                <a class="dropdown-item" href="/auth/login">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="/auth/register">
                    <i class="bi bi-person-plus"></i> Register
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>
