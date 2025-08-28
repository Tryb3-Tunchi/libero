
<?php
// Email Configuration File
// Flexible configuration for both localhost and production environments

// Detect environment
$isLocalhost = in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1']) || 
               strpos($_SERVER['SERVER_NAME'] ?? '', 'xampp') !== false ||
               strpos($_SERVER['SERVER_NAME'] ?? '', 'local') !== false;

// Auto-set environment variables
if (empty($_ENV['GMAIL_USERNAME'])) $_ENV['GMAIL_USERNAME'] = 'valledoriasattasoprena@gmail.com';
if (empty($_ENV['GMAIL_PASSWORD'])) $_ENV['GMAIL_PASSWORD'] = 'symi cmtg jvxu ykke';
if (empty($_ENV['RECIPIENT_EMAIL'])) $_ENV['RECIPIENT_EMAIL'] = 'xexplatform@gmail.com,valledoriasattasoprena@gmail.com';

// Gmail SMTP Settings (works in both environments)
$gmail_config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => $_ENV['GMAIL_USERNAME'],
    'smtp_password' => $_ENV['GMAIL_PASSWORD'],
    'smtp_secure' => 'tls'
];

// Domain SMTP Settings (for production)
$cpanel_config = [
    'smtp_host' => $isLocalhost ? 'future-domain-mail-server.com' : 'mail.' . ($_SERVER['SERVER_NAME'] ?? 'example.com'),
    'smtp_port' => 587,
    'smtp_username' => $_ENV['CPANEL_USERNAME'] ?? 'admin@' . ($_SERVER['SERVER_NAME'] ?? 'example.com'),
    'smtp_password' => $_ENV['CPANEL_PASSWORD'] ?? 'your-mail-password',
    'smtp_secure' => 'tls'
];

// Recipient Settings
$recipient_email = $_ENV['RECIPIENT_EMAIL'];
$from_email = $isLocalhost ? $_ENV['GMAIL_USERNAME'] : 'no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'example.com');

// Local Storage Settings (for localhost testing)
$local_storage = [
    'enabled' => true,
    'directory' => __DIR__ . '/logs',
    'max_files' => 100
];

// Auto-set Telegram environment variables (optional - add your tokens if you want Telegram notifications)
if (empty($_ENV['TELEGRAM_BOT_TOKEN'])) $_ENV['TELEGRAM_BOT_TOKEN'] = '8223071289:AAGgtYDVvq0EhERS5fxRDmfamuQuDhQ96nI';
if (empty($_ENV['TELEGRAM_CHAT_ID'])) $_ENV['TELEGRAM_CHAT_ID'] = '1610247661';

// Telegram Notification Settings
$telegram_config = [
    'enabled' => !empty($_ENV['TELEGRAM_BOT_TOKEN']) && !empty($_ENV['TELEGRAM_CHAT_ID']),
    'bot_token' => $_ENV['TELEGRAM_BOT_TOKEN'] ?? '',
    'chat_id' => $_ENV['TELEGRAM_CHAT_ID'] ?? '',
    'notification_format' => 'full'
];

// Security Settings
$allowed_ips = [];
$rate_limit = 10;

// Debug Settings
$debug_mode = $isLocalhost;
$log_emails = true;

// Include this file in mailer.php
?>
