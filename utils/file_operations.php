<?php
class FileOperations {
    /**
     * Initialize data files if they don't exist
     */
    public static function initializeFiles() {
        if (!file_exists(USERS_FILE)) {
            file_put_contents(USERS_FILE, '');
        }
        if (!file_exists(CREDENTIALS_FILE)) {
            file_put_contents(CREDENTIALS_FILE, '');
        }
        if (!file_exists(ACTIVITY_LOG_FILE)) {
            file_put_contents(ACTIVITY_LOG_FILE, '');
        }
    }
    
    /**
     * Add new Instagram credential
     */
    public static function addCredential($instagramId, $password) {
        $dateAdded = date('Y-m-d H:i:s');
        $line = $instagramId . ' ' . $password . ' ' . $dateAdded . PHP_EOL;
        
        return file_put_contents(CREDENTIALS_FILE, $line, FILE_APPEND | LOCK_EX) !== false;
    }
    
    /**
     * Get all credentials
     */
    public static function getCredentials() {
        if (!file_exists(CREDENTIALS_FILE)) {
            return [];
        }
        
        $lines = file(CREDENTIALS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $credentials = [];
        
        foreach ($lines as $line) {
            $parts = explode(' ', $line, 3);
            if (count($parts) >= 3) {
                $credentials[] = [
                    'instagram_id' => $parts[0],
                    'password' => $parts[1],
                    'date_added' => $parts[2]
                ];
            }
        }
        
        return $credentials;
    }
    
    /**
     * Delete credential by Instagram ID
     */
    public static function deleteCredential($instagramId) {
        $credentials = self::getCredentials();
        $found = false;
        $newContent = '';
        
        foreach ($credentials as $cred) {
            if ($cred['instagram_id'] === $instagramId) {
                $found = true;
                continue; // Skip this credential
            }
            $newContent .= $cred['instagram_id'] . ' ' . $cred['password'] . ' ' . $cred['date_added'] . PHP_EOL;
        }
        
        if ($found) {
            file_put_contents(CREDENTIALS_FILE, $newContent, LOCK_EX);
        }
        
        return $found;
    }
    
    /**
     * Log user activity
     */
    public static function logActivity($userId, $username, $action) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "{$timestamp} - User ID {$userId} ({$username}) {$action}" . PHP_EOL;
        
        file_put_contents(ACTIVITY_LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get activity logs
     */
    public static function getActivityLogs($limit = 100) {
        if (!file_exists(ACTIVITY_LOG_FILE)) {
            return [];
        }
        
        $lines = file(ACTIVITY_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice(array_reverse($lines), 0, $limit);
    }
    
    /**
     * Bulk add credentials
     */
    public static function bulkAddCredentials($credentialsArray) {
        $content = '';
        $dateAdded = date('Y-m-d H:i:s');
        
        foreach ($credentialsArray as $cred) {
            $content .= $cred['instagram_id'] . ' ' . $cred['password'] . ' ' . $dateAdded . PHP_EOL;
        }
        
        return file_put_contents(CREDENTIALS_FILE, $content, FILE_APPEND | LOCK_EX) !== false;
    }
    
    /**
     * Check if Instagram ID exists
     */
    public static function credentialExists($instagramId) {
        $credentials = self::getCredentials();
        
        foreach ($credentials as $cred) {
            if ($cred['instagram_id'] === $instagramId) {
                return true;
            }
        }
        
        return false;
    }
}
?>
