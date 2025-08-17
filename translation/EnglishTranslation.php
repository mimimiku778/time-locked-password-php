<?php

require_once __DIR__ . '/TranslationObject.php';

class EnglishTranslation extends TranslationObject {
    public function __construct() {
        $this->metaDescription = 'Generate time-locked passwords that decrypt only at your specified time. Perfect for scheduled releases and time-sensitive access control.';
        $this->pageTitle = '🔒 Password Generator That Shows Passwords at Scheduled Time';
        $this->ogTitle = 'Password Generator That Shows Passwords at Scheduled Time';
        $this->ogDescription = 'Generate time-locked passwords that decrypt only at your specified time. Perfect for scheduled releases and time-sensitive access control.';
        $this->twitterTitle = 'Password Generator That Shows Passwords at Scheduled Time';
        $this->twitterDescription = 'Generate time-locked passwords that decrypt only at your specified time. Perfect for scheduled releases and time-sensitive access control.';
        $this->unlockLabel = 'Unlock Date & Time';
        $this->localTimeNote = '(your local time)';
        $this->generateButton = 'Generate Password';
        $this->copyButton = 'Copy Password';
        $this->copyUrlButton = 'Copy URL';
        $this->generatedPasswordLabel = 'Generated Password:';
        $this->decryptUrlLabel = 'Decrypt URL:';
        $this->unlockTimeLabel = 'Unlock time:';
        $this->serviceDescription = 'Generate time-locked passwords that decrypt only at your specified time. Perfect for scheduled releases and time-sensitive access control.';
        $this->feature1 = 'Password is shown once when generated';
        $this->feature2 = 'Access Decrypt URL after scheduled time to view password again';
    }
    
    public function getLanguageCode(): string {
        return 'en';
    }
}