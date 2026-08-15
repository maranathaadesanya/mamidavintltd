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
