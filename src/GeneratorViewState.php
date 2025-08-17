<?php

/**
 * GeneratorViewState object to manage password generation state for the view layer
 */
class GeneratorViewState
{
    public ?string $generatedPassword = null;
    public ?string $encryptedData = null;
    public ?string $unlockTimeUTC = null;
    public ?string $decryptUrl = null;
    public ?string $errorMessage = null;

    /**
     * @param PasswordManager $passwordManager Password manager instance used for generation
     */
    public function __construct(
        private PasswordManager $passwordManager
    ) {}

    /**
     * Handle password generation from form submission
     */
    public function handleGeneration(?string $action, ?string $datetime, ?string $timezone, ?string $httpHost): void
    {
        if (!$action || $action !== 'generate' || !$datetime || !$httpHost) {
            $this->errorMessage = 'Invalid request';
            return;
        }

        try {
            // Convert local datetime to UTC
            $localDateTime = new DateTime($datetime, new DateTimeZone($timezone ?? 'UTC'));
            $localDateTime->setTimezone(new DateTimeZone('UTC'));

            $this->generatedPassword = $this->passwordManager->generateRandomPassword();
            $this->encryptedData = $this->passwordManager->encryptPassword(
                $this->generatedPassword,
                $localDateTime->format('Y-m-d H:i:s')
            );
            $this->unlockTimeUTC = $localDateTime->format('Y-m-d\TH:i:s\Z');
            $this->decryptUrl = 'http://' . $httpHost . '/?data=' . $this->encryptedData;
        } catch (Exception) {
            $this->errorMessage = 'Invalid datetime format';
        }
    }

    /**
     * Check if generation was successful
     */
    public function isGenerated(): bool
    {
        return $this->generatedPassword !== null;
    }

    /**
     * Check if there was an error
     */
    public function hasError(): bool
    {
        return $this->errorMessage !== null;
    }
}
