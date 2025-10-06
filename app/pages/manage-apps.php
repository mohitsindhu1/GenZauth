<?php
if ($_SESSION['role'] == "Reseller") {
    header("location: ./?page=reseller-licenses");
    die();
}

if (isset($_POST['selectApp'])) {
    $appName = misc\etc\sanitize($_POST['selectApp']);
    $query = misc\mysql\query("SELECT `secret`, `name`, `banned`, `sellerkey` FROM `apps` WHERE `owner` = ? AND `name` = ?", [$_SESSION['username'], $appName]);

    if ($query->num_rows < 1) {
        dashboard\primary\error("Application not found!");
    } else {
        $row = mysqli_fetch_array($query->result);
        $banned = $row["banned"];

        if ($banned) {
            dashboard\primary\error("Application is banned!");
        } else {
            $_SESSION["app"] = $row["secret"];
            $_SESSION["name"] = $appName;
            $_SESSION["selectedApp"] = $row["name"];
            $_SESSION['sellerkey'] = $row["sellerkey"];

            echo '<meta http-equiv="refresh" content="0">'; 
            dashboard\primary\success("Successfully Selected the App!");
        }
    }
}

if (isset($_POST['create_app'])) {

    $appname = misc\etc\sanitize($_POST['appname']);
    if ($appname == "") {
        dashboard\primary\error("Input a valid name");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }

    $query = misc\mysql\query("SELECT 1 FROM `apps` WHERE name = ? AND owner = ?", [$appname, $_SESSION['username']]);
    if ($query->num_rows > 0) {
        dashboard\primary\error("You already own application with this name!");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }

    $owner = $_SESSION['username'];

    if ($role == "tester") {
        $num_rows = misc\mysql\query("SELECT * FROM `apps` WHERE `owner` = ? AND `ownerid` = ?", [$_SESSION['username'], $_SESSION['ownerid']])->num_rows;

        if ($num_rows > 0) {
            dashboard\primary\error("Tester plan only supports one application!");
            echo '<meta http-equiv="refresh" content="2">';
            return;
        }
    }

    if ($role == "Manager") {
        dashboard\primary\error("Manager Accounts Are Not Allowed To Create Applications");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }

    $clientsecret = hash('sha256', misc\etc\generateRandomString());
    $algos = array(
        'ripemd128',
        'md5',
        'md4',
        'tiger128,4',
        'haval128,3',
        'haval128,4',
        'haval128,5'
    );
    $sellerkey = hash($algos[array_rand($algos)], misc\etc\generateRandomString());
    misc\mysql\query("INSERT INTO `subscriptions` (`name`, `level`, `app`) VALUES ('default', '1', ?)", [$clientsecret]);
    if (!isset($_SESSION['ownerid'])) {
        $ownerQuery = misc\mysql\query("SELECT ownerid FROM accounts WHERE username = ?", [$_SESSION['username']]);
        if ($ownerQuery->num_rows > 0) {
            $ownerRow = mysqli_fetch_array($ownerQuery->result);
            $_SESSION['ownerid'] = $ownerRow['ownerid'];
        }
    }
    
    $query = misc\mysql\query("INSERT INTO `apps` (`owner`, `name`, `secret`, `ownerid`, `enabled`, `hwidcheck`, `sellerkey`) VALUES (?, ?, ?, ?, '1', '1', ?)", [$_SESSION['username'], $appname, $clientsecret, $_SESSION['ownerid'], $sellerkey]);

    if ($query->affected_rows != 0) {
        $_SESSION['secret'] = $clientsecret;
        dashboard\primary\success("Successfully Created App!");
        $_SESSION['app'] = $clientsecret;
        $_SESSION["selectedapp"] = $appname;
        $_SESSION['name'] = $appname;
        $_SESSION['sellerkey'] = $sellerkey;
        echo '<meta http-equiv="refresh" content="2">';
    } else {
        dashboard\primary\error("Failed to create application!");
    }
}

