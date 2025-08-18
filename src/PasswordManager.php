<?php

require_once 'StringCryptor.php';
require_once 'TimeUtility.php';

class PasswordManager
{
    private StringCryptor $cryptor;

    /**
     * Constructs a new PasswordManager instance
     *
     * @param string $hkdfKey HKDF key for cryptographic operations
     * @param string $opensslKey OpenSSL key for encryption/decryption operations
     */
    public function __construct(string $hkdfKey, string $opensslKey)
    {
        $this->cryptor = new StringCryptor($hkdfKey, $opensslKey);
    }

    /**
     * Generates a random password with specified length
     *
     * @param int $length The desired length of the password (default: 16)
     *                   Must be a positive integer
     * @return string A randomly generated password containing alphanumeric characters and special symbols
     */
    public function generateRandomPassword(int $length = 16): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
    }

    /**
     * Encrypts a password with time-based unlocking mechanism
     *
     * @param string $password The plain text password to encrypt
     * @param string $localDateTime The local date and time when the password should be unlockable
     *                             Format: 'Y-m-d H:i:s' (e.g., '2024-12-25 14:30:00')
     * @param string $timezone The timezone identifier for the local date time
     *                        Format: PHP timezone identifier (e.g., 'Asia/Tokyo', 'America/New_York')
     * @return string Base64 encoded encrypted data that can only be decrypted after the specified time
     */
    public function encryptPassword(string $password, string $localDateTime, string $timezone): string
    {
        $data = json_encode([
            'password' => $password
        ]);

        // Use TimeUtility for consistent hashing
        $normalizedTime = TimeUtility::convertLocalToUTCForHashing($localDateTime, $timezone);
        return $this->cryptor->encryptAndHashString($data, $normalizedTime);
    }

    /**
     * Attempts to decrypt a time-locked password
     *
     * @param string $encryptedData Base64 encoded encrypted password data
     * @param string|null $readableTime Optional readable time for unlock attempt
     *                                 Format: 'Y-m-d H:i:s' (e.g., '2024-12-25 14:30:00')
     *                                 If null, uses the embedded unlock time from encryption
     * @param string|null $timezone Optional timezone identifier for the readable time
     *                             Format: PHP timezone identifier (e.g., 'Asia/Tokyo', 'America/New_York')
     *                             Required if $readableTime is provided
     * 
     * @return array{password:string,unlock_time:string}|array{error:string}|array{error:string,unlock_time:string} 
     *         Success: ['password' => string, 'unlock_time' => string]
     *         Error: ['error' => string] or ['error' => string, 'unlock_time' => string]
     */
    public function decryptPassword(string $encryptedData, ?string $readableTime = null, ?string $timezone = null): array
    {
        $normalizedTime = null;
        if ($readableTime && $timezone) {
            // Use TimeUtility to parse readable time and convert to UTC for hashing
            try {
                $normalizedTime = TimeUtility::parseReadableTimeToUTC($readableTime, $timezone);
            } catch (Exception $e) {
                return ['error' => 'Invalid unlock time format: ' . $e->getMessage()];
            }
        }

        try {
            $decrypted = $this->cryptor->verifyHashAndDecrypt($encryptedData, $normalizedTime);
        } catch (\RuntimeException | \LogicException $e) {
            return ['error' => 'Decryption failed: ' . $e->getMessage()];
        }

        $passwordData = json_decode($decrypted, true);
        if (!$passwordData || !isset($passwordData['password'])) {
            return ['error' => 'Invalid password data'];
        }

        if (!$normalizedTime && !isset($passwordData['unlock_time'])) {
            return ['error' => 'Invalid unlock time'];
        }

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $unlockTime = new DateTime($normalizedTime ?? $passwordData['unlock_time'], new DateTimeZone('UTC'));

        if ($now < $unlockTime) {
            return [
                'error' => 'Password cannot be unlocked yet',
                'unlock_time' => $unlockTime->format('Y-m-d\TH:i:s\Z')
            ];
        }

        return [
            'password' => $passwordData['password'],
            'unlock_time' => $unlockTime->format('Y-m-d\TH:i:s\Z')
        ];
    }
}
