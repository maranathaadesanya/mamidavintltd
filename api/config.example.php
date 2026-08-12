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
