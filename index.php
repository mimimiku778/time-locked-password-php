<?php
// Time-locked Password Management System
// Simple and clean architecture

// タイムゾーンをUTCに設定（サーバー側処理は全てUTC基準）
date_default_timezone_set('UTC');

require_once 'PasswordManager.php';

// Configuration - in production, use environment variables
$hkdfKey = hash('sha256', $_ENV['HKDF_KEY'] ?? 'your-secret-hkdf-key-here');
$opensslKey = hash('sha256', $_ENV['OPENSSL_KEY'] ?? 'your-secret-openssl-key-here');

$passwordManager = new PasswordManager($hkdfKey, $opensslKey);

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_POST['action'] === 'generate' && isset($_POST['datetime'])) {
        try {
            // クライアントから送信された日時をUTCとして処理
            $unlockDateTime = new DateTime($_POST['datetime'], new DateTimeZone('UTC'));
            $password = $passwordManager->generateRandomPassword();
            $encryptedData = $passwordManager->encryptPassword($password, $unlockDateTime->format('Y-m-d H:i:s'));
            
            echo json_encode([
                'password' => $password,
                'encrypted_data' => $encryptedData,
                'unlock_time' => $unlockDateTime->format('Y-m-d\TH:i:s\Z'),
                'decrypt_url' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?data=' . $encryptedData
            ]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Invalid datetime format']);
        }
    }
    exit;
}

