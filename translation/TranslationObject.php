<?php

abstract class TranslationObject {
    public string $metaDescription;
    public string $pageTitle;
    public string $ogTitle;
    public string $ogDescription;
    public string $twitterTitle;
    public string $twitterDescription;
    public string $unlockLabel;
    public string $localTimeNote;
    public string $generateButton;
    public string $copyButton;
    public string $serviceDescription;
    public string $feature1;
    public string $feature2;
    
    abstract public function getLanguageCode(): string;
}