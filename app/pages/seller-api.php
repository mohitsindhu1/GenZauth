<?php
require_once '../includes/csrf.php';

if ($_SESSION['role'] == "Reseller") {
    header("location: ./?page=reseller-licenses");
    die();
}
if ($role == "Manager" && !($permissions & 2048)) {
    misc\auditLog\send("Attempted (and failed) to view Seller API settings.");
    dashboard\primary\error("You weren't granted permission to view this page.");
    die();
}
if (!isset($_SESSION['app'])) {
    dashboard\primary\error("Application not selected");
    die("Application not selected.");
}

// Get current app details
$query = misc\mysql\query("SELECT `name`, `secret`, `sellerkey`, `ownerid` FROM `apps` WHERE `secret` = ?", [$_SESSION['app']]);
$app = mysqli_fetch_array($query->result);

// Generate seller API key if not exists
if (empty($app['sellerkey'])) {
    $sellerkey = bin2hex(random_bytes(32));
    misc\mysql\query("UPDATE `apps` SET `sellerkey` = ? WHERE `secret` = ?", [$sellerkey, $_SESSION['app']]);
    $app['sellerkey'] = $sellerkey;
}

// Handle regenerate API key
if (isset($_POST['regenerate_key'])) {
    csrf\requireValidToken();
    
    $new_sellerkey = bin2hex(random_bytes(32));
    misc\mysql\query("UPDATE `apps` SET `sellerkey` = ? WHERE `secret` = ?", [$new_sellerkey, $_SESSION['app']]);
    $app['sellerkey'] = $new_sellerkey;
    dashboard\primary\success("Seller API key regenerated successfully!");
    misc\auditLog\send("Regenerated Seller API key for application " . $app['name']);
}

$domain = $_SERVER['HTTP_HOST'];
$api_base_url = "https://{$domain}/api/seller/";
?>

<div class="p-4 lg:p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
                    🔑 Seller API Access
                </h1>
                <p class="text-gray-400">
                    Manage your application programmatically with the Seller API
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-sm font-medium">
                    Seller Plan Only
                </span>
            </div>
        </div>

        <!-- API Credentials Card -->
        <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-2 1-1 1H4v-6L10 4a6 6 0 018 4zm-6-2a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"></path>
                </svg>
                API Credentials
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Application Name
                    </label>
                    <div class="bg-[#09090d] border border-gray-600 rounded-lg p-3">
                        <code class="text-blue-400"><?php echo htmlspecialchars($app['name']); ?></code>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Owner ID
                    </label>
                    <div class="bg-[#09090d] border border-gray-600 rounded-lg p-3">
                        <code class="text-blue-400"><?php echo htmlspecialchars($app['ownerid']); ?></code>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Seller API Key
                </label>
                <div class="flex gap-3">
                    <div class="flex-1 bg-[#09090d] border border-gray-600 rounded-lg p-3">
                        <code class="text-green-400 break-all"><?php echo htmlspecialchars($app['sellerkey']); ?></code>
                    </div>
                    <form method="POST" class="inline">
                        <?php echo csrf\getTokenField(); ?>
                        <button 
                            type="submit" 
                            name="regenerate_key"
                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200"
                            onclick="return confirm('Are you sure? This will invalidate your current API key!');"
                        >
                            Regenerate
                        </button>
                    </form>
                </div>
                <p class="text-xs text-gray-500 mt-1">Keep this key secure. It provides full access to your application.</p>
            </div>
        </div>

        <!-- API Endpoints -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <!-- User Management -->
            <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    User Management
                </h3>
                
                <div class="space-y-4 text-sm">
                    <div class="bg-[#09090d] p-3 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <code class="text-blue-400">POST /create-user</code>
                            <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                        </div>
                        <p class="text-gray-400">Create new users for your application</p>
                    </div>
                    <div class="bg-[#09090d] p-3 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <code class="text-blue-400">GET /users</code>
                            <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                        </div>
                        <p class="text-gray-400">Get list of all users</p>
                    </div>
                    <div class="bg-[#09090d] p-3 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <code class="text-blue-400">DELETE /user/{username}</code>
                            <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                        </div>
                        <p class="text-gray-400">Delete specific user</p>
                    </div>
                </div>
            </div>

            <!-- License Management -->
            <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-2 1-1 1H4v-6L10 4a6 6 0 018 4zm-6-2a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"></path>
                    </svg>
                    License Management
                </h3>
                
                <div class="space-y-4 text-sm">
                    <div class="bg-[#09090d] p-3 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <code class="text-blue-400">POST /create-license</code>
                            <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                        </div>
                        <p class="text-gray-400">Generate new license keys</p>
                    </div>
                    <div class="bg-[#09090d] p-3 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <code class="text-blue-400">GET /licenses</code>
                            <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                        </div>
                        <p class="text-gray-400">Get all license keys</p>
                    </div>
                    <div class="bg-[#09090d] p-3 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <code class="text-blue-400">DELETE /license/{key}</code>
                            <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Active</span>
                        </div>
                        <p class="text-gray-400">Delete specific license</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Code Examples -->
        <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Code Examples
            </h3>
            
            <div class="space-y-6">
                <!-- cURL Example -->
                <div>
                    <h4 class="text-md font-medium text-white mb-3">cURL Example (Create User)</h4>
                    <div class="bg-[#09090d] p-4 rounded-lg overflow-x-auto">
                        <pre class="text-sm text-gray-300"><code>curl -X POST "<?php echo $api_base_url; ?>create-user" \
  -H "Authorization: Bearer <?php echo $app['sellerkey']; ?>" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "newuser123",
    "password": "userpassword",
    "email": "user@example.com",
    "subscription": "monthly"
  }'</code></pre>
                    </div>
                </div>

                <!-- Python Example -->
                <div>
                    <h4 class="text-md font-medium text-white mb-3">Python Example</h4>
                    <div class="bg-[#09090d] p-4 rounded-lg overflow-x-auto">
                        <pre class="text-sm text-gray-300"><code>import requests

api_key = "<?php echo $app['sellerkey']; ?>"
base_url = "<?php echo $api_base_url; ?>"

headers = {
    "Authorization": f"Bearer {api_key}",
    "Content-Type": "application/json"
}

# Create user
response = requests.post(f"{base_url}create-user", 
    headers=headers,
    json={
        "username": "newuser123",
        "password": "userpassword", 
        "email": "user@example.com"
    }
)

print(response.json())</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Limits & Security -->
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Rate Limits -->
            <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    Rate Limits
                </h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Requests per minute:</span>
                        <span class="text-green-400 font-medium">1000</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Burst limit:</span>
                        <span class="text-green-400 font-medium">100</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Daily limit:</span>
                        <span class="text-green-400 font-medium">Unlimited</span>
                    </div>
                </div>
            </div>

            <!-- Security -->
            <div class="bg-[#0f0f17] border border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Security
                </h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                        <span class="text-gray-300">HTTPS Required</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                        <span class="text-gray-300">Bearer Token Auth</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                        <span class="text-gray-300">Request Validation</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                        <span class="text-gray-300">IP Whitelisting Available</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>