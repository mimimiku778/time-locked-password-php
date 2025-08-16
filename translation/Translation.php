<?php

require_once __DIR__ . '/EnglishTranslation.php';
require_once __DIR__ . '/JapaneseTranslation.php';

class Translation {
    private const DEFAULT_LANGUAGE = 'en';
    private const LANGUAGE_MAP = [
        'ja' => JapaneseTranslation::class,
        'en' => EnglishTranslation::class,
    ];

    private static function detectLanguage(): string {
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        foreach (array_keys(self::LANGUAGE_MAP) as $lang) {
            if (strpos($acceptLanguage, $lang) !== false) {
                return $lang;
            }
        }
        
        return self::DEFAULT_LANGUAGE;
    }

    public static function getObject(): TranslationObject {
        $language = self::detectLanguage();
        $className = self::LANGUAGE_MAP[$language];
        return new $className();
    }
}