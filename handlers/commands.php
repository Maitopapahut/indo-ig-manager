<?php
require_once 'utils/csv_handler.php';
require_once 'utils/session_manager.php';

class CommandHandler {
    
    public static function handleMessage($chatId, $userId, $username, $text) {
        // Handle commands
        if (strpos($text, '/') === 0) {
            $parts = explode(' ', $text, 2);
            $command = $parts[0];
            $args = isset($parts[1]) ? $parts[1] : '';
            
            switch ($command) {
                case '/start':
                    self::handleStart($chatId, $userId, $username);
                    break;
                case '/add':
                    self::handleAdd($chatId, $userId, $username, $args);
                    break;
                case '/view':
                    self::handleView($chatId, $userId, $username);
                    break;
                case '/delete':
                    self::handleDelete($chatId, $userId, $username, $args);
                    break;
                case '/help':
                    self::handleHelp($chatId, $userId, $username);
                    break;
                case '/import':
                    self::handleImport($chatId, $userId, $username);
                    break;
                case '/export':
                    self::handleExport($chatId, $userId, $username);
                    break;
                case '/setloggroup':
                    self::handleSetLogGroup($chatId, $userId, $username, $args);
                    break;
                default:
                    TelegramAPI::sendMessage($chatId, "Unknown command. Use /help for assistance.");
            }
        } else {
            // Handle non-command messages based on user state
            self::handleStateBasedMessage($chatId, $userId, $username, $text);
        }
    }
    
    public static function handleStart($chatId, $userId, $username) {
        $message = "🚀 PREMIUM INSTAGRAM MANAGER BOT 🚀\n\n";
        $message .= "Welcome, @{$username}!\n";
        $message .= "Your premium Instagram credentials management system is ready.\n\n";
        $message .= "📊 Quick Stats:\n";
        $credentials = FileOperations::getCredentials();
        $message .= "• Total Accounts: " . count($credentials) . "\n";
        $message .= "• Status: 🟢 Online\n";
        $message .= "• Security: 🔒 Encrypted\n\n";
        $message .= "Choose your action:";
        
        $keyboard = [
            [
                ['text' => '➕ ADD ACCOUNT', 'callback_data' => 'add_credentials'],
                ['text' => '👁️ VIEW ALL', 'callback_data' => 'view_credentials'],
                ['text' => '🗑️ DELETE', 'callback_data' => 'delete_credentials']
            ],
            [
                ['text' => '📥 BULK IMPORT', 'callback_data' => 'import_credentials'],
                ['text' => '📤 EXPORT DATA', 'callback_data' => 'export_credentials'],
                ['text' => '📈 STATISTICS', 'callback_data' => 'statistics']
            ],
            [
                ['text' => '⚙️ SETTINGS', 'callback_data' => 'settings'],
                ['text' => '❓ HELP & GUIDE', 'callback_data' => 'help']
            ]
        ];
        
        TelegramAPI::sendMessageWithKeyboard($chatId, $message, $keyboard);
        FileOperations::logActivity($userId, $username, "Started bot");
    }
    
    public static function handleAdd($chatId, $userId, $username, $args = '') {
        if (empty($args)) {
            SessionManager::setState($userId, 'waiting_for_credentials');
            $message = "➕ ADD NEW INSTAGRAM ACCOUNT\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📝 Please send your Instagram credentials in one of these formats:\n\n";
            $message .= "🔸 Format 1: username (uses default password)\n";
            $message .= "🔸 Format 2: username password\n\n";
            $message .= "💡 Examples:\n";
            $message .= "• john_doe\n";
            $message .= "• john_doe mypassword123\n\n";
            $message .= "🔒 Your data is encrypted and secure!";
            TelegramAPI::sendMessage($chatId, $message);
            return;
        }
        
        self::processCredentials($chatId, $userId, $username, $args);
    }
    
