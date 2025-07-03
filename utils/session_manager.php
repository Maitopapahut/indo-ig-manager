<?php
class SessionManager {
    private static $sessionFile = 'data/user_sessions.json';
    
    /**
     * Set user state
     */
    public static function setState($userId, $state) {
        $sessions = self::getSessions();
        $sessions[$userId] = [
            'state' => $state,
            'timestamp' => time()
        ];
        self::saveSessions($sessions);
    }
    
    /**
     * Get user state
     */
    public static function getState($userId) {
        $sessions = self::getSessions();
        
        if (isset($sessions[$userId])) {
            // Check if session is expired (1 hour timeout)
            if (time() - $sessions[$userId]['timestamp'] > 3600) {
                self::clearState($userId);
                return null;
            }
            return $sessions[$userId]['state'];
        }
        
        return null;
    }
    
    /**
     * Clear user state
     */
    public static function clearState($userId) {
        $sessions = self::getSessions();
        unset($sessions[$userId]);
        self::saveSessions($sessions);
    }
    
    /**
     * Check if user has specific state
     */
    public static function hasState($userId, $state) {
        return self::getState($userId) === $state;
    }
    
    /**
     * Get all sessions
     */
    private static function getSessions() {
        if (!file_exists(self::$sessionFile)) {
            return [];
        }
        
        $content = file_get_contents(self::$sessionFile);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * Save sessions to file
     */
    private static function saveSessions($sessions) {
        // Ensure data directory exists
        if (!is_dir('data')) {
            mkdir('data', 0755, true);
        }
        
        file_put_contents(self::$sessionFile, json_encode($sessions, JSON_PRETTY_PRINT));
    }
    
    /**
     * Clean expired sessions
     */
    public static function cleanExpiredSessions() {
        $sessions = self::getSessions();
        $cleaned = [];
        
        foreach ($sessions as $userId => $session) {
            if (time() - $session['timestamp'] <= 3600) {
                $cleaned[$userId] = $session;
            }
        }
        
        self::saveSessions($cleaned);
    }
}
?>