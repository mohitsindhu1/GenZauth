<?php

namespace dashboard\primary;

use misc\mysql;

function time2str($date)
{
    $now = time();
    $diff = $now - $date;
    if ($diff < 60) {
        return sprintf($diff > 1 ? '%s seconds' : 'second', $diff);
    }
    $diff = floor($diff / 60);
    if ($diff < 60) {
        return sprintf($diff > 1 ? '%s minutes' : 'minute', $diff);
    }
    $diff = floor($diff / 60);
    if ($diff < 24) {
        return sprintf($diff > 1 ? '%s hours' : 'hour', $diff);
    }
    $diff = floor($diff / 24);
    if ($diff < 7) {
        return sprintf($diff > 1 ? '%s days' : 'day', $diff);
    }
    if ($diff < 30) {
        $diff = floor($diff / 7);
        return sprintf($diff > 1 ? '%s weeks' : 'week', $diff);
    }
    $diff = floor($diff / 30);
    if ($diff < 12) {
        return sprintf($diff > 1 ? '%s months' : 'month', $diff);
    }
    $diff = date('Y', $now) - date('Y', $date);
    return sprintf($diff > 1 ? '%s years' : 'year', $diff);
}
function expireCheck($username, $expires)
{
    // Check if subscription is expired
    $isExpired = ($expires < time());
    
    if ($isExpired) {
        // Store expired status and original role
        $_SESSION['subscription_expired'] = true;
        
        // Store original role only if not already stored
        if (!isset($_SESSION['original_role'])) {
            $_SESSION['original_role'] = $_SESSION['role'];
        }
        
        // Downgrade session role to tester for access control
        $_SESSION['role'] = "tester";
        $_SESSION['effective_role'] = "tester";
        
        // Log the expiry event for admin tracking
        error_log("User {$username} subscription expired at " . date('Y-m-d H:i:s', $expires) . ", temporarily downgraded to tester");
    } else {
        // Clear expired status if subscription is active
        $_SESSION['subscription_expired'] = false;
        
        // Restore original role if it was downgraded
        if (isset($_SESSION['original_role'])) {
            $_SESSION['role'] = $_SESSION['original_role'];
            $_SESSION['effective_role'] = $_SESSION['original_role'];
        }
    }
    
    if ($expires - time() < 2629743) // check if account expires in month or less
    {
        return true;
    } else {
        return false;
    }
}

// Helper function to get effective role for permission checks
function getEffectiveRole()
{
    if (isset($_SESSION['subscription_expired']) && $_SESSION['subscription_expired']) {
        return $_SESSION['effective_role'] ?? 'tester';
    }
    return $_SESSION['role'];
}

// Helper function to check if user has subscription access
function hasActiveSubscription()
{
    return !(isset($_SESSION['subscription_expired']) && $_SESSION['subscription_expired']);
}

// Function to restore role when subscription is renewed
function restoreOriginalRole($username)
{
    if (isset($_SESSION['original_role'])) {
        $_SESSION['role'] = $_SESSION['original_role'];
        $_SESSION['effective_role'] = $_SESSION['original_role'];
        $_SESSION['subscription_expired'] = false;
        unset($_SESSION['subscription_warning']);
        
        // Update database if role was changed incorrectly before
        mysql\query("UPDATE `accounts` SET `role` = ? WHERE `username` = ?", [$_SESSION['original_role'], $username]);
        
        error_log("Restored role for user {$username} to {$_SESSION['original_role']}");
        return true;
    }
    return false;
}

function wh_log($webhook_url, $msg, $un)
{
    $json_data = json_encode([
        // Message
        "content" => $msg,
        // Username
        "username" => "$un",
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
}
function error($msg)
{
    echo '<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css"><script type=\'text/javascript\'>

                

                            const notyf = new Notyf();

                            notyf

                              .error({

                                message: \'' . addslashes($msg) . '\',

                                duration: 3500,

                                dismissible: true

                              });               

                

                </script>';
}
function success($msg)
{
    echo '<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css"><script type=\'text/javascript\'>

                

                            const notyf = new Notyf();

                            notyf

                              .success({

                                message: \'' . addslashes($msg) . '\',

                                duration: 3500,

                                dismissible: true

                              });               

                

                </script>';
}

function popover($id, $title, $msg){
    echo '<div data-popover id="' . $id . '" role="tooltip"
            class="absolute z-10 invisible inline-block w-64 text-sm text-gray-500 transition-opacity duration-300 bg-[#09090d] rounded-lg shadow-sm opacity-0">
            <div class="px-3 py-2 bg-[#09090d]/70 rounded-t-lg">
                <h3 class="font-semibold text-white">' . $title . '</h3>
            </div>
            <div class="px-3 py-2">
                <p>' . $msg . '</p>
            </div>
            <div data-popper-arrow></div>
        </div>';
}
?>
