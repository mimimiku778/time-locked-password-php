# Password Generator That Shows Passwords at Scheduled Time

## Overview
A simple web service that generates passwords that cannot be decrypted until a specified date and time.

## Production Site
Production (long-term operated) URL: https://password.openchat-review.me  
This is the stable production deployment (not just a demo).

## How to Use
1. **Password Generation**
   - Specify the date and time when decryption becomes available
   - System generates a random password (16 characters)
   - The generated password is shown ONLY ONCE at this moment (please copy and store it now)
   - An encrypted URL is issued that can later reveal the password after the unlock time
   - Note: If you close the page now without copying, you cannot see the password again until the scheduled unlock time
2. **Password Decryption**
   - Access the generated URL
   - Before the specified date/time: the password cannot be displayed
   - After the specified date/time: the password is revealed again

## Features
- **No Database Required**: All information is contained in the encrypted URL parameters
- **Time Restriction**: Cannot be decrypted before the specified date and time
- **Maximum Duration Limit**: Future unlock times are limited to 3 months maximum for security and practical reasons
- **Encryption**: AES-256-CBC encryption
- **Tamper Protection**: Integrity guaranteed by HMAC-SHA256
- **GA4 Privacy**: Query parameters are masked in analytics (e.g., `?data=sensitive123` → `?data=data`)
- **Auto Language Detection**: UI automatically switches English/Japanese according to the browser language (currently en / ja implemented)

## Use Cases
- **Contest/Event Reveals**: Generate passwords for contest results or surprise announcements that unlock at predetermined times
- **Self-Imposed Access Restriction**: Set this generated password as your account password (without saving it), effectively restricting your own access to social media or other services until a specific date

## Development Setup & Local Usage

### Requirements
- Docker and Docker Compose
- OR PHP 8.0+ with Apache

### Quick Start with Docker (Recommended)
1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/time-locked-password-php.git
   cd time-locked-password-php
   ```

2. **Run with Docker Compose**
   ```bash
   docker compose up -d
   ```
   Then open http://localhost:10000 in your browser
   
   To use a different port:
   ```bash
   PORT=8080 docker compose up -d
   ```

### Deployment

```bash
# Clone repository to document root
cd /var/www/html  # or your web server's document root
git clone https://github.com/yourusername/time-locked-password-php.git .

# Create configuration file
cp src/config/example.secrets.php src/config/secrets.php

# Generate and set encryption keys
HKDF_KEY=$(openssl rand -hex 32)
OPENSSL_KEY=$(openssl rand -hex 32)
sed -i "s/your-secret-hkdf-key-here-replace/$HKDF_KEY/" src/config/secrets.php
sed -i "s/your-secret-openssl-key-here-replace/$OPENSSL_KEY/" src/config/secrets.php

# For Google Analytics 4 (optional)
# sed -i "s/private const string GA4_ID = '';/private const string GA4_ID = 'G-XXXXXXXXXX';/" src/config/secrets.php
```

---

# タイムロック式パスワードジェネレータ
## 概要
指定された日時まで復号できないパスワードを生成するシンプルなWebサービスです。

## 本番サイト
本番稼働URL（長期運用予定）: https://password.openchat-review.me  
デモではなく安定運用中の本番環境です。

## 使用方法
1. **パスワード生成**
   - 復号が可能になる日時を指定
   - システムがランダムパスワード（16文字）を生成
   - このタイミングでパスワードは「一度だけ」表示されます（必ずここでコピーしてください）
   - 後で指定時刻以降に再表示できる暗号化URLが発行される
   - ここでページを閉じたりコピーし忘れた場合、指定時刻を過ぎるまで再確認できません
2. **パスワード復号**
   - 生成されたURLにアクセス
   - 指定日時前は表示不可（ロック継続）
   - 指定日時を過ぎると再度パスワードが表示される

## 特徴
- **データベース不要**: すべての情報が暗号化されたURLパラメータに含まれる
- **時間制限**: 指定された日時前には復号できない
- **最大期間制限**: セキュリティと実用性の理由により、将来のアンロック時間は最大3ヶ月に制限されます
- **暗号化**: AES-256-CBC暗号化
- **改ざん防止**: HMAC-SHA256による完全性保証
- **GA4プライバシー**: クエリパラメータをアナリティクスでマスク（例：`?data=sensitive123` → `?data=data`）
- **自動言語判別**: ブラウザの言語設定に応じて英語 / 日本語へ自動切替（現状 en / ja 対応）

## 活用例
- **コンテスト・イベント発表**: 予め決められた時刻にコンテスト結果やサプライズ発表を行うパスワード生成
- **自主的アクセス制限**: 生成されたパスワードをアカウントパスワードに設定し（保存せずに）、特定の日付まで自らのSNSなどへのアクセスを制限

## 開発環境構築・ローカル利用方法

### 必要要件
- DockerとDocker Compose
- またはPHP 8.0以上とApache

### Dockerでクイックスタート（推奨）
1. **リポジトリをクローン**
   ```bash
   git clone https://github.com/yourusername/time-locked-password-php.git
   cd time-locked-password-php
   ```

2. **Docker Composeで実行**
   ```bash
   docker compose up -d
   ```
   ブラウザで http://localhost:10000 を開く
   
   別のポートを使用する場合：
   ```bash
   PORT=8080 docker compose up -d
   ```

### デプロイ

```bash
# ドキュメントルート直下でリポジトリをクローン
cd /var/www/html  # または、あなたのWebサーバーのドキュメントルート
git clone https://github.com/yourusername/time-locked-password-php.git .

# 設定ファイルの作成
cp src/config/example.secrets.php src/config/secrets.php

# 暗号化キーの生成と設定ファイルへの書き込み
HKDF_KEY=$(openssl rand -hex 32)
OPENSSL_KEY=$(openssl rand -hex 32)
sed -i "s/your-secret-hkdf-key-here-replace/$HKDF_KEY/" src/config/secrets.php
sed -i "s/your-secret-openssl-key-here-replace/$OPENSSL_KEY/" src/config/secrets.php

# Google Analytics 4を使用する場合（オプション）
# sed -i "s/private const string GA4_ID = '';/private const string GA4_ID = 'G-XXXXXXXXXX';/" src/config/secrets.php
```
