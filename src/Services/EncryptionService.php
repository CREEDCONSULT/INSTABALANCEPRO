<?php

namespace App\Services;

/**
 * EncryptionService — AES-256 encryption for sensitive data
 * 
 * Used for encrypting Instagram OAuth tokens and other sensitive fields
 * Tokens are encrypted at rest in the database and decrypted only when needed
 */
class EncryptionService
{
    private string $key;
    private const CIPHER = 'AES-256-CBC';
    private const KEY_LENGTH = 32;  // 256 bits

    /**
     * Initialize encryption service with key from config
     * 
     * @param string $key Encryption key (32 bytes for AES-256)
     * @throws \Exception If key is invalid
     */
    public function __construct(string $key)
    {
        if (strlen($key) !== self::KEY_LENGTH) {
            throw new \Exception(
                "Encryption key must be " . self::KEY_LENGTH . " bytes. Got " . strlen($key) . "."
            );
        }
        $this->key = $key;
    }

    /**
     * Encrypt plaintext value
     * 
     * @param string $plaintext Value to encrypt
     * @return string Base64-encoded encrypted value with IV prepended
     */
    public function encrypt(string $plaintext): string
    {
        // Generate random IV for this encryption
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER));

        if ($iv === false) {
            throw new \Exception("Failed to generate random IV");
        }

        // Encrypt the plaintext
        $encrypted = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new \Exception("Encryption failed");
        }

        // Prepend IV to encrypted data and base64 encode
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt encrypted value
     * 
     * @param string $encryptedBase64 Base64-encoded encrypted value (IV + ciphertext)
     * @return string Original plaintext value
     */
    public function decrypt(string $encryptedBase64): string
    {
        // Decode from base64
        $encryptedData = base64_decode($encryptedBase64, true);

        if ($encryptedData === false) {
            throw new \Exception("Failed to decode base64");
        }

        // Extract IV from encrypted data
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($encryptedData, 0, $ivLength);
        $encrypted = substr($encryptedData, $ivLength);

        if (strlen($iv) === 0 || strlen($encrypted) === 0) {
            throw new \Exception("Invalid encrypted data format");
        }

        // Decrypt
        $decrypted = openssl_decrypt(
            $encrypted,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new \Exception("Decryption failed");
        }

        return $decrypted;
    }

    /**
     * Generate a new encryption key
     * 
     * Useful for generating ENCRYPTION_KEY for .env
     * Run once and save to .env file
     */
    public static function generateKey(): string
    {
        $key = openssl_random_pseudo_bytes(self::KEY_LENGTH);
        if ($key === false) {
            throw new \Exception("Failed to generate random key");
        }
        return base64_encode($key);
    }

    /**
     * Generate a secure random token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Hash a value using HMAC-SHA256 (for verification codes, etc.)
     */
    public static function hash(string $value, string $key): string
    {
        return hash_hmac('sha256', $value, $key);
    }

    /**
     * Verify an HMAC hash (timing-safe comparison)
     */
    public static function verifyHash(string $value, string $hash, string $key): bool
    {
        $computed = static::hash($value, $key);
        return hash_equals($computed, $hash);
    }
}
