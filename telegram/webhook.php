<?php
require '../includes/misc/autoload.phtml';
require '../includes/dashboard/autoload.phtml';

// Set content type
header('Content-Type: application/json');

// Get input data
$input = file_get_contents('php://input');
$update = json_decode($input, true);

// Logging for debugging (optional)
error_log("Telegram webhook received: " . $input);

// Basic validation
if (!$update || !isset($update['message'])) {
    http_response_code(200);
    exit('OK');
}

$message = $update['message'];
$chat_id = $message['chat']['id'] ?? null;
$text = $message['text'] ?? '';
$from_username = $message['from']['username'] ?? 'Unknown';

// Bot commands
if (str_starts_with($text, '/')) {
    $command = explode(' ', $text)[0];
    $args = array_slice(explode(' ', $text), 1);
    
    $response_text = '';
    
    switch ($command) {
        case '/start':
            $response_text = "🤖 Welcome to KeyAuth Bot!\n\n" .
                "Available commands:\n" .
                "/stats - Get application statistics\n" .
                "/users - Get user count\n" .
                "/licenses - Get license information\n" .
                "/help - Show this message";
            break;
            
        case '/help':
            $response_text = "🔹 KeyAuth Bot Help 🔹\n\n" .
                "Commands available:\n" .
                "• /stats - Application statistics\n" .
                "• /users - Total user count\n" .
                "• /licenses - License details\n" .
                "• /ping - Check bot status\n\n" .
                "💡 This bot is part of your KeyAuth Seller Plan!";
            break;
            
        case '/ping':
            $response_text = "🟢 Bot is online and working!\n" .
                "Server time: " . date('Y-m-d H:i:s');
            break;
            
        case '/stats':
            try {
                // Get application stats (you'll need to adapt this to your database structure)
                $total_users = "N/A"; // Add your query here
                $total_licenses = "N/A"; // Add your query here
                $active_sessions = "N/A"; // Add your query here
                
                $response_text = "📊 KeyAuth Statistics\n\n" .
                    "👥 Total Users: $total_users\n" .
                    "🔑 Total Licenses: $total_licenses\n" .
                    "🟢 Active Sessions: $active_sessions\n\n" .
                    "📅 Generated: " . date('Y-m-d H:i:s');
            } catch (Exception $e) {
                $response_text = "❌ Error fetching statistics: " . $e->getMessage();
            }
            break;
            
        case '/users':
            try {
                // Count total users across all apps (adapt to your needs)
                $response_text = "👥 User Information\n\n" .
                    "Total registered users: Loading...\n" .
                    "Active subscriptions: Loading...\n\n" .
                    "💡 Use your KeyAuth dashboard for detailed user management.";
            } catch (Exception $e) {
                $response_text = "❌ Error fetching user data: " . $e->getMessage();
            }
            break;
            
        case '/licenses':
            $response_text = "🔑 License Overview\n\n" .
                "Total licenses: Loading...\n" .
                "Used licenses: Loading...\n" .
                "Available licenses: Loading...\n\n" .
                "📈 Use /stats for detailed analytics";
            break;
            
        default:
            $response_text = "❓ Unknown command: $command\n\n" .
                "Type /help to see available commands.";
            break;
    }
    
    // Send response back to Telegram
    if ($response_text && $chat_id) {
        sendTelegramMessage($chat_id, $response_text);
    }
}

// Function to send message to Telegram
function sendTelegramMessage($chat_id, $text, $bot_token = null) {
    // You would get the bot token from your database/config
    // For now, using a placeholder - replace with actual token retrieval
    if (!$bot_token) {
        // Get bot token from app settings (you'll need to implement this)
        $bot_token = "YOUR_BOT_TOKEN_HERE"; // Replace with actual token from database
    }
    
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log("Telegram API error: " . $response);
    }
    
    return $response;
}

// Send notification function (for external use)
function sendTelegramNotification($message, $chat_id = null) {
    // This function can be called from other parts of your application
    // to send notifications via Telegram
    if (!$chat_id) {
        // Get default notification chat ID from settings
        $chat_id = "YOUR_NOTIFICATION_CHAT_ID"; // Replace with actual implementation
    }
    
    return sendTelegramMessage($chat_id, $message);
}

// Return OK to Telegram
http_response_code(200);
echo 'OK';
?>