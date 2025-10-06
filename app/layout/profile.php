<?php
$query = misc\mysql\query("SELECT * FROM `accounts` WHERE `username` = ?", [$_SESSION['username']]);

if ($query->num_rows > 0) {
    while ($row_ = mysqli_fetch_array($query->result)) {
        $acclogs = $row_['acclogs'];
        $expiry = $row_["expires"];
        $emailVerify = $row_["emailVerify"];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: /login');
        exit;
    }
}

?>

<div class="w-full max-w-sm bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] border border-gray-700/50 rounded-2xl shadow-xl backdrop-blur-sm hover:shadow-2xl transition-all duration-300">
    <div class="flex justify-end px-4 pt-2">
        <button id="dropdownButton" data-dropdown-toggle="dropdown" class="inline-block text-gray-400 hover:text-white hover:bg-gray-800/50 focus:ring-0 p-2 rounded-lg transition-all duration-300" type="button">
            <span class="sr-only">Open dropdown</span>
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 3">
                <path d="M2 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm6.041 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM14 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" />
            </svg>
        </button>
        <!-- Dropdown menu -->
        <form method="post">
            <div id="dropdown" class="z-10 hidden text-base list-none bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] border border-blue-500/30 rounded-xl shadow-2xl w-48 backdrop-blur-xl">
                <ul class="py-2" aria-labelledby="dropdownButton">
                    <li>
                        <a href="?page=account-settings" class="flex items-center px-4 py-3 text-sm text-gray-300 hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-purple-600/20 hover:text-white transition-all duration-300 rounded-t-xl">
                            <i class="lni lni-cog mr-2 text-blue-400"></i> Account Settings
                        </a>
                    </li>
                    <li>
                        <a href="?page=account-logs" class="flex items-center px-4 py-3 text-sm text-gray-300 hover:bg-gradient-to-r hover:from-purple-600/20 hover:to-pink-600/20 hover:text-white transition-all duration-300">
                            <i class="lni lni-archive mr-2 text-purple-400"></i> Account Logs
                        </a>
                    </li>
                    <li>
                        <a href="?page=logout" class="flex items-center px-4 py-3 text-sm text-gray-300 hover:bg-gradient-to-r hover:from-red-600/20 hover:to-pink-600/20 hover:text-white transition-all duration-300 rounded-b-xl">
                            <i class="lni lni-exit mr-2 text-red-400"></i> Log Out
                        </a>
                    </li>
                </ul>
            </div>
        </form>
    </div>
    <div class="flex flex-col items-center pb-6 px-4">
        <div class="relative mb-4 group">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-300"></div>
            <img class="relative w-24 h-24 rounded-full border-4 border-gray-800 shadow-xl" src="<?= $_SESSION["img"]; ?>" alt="profile image" />
        </div>
        <?php if ($role == "seller") { ?>
            <h5 class="mb-2 text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 stars"><?= $_SESSION["username"]; ?></h5>
        <?php } else { ?>
            <h5 class="mb-2 text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400"><?= $_SESSION["username"]; ?></h5>
        <?php } ?>

        <?php
        $badgeClasses = "inline-flex items-center px-3 py-1 text-xs font-bold rounded-full border mb-3 transition-all duration-300 hover:scale-105";
        $textClasses = "";
        $borderColor = "";

        if ($role == "seller") {
            $textClasses = "text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400";
            $borderColor = "border-blue-400/50 bg-blue-500/10";
        } elseif ($role == "developer") {
            $textClasses = "text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400";
            $borderColor = "border-purple-400/50 bg-purple-500/10";
        } elseif ($role == "tester") {
            $textClasses = "text-transparent bg-clip-text bg-gradient-to-r from-gray-400 to-gray-500";
            $borderColor = "border-gray-400/50 bg-gray-500/10";
        } else {
            $textClasses = "text-gray-400";
            $borderColor = "border-gray-500/50 bg-gray-600/10";
        }

        echo "<span class=\"$badgeClasses $borderColor\"><span class=\"$textClasses\">" . strtoupper($role) . " PLAN</span></span>";
        ?>

        <?php
        $display = match ($role) {
            'tester' => '<div class="text-sm text-gray-400 flex items-center"><i class="lni lni-infinite mr-2 text-green-400"></i><b>Expires:</b>&nbsp;Free Forever</div>',
            'developer' => '<div class="text-sm text-gray-400 flex items-center"><i class="lni lni-calendar mr-2 text-purple-400"></i><b>Expires:</b>&nbsp;<span id="expiryLabel"></span></div>',
            'seller' => '<div class="text-sm text-gray-400 flex items-center" id="expirationLbl"><i class="lni lni-calendar mr-2 text-blue-400"></i><b>Expires:</b>&nbsp;<span id="expiryLabel"></span></div>',
            'Reseller' => '<div class="text-sm text-gray-400 flex items-center"><i class="lni lni-users mr-2 text-cyan-400"></i><b>Expires:</b>&nbsp;Owner Decides</div>',
            'Manager' => '<div class="text-sm text-gray-400 flex items-center"><i class="lni lni-user mr-2 text-yellow-400"></i><b>Expires:</b>&nbsp;Owner Decides</div>',
            'default' => '<div class="text-sm text-gray-400 flex items-center"><i class="lni lni-infinite mr-2 text-gray-400"></i><b>Expires:</b>&nbsp;Never</div>'
        };
        echo $display;

        if ($role === 'developer' || $role === 'seller') {
            echo '<script>';
            echo 'document.getElementById("expiryLabel").textContent = convertTimestamp(' . $expiry . ');';
            echo '</script>';
        }
        ?>
    </div>
</div>
<br>
