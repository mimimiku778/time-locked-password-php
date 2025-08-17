<?php

/**
 * ViewState object to manage application state for the view layer
 */
class ViewState
{

    public ?string $message = null;
    public ?string $messageType = null;
    public ?string $decryptedPassword = null;
    public ?string $unlockTimeUTC = null;

    /**
     * @param PasswordManager[] $passwordManagers Ordered list of password manager instances used for decryption.
     *               　　　　　　　　　　           Decryption is attempted in this order until one succeeds.
     */
    public function __construct(
        private array $passwordManagers
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
     * Handle password decryption by iterating through password managers in order.
     * Uses the first successful decryption result; if all fail, sets the first error.
     */
    public function handleDecryption(string $encryptedData): void
    {
        $firstError = null;

        foreach ($this->passwordManagers as $manager) {
            $result = $manager->decryptPassword($encryptedData);

            if (isset($result['error'])) {
                if ($firstError === null) {
                    $firstError = $result;
                }
                continue; // Continue to next fallback manager
            }

            // Use the first successful result
            $this->setSuccess($result['password'], $result['unlock_time'] ?? null);
            return;
        }

        // If all managers failed
        if ($firstError !== null) {
            $this->setError($firstError['error'], $firstError['unlock_time'] ?? null);
        }
    }
}
