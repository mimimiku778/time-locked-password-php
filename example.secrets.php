<?php
// Security configuration template
// Copy this file to config.php and generate your own keys with: openssl rand -hex 32

class Secrets
{
    public const HKDF_KEY = 'your-secret-hkdf-key-here-replace';
    public const OPENSSL_KEY = 'your-secret-openssl-key-here-replace';
    // Google Analytics 4 Measurement ID (e.g., G-XXXXXXXXXX). Keep empty to disable.
    public const GA4_ID = '';
}