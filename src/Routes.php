<?php

namespace App;

/**
 * Application Routes
 * 
 * Defines all HTTP routes for UnfollowIQ
 * Maps URLs to controller actions with middleware chains
 */
function registerRoutes(Router $router): void
{
    // ========================================================================
    // SECTION 1: PUBLIC / GUEST ROUTES
    // ========================================================================

    $router->get('/', 'HomeController@index');
    $router->get('/pricing', 'PricingController@index');
    $router->get('/features', 'FeaturesController@index');
    $router->get('/about', 'AboutController@index');

    // ========================================================================
    // SECTION 2: AUTHENTICATION ROUTES (PUBLIC - NO AUTH REQUIRED)
    // ========================================================================

    $router->group(['prefix' => '/auth'], function (Router $r) {
        // Registration
        $r->get('/register', 'AuthController@showRegister');
        $r->post('/register', 'AuthController@register');

        // Login
        $r->get('/login', 'AuthController@showLogin');
        $r->post('/login', 'AuthController@login');

        // Logout
        $r->post('/logout', 'AuthController@logout', ['auth']);

        // Instagram OAuth
        $r->get('/connect-instagram', 'AuthController@connectInstagram');
        $r->get('/instagram/callback', 'AuthController@instagramCallback');

        // Email verification
        $r->get('/verify-email/{token}', 'AuthController@verifyEmail');
        $r->post('/resend-verification', 'AuthController@resendVerification');

        // Password reset
        $r->get('/forgot-password', 'AuthController@showForgotPassword');
        $r->post('/forgot-password', 'AuthController@forgotPassword');
        $r->get('/reset-password/{token}', 'AuthController@showResetPassword');
        $r->post('/reset-password', 'AuthController@resetPassword');

        // Two-factor authentication
        $r->post('/2fa/setup', 'AuthController@setup2FA', ['auth']);
        $r->post('/2fa/verify', 'AuthController@verify2FA');
        $r->post('/2fa/disable', 'AuthController@disable2FA', ['auth']);
    });

    // ========================================================================
    // SECTION 3: AUTHENTICATED USER ROUTES
    // ========================================================================

    $router->group(['prefix' => '', 'middleware' => ['auth']], function (Router $r) {
        // Dashboard
        $r->get('/dashboard', 'DashboardController@index');
        $r->post('/dashboard/sync', 'DashboardController@startSync');
        $r->get('/dashboard/sync-status', 'DashboardController@syncStatus');

        // Unfollowers / Ranked List
        $r->get('/unfollowers', 'UnfollowController@index');
        $r->get('/api/unfollowers/list', 'UnfollowController@listFollowing');
        $r->post('/api/unfollowers/{id}/unfollow', 'UnfollowController@unfollowSingle');
        $r->post('/api/unfollowers/bulk/preview', 'UnfollowController@bulkUnfollowPreview');
        $r->post('/api/unfollowers/bulk/execute', 'UnfollowController@bulkUnfollowExecute');

        // Kanban board
        $r->get('/kanban', 'KanbanController@index');
        $r->get('/api/kanban/cards', 'KanbanController@getCards');
        $r->post('/api/kanban/card/{id}/move', 'KanbanController@moveCard');
        $r->post('/api/kanban/card/{id}/update', 'KanbanController@updateCard');

        // Activity calendar
        $r->get('/activity', 'ActivityController@index');
        $r->get('/api/activity/calendar/{year}/{month}', 'ActivityController@getCalendar');

        // Whitelist management
        $r->get('/whitelist', 'WhitelistController@index');
        $r->post('/api/whitelist/add', 'WhitelistController@add');
        $r->post('/api/whitelist/{id}/remove', 'WhitelistController@remove');

        // Settings
        $r->get('/settings', 'SettingsController@index');
        $r->post('/settings/profile', 'SettingsController@updateProfile');
        $r->post('/settings/email', 'SettingsController@updateEmail');
        $r->post('/settings/password', 'SettingsController@updatePassword');
        $r->post('/settings/scoring-preferences', 'SettingsController@updateScoringPreferences');
        $r->post('/settings/disconnect-instagram', 'SettingsController@disconnectInstagram');
        $r->post('/settings/export-data', 'SettingsController@exportData');
        $r->post('/settings/delete-account', 'SettingsController@deleteAccount');

        // Billing / Stripe
        $r->get('/billing', 'BillingController@index');
        $r->get('/billing/upgrade', 'BillingController@showUpgrade');
        $r->post('/billing/checkout', 'BillingController@checkout');
        $r->get('/billing/success', 'BillingController@success');
        $r->get('/billing/canceled', 'BillingController@canceled');
        $r->get('/billing/portal', 'BillingController@portal');
        $r->post('/billing/cancel-subscription', 'BillingController@cancelSubscription');
    });

    // ========================================================================
    // SECTION 4: WEBHOOK ROUTES (PUBLIC - SPECIAL TOKENS)
    // ========================================================================

    // Stripe webhook (no CSRF, uses signature validation)
    $router->post('/webhooks/stripe', 'WebhookController@stripe');

    // Instagram webhook status validation (GET only, for Instagram handshake)
    $router->get('/webhooks/instagram', 'WebhookController@instagramValidation');

    // ========================================================================
    // SECTION 5: ADMIN ROUTES
    // ========================================================================

    $router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function (Router $r) {
        // Dashboard
        $r->get('/', 'Admin\DashboardController@index');

        // User management
        $r->get('/users', 'Admin\UserController@index');
        $r->get('/users/{id}', 'Admin\UserController@show');
        $r->post('/users/{id}/suspend', 'Admin\UserController@suspend');
        $r->post('/users/{id}/activate', 'Admin\UserController@activate');
        $r->post('/users/{id}/tier', 'Admin\UserController@changeTier');
        $r->post('/users/{id}/reset-quotas', 'Admin\UserController@resetQuotas');

        // Monitoring
        $r->get('/sync-jobs', 'Admin\MonitoringController@syncJobs');
        $r->get('/unfollow-queue', 'Admin\MonitoringController@unfollowQueue');
        $r->get('/api-usage', 'Admin\MonitoringController@apiUsage');
        $r->get('/error-logs', 'Admin\MonitoringController@errorLogs');

        // Reports
        $r->get('/reports/revenue', 'Admin\ReportsController@revenue');
        $r->get('/reports/usage', 'Admin\ReportsController@usage');
        $r->get('/reports/signups', 'Admin\ReportsController@signups');

        // Settings
        $r->get('/settings', 'Admin\SettingsController@index');
        $r->post('/settings/update', 'Admin\SettingsController@update');
    });

    // ========================================================================
    // SECTION 6: API ROUTES (FOR AJAX/HTMX ENDPOINTS)
    // ========================================================================

    $router->group(['prefix' => '/api'], function (Router $r) {
        // Search & filtering
        $r->get('/search/accounts', 'API\SearchController@accounts');

        // Activity feed
        $r->get('/activity/feed', 'API\ActivityController@feed');

        // Export
        $r->post('/export/csv', 'API\ExportController@csv');
        $r->post('/export/json', 'API\ExportController@json');
    });

    // ========================================================================
    // SECTION 7: ERROR HANDLING
    // ========================================================================

    // Custom error pages
    // 404, 500, etc. handled by Router dispatch method
}
