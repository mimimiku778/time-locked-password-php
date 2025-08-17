<?php
// Security configuration template
// Copy this file to config.php and generate your own keys with: openssl rand -hex 32

class Secrets
{
    private const string HKDF_KEY = 'your-secret-hkdf-key-here-replace';
    private const string OPENSSL_KEY = 'your-secret-openssl-key-here-replace';
    // Google Analytics 4 Measurement ID (e.g., G-XXXXXXXXXX). Keep empty to disable.
    private const string GA4_ID = '';

    /**
     * Return class constant value if the constant exists; otherwise return default value.
     */
    public static function get(string $const, string|null $default = null): string|null
    {
        $constFqn = self::class . '::' . $const;
        if (!defined($constFqn)) {
            return $default;
        }
        return constant($constFqn);
    }
}
