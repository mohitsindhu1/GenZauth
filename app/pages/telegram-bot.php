<?php
require_once '../includes/csrf.php';

if ($_SESSION['role'] == "Reseller") {
    header("location: ./?page=reseller-licenses");
    die();
}
if ($role == "Manager" && !($permissions & 2048)) {
    misc\auditLog\send("Attempted (and failed) to view Telegram Bot settings.");
    dashboard\primary\error("You weren't granted permission to view this page.");
    die();
}
if (!isset($_SESSION['app'])) {
    dashboard\primary\error("Application not selected");
    die("Application not selected.");
}

// Handle Telegram Bot setup
if (isset($_POST['setup_bot'])) {
    csrf\requireValidToken();
    
    $bot_token = misc\etc\sanitize($_POST['bot_token']);
    $webhook_url = misc\etc\sanitize($_POST['webhook_url']);
    
    if (!empty($bot_token) && !empty($webhook_url)) {
        // Validate bot token format
        if (!preg_match('/^\d+:[A-Za-z0-9_-]{35}$/', $bot_token)) {
            dashboard\primary\error("Invalid bot token format!");
        } else {
            // Test bot token by calling getMe API
            $test_url = "https://api.telegram.org/bot{$bot_token}/getMe";
            $test_response = file_get_contents($test_url);
            $test_data = json_decode($test_response, true);
            
            if ($test_data && $test_data['ok']) {
                // Set webhook
                $webhook_set_url = "https://api.telegram.org/bot{$bot_token}/setWebhook";
                $webhook_data = json_encode(['url' => $webhook_url]);
                
                $context = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/json',
                        'content' => $webhook_data
                    ]
                ]);
                
                $webhook_response = file_get_contents($webhook_set_url, false, $context);
                $webhook_result = json_decode($webhook_response, true);
                
                if ($webhook_result && $webhook_result['ok']) {
                    // Store bot settings in database
                    $query = misc\mysql\query("UPDATE `apps` SET `telegram_bot_token` = ?, `telegram_webhook` = ? WHERE `secret` = ?", [$bot_token, $webhook_url, $_SESSION['app']]);
                    
                    if ($query->affected_rows > 0) {
                        dashboard\primary\success("Telegram Bot configured and webhook set successfully!");
                        misc\auditLog\send("Configured Telegram Bot for application " . $_SESSION['name']);
                    } else {
                        dashboard\primary\error("Failed to save bot configuration!");
                    }
                } else {
                    dashboard\primary\error("Failed to set webhook: " . ($webhook_result['description'] ?? 'Unknown error'));
                }
            } else {
                dashboard\primary\error("Invalid bot token or bot not accessible!");
            }
        }
    } else {
        dashboard\primary\error("Please fill in all required fields!");
    }
}

if (isset($_POST['test_bot'])) {
    csrf\requireValidToken();
    
    // Get bot settings
    $query = misc\mysql\query("SELECT `telegram_bot_token`, `telegram_chat_id` FROM `apps` WHERE `secret` = ?", [$_SESSION['app']]);
    $bot_settings = mysqli_fetch_array($query->result);
    
    if (!empty($bot_settings['telegram_bot_token'])) {
        $bot_token = $bot_settings['telegram_bot_token'];
        $chat_id = $bot_settings['telegram_chat_id'] ?? 'YOUR_CHAT_ID'; // You can set a default test chat ID
        
        $test_message = "🤖 KeyAuth Bot Test\n\n" .
                       "Application: " . $_SESSION['name'] . "\n" .
                       "Time: " . date('Y-m-d H:i:s') . "\n" .
                       "Status: ✅ Bot is working correctly!\n\n" .
                       "This is a test message from your KeyAuth Seller Plan.";
        
        $send_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
        $send_data = json_encode([
            'chat_id' => $chat_id,
            'text' => $test_message,
            'parse_mode' => 'HTML'
        ]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $send_data
            ]
        ]);
        
        $response = file_get_contents($send_url, false, $context);
        $result = json_decode($response, true);
        
        if ($result && $result['ok']) {
            dashboard\primary\success("Test message sent successfully! Check your Telegram chat.");
        } else {
            dashboard\primary\error("Failed to send test message. Please check your chat ID.");
        }
    } else {
        dashboard\primary\error("Please configure your bot first!");
    }
}
?>

