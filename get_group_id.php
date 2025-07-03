<?php
// Temporary script to get group chat ID
require_once 'config.php';

$input = file_get_contents('php://input');
$update = json_decode($input, true);

if ($update) {
    $chatId = null;
    $chatType = '';
    
    if (isset($update['message'])) {
        $chatId = $update['message']['chat']['id'];
        $chatType = $update['message']['chat']['type'];
    } elseif (isset($update['channel_post'])) {
        $chatId = $update['channel_post']['chat']['id'];
        $chatType = $update['channel_post']['chat']['type'];
    }
    
    if ($chatId && ($chatType === 'group' || $chatType === 'supergroup')) {
        // Log group information
        $groupInfo = "Group Chat ID: {$chatId}\nType: {$chatType}\nTime: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents('group_id.log', $groupInfo, FILE_APPEND);
        
        // Send response
        $response = [
            'method' => 'sendMessage',
            'chat_id' => $chatId,
            'text' => "✅ Group Chat ID: {$chatId}\n\nUse this ID with /setloggroup command to configure logging."
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
?>