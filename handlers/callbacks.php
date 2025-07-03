<?php
require_once 'utils/csv_handler.php';

class CallbackHandler {
    public static function handle($callbackQuery) {
        $chatId = $callbackQuery['message']['chat']['id'];
        $userId = $callbackQuery['from']['id'];
        $username = $callbackQuery['from']['username'] ?? 'Unknown';
        $data = $callbackQuery['data'];
        $messageId = $callbackQuery['message']['message_id'];
        
        // Check if user is authorized
        if (!Auth::isAuthorized($userId)) {
            TelegramAPI::answerCallbackQuery($callbackQuery['id'], "You are not authorized to use this bot.");
            return;
        }
        
        switch ($data) {
            case 'add_credentials':
                TelegramAPI::answerCallbackQuery($callbackQuery['id']);
                CommandHandler::handleAdd($chatId, $userId, $username);
                break;
                
            case 'view_credentials':
                TelegramAPI::answerCallbackQuery($callbackQuery['id']);
                CommandHandler::handleView($chatId, $userId, $username);
                break;
                
            case 'delete_credentials':
                TelegramAPI::answerCallbackQuery($callbackQuery['id']);
                CommandHandler::handleDelete($chatId, $userId, $username);
                break;
                
            case 'help':
                TelegramAPI::answerCallbackQuery($callbackQuery['id']);
                CommandHandler::handleHelp($chatId, $userId, $username);
                break;
                
            case 'import_credentials':
                TelegramAPI::answerCallbackQuery($callbackQuery['id']);
                CommandHandler::handleImport($chatId, $userId, $username);
                break;
                
            case 'export_credentials':
                TelegramAPI::answerCallbackQuery($callbackQuery['id']);
                CommandHandler::handleExport($chatId, $userId, $username);
                break;
                
            case 'export_csv':
                TelegramAPI::answerCallbackQuery($callbackQuery['id'], "Generating CSV file...");
                self::handleExportCSV($chatId, $userId, $username);
                break;
                
            case 'export_txt':
                TelegramAPI::answerCallbackQuery($callbackQuery['id'], "Generating TXT file...");
                self::handleExportTXT($chatId, $userId, $username);
                break;
                
            case 'statistics':
                TelegramAPI::answerCallbackQuery($callbackQuery['id']);
                self::handleStatistics($chatId, $userId, $username);
                break;
                
            case 'settings':
                TelegramAPI::answerCallbackQuery($callbackQuery['id']);
                self::handleSettings($chatId, $userId, $username);
                break;
                
            default:
                TelegramAPI::answerCallbackQuery($callbackQuery['id'], "Unknown action.");
        }
    }
    
    private static function handleExportCSV($chatId, $userId, $username) {
        $credentials = FileOperations::getCredentials();
        
        if (empty($credentials)) {
            TelegramAPI::sendMessage($chatId, "No credentials to export.");
            return;
        }
        
        $csvContent = CSVHandler::exportToCSV($credentials);
        $fileName = 'instagram_credentials_' . date('Y-m-d_H-i-s') . '.csv';
        $filePath = TEMP_DIR . $fileName;
        
        file_put_contents($filePath, $csvContent);
        
        $success = TelegramAPI::sendDocument($chatId, $filePath, $fileName);
        
        if ($success) {
            FileOperations::logActivity($userId, $username, "Exported " . count($credentials) . " credentials as CSV");
            GroupLogger::logActivity("EXPORT CSV", $userId, $username, "Exported: " . count($credentials) . " accounts");
        }
        
        // Clean up temporary file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    private static function handleExportTXT($chatId, $userId, $username) {
        $credentials = FileOperations::getCredentials();
        
        if (empty($credentials)) {
            TelegramAPI::sendMessage($chatId, "No credentials to export.");
            return;
        }
        
        $txtContent = CSVHandler::exportToTXT($credentials);
        $fileName = 'instagram_credentials_' . date('Y-m-d_H-i-s') . '.txt';
        $filePath = TEMP_DIR . $fileName;
        
        file_put_contents($filePath, $txtContent);
        
        $success = TelegramAPI::sendDocument($chatId, $filePath, $fileName);
        
        if ($success) {
            FileOperations::logActivity($userId, $username, "Exported " . count($credentials) . " credentials as TXT");
            GroupLogger::logActivity("EXPORT TXT", $userId, $username, "Exported: " . count($credentials) . " accounts");
        }
        
        // Clean up temporary file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    public static function handleStatistics($chatId, $userId, $username) {
        $credentials = FileOperations::getCredentials();
        $activityLogs = FileOperations::getActivityLogs(50);
        
        $message = "📈 PREMIUM STATISTICS DASHBOARD\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Account Statistics
        $message .= "📊 ACCOUNT STATISTICS:\n";
        $message .= "• Total Accounts: " . count($credentials) . "\n";
        $message .= "• Database Status: " . (count($credentials) > 0 ? "🟢 Active" : "🔴 Empty") . "\n";
        $message .= "• Storage Type: 🗃️ File-Based\n";
        $message .= "• Security Level: 🔒 High\n\n";
        
        // Recent Activity
        $message .= "📋 RECENT ACTIVITY:\n";
        if (!empty($activityLogs)) {
            foreach (array_slice($activityLogs, 0, 5) as $log) {
                $message .= "• " . substr($log, 0, 50) . "...\n";
            }
        } else {
            $message .= "• No recent activity\n";
        }
        $message .= "\n";
        
        // System Info
        $message .= "⚙️ SYSTEM INFO:\n";
        $message .= "• Bot Version: v2.0 Premium\n";
        $message .= "• Uptime: 🟢 Online\n";
        $message .= "• Features: All Premium Unlocked\n";
        $message .= "• Support: 24/7 Available";
        
        TelegramAPI::sendMessage($chatId, $message);
        FileOperations::logActivity($userId, $username, "Viewed statistics dashboard");
    }
    
    public static function handleSettings($chatId, $userId, $username) {
        $message = "⚙️ PREMIUM SETTINGS PANEL\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "🔧 CURRENT CONFIGURATION:\n";
        $message .= "• Default Password: " . DEFAULT_PASSWORD . "\n";
        $message .= "• Auto-Backup: 🟢 Enabled\n";
        $message .= "• Security Mode: 🔒 Maximum\n";
        $message .= "• Notifications: 🔔 On\n\n";
        $message .= "📂 DATA MANAGEMENT:\n";
        $message .= "• Storage Location: /data/\n";
        $message .= "• Backup Frequency: Daily\n";
        $message .= "• Log Retention: 30 days\n\n";
        $message .= "🚀 PREMIUM FEATURES:\n";
        $message .= "• ✅ Bulk Import/Export\n";
        $message .= "• ✅ Advanced Statistics\n";
        $message .= "• ✅ Activity Logging\n";
        $message .= "• ✅ Secure Storage\n";
        $message .= "• ✅ Premium Support\n\n";
        $message .= "💡 All premium features are active!";
        
        TelegramAPI::sendMessage($chatId, $message);
        FileOperations::logActivity($userId, $username, "Accessed settings panel");
    }
}
?>
