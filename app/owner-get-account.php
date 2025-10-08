<?php
include '../includes/misc/autoload.phtml';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    http_response_code(403);
    die(json_encode(array("error" => "Access denied. Owner role required.")));
}

if (!isset($_GET['username'])) {
    die(json_encode(array("error" => "Username required")));
}

$username = misc\etc\sanitize($_GET['username']);
$query = misc\mysql\query("SELECT * FROM `accounts` WHERE `username` = ?", [$username]);

if ($query->num_rows < 1) {
    die(json_encode(array("error" => "Account not found")));
}

$account = mysqli_fetch_assoc($query->result);

echo json_encode(array(
    "username" => $account['username'],
    "email" => $account['email'],
    "role" => $account['role'],
    "ownerid" => $account['ownerid'] ?? '',
    "expires" => $account['expires'] ?? 0,
    "banned" => $account['banned'] ?? ''
));
?>
