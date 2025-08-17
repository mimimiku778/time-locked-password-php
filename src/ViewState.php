<?php

/**
 * ViewState object to manage application state for the view layer
 */
class ViewState
{
    /**
     * @var string|null Message to display to user
     */
    public ?string $message = null;

    /**
     * @var string|null Message type: 'error' or 'success'
     */
    public ?string $messageType = null;

    /**
     * @var string|null The decrypted password
     */
    public ?string $decryptedPassword = null;

    /**
     * @var string|null UTC timestamp when password unlocks
     */
    public ?string $unlockTimeUTC = null;

    /**
     * @param PasswordManager[] $passwordManagers Ordered list of password manager instances used for decryption.  
     * Decryption is attempted in this order until one succeeds.
     */
    public function __construct(
        private array $passwordManagers
    ) {}

    /**
     * Set error state
     *
     * @param string $message Error message
     * @param string|null $unlockTime UTC unlock time
     */
    public function setError(string $message, ?string $unlockTime = null): void
    {
        $this->message = $message;
        $this->messageType = 'error';
        $this->unlockTimeUTC = $unlockTime;
    }

    /**
     * Set success state
     *
     * @param string $password Decrypted password
     * @param string|null $unlockTime UTC unlock time
     */
    public function setSuccess(string $password, ?string $unlockTime = null): void
    {
        $this->decryptedPassword = $password;
        $this->message = $password;
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
     * Handle password decryption by iterating through password managers
     *
     * @param string $encryptedData Encrypted password data
     * @param string|null $readableTime Readable time string
     * @param string|null $timezone Timezone identifier
     */
    public function handleDecryption(string $encryptedData, ?string $readableTime = null, ?string $timezone = null): void
    {
        $firstError = null;

        foreach ($this->passwordManagers as $manager) {
            $result = $manager->decryptPassword($encryptedData, $readableTime, $timezone);

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
