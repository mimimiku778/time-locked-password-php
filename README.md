# Time-Locked Password Generator Service

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
- **Encryption**: AES-256-CBC encryption
- **Tamper Protection**: Integrity guaranteed by HMAC-SHA256

## Components
- **PasswordManager.php**: Password generation, encryption, and decryption
- **StringCryptor.php**: Encryption processing
- **index.php**: Web interface