    public static function handleView($chatId, $userId, $username) {
        $credentials = FileOperations::getCredentials();
        
        if (empty($credentials)) {
            $message = "📱 INSTAGRAM ACCOUNTS DATABASE\n\n";
            $message .= "🔍 No accounts found in your database.\n";
            $message .= "💡 Use 'ADD ACCOUNT' to get started!\n\n";
            $message .= "📊 Status: Empty Database";
            TelegramAPI::sendMessage($chatId, $message);
            FileOperations::logActivity($userId, $username, "Viewed credentials (empty)");
            return;
        }
        
        $totalCount = count($credentials);
        
        // If more than 10 accounts, show summary first
        if ($totalCount > 10) {
            $message = "📱 INSTAGRAM ACCOUNTS DATABASE\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📊 **SUMMARY:**\n";
            $message .= "• Total Accounts: {$totalCount}\n";
            $message .= "• Status: 🟢 Active Database\n\n";
            $message .= "📋 **QUICK COPY FORMAT:**\n";
            
            foreach ($credentials as $index => $cred) {
                if ($index >= 10) break;
                $message .= "{$cred['instagram_id']}:{$cred['password']}\n";
            }
            
            if ($totalCount > 10) {
                $message .= "\n...and " . ($totalCount - 10) . " more accounts\n";
            }
            
            $message .= "\n💡 Export your data for full list.";
        } else {
            $message = "📱 INSTAGRAM ACCOUNTS DATABASE\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $count = 0;
foreach ($credentials as $cred) {
    $count++;
    $message .= "*🔸 ACCOUNT #{$count}*\n\n";
    
    $message .= "👤 *Username:* \n";
    $message .= "`{$cred['instagram_id']}`\n\n";

    $message .= "🔑 *Password:* \n";
    $message .= "`{$cred['password']}`\n\n";

    $message .= "📋 *Login Format:* \n";
    $message .= "`{$cred['instagram_id']}:{$cred['password']}`\n\n";

    $message .= "📅 *Added:* `{$cred['date_added']}`\n";
    $message .= "━━━━━━━━━━━━━━\n\n";
}

            
            $message .= "📊 TOTAL ACCOUNTS: {$count}\n";
            $message .= "🟢 Status: Active Database";
        }
        
        TelegramAPI::sendMessage($chatId, $message, "Markdown");
        FileOperations::logActivity($userId, $username, "Viewed credentials ({$totalCount} total)");
    }
    
    public static function handleDelete($chatId, $userId, $username, $instagramId = '') {
        if (empty($instagramId)) {
            SessionManager::setState($userId, 'waiting_for_delete_id');
            $message = "🗑️ DELETE INSTAGRAM ACCOUNT\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "⚠️ SECURITY WARNING:\n";
            $message .= "This action will permanently remove the account from your database.\n\n";
            $message .= "📝 Please send the Instagram username you want to delete:\n\n";
            $message .= "Example: john_doe\n\n";
            $message .= "🔒 This action cannot be undone!";
            TelegramAPI::sendMessage($chatId, $message);
            return;
        }
        
        $deleted = FileOperations::deleteCredential($instagramId);
        
        if ($deleted) {
            $message = "✅ ACCOUNT DELETED SUCCESSFULLY!\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "🗑️ REMOVED ACCOUNT:\n";
            $message .= "👤 Username: {$instagramId}\n";
            $message .= "📅 Deleted On: " . date('Y-m-d H:i:s') . "\n";
            $message .= "🔒 Status: Permanently Removed\n\n";
            
            $credentials = FileOperations::getCredentials();
            $message .= "📊 REMAINING ACCOUNTS: " . count($credentials) . "\n";
            $message .= "✨ Database updated successfully!";
            
            TelegramAPI::sendMessage($chatId, $message);
            FileOperations::logActivity($userId, $username, "Deleted account: {$instagramId}");
            
            // Send log to group
            GroupLogger::sendCredentialsList("ACCOUNT DELETED", $userId, $username, $instagramId);
            GroupLogger::logActivity("DELETE ACCOUNT", $userId, $username, "Deleted: {$instagramId}");
        } else {
            $message = "❌ ACCOUNT NOT FOUND!\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "🔍 The username '{$instagramId}' was not found in your database.\n\n";
            $message .= "💡 Tips:\n";
            $message .= "• Check spelling and try again\n";
            $message .= "• Use 'VIEW ALL' to see existing accounts\n";
            $message .= "• Username is case-sensitive";
            TelegramAPI::sendMessage($chatId, $message);
        }
    }
    
