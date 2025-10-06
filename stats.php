<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

include 'includes/misc/autoload.phtml';

$row = misc\cache\fetch('KeyAuthStats', "SELECT FORMAT((SELECT COUNT(*) FROM `accounts`), '') AS 'numAccs', FORMAT((SELECT COUNT(DISTINCT `ip_address`) FROM `sessions` WHERE `is_active` = 1 AND `expires_at` > NOW()), '') AS 'numOnlineUsers', FORMAT((SELECT COUNT(*) FROM `keys`), '') AS 'numKeys', FORMAT((SELECT COUNT(*) FROM `apps`), '') AS 'numApps';", [], 0, 3600);

$numAccs = $row['numAccs'];
$numOnlineUsers = $row['numOnlineUsers'];
$numApps = $row['numApps'];
$numKeys = $row['numKeys'];

die(json_encode(array(
    "accounts" => $numAccs,
    "applications" => $numApps,
    "licenses" => $numKeys,
    "activeUsers" => $numOnlineUsers
)));