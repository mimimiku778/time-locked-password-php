<?php

/**
 * ViewConfiguration object to manage all application settings for the view layer
 */
class ViewConfiguration
{
    public const string CSS_PATH = 'assets/style.css';
    public const string JS_PATH = 'assets/script.js';
    public const string ICON_PATH = 'assets/calendar.svg';

    public readonly int $cssVersion;
    public readonly int $jsVersion;
    public readonly string $currentDateTime;
    public readonly string $maxDateTime;

    public function __construct()
    {
        // Get file modification times for cache busting
        $this->cssVersion = filemtime(__DIR__ . '/../' . self::CSS_PATH);
        $this->jsVersion = filemtime(__DIR__ . '/../' . self::JS_PATH);

        // Get current UTC time (to be converted on client side)
        $this->currentDateTime = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
        // Calculate maximum date (3 months from now) for security
        $this->maxDateTime = (new DateTime('now', new DateTimeZone('UTC')))->add(new DateInterval('P3M'))->format('Y-m-d\TH:i:s');
    }
}