    public static function handleHelp($chatId, $userId, $username) {
        $message = "📚 PREMIUM HELP & GUIDE CENTER\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "🚀 QUICK START COMMANDS:\n";
        $message .= "• /start - Launch premium dashboard\n";
        $message .= "• /add - Add Instagram account\n";
        $message .= "• /view - View account database\n";
        $message .= "• /delete - Remove account\n";
        $message .= "• /import - Bulk import accounts\n";
        $message .= "• /export - Export account data\n";
        $message .= "• /setloggroup - Configure activity logging (admin only)\n\n";
        
        $message .= "📱 ADDING ACCOUNTS:\n";
        $message .= "• Format 1: username (auto password)\n";
        $message .= "• Format 2: username password\n";
        $message .= "• Examples:\n";
        $message .= "  - john_doe\n";
        $message .= "  - john_doe mypass123\n\n";
        
        $message .= "📂 BULK OPERATIONS:\n";
        $message .= "• Import: Upload CSV/TXT files\n";
        $message .= "• Export: Download as CSV/TXT\n";
        $message .= "• Formats Supported:\n";
        $message .= "  - CSV: username,password\n";
        $message .= "  - TXT: username password\n\n";
        
        $message .= "🔒 PREMIUM FEATURES:\n";
        $message .= "• Advanced Statistics Dashboard\n";
        $message .= "• Real-time Activity Monitoring\n";
        $message .= "• Secure Encrypted Storage\n";
        $message .= "• Bulk Import/Export Tools\n";
        $message .= "• 24/7 Premium Support\n\n";
        
        $message .= "💡 PRO TIPS:\n";
        $message .= "• Use strong passwords for security\n";
        $message .= "• Regular backups via export\n";
        $message .= "• Monitor via statistics panel\n\n";
        
        $message .= "🎯 Need help? Your premium support is ready!";
        
        TelegramAPI::sendMessage($chatId, $message);
        FileOperations::logActivity($userId, $username, "Viewed premium help guide");
    }
    
    public static function handleSetLogGroup($chatId, $userId, $username, $groupId = '') {
        // Only allow admin (first authorized user) to set log group
        $authorizedUsers = file(USERS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($authorizedUsers) || $authorizedUsers[0] != $userId) {
            TelegramAPI::sendMessage($chatId, "❌ Only admin can configure log group.");
            return;
        }
        
        if (empty($groupId)) {
            $message = "🔧 CONFIGURE LOG GROUP\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📝 To configure the log group, send:\n";
            $message .= "/setloggroup [GROUP_CHAT_ID]\n\n";
            $message .= "💡 How to get Group Chat ID:\n";
            $message .= "1. Add your bot to the group\n";
            $message .= "2. Send any message in the group\n";
            $message .= "3. Check bot logs for chat ID\n\n";
            $message .= "📄 Current Log Group: " . (LOG_GROUP_ID === '-1002345678901' ? 'Not configured' : LOG_GROUP_ID);
            TelegramAPI::sendMessage($chatId, $message);
            return;
        }
        
        // Update config file
        $configContent = file_get_contents('config.php');
        $configContent = preg_replace(
            "/define\('LOG_GROUP_ID', getenv\('LOG_GROUP_ID'\) \?: '.*?'\);/",
            "define('LOG_GROUP_ID', getenv('LOG_GROUP_ID') ?: '{$groupId}');",
            $configContent
        );
        file_put_contents('config.php', $configContent);
        
        $message = "✅ LOG GROUP CONFIGURED!\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "📊 Log Group ID: {$groupId}\n";
        $message .= "🔄 Configuration updated successfully!\n";
        $message .= "📝 All activities will now be logged to the group.\n\n";
        $message .= "🚀 Bot restart required for changes to take effect.";
        
        TelegramAPI::sendMessage($chatId, $message);
        FileOperations::logActivity($userId, $username, "Configured log group: {$groupId}");
    }
    
    public static function handleImport($chatId, $userId, $username) {
        SessionManager::setState($userId, 'waiting_for_import_file');
        
        $message = "📥 BULK IMPORT - PREMIUM FEATURE\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "📁 **Supported File Formats:**\n";
        $message .= "• **CSV**: `instagram_id,password`\n";
        $message .= "• **TXT**: `instagram_id password` (space separated)\n\n";
        $message .= "🔑 **Default Password Support:**\n";
        $message .= "• Files with missing passwords will use: `" . DEFAULT_PASSWORD . "`\n";
        $message .= "• Format: `username,` or `username` (empty password)\n\n";
        $message .= "📤 **Upload your file now to begin import...**";
        
        TelegramAPI::sendMessage($chatId, $message);
    }
    
    public static function handleExport($chatId, $userId, $username) {
        $credentials = FileOperations::getCredentials();
        
        if (empty($credentials)) {
            TelegramAPI::sendMessage($chatId, "No credentials to export.");
            return;
        }
        
        $keyboard = [
            [
                ['text' => '📄 Export as CSV', 'callback_data' => 'export_csv'],
                ['text' => '📋 Export as TXT', 'callback_data' => 'export_txt']
            ]
        ];
        
        TelegramAPI::sendMessageWithKeyboard($chatId, "Choose export format:", $keyboard);
    }
    
