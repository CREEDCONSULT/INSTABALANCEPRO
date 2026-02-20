<?php

namespace App\Controllers;

use App\Controller;

/**
 * AuthController — Authentication and authorization flows
 */
class AuthController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegister()
    {
        // TODO: Show registration form template
        echo '<h1>Register</h1>';
        echo '<p>Registration form coming in PROMPT 4.</p>';
    }

    /**
     * Handle registration submission
     */
    public function register()
    {
        if ($this->isPost()) {
            // TODO: Validate input, create user, send verification email
            $this->redirectWith('/auth/login', 'success', 'Registration successful! Check your email to verify your account.');
        }
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        // TODO: Show login form template
        echo '<h1>Login</h1>';
        echo '<p>Login form coming in PROMPT 4.</p>';
    }

    /**
     * Handle login submission
     */
    public function login()
    {
        if ($this->isPost()) {
            // TODO: Validate credentials, handle 2FA, set session
            $this->redirect('/dashboard');
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        // TODO: Clear session, destroy tokens
        session_destroy();
        $this->redirect('/');
    }

    /**
     * Redirect to Instagram OAuth
     */
    public function instagramRedirect()
    {
        // TODO: Build OAuth URL, redirect to Instagram
        echo '<h1>Redirecting to Instagram...</h1>';
    }

    /**
     * Handle Instagram OAuth callback
     */
    public function instagramCallback()
    {
        // TODO: Exchange code for token, store encrypted, redirect to dashboard
        echo '<h1>Instagram Connected!</h1>';
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
        if ($this->isPost()) {
            // TODO: Generate new token, send email
            $this->redirectWith('/auth/login', 'info', 'Verification email sent!');
        }
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        echo '<h1>Forgot Password</h1>';
    }

    /**
     * Handle forgot password submission
     */
    public function forgotPassword()
    {
        if ($this->isPost()) {
            // TODO: Look up user, generate reset token, send email
            $this->redirectWith('/auth/login', 'info', 'Password reset link sent to your email.');
        }
    }

    /**
     * Show password reset form
     */
    public function showResetPassword()
    {
        echo '<h1>Reset Password</h1>';
    }

    /**
     * Handle password reset
     */
    public function resetPassword()
    {
        if ($this->isPost()) {
            // TODO: Verify token, hash password, update user
            $this->redirectWith('/auth/login', 'success', 'Password updated! Please log in.');
        }
    }

    /**
     * Setup 2FA (TOTP)
     */
    public function setup2FA()
    {
        if ($this->isPost()) {
            // TODO: Generate secret, show QR code, save recovery codes
            $this->json(['qr_code_url' => 'data:image/png;base64,...']);
        }
    }

    /**
     * Verify 2FA code
     */
    public function verify2FA()
    {
        if ($this->isPost()) {
            // TODO: Check TOTP code against secret
            $this->json(['success' => '2FA enabled']);
        }
    }

    /**
     * Disable 2FA
     */
    public function disable2FA()
    {
        if ($this->isPost()) {
            // TODO: Remove 2FA from user account
            $this->json(['success' => '2FA disabled']);
        }
    }
}