if (isset($_POST['rename_app'])) {
    $appname = misc\etc\sanitize($_POST['appname']);
    if ($appname == "") {
        dashboard\primary\error("Input a valid name");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }
    if ($role == "Manager") {
        dashboard\primary\error("Manager Accounts Aren't Allowed To Rename Applications");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }
    $query = misc\mysql\query("SELECT 1 FROM `apps` WHERE `owner` = ? AND `name` = ?", [$_SESSION['username'], $appname]);
    if ($query->num_rows > 0) {
        dashboard\primary\error("You already have an application with this name!");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }
    misc\mysql\query("UPDATE `accounts` SET `app` = ? WHERE `app` = ? AND `owner` = ?", [$appname, $_SESSION['name'], $_SESSION['username']]);
    $query = misc\mysql\query("UPDATE `apps` SET `name` = ? WHERE `secret` = ? AND `owner` = ?", [$appname, $_SESSION['app'], $_SESSION['username']]);

    if ($query->affected_rows != 0) {
        $oldName = $_SESSION['name'];
        $_SESSION['name'] = $appname;
        dashboard\primary\success("Successfully Renamed App!");
        misc\cache\purge('KeyAuthApp:' . $oldName . ':' . $_SESSION['ownerid']);
        if ($_SESSION['role'] == "seller") {
            $query = misc\mysql\query("SELECT `customDomain`, `sellerkey`, `customDomainAPI` FROM `apps` WHERE `owner` = ? AND `name` = ?", [$_SESSION['username'], $appname]);
            $row = mysqli_fetch_array($query->result);
            misc\cache\purge('KeyAuthAppPanel:' . $row['customDomain']);
            misc\cache\purge('KeyAuthAppSeller:' . $row['sellerkey']);
        }
        $_SESSION["selectedapp"] = $appname;
        echo '<meta http-equiv="refresh" content="2">';
    } else {
        dashboard\primary\error("Application Renamed Failed!");
    }
}

if (isset($_POST['pauseapp'])) {
    if ($role == "Manager") {
        dashboard\primary\error("Manager accounts aren't allowed to pause applications");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }
    misc\cache\purgePattern('KeyAuthSubs:' . $_SESSION["app"]);
    misc\app\pause();
    dashboard\primary\success("Paused application and any active subscriptions!");
    echo '<meta http-equiv="refresh" content="2">';
}
if (isset($_POST['unpauseapp'])) {
    if ($role == "Manager") {
        dashboard\primary\error("Manager accounts aren't allowed to unpause applications");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }
    misc\cache\purgePattern('KeyAuthSubs:' . $_SESSION["app"]);
    misc\app\unpause();
    dashboard\primary\success("Unpaused application and any paused subscriptions!");
    echo '<meta http-equiv="refresh" content="2">';
}