<div class="p-4 lg:p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
                    🤖 Telegram Bot Integration
                </h1>
                <p class="text-gray-400">
                    Set up your Telegram bot to receive real-time notifications about your application
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-sm font-medium">
                    Seller Feature
                </span>
            </div>
        </div>

        <!-- Bot Setup Card -->
        <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                </svg>
                Bot Configuration
            </h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Bot Token
                        </label>
                        <input 
                            type="text" 
                            name="bot_token" 
                            placeholder="1234567890:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijk"
                            class="w-full px-3 py-2 bg-[#09090d] border border-gray-600 rounded-lg text-white focus:border-blue-500 focus:outline-none"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">Get this from @BotFather on Telegram</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Webhook URL
                        </label>
                        <input 
                            type="url" 
                            name="webhook_url" 
                            placeholder="https://your-domain.com/telegram/webhook"
                            class="w-full px-3 py-2 bg-[#09090d] border border-gray-600 rounded-lg text-white focus:border-blue-500 focus:outline-none"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">URL where Telegram will send updates</p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <button 
                        type="submit" 
                        name="setup_bot"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200"
                    >
                        Configure Bot
                    </button>
                    
                    <button 
                        type="submit" 
                        name="test_bot"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200"
                    >
                        Test Bot
                    </button>
                </div>
            </form>
        </div>

        <!-- Bot Features -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <!-- Available Notifications -->
            <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2L3 7v11a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V7l-7-5z"></path>
                    </svg>
                    Available Notifications
                </h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-300">New User Registrations</span>
                        <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-300">License Activations</span>
                        <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-300">Failed Login Attempts</span>
                        <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-300">HWID Violations</span>
                        <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-300">Subscription Expiries</span>
                        <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                    </div>
                </div>
            </div>

            <!-- Bot Commands -->
            <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Bot Commands
                </h3>
                
                <div class="space-y-3 text-sm">
                    <div class="bg-[#09090d] p-3 rounded">
                        <code class="text-blue-400">/stats</code>
                        <p class="text-gray-400 mt-1">Get application statistics</p>
                    </div>
                    <div class="bg-[#09090d] p-3 rounded">
                        <code class="text-blue-400">/users</code>
                        <p class="text-gray-400 mt-1">Get total user count</p>
                    </div>
                    <div class="bg-[#09090d] p-3 rounded">
                        <code class="text-blue-400">/licenses</code>
                        <p class="text-gray-400 mt-1">Get license information</p>
                    </div>
                    <div class="bg-[#09090d] p-3 rounded">
                        <code class="text-blue-400">/ban [username]</code>
                        <p class="text-gray-400 mt-1">Ban a specific user</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Setup Instructions -->
        <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Setup Instructions
            </h3>
            
            <div class="prose prose-invert max-w-none">
                <ol class="list-decimal list-inside space-y-3 text-gray-300">
                    <li>
                        <strong>Create a Telegram Bot:</strong>
                        <ul class="list-disc list-inside ml-4 mt-2 space-y-1 text-sm text-gray-400">
                            <li>Message @BotFather on Telegram</li>
                            <li>Send <code>/newbot</code> command</li>
                            <li>Follow the instructions to create your bot</li>
                            <li>Copy the bot token provided</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Set up Webhook:</strong>
                        <ul class="list-disc list-inside ml-4 mt-2 space-y-1 text-sm text-gray-400">
                            <li>Use your domain + <code>/telegram/webhook</code></li>
                            <li>Make sure your server supports HTTPS</li>
                            <li>The webhook will receive all bot updates</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Configure Settings:</strong>
                        <ul class="list-disc list-inside ml-4 mt-2 space-y-1 text-sm text-gray-400">
                            <li>Enter your bot token above</li>
                            <li>Enter your webhook URL</li>
                            <li>Click "Configure Bot" to save settings</li>
                            <li>Use "Test Bot" to verify everything works</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>