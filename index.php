<?php
header('Cache-Control: no-store');
date_default_timezone_set('UTC');

// Load secrets class
if (file_exists(__DIR__ . '/src/config/secrets.php')) {
    require_once 'src/config/secrets.php';
} else {
    require_once 'src/config/example.secrets.php';
}

// Load required classes
require_once 'src/PasswordManager.php';
require_once 'src/ViewConfiguration.php';
require_once 'src/ViewState.php';
require_once 'src/GeneratorViewState.php';
require_once 'src/translation/Translation.php';
require_once 'src/Tracking.php';

// Escape HTML special characters
function h(string $str): string
{
    return htmlspecialchars($str);
}

// Initialize configuration and state
$config = new ViewConfiguration();

// Initialize view state
$state = new ViewState(
    [
        new PasswordManager(Secrets::get('HKDF_KEY'), Secrets::get('OPENSSL_KEY')),
        // Fallback password manager
        new PasswordManager('your-secret-hkdf-key-here-replace', 'your-secret-openssl-key-here-replace'),
    ]
);

// Initialize generator state
$generatorState = new GeneratorViewState(
    new PasswordManager(Secrets::get('HKDF_KEY'), Secrets::get('OPENSSL_KEY'))
);

// Get translation object
$t = Translation::getObject();

// Handle requests
switch ($_SERVER['REQUEST_METHOD'] ?? null) {
    case 'POST':
        $generatorState->handleGeneration(
            $_POST['action'] ?? null,
            $_POST['datetime'] ?? null,
            $_POST['timezone'] ?? null,
            $_SERVER['HTTP_HOST'] ?? null,
        );
        break;
    case 'GET':
        if (!empty($_GET['data'])) {
            $state->handleDecryption($_GET['data'], $_GET['unlock_time'] ?? null);
        }
        break;
    default:
        exit('Invalid request method');
}

?>
<!DOCTYPE html>
<html lang="<?php echo $t->getLanguageCode(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($t->ogTitle); ?></title>
    <meta name="description" content="<?php echo h($t->metaDescription); ?>">
    <link rel="icon" type="image/png" href="assets/favicon.png">

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

    <?php if (Secrets::get('GA4_ID')): ?>
        <!-- Google Analytics 4 -->
        <?php echo Tracking::renderGA(Secrets::get('GA4_ID')); ?>
    <?php endif; ?>

    <link rel="stylesheet" href="<?php echo ViewConfiguration::CSS_PATH; ?>?v=<?php echo $config->cssVersion; ?>">
</head>

<body>
    <div class="container">
        <?php if (!$state->hasMessage() && !$generatorState->isGenerated()): ?>
            <h1><?php echo h($t->pageTitle); ?></h1>
            <form id="passwordForm" method="POST" action="/">
                <div class="form-group">
                    <label for="datetime"><?php echo h($t->unlockLabel); ?> <span class="label-note"><?php echo h($t->localTimeNote); ?></span>:</label>
                    <div class="datetime-wrapper">
                        <input type="datetime-local" id="datetime" name="datetime" required max="<?php echo $config->maxDateTime; ?>" onclick="this.showPicker()" data-utc-now="<?php echo $config->currentDateTime; ?>">
                        <input type="hidden" name="action" value="generate">
                        <input type="hidden" id="timezone" name="timezone" value="">
                        <button type="button" class="calendar-btn" onclick="document.getElementById('datetime').showPicker()">
                            <img src="<?php echo ViewConfiguration::ICON_PATH; ?>" alt="Calendar" width="20" height="20">
                        </button>
                    </div>
                </div>
                <button type="submit"><?php echo h($t->generateButton); ?></button>
            </form>
        <?php endif; ?>

        <?php if ($generatorState->isGenerated()): ?>
            <h1><a href="/" id="pageTitle" target="_blank"><?php echo h($t->pageTitle); ?></a></h1>
            <div id="result" class="success" style="display: block;">
                <div style="background-color: #fff3cd; color: #856404; padding: 12px; border: 1px solid #ffeaa7; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo h($t->passwordWarning); ?>
                </div>

                <strong><?php echo h($t->generatedPasswordLabel); ?></strong><br>
                <div class="url-box"><?php echo h($generatorState->generatedPassword); ?></div>
                <button type="button" class="copy-btn" onclick="copyToClipboard('<?php echo h($generatorState->generatedPassword); ?>')"><?php echo h($t->copyButton); ?></button>

                <div style="margin-top: 30px;">
                    <strong><?php echo h($t->decryptUrlLabel); ?></strong><br>
                    <div class="url-box">
                        <a href="<?php echo h($generatorState->decryptUrl); ?>" class="decrypt-link" target="_blank"><?php echo h($generatorState->decryptUrl); ?></a>
                    </div>
                    <button type="button" class="copy-btn" onclick="copyToClipboard('<?php echo h($generatorState->decryptUrl); ?>')"><?php echo h($t->copyUrlButton); ?></button>
                </div>

                <small><?php echo h($t->unlockTimeLabel); ?> <span id="unlockTimeLocal" data-utc-time="<?php echo $generatorState->unlockTimeUTC; ?>"></span></small>
            </div>
        <?php elseif ($generatorState->hasError()): ?>
            <h1><a href="/" id="pageTitle"><?php echo h($t->pageTitle); ?></a></h1>
            <div id="result" class="error" style="display: block;">
                <?php echo h($generatorState->errorMessage); ?>
            </div>
        <?php else: ?>
            <div id="result"></div>
        <?php endif; ?>

        <?php if ($state->hasMessage()): ?>
            <h1><a href="/" id="pageTitle"><?php echo h($t->pageTitle); ?></a></h1>
            <div class="message <?php echo $state->messageType; ?>" id="messageDiv" data-unlock-time="<?php echo $state->unlockTimeUTC ?? ''; ?>">
                <span id="messageText"><?php echo h($state->message); ?></span>
                <?php if ($state->unlockTimeUTC && $state->messageType === 'error'): ?>
                    <div class="unlock-time-display"><?php echo h($t->unlockTimeLabel); ?> <span id="unlockTimeDisplay"></span></div>
                <?php endif; ?>
            </div>
            <?php if ($state->messageType === 'success' && $state->decryptedPassword): ?>
                <button class="copy-btn" onclick="copyToClipboard('<?php echo h($state->decryptedPassword); ?>')"><?php echo h($t->copyButton); ?></button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-content">
            <p id="serviceDescription"><?php echo h($t->serviceDescription); ?></p>
            <ul id="serviceFeatures">
                <li><?php echo h($t->feature1); ?></li>
                <li><?php echo h($t->feature2); ?></li>
                <li><?php echo h($t->feature3); ?></li>
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