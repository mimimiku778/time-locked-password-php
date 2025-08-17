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
    public static function renderGA(string $measurementId): string
    {
        $id = htmlspecialchars($measurementId, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$id}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    
    // Mask query parameter values for privacy
    const url = new URL(window.location.href);
    const maskedParams = Array.from(url.searchParams.keys())
        .map(key => `\${key}=\${key}`)
        .join('&');
    const maskedUrl = url.origin + url.pathname + (maskedParams ? '?' + maskedParams : '');
    
    // Append _data to page title if data parameter exists
    const hasDataParam = url.searchParams.has('data');
    const pageTitle = document.title + (hasDataParam ? '_data' : '');
    
    gtag('config', '{$id}', {
        'page_location': maskedUrl,
        'page_title': pageTitle,
        'send_page_view': true
    });
</script>
HTML;
    }

    // Example placeholder for an ad service tag (not implemented yet):
    // public static function renderAdService(string $publisherId): string { ... }
}
