<?php
// Copy this file to config.local.php on the Hostinger server (same /api folder)
// and fill in the real values from hPanel -> Databases.
//
// Do NOT commit config.local.php to git. It's already excluded via .gitignore
// because it holds real database credentials.

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');

// Set these on the server as environment variables, or define them only in
// config.local.php. Never commit real values to this repository.
define('PAYSTACK_SECRET_KEY', getenv('PAYSTACK_SECRET_KEY') ?: '');
define('PAYSTACK_PUBLIC_KEY', getenv('PAYSTACK_PUBLIC_KEY') ?: '');
define('PAYSTACK_WEBHOOK_SECRET', getenv('PAYSTACK_WEBHOOK_SECRET') ?: PAYSTACK_SECRET_KEY);

// Google sign-in. Create credentials at https://console.cloud.google.com/apis/credentials
// (OAuth client ID, type "Web application"). Add the redirect URI below to
// "Authorized redirect URIs" in that same screen, exactly as written.
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: 'https://www.mamidavintltd.com/api/auth_google_callback.php');
