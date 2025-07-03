<?php
// Bot configuration
define('BOT_TOKEN', '7673342712:AAEPVmPN-yhZYl4zumksfqK4IZONTWDzAj8');
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

// File paths
define('USERS_FILE', 'data/users.txt');
define('CREDENTIALS_FILE', 'data/credentials.txt');
define('ACTIVITY_LOG_FILE', 'data/activity_log.txt');
define('TEMP_DIR', 'temp/');

// Default password for Instagram credentials
define('DEFAULT_PASSWORD', '000111');

// Log group configuration (set your group chat ID here)
define('LOG_GROUP_ID', getenv('LOG_GROUP_ID') ?: '-1001996108499'); // Replace with your actual group ID

// Timezone
date_default_timezone_set('UTC');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
?>