    public static function handleFileUpload($chatId, $userId, $username, $document) {
        // Log file upload attempt
        FileOperations::logActivity($userId, $username, "File upload attempt: " . ($document['file_name'] ?? 'unknown'));
        
        if (!SessionManager::hasState($userId, 'waiting_for_import_file')) {
            $message = "⚠️ IMPORT SESSION NOT ACTIVE\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "🔧 Please use `/import` command first to start the import process.\n\n";
            $message .= "📝 Steps to import:\n";
            $message .= "1. Send `/import` command\n";
            $message .= "2. Upload your CSV or TXT file\n";
            $message .= "3. Wait for processing results";
            
            TelegramAPI::sendMessage($chatId, $message);
            return;
        }
        
        // Show processing message
        TelegramAPI::sendMessage($chatId, "⏳ **Processing your file...**\n\n📁 Analyzing and importing credentials...");
        
        $fileId = $document['file_id'];
        $fileName = $document['file_name'] ?? 'import_file';
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileSize = $document['file_size'] ?? 0;
        
        // Log file details
        FileOperations::logActivity($userId, $username, "Processing file: {$fileName} ({$fileSize} bytes, {$fileExtension})");
        
        if (!in_array($fileExtension, ['csv', 'txt'])) {
            $message = "❌ INVALID FILE FORMAT\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📄 **File received**: {$fileName}\n";
            $message .= "⚠️ **Only CSV and TXT files are supported**\n\n";
            $message .= "📁 **Supported formats:**\n";
            $message .= "• `.csv` - Comma separated values\n";
            $message .= "• `.txt` - Space separated text\n\n";
            $message .= "🔄 Please upload a valid file format.";
            
            TelegramAPI::sendMessage($chatId, $message);
            return;
        }
        
        // Download file from Telegram
        $fileInfo = TelegramAPI::getFile($fileId);
        if (!$fileInfo) {
            $message = "❌ DOWNLOAD FAILED\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "🔗 **Unable to get file information from Telegram**\n";
            $message .= "📄 File: {$fileName}\n";
            $message .= "🔄 Please try uploading the file again.\n\n";
            $message .= "💡 **Troubleshooting:**\n";
            $message .= "• Check your internet connection\n";
            $message .= "• Ensure file size is under 20MB\n";
            $message .= "• Try re-uploading the file";
            
            TelegramAPI::sendMessage($chatId, $message);
            FileOperations::logActivity($userId, $username, "Failed to get file info for: {$fileName}");
            return;
        }
        
        $fileContent = TelegramAPI::downloadFile($fileInfo['file_path']);
        if (!$fileContent) {
            $message = "❌ CONTENT DOWNLOAD FAILED\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📄 File: {$fileName}\n";
            $message .= "🔗 File path: {$fileInfo['file_path']}\n";
            $message .= "⚠️ **Unable to download file content**\n\n";
            $message .= "🔄 Please try again or contact admin if issue persists.";
            
            TelegramAPI::sendMessage($chatId, $message);
            FileOperations::logActivity($userId, $username, "Failed to download content for: {$fileName}");
            return;
        }
        
        // Parse and import credentials
        $imported = 0;
        $errors = [];
        
        if ($fileExtension === 'csv') {
            $result = CSVHandler::importFromCSV($fileContent);
        } else {
            $result = CSVHandler::importFromTXT($fileContent);
        }
        
        $imported = $result['imported'];
        $errors = $result['errors'];
        
        SessionManager::clearState($userId);
        
        // Count credentials that used default password
        $defaultPasswordCount = 0;
        $allCredentials = FileOperations::getCredentials();
        foreach ($allCredentials as $cred) {
            if ($cred['password'] === DEFAULT_PASSWORD) {
                $defaultPasswordCount++;
            }
        }
        
        $message = "📥 IMPORT COMPLETED - PREMIUM RESULTS\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "✅ **Successfully imported**: {$imported} credentials\n";
        $message .= "📄 **File processed**: {$fileName}\n";
        $message .= "📊 **Total database size**: " . count($allCredentials) . " accounts\n\n";
        
        if ($defaultPasswordCount > 0) {
            $message .= "🔑 **Default Password Applied**: {$defaultPasswordCount} accounts\n";
            $message .= "📝 Password used: `" . DEFAULT_PASSWORD . "`\n\n";
        }
        
        if (!empty($errors)) {
            $message .= "⚠️ **Issues found**: " . count($errors) . " entries\n\n";
            $message .= "📋 **Error details:**\n";
            foreach (array_slice($errors, 0, 5) as $error) {
                $message .= "• {$error}\n";
            }
            if (count($errors) > 5) {
                $message .= "• ... and " . (count($errors) - 5) . " more issues\n";
            }
            $message .= "\n";
        }
        
        $message .= "🎯 **Import Summary:**\n";
        $message .= "• Valid entries: {$imported}\n";
        $message .= "• Failed entries: " . count($errors) . "\n";
        $message .= "• Success rate: " . round(($imported / ($imported + count($errors))) * 100, 1) . "%\n\n";
        $message .= "✨ Database updated successfully!";
        
        TelegramAPI::sendMessage($chatId, $message);
        FileOperations::logActivity($userId, $username, "Imported {$imported} credentials from {$fileExtension} file");
        
        // Send log to group
        if ($imported > 0) {
            GroupLogger::sendCredentialsList("BULK IMPORT", $userId, $username, "{$imported} accounts from {$fileExtension} file");
            GroupLogger::logActivity("BULK IMPORT", $userId, $username, "Imported: {$imported} accounts");
        }
    }
    