if (isset($_POST['refreshapp'])) {
    if ($role == "Manager") {
        dashboard\primary\error("Manager Accounts Aren't Allowed To Refresh Applications");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }
    $gen = misc\etc\generateRandomString();
    $new_secret = hash('sha256', $gen);
    $query = misc\mysql\query("UPDATE `apps` SET `secret` = ? WHERE `secret` = ? AND `owner` = ?", [$new_secret, $_SESSION['app'], $_SESSION['username']]);
    $_SESSION['secret'] = $new_secret;
    if ($query->affected_rows != 0) {
        misc\mysql\query("UPDATE `bans` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `buttons` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `chatmsgs` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `chatmutes` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `chats` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `files` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `keys` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `logs` SET `logapp` = ? WHERE `logapp` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `subs` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `subscriptions` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `users` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `uservars` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `vars` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        misc\mysql\query("UPDATE `webhooks` SET `app` = ? WHERE `app` = ?", [$new_secret, $_SESSION['app']]);
        $_SESSION['app'] = $new_secret;
        misc\cache\purge('KeyAuthApp:' . $_SESSION['name'] . ':' . $_SESSION['ownerid']);
        if ($_SESSION['role'] == "seller" || $_SESSION['role'] == "developer") {
            $query = misc\mysql\query("SELECT `sellerkey`,`customDomainAPI` FROM `apps` WHERE `owner` = ? AND `name` = ?", [$_SESSION['username'], $_SESSION['name']]);
            $row = mysqli_fetch_array($query->result);
            misc\cache\purge('KeyAuthAppSeller:' . $row['sellerkey']);
            misc\cache\purge('KeyAuthApp:' . $row['customDomainAPI']);
        }
        dashboard\primary\success("Successfully Refreshed App!");
        echo '<meta http-equiv="refresh" content="2">';
    } else {
        dashboard\primary\error("Application Refresh Failed!");
    }
}

if (isset($_POST['deleteapp'])) {
    if ($role == "Manager") {
        dashboard\primary\error("Manager Accounts Aren't Allowed To Delete Applications");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }

    $confirmName = misc\etc\sanitize($_POST['confirmappname']);
    if ($confirmName != $_SESSION['name']) {
        dashboard\primary\error("Application name doesn't match!");
        echo '<meta http-equiv="refresh" content="2">';
        return;
    }

    $app = $_SESSION['app'];
    $query = misc\mysql\query("DELETE FROM `apps` WHERE `secret` = ?", [$app]);
    if ($query->affected_rows != 0) {
        misc\mysql\query("DELETE FROM `bans` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `buttons` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `chatmsgs` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `chatmutes` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `chats` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `files` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `keys` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `logs` WHERE `logapp` = ?", [$app]);
        misc\mysql\query("DELETE FROM `subs` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `subscriptions` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `users` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `uservars` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `vars` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `webhooks` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `auditLog` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `sellerLogs` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `whitelist` WHERE `app` = ?", [$app]);
        misc\mysql\query("DELETE FROM `accounts` WHERE `app` = ? AND `owner` = ?", [$_SESSION['name'], $_SESSION['username']]);

        misc\cache\purge('KeyAuthApp:' . $_SESSION['name'] . ':' . $_SESSION['ownerid']);
        if ($_SESSION['role'] == "seller" || $_SESSION['role'] == "developer") {
            $query = misc\mysql\query("SELECT `sellerkey`,`customDomainAPI` FROM `apps` WHERE `owner` = ? AND `name` = ?", [$_SESSION['username'], $_SESSION['name']]);
            $row = mysqli_fetch_array($query->result);
            misc\cache\purge('KeyAuthAppSeller:' . $row['sellerkey']);
            misc\cache\purge('KeyAuthApp:' . $row['customDomainAPI']);
        }

        $_SESSION['app'] = NULL;
        dashboard\primary\success("Successfully deleted App!");
        $query = misc\mysql\query("SELECT * FROM `apps` WHERE `owner` = ? AND `ownerid` = ?", [$_SESSION['username'], $_SESSION['ownerid']]);
        if ($query->num_rows == 1) {
            $row = mysqli_fetch_array($query->result);
            $_SESSION['name'] = $row["name"];
            $_SESSION["selectedApp"] = $row["name"];
            $_SESSION['app'] = $row["secret"];
        } else {
            $_SESSION['name'] = NULL;
            $_SESSION["selectedApp"] = NULL;
        }
        echo '<meta http-equiv="refresh" content="2">';
    } else {
        dashboard\primary\error("Application Deletion Failed!");
    }
}

if (isset($_SESSION["app"])) {
    $appsecret = $_SESSION["app"];
    $query = misc\mysql\query("SELECT * FROM `apps` WHERE `secret` = ?", [$appsecret]);

    $row = mysqli_fetch_array($query->result);
    $appname = $row["name"];
    $secret = $row["secret"];
    $version = $row["ver"];
    $ownerid = $row["ownerid"];
    $paused = $row["paused"];

    $_SESSION["secret"] = $secret;
}

?>

<div class="p-4 bg-[#09090d] min-h-screen">
    <div class="mb-1 w-full modern-card mt-4 md:mt-12">
        <div class="modern-card-header">
            <?php require '../app/layout/breadcrumb.php'; ?>
            
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400">
                        <?php echo isset($appname) ? "Manage Applications - " . htmlspecialchars($appname) : "Manage Applications"; ?>
                    </h1>
                    <p class="text-gray-400 mt-2">
                        This is where it all begins. 
                        <a href="https://keyauth.readme.io/reference/manage-application" target="_blank" class="text-blue-400 hover:text-blue-300 hover:underline transition-colors">
                            Learn More
                        </a>
                    </p>
                </div>
                <?php if ($_SESSION['role'] != "Manager") { ?>
                <button data-modal-target="create-app-modal" data-modal-toggle="create-app-modal" class="inline-flex items-center text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:ring-4 focus:ring-blue-500/50 font-semibold rounded-xl text-sm px-6 py-3 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-blue-500/30">
                    <i class="lni lni-circle-plus mr-2 text-lg"></i>
                    Create Application
                </button>
                <?php } ?>
            </div>

            <?php if (isset($_SESSION["app"])) { ?>
            <div class="mb-8 p-6 bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] rounded-2xl border border-gray-700/50 shadow-2xl">
                <div class="flex items-center mb-4">
                    <i class="lni lni-code-alt text-blue-400 text-2xl mr-3"></i>
                    <h2 class="text-xl font-bold text-white">Application Credentials</h2>
                </div>
                <p class="text-sm text-gray-400 mb-4">Use these credentials in your application</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700/50">
                        <p class="text-xs text-gray-500 mb-1">Application Name</p>
                        <p class="text-white font-mono text-sm"><?= htmlspecialchars($appname); ?></p>
                    </div>
                    <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700/50">
                        <p class="text-xs text-gray-500 mb-1">Owner ID</p>
                        <p class="text-white font-mono text-sm"><?= htmlspecialchars($ownerid); ?></p>
                    </div>
                    <div class="p-4 bg-gray-800/30 rounded-lg border border-gray-700/50">
                        <p class="text-xs text-gray-500 mb-1">Version</p>
                        <p class="text-white font-mono text-sm"><?= htmlspecialchars($version); ?></p>
                    </div>
                </div>

                <div class="p-4 bg-gray-900/50 rounded-lg border border-gray-700/50">
                    <p class="text-xs text-gray-500 mb-2">Application Secret</p>
                    <div class="flex items-center justify-between">
                        <code class="text-white font-mono text-sm blur-sm hover:blur-none transition-all cursor-pointer"><?= htmlspecialchars($secret); ?></code>
                        <button onclick="copyToClipboard('<?= htmlspecialchars($secret); ?>')" class="ml-4 inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-500/50 font-medium rounded-lg text-xs px-3 py-2 transition-all">
                            <i class="lni lni-clipboard mr-1"></i>
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mb-8">
                <?php if (isset($_SESSION['app']) && $_SESSION['role'] != "Manager") { ?>
                <button data-modal-target="rename-app-modal" data-modal-toggle="rename-app-modal" class="inline-flex items-center text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 focus:ring-4 focus:ring-purple-500/50 font-semibold rounded-xl text-sm px-5 py-2.5 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-purple-500/30">
                    <i class="lni lni-pencil-alt mr-2"></i>
                    Rename Application
                </button>

                <?php if (!$paused) { ?>
                <button data-modal-target="pause-app-modal" data-modal-toggle="pause-app-modal" class="inline-flex items-center text-white bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 focus:ring-4 focus:ring-orange-500/50 font-semibold rounded-xl text-sm px-5 py-2.5 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-orange-500/30">
                    <i class="lni lni-pause mr-2"></i>
                    Pause Application
                </button>
                <?php } else { ?>
                <button data-modal-target="unpause-app-modal" data-modal-toggle="unpause-app-modal" class="inline-flex items-center text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-500/50 font-semibold rounded-xl text-sm px-5 py-2.5 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-green-500/30">
                    <i class="lni lni-play mr-2"></i>
                    Unpause Application
                </button>
                <?php } ?>

                <button data-modal-target="refresh-app-modal" data-modal-toggle="refresh-app-modal" class="inline-flex items-center text-white bg-gradient-to-r from-cyan-600 to-cyan-700 hover:from-cyan-700 hover:to-cyan-800 focus:ring-4 focus:ring-cyan-500/50 font-semibold rounded-xl text-sm px-5 py-2.5 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-cyan-500/30">
                    <i class="lni lni-reload mr-2"></i>
                    Refresh Secret
                </button>

                <button data-modal-target="delete-app-modal" data-modal-toggle="delete-app-modal" class="inline-flex items-center text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-4 focus:ring-red-500/50 font-semibold rounded-xl text-sm px-5 py-2.5 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-red-500/30">
                    <i class="lni lni-trash-can mr-2"></i>
                    Delete Application
                </button>
                <?php } ?>
            </div>
            <?php } ?>

            <?php if ($_SESSION['role'] != "Manager") { ?>
            <div class="bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] rounded-2xl border border-gray-700/50 shadow-2xl overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <i class="lni lni-package text-blue-400 text-2xl mr-3"></i>
                        <h3 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">
                            Your Applications
                        </h3>
                    </div>
                    
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-white">
                            <thead>
                                <tr class="border-b border-gray-700/50">
                                    <th class="px-6 py-4 text-blue-400 font-semibold">
                                        <div class="flex items-center">
                                            <i class="lni lni-code-alt mr-2"></i>
                                            Application Name
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-purple-400 font-semibold">
                                        <div class="flex items-center">
                                            <i class="lni lni-signal mr-2"></i>
                                            Status
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-cyan-400 font-semibold text-right">
                                        <div class="flex items-center justify-end">
                                            <i class="lni lni-pointer mr-2"></i>
                                            Action
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                $rows = array();
                $query = misc\mysql\query("SELECT * FROM `apps` WHERE `owner` = ? AND `ownerid` = ? ORDER BY `name` ASC", [$_SESSION['username'], $_SESSION['ownerid']]);
                while ($r = mysqli_fetch_assoc($query->result)) {
                    $rows[] = $r;
                }

                if (count($rows) == 0) {
                    echo '<tr><td colspan="3" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <i class="lni lni-inbox text-7xl mb-4 text-gray-600"></i>
                            <p class="text-lg font-semibold mb-2 text-gray-300">No applications yet</p>
                            <p class="text-sm text-gray-500">Click "Create Application" to get started</p>
                        </div>
                    </td></tr>';
                }

                foreach ($rows as $row) {
                    $appName = $row['name'];
                    $paused = $row['paused'];

                    if (isset($_SESSION["selectedApp"])) {
                        $appSelected = ($_SESSION["selectedApp"] == $appName);
                    } else {
                        $appSelected = 0;
                    }
                ?>
                                <tr class="border-b border-gray-800/30 hover:bg-gradient-to-r hover:from-blue-600/10 hover:to-purple-600/10 transition-all duration-300 <?= $appSelected ? 'bg-gradient-to-r from-blue-600/20 to-purple-600/20' : ''; ?>">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center mr-3 shadow-lg shadow-blue-500/30">
                                                <i class="lni lni-code text-white text-lg"></i>
                                            </div>
                                            <span class="font-semibold text-white"><?= htmlspecialchars($appName) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($paused == 1) { ?>
                                        <span class="inline-flex items-center bg-orange-500/20 text-orange-400 text-xs font-semibold px-3 py-1.5 rounded-full border border-orange-500/30">
                                            <span class="h-2 w-2 rounded-full bg-orange-400 mr-2 animate-pulse"></span>
                                            Paused
                                        </span>
                                        <?php } else { ?>
                                        <span class="inline-flex items-center bg-green-500/20 text-green-400 text-xs font-semibold px-3 py-1.5 rounded-full border border-green-500/30">
                                            <span class="h-2 w-2 rounded-full bg-green-400 mr-2 animate-pulse"></span>
                                            Active
                                        </span>
                                        <?php } ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form method="POST">
                                            <?php if ($appSelected) { ?>
                                            <button type="submit" value="<?= htmlspecialchars($appName) ?>" name="selectApp" class="inline-flex items-center bg-gradient-to-r from-blue-600 to-purple-600 text-white text-xs font-semibold px-4 py-2 rounded-lg shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all duration-300 hover:scale-105">
                                                <span class="h-2 w-2 rounded-full bg-white mr-2 animate-pulse"></span>
                                                Selected
                                            </button>
                                            <?php } else { ?>
                                            <button type="submit" value="<?= htmlspecialchars($appName) ?>" name="selectApp" class="inline-flex items-center bg-gray-700 hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 text-gray-300 hover:text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-blue-500/30">
                                                <span class="h-2 w-2 rounded-full bg-gray-500 mr-2"></span>
                                                Select
                                            </button>
                                            <?php } ?>
                                        </form>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Create New App Modal -->
<div id="create-app-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] rounded-2xl border border-blue-500/30 shadow-2xl">
            <div class="px-6 py-6 lg:px-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">
                        Create New Application
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-white" data-modal-hide="create-app-modal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <form class="space-y-6" method="POST">
                    <div class="relative">
                        <input type="text" id="appname" name="appname" class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-white bg-transparent rounded-lg border border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 appearance-none peer transition-all" placeholder=" " required>
                        <label for="appname" class="absolute text-sm text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-[#0f0f17] px-2 peer-focus:px-2 peer-focus:text-blue-400 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 left-1">
                            Application Name
                        </label>
                    </div>
                    <button type="submit" name="create_app" class="w-full text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:ring-4 focus:ring-blue-500/50 font-bold rounded-lg text-sm px-5 py-3 text-center transition-all duration-300 transform hover:scale-105">
                        <i class="lni lni-circle-plus mr-2"></i>
                        Create Application
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rename App Modal -->
<div id="rename-app-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] rounded-2xl border border-purple-500/30 shadow-2xl">
            <div class="px-6 py-6 lg:px-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500">
                        Rename Application
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-white" data-modal-hide="rename-app-modal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <form class="space-y-6" method="POST">
                    <div class="relative">
                        <input type="text" id="appname" name="appname" class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-white bg-transparent rounded-lg border border-gray-600 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/50 appearance-none peer transition-all" placeholder=" " required>
                        <label for="appname" class="absolute text-sm text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-[#0f0f17] px-2 peer-focus:px-2 peer-focus:text-purple-400 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 left-1">
                            New Application Name
                        </label>
                    </div>
                    <button type="submit" name="rename_app" class="w-full text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 focus:ring-4 focus:ring-purple-500/50 font-bold rounded-lg text-sm px-5 py-3 text-center transition-all duration-300 transform hover:scale-105">
                        <i class="lni lni-pencil-alt mr-2"></i>
                        Rename Application
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Pause App Modal -->
<div id="pause-app-modal" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] rounded-2xl border border-orange-500/30 shadow-2xl">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-500/20 mb-4">
                    <i class="lni lni-warning text-orange-400 text-3xl"></i>
                </div>
                <h3 class="mb-4 text-xl font-bold text-white">Pause Application?</h3>
                <p class="mb-6 text-sm text-gray-400">
                    Pausing your app will make it unusable until you unpause it. All users will be temporarily blocked.
                </p>
                <form method="POST" class="flex gap-3 justify-center">
                    <button type="submit" name="pauseapp" class="text-white bg-orange-600 hover:bg-orange-700 focus:ring-4 focus:ring-orange-500/50 font-semibold rounded-lg text-sm px-6 py-2.5 transition-all">
                        Yes, Pause It
                    </button>
                    <button type="button" data-modal-hide="pause-app-modal" class="text-white bg-gray-700 hover:bg-gray-600 focus:ring-4 focus:ring-gray-500/50 font-semibold rounded-lg text-sm px-6 py-2.5 transition-all">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Unpause App Modal -->
<div id="unpause-app-modal" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] rounded-2xl border border-green-500/30 shadow-2xl">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-500/20 mb-4">
                    <i class="lni lni-play text-green-400 text-3xl"></i>
                </div>
                <h3 class="mb-4 text-xl font-bold text-white">Unpause Application?</h3>
                <p class="mb-6 text-sm text-gray-400">
                    Unpausing your app will make it accessible to all users again.
                </p>
                <form method="POST" class="flex gap-3 justify-center">
                    <button type="submit" name="unpauseapp" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-500/50 font-semibold rounded-lg text-sm px-6 py-2.5 transition-all">
                        Yes, Unpause It
                    </button>
                    <button type="button" data-modal-hide="unpause-app-modal" class="text-white bg-gray-700 hover:bg-gray-600 focus:ring-4 focus:ring-gray-500/50 font-semibold rounded-lg text-sm px-6 py-2.5 transition-all">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Refresh App Secret Modal -->
<div id="refresh-app-modal" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] rounded-2xl border border-cyan-500/30 shadow-2xl">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-cyan-500/20 mb-4">
                    <i class="lni lni-reload text-cyan-400 text-3xl"></i>
                </div>
                <h3 class="mb-4 text-xl font-bold text-white">Refresh Application Secret?</h3>
                <p class="mb-6 text-sm text-gray-400">
                    Make sure you update your application secret in your program after refreshing.
                </p>
                <form method="POST" class="flex gap-3 justify-center">
                    <button type="submit" name="refreshapp" class="text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-500/50 font-semibold rounded-lg text-sm px-6 py-2.5 transition-all">
                        Yes, Refresh It
                    </button>
                    <button type="button" data-modal-hide="refresh-app-modal" class="text-white bg-gray-700 hover:bg-gray-600 focus:ring-4 focus:ring-gray-500/50 font-semibold rounded-lg text-sm px-6 py-2.5 transition-all">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete App Modal -->
