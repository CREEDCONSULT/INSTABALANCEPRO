<?php

namespace App\Models;

use App\Model;
use App\Database;

/**
 * User — Core user model with authentication
 */
class User extends Model
{
    protected static string $table = 'users';

    /**
     * Hash a password using bcrypt
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a password against hash
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Find user by email
     */
    public static function findByEmail(Database $db, string $email): ?self
    {
        return static::firstWhere($db, "email = ? AND deleted_at IS NULL", [$email]);
    }

    /**
     * Create new user account with email and hashed password
     */
    public static function createWithPassword(Database $db, array $data): self
    {
        $user = new static($db);
        $data['password_hash'] = static::hashPassword($data['password']);
        unset($data['password']);
        $user->fill($data);
        $user->save();
        return $user;
    }

    /**
     * Check if email exists
     */
    public static function emailExists(Database $db, string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM " . static::getTable() . " WHERE email = ? AND deleted_at IS NULL";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return (int)$db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedLogins(): void
    {
        $this->attributes['failed_login_attempts'] = ($this->attributes['failed_login_attempts'] ?? 0) + 1;
        $this->save();
    }

    /**
     * Lock account for failed login attempts
     */
    public function lockForFailedLogins(int $minutes = 15): void
    {
        $lockUntil = new \DateTime();
        $lockUntil->modify("+$minutes minutes");
        
        $this->attributes['locked_until'] = $lockUntil->format('Y-m-d H:i:s');
        $this->attributes['failed_login_attempts'] = 5;  // Max attempts reached
        $this->save();
    }

    /**
     * Check if account is locked
     */
    public function isLocked(): bool
    {
        if (!$this->attributes['locked_until'] ?? null) {
            return false;
        }

        $lockedUntil = new \DateTime($this->attributes['locked_until']);
        $now = new \DateTime();

        return $now < $lockedUntil;
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedLogins(): void
    {
        $this->attributes['failed_login_attempts'] = 0;
        $this->attributes['locked_until'] = null;
        $this->attributes['last_login_at'] = (new \DateTime())->format('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Enable 2FA with TOTP secret
     */
    public function enable2FA(string $secret): void
    {
        // Generate 8 single-use recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = bin2hex(random_bytes(4));
        }

        $this->attributes['two_fa_enabled'] = true;
        $this->attributes['two_fa_secret'] = $secret;
        $this->attributes['recovery_codes'] = json_encode($recoveryCodes);
        $this->save();
    }

    /**
     * Get recovery codes (for display during setup)
     */
    public function getRecoveryCodes(): array
    {
        if (!$this->attributes['recovery_codes'] ?? null) {
            return [];
        }
        return json_decode($this->attributes['recovery_codes'], true);
    }

    /**
     * Disable 2FA
     */
    public function disable2FA(): void
    {
        $this->attributes['two_fa_enabled'] = false;
        $this->attributes['two_fa_secret'] = null;
        $this->attributes['recovery_codes'] = null;
        $this->save();
    }

    /**
     * Get Instagram connection (if any)
     */
    public function getInstagramConnection(Database $db): ?InstagramConnection
    {
        return InstagramConnection::firstWhere($db, "user_id = ? AND disconnected_at IS NULL", [$this->attributes['id']]);
    }

    /**
     * Check if Instagram is connected
     */
    public function hasInstagram(Database $db): bool
    {
        return $this->getInstagramConnection($db) !== null;
    }

    /**
     * Get subscription tier
     */
    public function getTier(): string
    {
        return $this->attributes['subscription_tier'] ?? 'free';
    }

    /**
     * Is free tier user
     */
    public function isFree(): bool
    {
        return $this->getTier() === 'free';
    }

    /**
     * Is pro tier user
     */
    public function isPro(): bool
    {
        return $this->getTier() === 'pro';
    }

    /**
     * Is premium tier user
     */
    public function isPremium(): bool
    {
        return $this->getTier() === 'premium';
    }

    /**
     * Soft delete account
     */
    public function softDelete(): void
    {
        $this->attributes['deleted_at'] = (new \DateTime())->format('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Is deleted
     */
    public function isDeleted(): bool
    {
        return $this->attributes['deleted_at'] !== null;
    }
}

/**
 * EmailVerification — Email verification tokens
 */
class EmailVerification extends Model
{
    protected static string $table = 'email_verifications';

    /**
     * Create new verification token
     */
    public static function createForUser(Database $db, int $userId, string $email): self
    {
        $token = bin2hex(random_bytes(16));
        $expiresAt = (new \DateTime())->modify('+24 hours');

        $verification = new static($db);
        $verification->fill([
            'user_id' => $userId,
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
        $verification->save();

        return $verification;
    }

    /**
     * Find by token and check if not expired
     */
    public static function findValid(Database $db, string $token): ?self
    {
        return static::firstWhere(
            $db,
            "token = ? AND expires_at > NOW() AND verified_at IS NULL",
            [$token]
        );
    }

    /**
     * Mark as verified
     */
    public function verify(): void
    {
        $this->attributes['verified_at'] = (new \DateTime())->format('Y-m-d H:i:s');
        $this->save();
    }
}

/**
 * PasswordReset — Password reset tokens
 */
class PasswordReset extends Model
{
    protected static string $table = 'password_resets';

    /**
     * Create new password reset token
     */
    public static function createForUser(Database $db, int $userId): self
    {
        $token = bin2hex(random_bytes(16));
        $expiresAt = (new \DateTime())->modify('+1 hour');

        $reset = new static($db);
        $reset->fill([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
        $reset->save();

        return $reset;
    }

    /**
     * Find by token and check if not expired
     */
    public static function findValid(Database $db, string $token): ?self
    {
        return static::firstWhere(
            $db,
            "token = ? AND expires_at > NOW() AND reset_at IS NULL",
            [$token]
        );
    }

    /**
     * Mark as reset
     */
    public function markReset(): void
    {
        $this->attributes['reset_at'] = (new \DateTime())->format('Y-m-d H:i:s');
        $this->save();
    }
}

/**
 * InstagramConnection — OAuth connection and token storage
 */
class InstagramConnection extends Model
{
    protected static string $table = 'instagram_connections';

    /**
     * Get decrypted access token
     */
    public function getAccessToken(string $encryptionKey): string
    {
        // TODO: Decrypt token using EncryptionService
        return $this->attributes['access_token'];
    }

    /**
     * Set access token (will be encrypted before saving)
     */
    public function setAccessToken(string $token, string $encryptionKey): void
    {
        // TODO: Encrypt token using EncryptionService
        $this->attributes['access_token'] = $token;
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired(): bool
    {
        if (!$this->attributes['token_expires_at'] ?? null) {
            return false;  // No expiry set
        }

        $expiresAt = new \DateTime($this->attributes['token_expires_at']);
        $now = new \DateTime();

        return $now >= $expiresAt;
    }

    /**
     * Get verified badge status
     */
    public function isVerified(): bool
    {
        return $this->attributes['is_verified'] ?? false;
    }
}
