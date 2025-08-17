<?php

/**
 * Central helper for site tracking / advertising / external tag snippets.
 * Currently only Google Analytics 4 is implemented. Add new static methods for other services.
 */
class Tracking
{
    /**
     * Returns the GA4 tag snippet. If the measurement ID is empty or null, returns an empty string.
     */
    public static function renderGA(?string $measurementId): string
    {
        if (!$measurementId) {
            return '';
        }
        $id = htmlspecialchars($measurementId, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$id}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{$id}');
</script>
HTML;
    }

    // Example placeholder for an ad service tag (not implemented yet):
    // public static function renderAdService(string $publisherId): string { ... }
}
