<?php
require_once 'config.php';
require_once 'handlers/auth.php';
require_once 'handlers/commands.php';
require_once 'handlers/callbacks.php';
require_once 'utils/telegram_api.php';
require_once 'utils/file_operations.php';
require_once 'utils/group_logger.php';
require_once 'utils/session_manager.php';

// Create data directories if they don't exist
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}
if (!file_exists('temp')) {
    mkdir('temp', 0755, true);
}

// Initialize data files if they don't exist
FileOperations::initializeFiles();

// Clean expired sessions periodically
SessionManager::cleanExpiredSessions();

// Get incoming update
$input = file_get_contents('php://input');

if (empty($input)) {
    http_response_code(200);
    exit('OK');
}

$update = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE || !$update) {
    error_log("JSON parsing error: " . json_last_error_msg() . " - Input: " . $input);
    http_response_code(400);
    exit('Invalid JSON');
}

try {
    // Handle callback queries (inline button clicks)
    if (isset($update['callback_query'])) {
        CallbackHandler::handle($update['callback_query']);
    }
    // Handle messages
    elseif (isset($update['message'])) {
        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $userId = $message['from']['id'];
        $username = $message['from']['username'] ?? 'Unknown';
        $text = $message['text'] ?? '';

        // Check if user is authorized
        if (!Auth::isAuthorized($userId)) {
            TelegramAPI::sendMessage($chatId, "You are not authorized to use this bot. Contact admin for access.");
            FileOperations::logActivity($userId, $username, "Unauthorized access attempt");
            exit();
        }

        // Handle file uploads
        if (isset($message['document'])) {
            // Log document details for debugging
            error_log("Document upload: " . json_encode($message['document']));
            CommandHandler::handleFileUpload($chatId, $userId, $username, $message['document']);
        }
        // Handle text messages and commands
        else {
            CommandHandler::handleMessage($chatId, $userId, $username, $text);
        }
    }
} catch (Exception $e) {
    error_log("Bot error: " . $e->getMessage());
    
    // Log error to group
    GroupLogger::logError("Bot Exception: " . $e->getMessage(), $userId ?? null, $username ?? null);
    
    if (isset($chatId)) {
        TelegramAPI::sendMessage($chatId, "An error occurred. Please try again later.");
    }
}
?>
