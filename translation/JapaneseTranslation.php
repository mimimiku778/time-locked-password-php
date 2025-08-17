<?php

require_once __DIR__ . '/TranslationObject.php';

class JapaneseTranslation extends TranslationObject {
    public function __construct() {
        $this->metaDescription = '指定した時刻以降に復号される時間ロック付きパスワードを生成。予定リリース、指定時間までのアクセス制御に最適。';
        $this->pageTitle = '🔒 タイムロック式パスワードジェネレータ';
        $this->ogTitle = 'タイムロック式パスワードジェネレータ';
        $this->ogDescription = '指定した時刻以降に復号される時間ロック付きパスワードを生成。予定リリース、指定時間までのアクセス制御に最適。';
        $this->twitterTitle = 'タイムロック式パスワードジェネレータ';
        $this->twitterDescription = '指定した時刻以降に復号される時間ロック付きパスワードを生成。予定リリース、指定時間までのアクセス制御に最適。';
        $this->unlockLabel = 'ロック解除日時';
        $this->localTimeNote = '(現地時間)';
        $this->generateButton = 'パスワード生成';
        $this->copyButton = 'パスワードをコピー';
        $this->copyUrlButton = 'URLをコピー';
        $this->generatedPasswordLabel = '生成されたパスワード:';
        $this->decryptUrlLabel = 'Decrypt URL:';
        $this->unlockTimeLabel = 'ロック解除時刻:';
        $this->serviceDescription = '指定した時刻以降に復号される時間ロック付きパスワードを生成。予定リリース、指定時間までのアクセス制御に最適。';
        $this->feature1 = 'パスワードは生成時に一度だけ表示';
        $this->feature2 = 'Decrypt URLに指定時刻以降にアクセスすると再びパスワードを表示';
    }
    
    public function getLanguageCode(): string {
        return 'ja';
    }
}