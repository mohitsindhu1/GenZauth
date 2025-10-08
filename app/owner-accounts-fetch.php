<?php
include '../includes/misc/autoload.phtml';

set_exception_handler(function ($exception) {
    error_log("\n--------------------------------------------------------------\n");
    error_log($exception);
    error_log("\nRequest data:");
    error_log(print_r($_POST, true));
    error_log("\n--------------------------------------------------------------");
    http_response_code(500);
    die("Error: " . $exception->getMessage());
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    http_response_code(403);
    die(json_encode(array("error" => "Access denied. Owner role required.")));
}

if (isset($_POST['draw'])) {
    $draw = intval($_POST['draw']);
    $row = intval($_POST['start']);
    $rowperpage = intval($_POST['length']);
    $columnIndex = misc\etc\sanitize($_POST['order'][0]['column'] ?? 0);
    $columnName = misc\etc\sanitize($_POST['columns'][$columnIndex]['data'] ?? 'username');
    $columnSortOrder = misc\etc\sanitize($_POST['order'][0]['dir'] ?? 'asc');
    $searchValue = misc\etc\sanitize($_POST['search']['value'] ?? '');

    // Whitelist column names
    if (!in_array($columnName, array("username", "email", "role", "ownerid", "expires", "status"))) {
        $columnName = "username";
    }

    if (!in_array($columnSortOrder, array("desc", "asc"))) {
        $columnSortOrder = "asc";
    }

    if (!empty($searchValue)) {
        $query = misc\mysql\query("SELECT * FROM `accounts` WHERE (`username` LIKE ? OR `email` LIKE ? OR `ownerid` LIKE ? OR `role` LIKE ?) ORDER BY `$columnName` $columnSortOrder LIMIT $row, $rowperpage", ["%$searchValue%", "%$searchValue%", "%$searchValue%", "%$searchValue%"]);
    } else {
        $query = misc\mysql\query("SELECT * FROM `accounts` ORDER BY `$columnName` $columnSortOrder LIMIT $row, $rowperpage", []);
    }

    $data = array();

    while ($row = mysqli_fetch_assoc($query->result)) {
        $expires = $row['expires'] ?? 0;
        $expiryText = '<span class="text-gray-400">N/A</span>';
        if ($expires > 0 && $expires < 9999999999) {
            $expiryText = '<script>document.write(convertTimestamp(' . $expires . '));</script>';
            if ($expires < time()) {
                $expiryText = '<span class="text-red-400">' . $expiryText . '</span>';
            } else {
                $expiryText = '<span class="text-green-400">' . $expiryText . '</span>';
            }
        } else {
            $expiryText = '<span class="text-blue-400">Lifetime</span>';
        }

        $banned = $row['banned'] ?? null;
        $statusBadge = '';
        if (!empty($banned)) {
            $statusBadge = '<span class="px-3 py-1 bg-red-600/20 text-red-400 rounded-full text-xs border border-red-500/30">Banned</span>';
        } else if ($expires > 0 && $expires < time() && $expires < 9999999999) {
            $statusBadge = '<span class="px-3 py-1 bg-yellow-600/20 text-yellow-400 rounded-full text-xs border border-yellow-500/30">Expired</span>';
        } else {
            $statusBadge = '<span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-xs border border-green-500/30">Active</span>';
        }

        $roleBadge = '<span class="px-3 py-1 bg-blue-600/20 text-blue-400 rounded-full text-xs border border-blue-500/30">' . ucfirst($row['role']) . '</span>';
        if ($row['role'] == 'owner') {
            $roleBadge = '<span class="px-3 py-1 bg-gradient-to-r from-yellow-600/20 to-orange-600/20 text-yellow-400 rounded-full text-xs border border-yellow-500/30"><i class="lni lni-crown"></i> Owner</span>';
        } elseif ($row['role'] == 'seller') {
            $roleBadge = '<span class="px-3 py-1 bg-purple-600/20 text-purple-400 rounded-full text-xs border border-purple-500/30">Seller</span>';
        } elseif ($row['role'] == 'developer') {
            $roleBadge = '<span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-xs border border-green-500/30">Developer</span>';
        }

        $data[] = array(
            "username" => '<span class="font-semibold text-white">' . htmlspecialchars($row['username']) . '</span>',
            "email" => '<span class="text-gray-400 blur-sm hover:blur-none">' . substr(htmlspecialchars($row['email']), 0, 40) . '</span>',
            "role" => $roleBadge,
            "ownerid" => '<span class="text-gray-400 font-mono text-xs">' . ($row['ownerid'] ?? 'N/A') . '</span>',
            "expires" => $expiryText,
            "status" => $statusBadge,
            "actions" => '
                <div class="flex gap-2">
                    <button onclick="editAccount(\'' . htmlspecialchars($row['username']) . '\')" class="px-3 py-1 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded-lg border border-blue-500/30 transition-colors text-xs">
                        <i class="lni lni-pencil"></i> Edit
                    </button>
                    <button onclick="deleteAccount(\'' . htmlspecialchars($row['username']) . '\')" class="px-3 py-1 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg border border-red-500/30 transition-colors text-xs">
                        <i class="lni lni-trash"></i> Delete
                    </button>
                </div>'
        );
    }

    $response = array(
        "draw" => intval($draw),
        "recordsTotal" => misc\mysql\query("SELECT COUNT(*) as count FROM `accounts`", [])->result->fetch_assoc()['count'],
        "recordsFiltered" => !empty($searchValue) ? count($data) : misc\mysql\query("SELECT COUNT(*) as count FROM `accounts`", [])->result->fetch_assoc()['count'],
        "data" => $data
    );

    die(json_encode($response));
}

die("Request not from datatables, aborted.");
?>