// Handle password decryption
if (isset($_GET['data'])) {
    $result = $passwordManager->decryptPassword($_GET['data']);
    
    if (isset($result['error'])) {
        if (isset($result['unlock_time'])) {
            // UTC時刻をクライアント側で変換するため、メッセージは簡潔に
            $message = $result['error'];
            $unlockTimeUTC = $result['unlock_time'];
        } else {
            $message = $result['error'];
        }
        $messageType = 'error';
    } else {
        $decryptedPassword = $result['password'];
        $message = 'Password: ' . $decryptedPassword;
        $messageType = 'success';
        $unlockTimeUTC = $result['unlock_time'] ?? null;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time-locked Password Service</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #0d1117;
            color: #c9d1d9;
        }
        .container {
            background: #161b22;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            border: 1px solid #30363d;
        }
        h1 {
            color: #f0f6fc;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #c9d1d9;
        }
        input[type="datetime-local"], input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #30363d;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            background-color: #21262d;
            color: #c9d1d9;
            cursor: pointer;
        }
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            display: none;
        }
        input[type="datetime-local"]::-webkit-inner-spin-button {
            display: none;
        }
        input[type="datetime-local"]:focus, input[type="text"]:focus {
            outline: none;
            border-color: #58a6ff;
        }
        .datetime-wrapper {
            position: relative;
            width: 100%;
        }
        .calendar-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            padding: 4px;
            cursor: pointer;
            width: auto;
            color: #8b949e;
            transition: color 0.2s;
        }
        .calendar-btn:hover {
            background: transparent;
            color: #c9d1d9;
        }
        .calendar-btn svg {
            width: 20px;
            height: 20px;
        }
        button {
            background: #238636;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.2s;
        }
        button:hover {
            background: #2ea043;
        }
        #result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .success {
            background: #0d2818;
            border: 1px solid #238636;
            color: #3fb950;
        }
        .error {
            background: #490202;
            border: 1px solid #f85149;
            color: #ff7b72;
        }
        .url-box {
            background: #0d1117;
            border: 1px solid #30363d;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            word-break: break-all;
            font-family: monospace;
            color: #58a6ff;
        }
        .copy-btn {
            background: #1f6feb;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 5px;
            width: auto;
        }
        .copy-btn:hover {
            background: #58a6ff;
        }
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .debug {
            color: #8b949e;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Time-locked Password Generator</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>" id="messageDiv" data-unlock-time="<?php echo isset($unlockTimeUTC) ? $unlockTimeUTC : ''; ?>">
                <span id="messageText"><?php echo htmlspecialchars($message); ?></span>
                <?php if (isset($unlockTimeUTC) && $messageType === 'error'): ?>
                    <div id="unlockTimeDisplay" style="margin-top: 10px; font-size: 14px;"></div>
                <?php endif; ?>
                <?php if ($messageType === 'success' && isset($decryptedPassword)): ?>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($decryptedPassword); ?>')">Copy Password</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!isset($message)): ?>
            <?php
                // 現在のUTC時刻を取得（クライアント側で変換）
                $currentDateTime = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
            ?>
            <form id="passwordForm">
                <div class="form-group">
                    <label for="datetime">Unlock Date & Time <span style="color: #8b949e; font-weight: normal;">(your local time)</span>:</label>
                    <div class="datetime-wrapper">
                        <input type="datetime-local" id="datetime" name="datetime" required onclick="this.showPicker()" data-utc-now="<?php echo $currentDateTime; ?>">
                        <button type="button" class="calendar-btn" onclick="document.getElementById('datetime').showPicker()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit">Generate Password</button>
            </form>
        <?php endif; ?>
        
        <div id="result"></div>
    </div>

    <script>
        // UTC時刻をローカル時刻に変換する関数（ロケールに応じた形式）
        function convertUTCToLocal(utcDateTimeString) {
            const utcDate = new Date(utcDateTimeString);
            // ブラウザのロケールに応じた日時表示
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'long',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            // 日本語環境では「2025年8月19日 火曜日 21:57:00」のような形式になる
            return utcDate.toLocaleString(undefined, options);
        }
        
        // ローカル時刻をUTCに変換する関数
        function convertLocalToUTC(localDateTimeString) {
            const localDate = new Date(localDateTimeString);
            return localDate.toISOString();
        }
        
        // datetime-local入力フィールドの最小値を設定
        document.addEventListener('DOMContentLoaded', function() {
            const datetimeInput = document.getElementById('datetime');
            if (datetimeInput) {
                // UTC時刻を取得してローカル時刻に変換
                const utcNow = datetimeInput.getAttribute('data-utc-now');
                const now = new Date(utcNow);
                
                // datetime-local形式に変換（YYYY-MM-DDTHH:MM）
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                
                const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                datetimeInput.setAttribute('min', localDateTime);
            }
            
            // メッセージの時刻をローカル形式に変換
            const messageDiv = document.getElementById('messageDiv');
            if (messageDiv) {
                const unlockTime = messageDiv.getAttribute('data-unlock-time');
                if (unlockTime) {
                    // UTC時刻をローカル形式に変換
                    const localUnlockTime = convertUTCToLocal(unlockTime);
                    
                    // エラーメッセージの場合、unlock時刻を表示
                    const unlockTimeDisplay = document.getElementById('unlockTimeDisplay');
                    if (unlockTimeDisplay) {
                        unlockTimeDisplay.innerHTML = `Unlock time: <strong>${localUnlockTime}</strong>`;
                    }
                }
            }
        });
        
        document.getElementById('passwordForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // ローカル時刻をUTCに変換してサーバーに送信
            const localDateTime = document.getElementById('datetime').value;
            const utcDateTime = convertLocalToUTC(localDateTime);
            
            const formData = new FormData();
            formData.append('action', 'generate');
            formData.append('datetime', utcDateTime);
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                const resultDiv = document.getElementById('result');
                
                if (data.error) {
                    resultDiv.className = 'error';
                    resultDiv.innerHTML = data.error;
                } else {
                    // UTC時刻をローカル時刻に変換して表示
                    const localUnlockTime = convertUTCToLocal(data.unlock_time);
                    
                    resultDiv.className = 'success';
                    resultDiv.innerHTML = `
                        <strong>Generated Password:</strong><br>
                        <div class="url-box">${data.password}
                            <button class="copy-btn" onclick="copyToClipboard('${data.password}')">Copy Password</button>
                        </div>
                        
                        <strong>Decrypt URL:</strong><br>
                        <div class="url-box">${data.decrypt_url}
                            <button class="copy-btn" onclick="copyToClipboard('${data.decrypt_url}')">Copy URL</button>
                        </div>
                        
                        <small>Unlock time: ${localUnlockTime}</small>
                    `;
                }
                
                resultDiv.style.display = 'block';
            } catch (error) {
                const resultDiv = document.getElementById('result');
                resultDiv.className = 'error';
                resultDiv.innerHTML = 'An error occurred';
                resultDiv.style.display = 'block';
            }
        });
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied to clipboard');
            });
        }
    </script>
</body>
</html>
