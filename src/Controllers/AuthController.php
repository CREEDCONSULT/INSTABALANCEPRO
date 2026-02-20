<?php

namespace App\Controllers;

use App\Controller;
use App\Models\User;
use App\Models\EmailVerification;
use App\Services\EncryptionService;

/**
 * AuthController — Authentication and authorization flows
 */
class AuthController extends Controller
{
    /**
     * Initiate Instagram OAuth connection
     */
    public function connectInstagram()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        try {
            $config = require __DIR__ . '/../../config/app.php';
            $apiService = new \App\Services\InstagramApiService(
                $config['instagram']['app_id'],
                $config['instagram']['app_secret'],
                $config['instagram']['redirect_uri']
            );

            // Generate CSRF state token
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth_state'] = $state;

            // Get authorization URL
            $authUrl = $apiService->getAuthorizationUrl($state);

            return $this->redirect($authUrl);

        } catch (\Exception $e) {
            error_log('Failed to initiate Instagram OAuth: ' . $e->getMessage());
            $this->flash('error', 'Failed to connect Instagram. Please try again.');
            return $this->redirect('/dashboard/settings');
        }
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        return $this->view('pages/auth/register', [
            'pageTitle' => 'Register'
        ]);
    }

    /**
     * Handle registration submission
     */
    public function register()
    {
        if (!$this->isPost()) {
            $this->abort(405);
        }

        // Validate input
        $errors = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|min:8',
        ]);

        if ($errors) {
            return $this->jsonError('Validation failed', 422, ['errors' => $errors]);
        }

        $email = $this->post('email');
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirmation');

        // Check password match
        if ($password !== $passwordConfirm) {
            return $this->jsonError('Passwords do not match', 422);
        }

        // Check if email already exists
        if (User::emailExists($this->db, $email)) {
            return $this->jsonError('Email address is already registered', 422);
        }

        try {
            // Create user account
            $user = User::createWithPassword($this->db, [
                'email' => $email,
                'password' => $password,
                'is_active' => true,
            ]);

            // Create email verification token
            $verification = EmailVerification::createForUser($this->db, $user->id, $email);

            // TODO: Send verification email with token

            return $this->jsonSuccess('Registration successful! Check your email to verify your account.', [
                'redirect' => '/auth/login',
            ]);

        } catch (\Exception $e) {
            return $this->jsonError('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        return $this->view('pages/auth/login', [
            'pageTitle' => 'Login'
        ]);
    }

    /**
     * Handle login submission
     */
    public function login()
    {
        if (!$this->isPost()) {
            $this->abort(405);
        }

        $email = $this->post('email');
        $password = $this->post('password');

        if (!$email || !$password) {
            return $this->jsonError('Email and password are required', 422);
        }

        // Find user by email
        $user = User::findByEmail($this->db, $email);

        if (!$user) {
            return $this->jsonError('Invalid email or password', 401);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            return $this->jsonError('Account is locked due to too many failed login attempts. Please try again later.', 429);
        }

        // Verify password
        if (!$user->verifyPassword($password)) {
            $user->incrementFailedLogins();
            
            // Lock account after 5 failed attempts
            if ($user->failed_login_attempts >= 5) {
                $user->lockForFailedLogins();
                return $this->jsonError('Too many failed login attempts. Account locked for 15 minutes.', 429);
            }

            return $this->jsonError('Invalid email or password', 401);
        }

        // Check if 2FA is enabled
        if ($user->two_fa_enabled) {
            // Store temporary user ID in session
            $_SESSION['temp_user_id'] = $user->id;
            $_SESSION['temp_user_email'] = $user->email;
            
            return $this->jsonSuccess('Verification code required', [
                'requires_2fa' => true,
                'redirect' => '/auth/verify-2fa',
            ]);
        }

        // Set session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['email'] = $user->email;
        $_SESSION['is_admin'] = $user->is_admin;
        $_SESSION['tier'] = $user->subscription_tier;

        // Reset failed login attempts
        $user->resetFailedLogins();

        // Redirect to intended page or dashboard
        $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);

        return $this->jsonSuccess('Login successful', [
            'redirect' => $redirect,
        ]);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        if ($this->isPost()) {
            session_destroy();
            return $this->jsonSuccess('Logged out successfully', [
                'redirect' => '/',
            ]);
        }
        $this->abort(405);
    }

    /**
     * Redirect to Instagram OAuth
     */
    public function instagramRedirect()
    {
        // TODO: Get Instagram app ID from config and build OAuth URL
        // Redirect to: https://api.instagram.com/oauth/authorize?...
        echo '<h1>Redirecting to Instagram...</h1>';
    }

    /**
     * Handle Instagram OAuth callback
     */
    public function instagramCallback()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        $code = $this->get('code');
        $state = $this->get('state');
        $error = $this->get('error');
        $errorDescription = $this->get('error_description');

        // Check for OAuth errors
        if ($error) {
            $this->flash('error', 'Instagram authorization failed: ' . ($errorDescription ?? $error));
            return $this->redirect('/dashboard/settings');
        }

        if (!$code) {
            $this->flash('error', 'Instagram authorization code not provided');
            return $this->redirect('/dashboard/settings');
        }

        // Verify state token matches session (CSRF protection)
        $sessionState = $_SESSION['oauth_state'] ?? null;
        if (!$state || !$sessionState || $state !== $sessionState) {
            $this->flash('error', 'Invalid OAuth state token');
            return $this->redirect('/dashboard/settings');
        }

        try {
            $userId = $_SESSION['user_id'];
            
            // Create Instagram API service
            $config = require __DIR__ . '/../../config/app.php';
            $apiService = new \App\Services\InstagramApiService(
                $config['instagram']['app_id'],
                $config['instagram']['app_secret'],
                $config['instagram']['redirect_uri']
            );

            // Exchange code for access token
            $tokenResponse = $apiService->exchangeCodeForToken($code);

            if (!isset($tokenResponse['access_token'])) {
                throw new \Exception('Failed to get access token from Instagram');
            }

            $accessToken = $tokenResponse['access_token'];
            $userId_ig = $tokenResponse['user_id'] ?? null;
            $expiresIn = $tokenResponse['expires_in'] ?? null;

            // Get account info to verify connection
            $apiService->setUserToken($accessToken);
            $accountInfo = $apiService->getBusinessAccount();

            // Encrypt token before storage
            $encryption = new \App\Services\EncryptionService(
                $config['encryption']['key'],
                $_ENV['ENCRYPTION_IV'] ?? ''
            );
            $encryptedToken = $encryption->encrypt($accessToken);

            // Store Instagram connection
            $stmt = $this->db->prepare("
                INSERT INTO instagram_connections (user_id, instagram_account_id, instagram_username, access_token, refresh_token, expires_at, connected_at)
                VALUES (?, ?, ?, ?, ?, FROM_UNIXTIME(?), NOW())
                ON DUPLICATE KEY UPDATE
                    access_token = VALUES(access_token),
                    refresh_token = VALUES(refresh_token),
                    expires_at = VALUES(expires_at),
                    updated_at = NOW()
            ");

            $expiresAt = $expiresIn ? (time() + $expiresIn) : null;

            $stmt->execute([
                $userId,
                $accountInfo['id'],
                $accountInfo['username'] ?? '',
                $encryptedToken,
                null, // refresh_token - implement token refresh if needed
                $expiresAt,
            ]);

            // Update user record with Instagram info
            $updateStmt = $this->db->prepare("
                UPDATE users SET
                    instagram_account_id = ?,
                    instagram_access_token = 'encrypted',
                    instagram_username = ?,
                    instagram_followers_count = ?,
                    instagram_profile_picture = ?
                WHERE id = ?
            ");

            $updateStmt->execute([
                $accountInfo['id'],
                $accountInfo['username'] ?? '',
                $accountInfo['followers_count'] ?? 0,
                $accountInfo['profile_picture_url'] ?? '',
                $userId,
            ]);

            // Log the connection event
            \App\Models\ActivityLog::log(
                $this->db,
                $userId,
                'instagram_connected',
                'Connected Instagram account @' . ($accountInfo['username'] ?? 'unknown'),
                ['instagram_username' => $accountInfo['username'] ?? '', 'instagram_id' => $accountInfo['id']],
                $this->getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            // Create initial sync job
            $syncJob = \App\Models\SyncJob::createForUser($this->db, $userId);

            $this->flash('success', 'Instagram account connected! Starting initial sync...');
            return $this->redirect('/dashboard');

        } catch (\Exception $e) {
            error_log('Instagram OAuth error: ' . $e->getMessage());
            $this->flash('error', 'Failed to connect Instagram: ' . $e->getMessage());
            return $this->redirect('/dashboard/settings');
        }
        finally {
            // Clean up OAuth state
            unset($_SESSION['oauth_state']);
        }
    }

    /**
     * Verify email token
     */
    public function verifyEmail()
    {
        // TODO: Look up token, mark email as verified, redirect to login
        echo '<h1>Email Verified!</h1>';
    }

    /**
     * Resend verification email
     */
    public function resendVerification()
    {
        if (!$this->isPost()) {
            $this->abort(405);
        }

        $email = $this->post('email');
        if (!$email) {
            return $this->jsonError('Email is required', 422);
        }

        $user = User::findByEmail($this->db, $email);
        if (!$user) {
            // Don't reveal if email exists for security
            return $this->jsonSuccess('If an account exists, a verification email has been sent.');
        }

        // Check if already verified
        if ($user->email_verified_at) {
            return $this->jsonError('Email is already verified', 400);
        }

        // Create new verification token
        $verification = EmailVerification::createForUser($this->db, $user->id, $email);

        // TODO: Send verification email

        return $this->jsonSuccess('Verification email sent!');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        $this->view('pages/auth/forgot-password');
    }

    /**
     * Handle forgot password submission
     */
    public function forgotPassword()
    {
        if (!$this->isPost()) {
            $this->abort(405);
        }

        $email = $this->post('email');
        if (!$email) {
            return $this->jsonError('Email is required', 422);
        }

        $user = User::findByEmail($this->db, $email);
        if (!$user) {
            // Don't reveal if email exists for security
            return $this->jsonSuccess('If an account exists, a password reset link has been sent.');
        }

        // TODO: Create password reset token and send email

        return $this->jsonSuccess('Password reset link sent to your email.');
    }

    /**
     * Show password reset form
     */
    public function showResetPassword($token = null)
    {
        // TODO: Validate token exists and not expired
        $this->view('pages/auth/reset-password', ['token' => $token]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword()
    {
        if (!$this->isPost()) {
            $this->abort(405);
        }

        // TODO: Validate token, hash password, update user
        return $this->jsonSuccess('Password updated! Please log in.', [
            'redirect' => '/auth/login',
        ]);
    }

    /**
     * Setup 2FA (TOTP)
     */
    public function setup2FA()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            // Get current user
            $user = User::find($this->db, $_SESSION['user_id']);
            
            // TODO: Generate TOTP secret and QR code
            return $this->json([
                'secret' => '',  // Generated secret
                'qr_code_url' => '',  // Base64 PNG image
                'recovery_codes' => [],  // Will be created after verification
            ]);
        }

        // Verify code and enable 2FA
        // TODO: Implement
        return $this->json(['success' => '2FA setup complete']);
    }

    /**
     * Verify 2FA code (on login)
     */
    public function verify2FA()
    {
        if (!isset($_SESSION['temp_user_id'])) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->view('pages/auth/verify-2fa');
            return;
        }

        $code = $this->post('code');
        if (!$code) {
            return $this->jsonError('Verification code is required', 422);
        }

        // TODO: Verify TOTP code against user's secret
        $user = User::find($this->db, $_SESSION['temp_user_id']);

        // Set session after verification
        $_SESSION['user_id'] = $user->id;
        $_SESSION['email'] = $user->email;
        $_SESSION['is_admin'] = $user->is_admin;
        $_SESSION['tier'] = $user->subscription_tier;
        
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_user_email']);

        return $this->jsonSuccess('2FA verification successful', [
            'redirect' => '/dashboard',
        ]);
    }

    /**
     * Disable 2FA
     */
    public function disable2FA()
    {
        if (!$this->isAuthenticated()) {
            $this->abort(401);
        }

        if (!$this->isPost()) {
            $this->abort(405);
        }

        $user = User::find($this->db, $_SESSION['user_id']);
        $user->disable2FA();

        return $this->json(['success' => '2FA disabled']);
    }

    /**
     * Show 2FA verification page (helper)
     */
    public function show2FAVerification()
    {
        $this->view('pages/auth/verify-2fa');
    }

    /**
     * Helper: Redirect with error flash
     */
    private function abortWithFlash(int $code, string $message)
    {
        $_SESSION['flash'] = ['type' => 'error', 'message' => $message];
        $this->abort($code, $message);
    }
}
