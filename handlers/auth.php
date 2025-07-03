<?php
class Auth {
    /**
     * Check if user is authorized
     */
    public static function isAuthorized($userId) {
        if (!file_exists(USERS_FILE)) {
            return false;
        }
        
        $authorizedUsers = file(USERS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return in_array($userId, $authorizedUsers);
    }
    
    /**
     * Add user to authorized users list
     */
    public static function addAuthorizedUser($userId) {
        if (!self::isAuthorized($userId)) {
            file_put_contents(USERS_FILE, $userId . PHP_EOL, FILE_APPEND | LOCK_EX);
            return true;
        }
        return false;
    }
    
    /**
     * Remove user from authorized users list
     */
    public static function removeAuthorizedUser($userId) {
        if (!file_exists(USERS_FILE)) {
            return false;
        }
        
        $users = file(USERS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $users = array_filter($users, function($user) use ($userId) {
            return $user != $userId;
        });
        
        file_put_contents(USERS_FILE, implode(PHP_EOL, $users) . PHP_EOL, LOCK_EX);
        return true;
    }
}
?>
