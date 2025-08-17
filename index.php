<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
date_default_timezone_set('UTC');

// Load secrets for API processing
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once 'secrets.php';
} else {
    require_once 'example.secrets.php';
}

require_once 'src/PasswordManager.php';

// Handle API requests
(function () {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_POST['action'] === 'generate' && isset($_POST['datetime'])) {
            try {
                $passwordManager = new PasswordManager(Secrets::HKDF_KEY, Secrets::OPENSSL_KEY);
                
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
            } catch (Exception) {
                echo json_encode(['error' => 'Invalid datetime format']);
            }
        }
        exit;
    }
})();

require_once 'src/ViewConfiguration.php';
require_once 'src/ViewState.php';
require_once 'translation/Translation.php';
require_once 'src/Tracking.php';

function h(string $str): string
{
    return htmlspecialchars($str);
}

// Initialize configuration and state
$config = new ViewConfiguration();

$state = new ViewState(
    [
        new PasswordManager(Secrets::HKDF_KEY, Secrets::OPENSSL_KEY),
        // Fallback password manager
        new PasswordManager('your-secret-hkdf-key-here-replace', 'your-secret-openssl-key-here-replace')
    ]
);

$t = Translation::getObject();

$state->handleDecryption($_GET['data'] ?? null);

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
    <link rel="stylesheet" href="<?php echo ViewConfiguration::CSS_PATH; ?>?v=<?php echo $config->cssVersion; ?>">
    <?php echo Tracking::renderGA(Secrets::GA4_ID ?? ''); ?>
</head>

<body>
    <div class="container">
        <h1><a href="/" id="pageTitle"><?php echo h($t->pageTitle); ?></a></h1>

        <?php if ($state->hasMessage()): ?>
            <div class="message <?php echo $state->messageType; ?>" id="messageDiv" data-unlock-time="<?php echo $state->unlockTimeUTC ?? ''; ?>">
                <span id="messageText"><?php echo h($state->message); ?></span>
                <?php if ($state->unlockTimeUTC && $state->messageType === 'error'): ?>
                    <div id="unlockTimeDisplay" class="unlock-time-display"></div>
                <?php endif; ?>
            </div>
            <?php if ($state->messageType === 'success' && $state->decryptedPassword): ?>
                <button class="copy-btn" onclick="copyToClipboard('<?php echo h($state->decryptedPassword); ?>')"><?php echo h($t->copyButton); ?></button>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$state->hasMessage()): ?>
            <form id="passwordForm">
                <div class="form-group">
                    <label for="datetime"><?php echo h($t->unlockLabel); ?> <span class="label-note"><?php echo h($t->localTimeNote); ?></span>:</label>
                    <div class="datetime-wrapper">
                        <input type="datetime-local" id="datetime" name="datetime" required max="<?php echo $config->maxDateTime; ?>" onclick="this.showPicker()" data-utc-now="<?php echo $config->currentDateTime; ?>">
                        <button type="button" class="calendar-btn" onclick="document.getElementById('datetime').showPicker()">
                            <img src="<?php echo ViewConfiguration::ICON_PATH; ?>" alt="Calendar" width="20" height="20">
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
                <a href="https://github.com/mimimiku778/time-locked-password-php" target="_blank">GitHub</a>
                <span>MIT License</span>
            </div>
            <p class="footer-disclaimer">
                Experimental tool. No warranty. Use at your own risk. Do not use for critical data.
            </p>
        </div>
    </footer>

    <script src="<?php echo ViewConfiguration::JS_PATH; ?>?v=<?php echo $config->jsVersion; ?>"></script>
</body>

</html>