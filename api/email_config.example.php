<?php
// Copy this file to api/email_config.local.php and fill in real SMTP credentials.
// Do NOT commit api/email_config.local.php — it should be excluded from version control.

// SMTP configuration for PHPMailer
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'no-reply@mamidavintltd.com');
define('SMTP_PASS', 'put_smtp_password_here'); // <-- DO NOT commit real password
define('SMTP_SECURE', 'tls'); // 'tls' for STARTTLS

// Optional: override From name/address
define('MAIL_FROM_ADDRESS', 'no-reply@mamidavintltd.com');
define('MAIL_FROM_NAME', 'Mamidav Website');
