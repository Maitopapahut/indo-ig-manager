<?php
class TelegramAPI {
    /**
     * Send a simple text message
     */
    public static function sendMessage($chatId, $text, $parseMode = null) {
        $data = [
            'chat_id' => $chatId,
            'text' => $text
        ];
        
        if ($parseMode) {
            $data['parse_mode'] = $parseMode;
        }
        
        return self::makeRequest('sendMessage', $data);
    }
    
    /**
     * Send message with inline keyboard
     */
    public static function sendMessageWithKeyboard($chatId, $text, $keyboard, $parseMode = null) {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ];
        
        if ($parseMode) {
            $data['parse_mode'] = $parseMode;
        }
        
        return self::makeRequest('sendMessage', $data);
    }
    
    /**
     * Answer callback query
     */
    public static function answerCallbackQuery($callbackQueryId, $text = null) {
        $data = [
            'callback_query_id' => $callbackQueryId
        ];
        
        if ($text) {
            $data['text'] = $text;
        }
        
        return self::makeRequest('answerCallbackQuery', $data);
    }
    
    /**
     * Get file information
     */
    public static function getFile($fileId) {
        $data = [
            'file_id' => $fileId
        ];
        
        $response = self::makeRequest('getFile', $data);
        return $response ? $response['result'] : null;
    }
    
    /**
     * Download file from Telegram servers
     */
    public static function downloadFile($filePath) {
        $url = 'https://api.telegram.org/file/bot' . BOT_TOKEN . '/' . $filePath;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode == 200) ? $content : null;
    }
    
    /**
     * Send document
     */
    public static function sendDocument($chatId, $filePath, $fileName = null) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $data = [
            'chat_id' => $chatId,
            'document' => new CURLFile($filePath, mime_content_type($filePath), $fileName ?: basename($filePath))
        ];
        
        return self::makeRequest('sendDocument', $data, true);
    }
    
    /**
     * Make HTTP request to Telegram API
     */
    private static function makeRequest($method, $data, $isMultipart = false) {
        $url = TELEGRAM_API_URL . $method;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($isMultipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode != 200) {
            error_log("Telegram API error: HTTP {$httpCode} - {$response}");
            return false;
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg() . " - Response: " . $response);
            return false;
        }
        
        if (!isset($decodedResponse['ok']) || !$decodedResponse['ok']) {
            error_log("Telegram API error: " . ($decodedResponse['description'] ?? 'Unknown error'));
            return false;
        }
        
        return $decodedResponse;
    }
}
?>