    private static function handleStateBasedMessage($chatId, $userId, $username, $text) {
        $state = SessionManager::getState($userId);
        if (!$state) {
            return;
        }
        
        switch ($state) {
            case 'waiting_for_credentials':
                self::processCredentials($chatId, $userId, $username, $text);
                SessionManager::clearState($userId);
                break;
                
            case 'waiting_for_delete_id':
                self::handleDelete($chatId, $userId, $username, trim($text));
                SessionManager::clearState($userId);
                break;
        }
    }
    
    private static function processCredentials($chatId, $userId, $username, $input) {
        $parts = explode(' ', trim($input), 2);
        $instagramId = $parts[0];
        $password = isset($parts[1]) ? $parts[1] : DEFAULT_PASSWORD;
        
        if (empty($instagramId)) {
            $message = "❌ INVALID INPUT\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "⚠️ Please provide a valid Instagram username.\n\n";
            $message .= "📝 **Correct formats:**\n";
            $message .= "• `username` (uses default password: 000111)\n";
            $message .= "• `username password`\n\n";
            $message .= "💡 **Examples:**\n";
            $message .= "• john_doe\n";
            $message .= "• john_doe mypass123\n\n";
            $message .= "🔄 Please try again with a valid format.";
            TelegramAPI::sendMessage($chatId, $message);
            return;
        }
        
        // Check if Instagram ID already exists
        $credentials = FileOperations::getCredentials();
        foreach ($credentials as $cred) {
            if ($cred['instagram_id'] === $instagramId) {
                TelegramAPI::sendMessage($chatId, "❌ Instagram ID '{$instagramId}' already exists. Use /delete to remove it first.");
                return;
            }
        }
        
        $success = FileOperations::addCredential($instagramId, $password);
        
        if ($success) {
            $message = "✅ ACCOUNT ADDED SUCCESSFULLY!\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📱 NEW INSTAGRAM ACCOUNT:\n";
            $message .= "👤 Username: {$instagramId}\n";
            $message .= "🔑 Password: {$password}\n";
            $message .= "📅 Added On: " . date('Y-m-d H:i:s') . "\n";
            $message .= "🔒 Security: Encrypted & Secure\n\n";
            
            if ($password === DEFAULT_PASSWORD) {
                $message .= "💡 INFO: Default password applied automatically.\n";
                $message .= "Use custom password format next time for enhanced security.\n\n";
            }
            
            $credentials = FileOperations::getCredentials();
            $message .= "📊 TOTAL ACCOUNTS: " . count($credentials) . "\n";
            $message .= "🎉 Ready to manage your Instagram empire!";
            
            TelegramAPI::sendMessage($chatId, $message);
            FileOperations::logActivity($userId, $username, "Added Instagram ID: {$instagramId}");
            
            // Send log to group
            GroupLogger::sendCredentialsList("ACCOUNT ADDED", $userId, $username, $instagramId);
            GroupLogger::logActivity("ADD ACCOUNT", $userId, $username, "Added: {$instagramId}");
        } else {
            $errorMsg = "Failed to save credentials for user {$userId} (@{$username})";
            TelegramAPI::sendMessage($chatId, "❌ Failed to save credentials. Please try again.");
            GroupLogger::logError($errorMsg, $userId, $username);
        }
    }
}
?>
