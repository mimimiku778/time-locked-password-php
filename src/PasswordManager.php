<?php

require_once 'StringCryptor.php';

class PasswordManager {
    private StringCryptor $cryptor;
    
    public function __construct(string $hkdfKey, string $opensslKey) {
        $this->cryptor = new StringCryptor($hkdfKey, $opensslKey);
    }
    
    public function generateRandomPassword(int $length = 16): string {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
    }
    
    public function encryptPassword(string $password, string $unlockDateTime): string {
        $data = json_encode([
            'password' => $password
        ]);
        
        return $this->cryptor->encryptAndHashString($data, $unlockDateTime);
    }
    
    public function decryptPassword(string $encryptedData, ?string $unlockTimeFromUrl = null): array {
        try {
            $decrypted = $this->cryptor->verifyHashAndDecrypt($encryptedData, $unlockTimeFromUrl);
        } catch (\RuntimeException | \LogicException $e) {
            return ['error' => 'Decryption failed: ' . $e->getMessage()];
        }
        
        $passwordData = json_decode($decrypted, true);
        if (!$passwordData || !isset($passwordData['password'])) {
            return ['error' => 'Invalid password data'];
        }
        
        // Determine unlock time (old format has it in JSON, new format gets it from URL)
        $unlockTimeString = null;
        if (isset($passwordData['unlock_time'])) {
            // Old format - unlock_time is in the encrypted data
            $unlockTimeString = $passwordData['unlock_time'];
        } elseif ($unlockTimeFromUrl) {
            // New format - unlock_time is from URL parameter
            $unlockTimeString = $unlockTimeFromUrl;
        } else {
            return ['error' => 'Missing unlock time'];
        }
        
        $now = new DateTime('now', new DateTimeZone('UTC'));
        
        // Handle both old format (Y-m-d H:i:s) and new format (Y-m-d\TH:i:s\Z)
        try {
            $unlockTime = new DateTime($unlockTimeString, new DateTimeZone('UTC'));
        } catch (Exception $e) {
            // Try parsing as ISO 8601 format if the first attempt fails
            $unlockTime = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $unlockTimeString, new DateTimeZone('UTC'));
            if (!$unlockTime) {
                return ['error' => 'Invalid unlock time format'];
            }
        }
        
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