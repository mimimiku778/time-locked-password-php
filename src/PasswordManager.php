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
            'password' => $password,
            'unlock_time' => $unlockDateTime
        ]);
        
        return $this->cryptor->encryptAndHashString($data);
    }
    
    public function decryptPassword(string $encryptedData): array {
        try {
            $decrypted = $this->cryptor->verifyHashAndDecrypt($encryptedData);
        } catch (\RuntimeException | \LogicException $e) {
            return ['error' => 'Decryption failed: ' . $e->getMessage()];
        }
        
        $passwordData = json_decode($decrypted, true);
        if (!$passwordData || !isset($passwordData['password'], $passwordData['unlock_time'])) {
            return ['error' => 'Invalid password data'];
        }
        
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $unlockTime = new DateTime($passwordData['unlock_time'], new DateTimeZone('UTC'));
        
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