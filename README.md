# Password Generator That Shows Passwords at Scheduled Time

## Overview
A simple web service that generates passwords that cannot be decrypted until a specified date and time.

## How to Use
1. **Password Generation**
   - Specify the date and time when decryption becomes available
   - System generates a random password (16 characters)
   - An encrypted URL is issued

2. **Password Decryption**
   - Access the generated URL
   - Password is automatically displayed after the specified date and time

## Features
- **No Database Required**: All information is contained in the encrypted string
- **Time Restriction**: Cannot be decrypted before the specified date and time
- **Maximum Duration Limit**: Future unlock times are limited to 3 months maximum for security and practical reasons
- **Encryption**: AES-256-CBC encryption
- **Tamper Protection**: Integrity guaranteed by HMAC-SHA256

## Use Cases
- **Contest/Event Reveals**: Generate passwords for contest results or surprise announcements that unlock at predetermined times
- **Self-Imposed Access Restriction**: Set this generated password as your account password (without saving it), effectively restricting your own access to social media or other services until a specific date
- **Planned System Maintenance**: Create temporary passwords that become active only during scheduled maintenance windows

## Components
- **PasswordManager.php**: Password generation, encryption, and decryption
- **StringCryptor.php**: Encryption processing
- **index.php**: Web interface

---

# 時間になったら見れるパスワード生成サービス

## 概要
指定された日時まで復号できないパスワードを生成するシンプルなWebサービスです。

## 使用方法
1. **パスワード生成**
   - 復号が可能になる日時を指定
   - システムがランダムパスワード（16文字）を生成
   - 暗号化されたURLが発行される

2. **パスワード復号**
   - 生成されたURLにアクセス
   - 指定された日時を過ぎると自動的にパスワードが表示される

## 特徴
- **データベース不要**: すべての情報が暗号化された文字列に含まれる
- **時間制限**: 指定された日時前には復号できない
- **最大期間制限**: セキュリティと実用性の理由により、将来のアンロック時間は最大3ヶ月に制限されます
- **暗号化**: AES-256-CBC暗号化
- **改ざん防止**: HMAC-SHA256による完全性保証

## 活用例
- **コンテスト・イベント発表**: 予め決められた時刻にコンテスト結果やサプライズ発表を行うパスワード生成
- **自主的アクセス制限**: 生成されたパスワードをアカウントパスワードに設定し（保存せずに）、特定の日付まで自らのSNSなどへのアクセスを制限
- **計画的システムメンテナンス**: 予定されたメンテナンス時間中にのみ有効になる一時パスワードの作成

## 構成要素
- **PasswordManager.php**: パスワード生成、暗号化、復号
- **StringCryptor.php**: 暗号化処理
- **index.php**: Webインターフェース
