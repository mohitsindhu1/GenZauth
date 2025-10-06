<?php
require_once 'includes/credentials.php';

// Connect to database
$conn = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName, $databasePort);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "🔧 KeyAuth Database Performance & Structure Fix\n";
echo "================================================\n\n";

// 1. Check current seller_admin role
echo "1. Checking seller_admin role:\n";
$result = mysqli_query($conn, "SELECT username, role FROM accounts WHERE username = 'seller_admin'");
if ($result && $row = mysqli_fetch_assoc($result)) {
    echo "   Current role: '{$row['role']}'\n";
    
    // Fix role to be consistent
    if ($row['role'] !== 'seller') {
        mysqli_query($conn, "UPDATE accounts SET role = 'seller' WHERE username = 'seller_admin'");
        echo "   ✅ Updated role to 'seller'\n";
    } else {
        echo "   ✅ Role is correct\n";
    }
} else {
    echo "   ❌ seller_admin not found\n";
}

// 2. Show all tables
echo "\n2. Database Tables:\n";
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_array($result)) {
    echo "   - {$row[0]}\n";
}

// 3. Add Performance Indexes
echo "\n3. Adding Performance Indexes:\n";

$indexes = [
    "users" => [
        "ALTER TABLE `users` ADD INDEX `idx_app` (`app`)",
        "ALTER TABLE `users` ADD INDEX `idx_app_username` (`app`, `username`)",
        "ALTER TABLE `users` ADD UNIQUE KEY `unique_app_username` (`app`, `username`)"
    ],
    "subs" => [
        "ALTER TABLE `subs` ADD INDEX `idx_app` (`app`)",
        "ALTER TABLE `subs` ADD INDEX `idx_app_user` (`app`, `user`)"
    ],
    "uservars" => [
        "ALTER TABLE `uservars` ADD INDEX `idx_app` (`app`)",
        "ALTER TABLE `uservars` ADD INDEX `idx_app_user` (`app`, `user`)"
    ],
    "tokens" => [
        "ALTER TABLE `tokens` ADD INDEX `idx_app` (`app`)",
        "ALTER TABLE `tokens` ADD INDEX `idx_app_type` (`app`, `type`)"
    ],
    "logs" => [
        "ALTER TABLE `logs` ADD INDEX `idx_app` (`app`)",
        "ALTER TABLE `logs` ADD INDEX `idx_app_date` (`app`, `date`)"
    ],
    "sessions" => [
        "ALTER TABLE `sessions` ADD INDEX `idx_app` (`app`)",
        "ALTER TABLE `sessions` ADD INDEX `idx_app_credential` (`app`, `credential`)"
    ]
];

foreach ($indexes as $table => $table_indexes) {
    echo "   Adding indexes for '$table':\n";
    foreach ($table_indexes as $index_sql) {
        $result = mysqli_query($conn, $index_sql);
        if ($result) {
            echo "     ✅ " . substr($index_sql, strpos($index_sql, 'ADD') + 4) . "\n";
        } else {
            $error = mysqli_error($conn);
            if (strpos($error, 'Duplicate key name') !== false || strpos($error, 'already exists') !== false) {
                echo "     ⚠️ " . substr($index_sql, strpos($index_sql, 'ADD') + 4) . " (already exists)\n";
            } else {
                echo "     ❌ " . substr($index_sql, strpos($index_sql, 'ADD') + 4) . " - Error: $error\n";
            }
        }
    }
}

// 4. Check apps table structure and add missing columns
echo "\n4. Checking apps table structure:\n";
$result = mysqli_query($conn, "DESCRIBE apps");
$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
}

$required_columns = [
    'telegram_bot_token' => "VARCHAR(200) DEFAULT NULL",
    'telegram_webhook' => "VARCHAR(300) DEFAULT NULL", 
    'telegram_chat_id' => "VARCHAR(50) DEFAULT NULL",
    'sellerkey' => "VARCHAR(100) DEFAULT NULL"
];

foreach ($required_columns as $col => $definition) {
    if (!in_array($col, $columns)) {
        $sql = "ALTER TABLE `apps` ADD COLUMN `$col` $definition";
        if (mysqli_query($conn, $sql)) {
            echo "   ✅ Added column '$col'\n";
        } else {
            echo "   ❌ Failed to add column '$col': " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "   ✅ Column '$col' exists\n";
    }
}

// 5. Check database statistics
echo "\n5. Database Statistics:\n";
$tables_to_check = ['users', 'apps', 'subs', 'tokens', 'logs', 'sessions'];

foreach ($tables_to_check as $table) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        echo "   $table: {$row['count']} records\n";
    }
}

// 6. Create test user if none exist
echo "\n6. Checking for test users:\n";
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
if ($result && $row = mysqli_fetch_assoc($result)) {
    echo "   Total users: {$row['count']}\n";
}

echo "\n🎉 Database optimization completed!\n";
echo "Performance should be significantly improved now.\n";

mysqli_close($conn);
?>