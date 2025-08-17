<?php

require_once 'TimeUtility.php';

/**
 * GeneratorViewState object to manage password generation state for the view layer
 */
class GeneratorViewState
{
    /**
     * @var string|null The generated password, or null if generation failed
     */
    public ?string $generatedPassword = null;

    /**
     * @var string|null UTC timestamp when password becomes decryptable
     */
    public ?string $unlockTimeUTC = null;

    /**
     * @var string|null Query parameters for decryption URL
     */
    public ?string $decryptParams = null;

    /**
     * @var string|null Error message from generation process
     */
    public ?string $errorMessage = null;

    /**
     * @param PasswordManager $passwordManager Password manager instance used for generation
     */
    public function __construct(
        private PasswordManager $passwordManager
    ) {}

    /**
     * Handle password generation from form submission
     *
     * @param string|null $action Form action parameter
     * @param string|null $datetime Local datetime string
     * @param string|null $timezone Timezone identifier
     */
    public function handleGeneration(?string $action, ?string $datetime, ?string $timezone): void
    {
        if (!$action || $action !== 'generate' || !$datetime) {
            $this->errorMessage = 'Invalid request';
            return;
        }

        try {
            $this->generatedPassword = $this->passwordManager->generateRandomPassword();

            // Get UTC time for storage and internal use
            $this->unlockTimeUTC = TimeUtility::convertLocalToUTCForHashing($datetime, $timezone ?? 'UTC');

            $params = [
                // Use original local datetime for readable URL format
                'unlock' => TimeUtility::convertLocalToReadableFormat($datetime),
                'tz' => $timezone ?? 'UTC',
                'data' => $this->passwordManager->encryptPassword(
                    $this->generatedPassword,
                    $datetime,
                    $timezone ?? 'UTC'
                )
            ];
            
            $this->decryptParams = '?' . http_build_query($params);
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
