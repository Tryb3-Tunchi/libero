<?php
// Test Email Configuration
header('Content-Type: text/html; charset=utf-8');

echo "<h2>📧 Testing Email Configuration</h2>";

// Load config
require_once 'config.php';

echo "<h3>📋 Configuration Loaded:</h3>";
echo "<p><strong>Gmail Username:</strong> " . $gmail_config['smtp_username'] . "</p>";
echo "<p><strong>Gmail Password:</strong> " . substr($gmail_config['smtp_password'], 0, 10) . "...</p>";
echo "<p><strong>Recipient Email:</strong> " . $recipient_email . "</p>";
echo "<p><strong>From Email:</strong> " . $from_email . "</p>";

echo "<h3>🧪 Testing Gmail SMTP Connection...</h3>";

// Test SMTP connection
$smtp = @fsockopen($gmail_config['smtp_host'], $gmail_config['smtp_port'], $errno, $errstr, 10);

if (!$smtp) {
    echo "<p>❌ <strong>SMTP Connection Failed:</strong> $errstr ($errno)</p>";
    echo "<p>This usually means:</p>";
    echo "<ul>";
    echo "<li>Firewall blocking port 587</li>";
    echo "<li>Gmail SMTP server is down</li>";
    echo "<li>Network connectivity issues</li>";
    echo "</ul>";
} else {
    echo "<p>✅ <strong>SMTP Connection Successful!</strong></p>";
    
    // Read server greeting
    $response = fgets($smtp, 515);
    echo "<p><strong>Server Greeting:</strong> " . trim($response) . "</p>";
    
    // Test EHLO
    fputs($smtp, "EHLO localhost\r\n");
    $response = fgets($smtp, 515);
    echo "<p><strong>EHLO Response:</strong> " . trim($response) . "</p>";
    
    // Test STARTTLS
    fputs($smtp, "STARTTLS\r\n");
    $response = fgets($smtp, 515);
    echo "<p><strong>STARTTLS Response:</strong> " . trim($response) . "</p>";
    
    if (substr($response, 0, 3) == '220') {
        echo "<p>✅ <strong>STARTTLS Supported</strong></p>";
        
        // Enable TLS
        if (stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            echo "<p>✅ <strong>TLS Encryption Successful</strong></p>";
            
            // EHLO again after TLS
            fputs($smtp, "EHLO localhost\r\n");
            $response = fgets($smtp, 515);
            echo "<p><strong>Post-TLS EHLO:</strong> " . trim($response) . "</p>";
            
            // Test AUTH LOGIN
            fputs($smtp, "AUTH LOGIN\r\n");
            $response = fgets($smtp, 515);
            echo "<p><strong>AUTH LOGIN Response:</strong> " . trim($response) . "</p>";
            
            if (substr($response, 0, 3) == '334') {
                echo "<p>✅ <strong>Authentication Ready</strong></p>";
                
                // Send username
                fputs($smtp, base64_encode($gmail_config['smtp_username']) . "\r\n");
                $response = fgets($smtp, 515);
                echo "<p><strong>Username Response:</strong> " . trim($response) . "</p>";
                
                if (substr($response, 0, 3) == '334') {
                    echo "<p>✅ <strong>Username Accepted</strong></p>";
                    
                    // Send password
                    fputs($smtp, base64_encode($gmail_config['smtp_password']) . "\r\n");
                    $response = fgets($smtp, 515);
                    echo "<p><strong>Password Response:</strong> " . trim($response) . "</p>";
                    
                    if (substr($response, 0, 3) == '235') {
                        echo "<p>✅ <strong>Authentication Successful!</strong></p>";
                        echo "<p>🎉 <strong>Gmail SMTP is working perfectly!</strong></p>";
                    } else {
                        echo "<p>❌ <strong>Password Authentication Failed:</strong> " . trim($response) . "</p>";
                        echo "<p>This usually means:</p>";
                        echo "<ul>";
                        echo "<li>App password is incorrect</li>";
                        echo "<li>2FA is not enabled on Gmail</li>";
                        echo "<li>App password was revoked</li>";
                        echo "</ul>";
                    }
                } else {
                    echo "<p>❌ <strong>Username Rejected:</strong> " . trim($response) . "</p>";
                }
            } else {
                echo "<p>❌ <strong>AUTH LOGIN Not Supported:</strong> " . trim($response) . "</p>";
            }
        } else {
            echo "<p>❌ <strong>TLS Encryption Failed</strong></p>";
        }
    } else {
        echo "<p>❌ <strong>STARTTLS Not Supported:</strong> " . trim($response) . "</p>";
    }
    
    // Close connection
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
}

echo "<hr>";
echo "<h3>🔧 Troubleshooting Tips:</h3>";
echo "<ol>";
echo "<li><strong>Check Gmail Settings:</strong> Make sure 2FA is enabled and app password is correct</li>";
echo "<li><strong>Check Firewall:</strong> Port 587 should be open</li>";
echo "<li><strong>Check Network:</strong> Ensure internet connection is stable</li>";
echo "<li><strong>Check App Password:</strong> Generate a new one if needed</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='index.html'>← Back to Main Page</a> | <a href='test-telegram.php'>Test Telegram</a></p>";
?>