<div id="delete-app-modal" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] rounded-2xl border border-red-500/30 shadow-2xl">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-500/20 mb-4">
                    <i class="lni lni-trash-can text-red-400 text-3xl"></i>
                </div>
                <h3 class="mb-4 text-xl font-bold text-white">Delete Application?</h3>
                <p class="mb-4 text-sm text-red-400 font-semibold">
                    This action cannot be undone!
                </p>
                <p class="mb-6 text-sm text-gray-400">
                    Please enter your application name to confirm: <br>
                    <span class="font-mono text-white"><?= isset($appname) ? htmlspecialchars($appname) : '' ?></span>
                </p>
                <form method="POST" class="space-y-4">
                    <div class="relative">
                        <input type="text" id="confirmappname" name="confirmappname" class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-white bg-transparent rounded-lg border border-gray-600 focus:border-red-500 focus:ring-2 focus:ring-red-500/50 appearance-none peer transition-all" placeholder=" " required>
                        <label for="confirmappname" class="absolute text-sm text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-[#0f0f17] px-2 peer-focus:px-2 peer-focus:text-red-400 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 left-1">
                            Application Name
                        </label>
                    </div>
                    <div class="flex gap-3 justify-center">
                        <button type="submit" name="deleteapp" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-500/50 font-semibold rounded-lg text-sm px-6 py-2.5 transition-all">
                            Yes, Delete It
                        </button>
                        <button type="button" data-modal-hide="delete-app-modal" class="text-white bg-gray-700 hover:bg-gray-600 focus:ring-4 focus:ring-gray-500/50 font-semibold rounded-lg text-sm px-6 py-2.5 transition-all">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Secret copied to clipboard',
            showConfirmButton: false,
            timer: 1500,
            iconColor: '#3b82f6',
            customClass: {
                popup: 'bg-[#0f0f17] border border-blue-500/30 rounded-2xl',
                title: 'text-white',
                content: 'text-gray-400'
            }
        });
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
    });
}
</script>
