<?php
// Time-locked Password Management System
// Simple and clean architecture

// Prevent caching - always fetch from server
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Set timezone to UTC (all server-side processing is based on UTC)
date_default_timezone_set('UTC');

// File paths and configuration
$baseDir = __DIR__;
$passwordManagerPath = 'src/PasswordManager.php';
$translationPath = 'translation/Translation.php';
$secretsPath = 'secrets.php';
$secretsExamplePath = 'secrets.example.php';
$cssPath = 'assets/style.css';
$jsPath = 'assets/script.js';
$iconPath = 'assets/calendar.svg';

// Get file modification times for cache busting
$cssVersion = filemtime($baseDir . '/' . $cssPath);
$jsVersion = filemtime($baseDir . '/' . $jsPath);

require_once $passwordManagerPath;
require_once $translationPath;

$t = Translation::getObject();

// Load secrets
if (file_exists($baseDir . '/' . $secretsPath)) {
    require_once $secretsPath;
} else {
    require_once $secretsExamplePath;
}
$hkdfKey = Secrets::HKDF_KEY;
$opensslKey = Secrets::OPENSSL_KEY;

$passwordManager = new PasswordManager($hkdfKey, $opensslKey);

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if ($_POST['action'] === 'generate' && isset($_POST['datetime'])) {
        try {
            // Process datetime sent from client as UTC
            $unlockDateTime = new DateTime($_POST['datetime'], new DateTimeZone('UTC'));
            $password = $passwordManager->generateRandomPassword();
            $encryptedData = $passwordManager->encryptPassword($password, $unlockDateTime->format('Y-m-d H:i:s'));

            echo json_encode([
                'password' => $password,
                'encrypted_data' => $encryptedData,
                'unlock_time' => $unlockDateTime->format('Y-m-d\TH:i:s\Z'),
                'decrypt_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/?data=' . $encryptedData
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
            // Keep message concise as UTC time will be converted on client side
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

function h(string $str): string
{
    return htmlspecialchars($str);
}

?>
<!DOCTYPE html>
<html lang="<?php echo $t->getLanguageCode(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo h($t->metaDescription); ?>">

    <!-- Open Graph tags -->
    <meta property="og:title" content="<?php echo h($t->ogTitle); ?>">
    <meta property="og:description" content="<?php echo h($t->ogDescription); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>">
    <meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/assets/ogp.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/png">

    <!-- Twitter Card tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($t->twitterTitle); ?>">
    <meta name="twitter:description" content="<?php echo h($t->twitterDescription); ?>">
    <meta name="twitter:image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/assets/ogp.png">

    <title><?php echo h($t->ogTitle); ?></title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>?v=<?php echo $cssVersion; ?>">
</head>

<body>
    <div class="container">
        <h1><a href="/" id="pageTitle"><?php echo h($t->pageTitle); ?></a></h1>

        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>" id="messageDiv" data-unlock-time="<?php echo isset($unlockTimeUTC) ? $unlockTimeUTC : ''; ?>">
                <span id="messageText"><?php echo h($message); ?></span>
                <?php if (isset($unlockTimeUTC) && $messageType === 'error'): ?>
                    <div id="unlockTimeDisplay" style="margin-top: 10px; font-size: 14px;"></div>
                <?php endif; ?>
            </div>
            <?php if ($messageType === 'success' && isset($decryptedPassword)): ?>
                <button class="copy-btn" onclick="copyToClipboard('<?php echo h($decryptedPassword); ?>')"><?php echo h($t->copyButton); ?></button>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!isset($message)): ?>
            <?php
            // Get current UTC time (to be converted on client side)
            $currentDateTime = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
            // Calculate maximum date (3 months from now) for security
            $maxDateTime = (new DateTime('now', new DateTimeZone('UTC')))->add(new DateInterval('P3M'))->format('Y-m-d\TH:i:s');
            ?>
            <form id="passwordForm">
                <div class="form-group">
                    <label for="datetime"><?php echo h($t->unlockLabel); ?> <span style="color: #8b949e; font-weight: normal;"><?php echo h($t->localTimeNote); ?></span>:</label>
                    <div class="datetime-wrapper">
                        <input type="datetime-local" id="datetime" name="datetime" required max="<?php echo $maxDateTime; ?>" onclick="this.showPicker()" data-utc-now="<?php echo $currentDateTime; ?>">
                        <button type="button" class="calendar-btn" onclick="document.getElementById('datetime').showPicker()">
                            <img src="<?php echo $iconPath; ?>" alt="Calendar" width="20" height="20">
                        </button>
                    </div>
                </div>
                <button type="submit"><?php echo h($t->generateButton); ?></button>
            </form>
        <?php endif; ?>

        <div id="result"></div>
    </div>

    <footer>
        <div class="footer-content">
            <p id="serviceDescription"><?php echo h($t->serviceDescription); ?></p>
            <ul id="serviceFeatures">
                <li><?php echo h($t->feature1); ?></li>
                <li><?php echo h($t->feature2); ?></li>
            </ul>
            <div class="footer-links">
                <a href="https://github.com/mimimiku778/time-locked-password-php" target="_blank" rel="noopener noreferrer">GitHub</a>
                <span>MIT License</span>
            </div>
            <p style="font-size: 12px; color: #6b7280; margin-top: 10px;">
                Experimental tool. No warranty. Use at your own risk. Do not use for critical data.
            </p>
        </div>
    </footer>

    <script src="<?php echo $jsPath; ?>?v=<?php echo $jsVersion; ?>"></script>
</body>

</html>