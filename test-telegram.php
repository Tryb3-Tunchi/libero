<?php
// Test Telegram Bot Configuration
header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔍 Testing Telegram Bot Configuration</h2>";

// Load config
require_once 'config.php';

echo "<h3>📋 Configuration Loaded:</h3>";
echo "<p><strong>Bot Token:</strong> " . substr($telegram_config['bot_token'], 0, 20) . "...</p>";
echo "<p><strong>Chat ID:</strong> " . $telegram_config['chat_id'] . "</p>";
echo "<p><strong>Enabled:</strong> " . ($telegram_config['enabled'] ? '✅ Yes' : '❌ No') . "</p>";

if ($telegram_config['enabled']) {
    echo "<h3>🧪 Testing Telegram API...</h3>";
    
    // Test 1: Get bot info
    $botInfoUrl = "https://api.telegram.org/bot{$telegram_config['bot_token']}/getMe";
    $botInfo = @file_get_contents($botInfoUrl);
    
    if ($botInfo) {
        $botData = json_decode($botInfo, true);
        if ($botData['ok']) {
            echo "<p>✅ <strong>Bot Info:</strong> " . $botData['result']['first_name'] . " (@" . $botData['result']['username'] . ")</p>";
        } else {
            echo "<p>❌ <strong>Bot Error:</strong> " . $botData['description'] . "</p>";
        }
    } else {
        echo "<p>❌ <strong>Bot Test Failed:</strong> Could not connect to Telegram API</p>";
    }
    
    // Test 2: Send test message
    echo "<h3>📤 Sending Test Message...</h3>";
    
    $testMessage = "🧪 Test message from Virgilio system\n⏰ Time: " . date('Y-m-d H:i:s') . "\n✅ Bot is working!";
    
    $telegramApiUrl = "https://api.telegram.org/bot{$telegram_config['bot_token']}/sendMessage";
    $postData = http_build_query([
        'chat_id' => $telegram_config['chat_id'],
        'text' => $testMessage,
        'parse_mode' => 'HTML'
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData,
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents($telegramApiUrl, false, $context);
    
    if ($response) {
        $responseData = json_decode($response, true);
        if ($responseData['ok']) {
            echo "<p>✅ <strong>Test Message Sent Successfully!</strong></p>";
            echo "<p><strong>Message ID:</strong> " . $responseData['result']['message_id'] . "</p>";
            echo "<p><strong>Chat ID:</strong> " . $responseData['result']['chat']['id'] . "</p>";
            echo "<p><strong>Chat Type:</strong> " . $responseData['result']['chat']['type'] . "</p>";
        } else {
            echo "<p>❌ <strong>Test Message Failed:</strong> " . $responseData['description'] . "</p>";
            echo "<p><strong>Error Code:</strong> " . $responseData['error_code'] . "</p>";
            
            // Common error solutions
            if ($responseData['error_code'] == 400 && strpos($responseData['description'], 'chat not found') !== false) {
                echo "<h4>💡 Solution for 'chat not found':</h4>";
                echo "<ol>";
                echo "<li>Make sure you've started a conversation with your bot</li>";
                echo "<li>Send /start to your bot first</li>";
                echo "<li>Check if the chat ID is correct</li>";
                echo "<li>Try using the bot's username: @" . (isset($botData['result']['username']) ? $botData['result']['username'] : 'unknown') . "</li>";
                echo "</ol>";
            }
        }
    } else {
        echo "<p>❌ <strong>Test Failed:</strong> No response from Telegram API</p>";
    }
    
} else {
    echo "<p>❌ <strong>Telegram is disabled</strong> - Check your configuration</p>";
}

echo "<hr>";
echo "<h3>📧 Email Configuration Test:</h3>";
echo "<p><strong>Gmail Username:</strong> " . $gmail_config['smtp_username'] . "</p>";
echo "<p><strong>Gmail Password:</strong> " . substr($gmail_config['smtp_password'], 0, 10) . "...</p>";
echo "<p><strong>Recipient Email:</strong> " . $recipient_email . "</p>";
echo "<p><strong>From Email:</strong> " . $from_email . "</p>";

echo "<hr>";
echo "<p><a href='index.html'>← Back to Main Page</a></p>";
?>
