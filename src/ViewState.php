<?php

/**
 * ViewState object to manage application state for the view layer
 */
class ViewState
{
    public function __construct(
        private PasswordManager $passwordManager,
        public ?string $message = null,
        public ?string $messageType = null,
        public ?string $decryptedPassword = null,
        public ?string $unlockTimeUTC = null,
    ) {}

    /**
     * Set error state
     */
    public function setError(string $message, ?string $unlockTime = null): void
    {
        $this->message = $message;
        $this->messageType = 'error';
        $this->unlockTimeUTC = $unlockTime;
    }

    /**
     * Set success state
     */
    public function setSuccess(string $password, ?string $unlockTime = null): void
    {
        $this->decryptedPassword = $password;
        $this->message = 'Password: ' . $password;
        $this->messageType = 'success';
        $this->unlockTimeUTC = $unlockTime;
    }

    /**
     * Check if state has a message
     */
    public function hasMessage(): bool
    {
        return $this->message !== null;
    }

    /**
     * Handle password decryption and set appropriate state
     */
    public function handleDecryption(?string $encryptedData): void
    {
        if (!$encryptedData) {
            return;
        }

        $result = $this->passwordManager->decryptPassword($encryptedData);

        if (isset($result['error'])) {
            $this->setError($result['error'], $result['unlock_time'] ?? null);
        } else {
            $this->setSuccess($result['password'], $result['unlock_time'] ?? null);
        }
    }
}
