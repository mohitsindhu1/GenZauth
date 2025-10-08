<?php
include '../includes/misc/autoload.phtml';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    http_response_code(403);
    die(json_encode(array("error" => "Access denied. Owner role required.")));
}

$totalAccounts = misc\mysql\query("SELECT COUNT(*) as count FROM `accounts`", [])->result->fetch_assoc()['count'];
$sellers = misc\mysql\query("SELECT COUNT(*) as count FROM `accounts` WHERE `role` = 'seller'", [])->result->fetch_assoc()['count'];
$developers = misc\mysql\query("SELECT COUNT(*) as count FROM `accounts` WHERE `role` = 'developer'", [])->result->fetch_assoc()['count'];
$totalApps = misc\mysql\query("SELECT COUNT(*) as count FROM `apps`", [])->result->fetch_assoc()['count'];

echo json_encode(array(
    "total" => $totalAccounts,
    "sellers" => $sellers,
    "developers" => $developers,
    "apps" => $totalApps
));
?>
