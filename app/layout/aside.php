<?php

$page = isset($_GET['page']) ? $_GET['page'] : "manage-apps";

?>

<style>
/* Hide vertical scrollbar for Webkit-based browsers (e.g., Chrome, Safari) */
*::-webkit-scrollbar {
    width: 6px;
}

*::-webkit-scrollbar-track {
    background: transparent;
}

*::-webkit-scrollbar-thumb {
    background: rgba(96, 165, 250, 0.3);
    border-radius: 10px;
}

*::-webkit-scrollbar-thumb:hover {
    background: rgba(96, 165, 250, 0.5);
}
</style>

<aside id="sidebar"
    class="flex hidden fixed top-0 left-0 z-20 flex-col flex-shrink-0 pt-16 w-64 h-full duration-200 lg:flex transition-width"
    aria-label="Sidebar">
    <div class="flex relative flex-col flex-1 pt-0 min-h-0 bg-gradient-to-b from-[#0f0f17] via-[#1a1a24] to-[#0f0f17] border-r border-gray-800/50 backdrop-blur-xl">
        <div class="flex overflow-y-auto flex-col flex-1 pt-5 pb-4">
            <div class="flex-1 px-3 space-y-1 bg-transparent mt-8">
                <?php require '../app/layout/profile.php';?>
                <div class="mb-4 border-b border-gray-800/50">
                    <ul class="grid grid-cols-2 -mb-px text-sm font-medium text-center" id="myTab"
                        data-tabs-toggle="#myTabContent" role="tablist">
                        <?php if (isset($_SESSION["app"])){ ?>
                        <li class="mr-1" role="presentation">
                            <button class="inline-block p-4 rounded-t-lg text-gray-400 hover:text-white hover:bg-gradient-to-r hover:from-blue-600/10 hover:to-purple-600/10 transition-all duration-300 w-full" id="app-tab"
                                data-tabs-target="#app" type="button" role="tab" aria-controls="app" data-tbt="app"
                                aria-selected="false" data-popover-target="app-popover">
                                <i class="lni lni-package mr-1"></i>App
                            </button>
                                <?php dashboard\primary\popover("app-popover", "Application", "Find everything related to your application here"); ?>
                        </li>
                        <?php } ?>
                        <li class="mr-1" role="presentation">
                            <button class="inline-block p-4 rounded-t-lg text-gray-400 hover:text-white hover:bg-gradient-to-r hover:from-purple-600/10 hover:to-pink-600/10 transition-all duration-300 w-full" id="account-tab"
                                data-tabs-target="#account" type="button" role="tab" data-tbt="account"
                                aria-controls="account" aria-selected="false"
                                data-popover-target="account-popover">
                                <i class="lni lni-user mr-1"></i>Account
                            </button>
                                <?php dashboard\primary\popover("account-popover", "Account", "Find everything related to your Account here."); ?>
                        </li>
                    </ul>
                </div>
                <div id="myTabContent">
                    <div class="hidden p-2 rounded-lg" id="app" role="tabpanel" aria-labelledby="app-tab">
                        <ul class="space-y-1 font-medium">
                            <?php if (!($role == "Manager" && !($permissions & 1))){ ?>
                            <li>
                                <a href="?page=licenses"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-purple-600/20 border border-transparent hover:border-blue-500/30 group transition-all duration-300">
                                    <i class="lni lni-key text-blue-400"></i>
                                    <span class="ml-3">Licenses</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 2))){ ?>
                            <li>
                                <a href="?page=users"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-purple-600/20 hover:to-pink-600/20 border border-transparent hover:border-purple-500/30 group transition-all duration-300">
                                    <i class="lni lni-users text-purple-400"></i>
                                    <span class="ml-3">Users</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 2048))){ ?>
                            <li>
                                <a href="?page=tokens"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-pink-600/20 hover:to-red-600/20 border border-transparent hover:border-pink-500/30 group transition-all duration-300">
                                    <i class="lni lni-tag text-pink-400"></i>
                                    <span class="ml-3">Tokens</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 4))){ ?>
                            <li>
                                <a href="?page=subscriptions"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-yellow-600/20 hover:to-orange-600/20 border border-transparent hover:border-yellow-500/30 group transition-all duration-300">
                                    <i class="lni lni-crown text-yellow-400"></i>
                                    <span class="ml-3">Subscriptions</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 8))){ ?>
                            <li>
                                <a href="?page=chats"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-green-600/20 hover:to-emerald-600/20 border border-transparent hover:border-green-500/30 group transition-all duration-300">
                                    <i class="lni lni-popup text-green-400"></i>
                                    <span class="ml-3">Chats</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 16))){ ?>
                            <li>
                                <a href="?page=sessions"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-cyan-600/20 hover:to-blue-600/20 border border-transparent hover:border-cyan-500/30 group transition-all duration-300">
                                    <i class="lni lni-timer text-cyan-400"></i>
                                    <span class="ml-3">Sessions</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 32))){ ?>
                            <li>
                                <a href="?page=webhooks"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-indigo-600/20 hover:to-purple-600/20 border border-transparent hover:border-indigo-500/30 group transition-all duration-300">
                                    <i class="lni lni-webhook text-indigo-400"></i>
                                    <span class="ml-3">Webhooks</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 64))){ ?>
                            <li>
                                <a href="?page=files"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-teal-600/20 hover:to-green-600/20 border border-transparent hover:border-teal-500/30 group transition-all duration-300">
                                    <i class="lni lni-files text-teal-400"></i>
                                    <span class="ml-3">Files</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 128))){ ?>
                            <li>
                                <a href="?page=vars"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-violet-600/20 hover:to-purple-600/20 border border-transparent hover:border-violet-500/30 group transition-all duration-300">
                                    <i class="lni lni-code text-violet-400"></i>
                                    <span class="ml-3">Variables</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 256))){ ?>
                            <li>
                                <a href="?page=logs"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-orange-600/20 hover:to-yellow-600/20 border border-transparent hover:border-orange-500/30 group transition-all duration-300">
                                    <i class="lni lni-archive text-orange-400"></i>
                                    <span class="ml-3">Logs</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 512))){ ?>
                            <li>
                                <a href="?page=blacklists"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-red-600/20 hover:to-pink-600/20 border border-transparent hover:border-red-500/30 group transition-all duration-300">
                                    <i class="lni lni-ban text-red-400"></i>
                                    <span class="ml-3">Blacklists</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!($role == "Manager" && !($permissions & 1024))){ ?>
                            <li>
                                <a href="?page=app-settings"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-gray-600/20 hover:to-slate-600/20 border border-transparent hover:border-gray-500/30 group transition-all duration-300">
                                    <i class="lni lni-cog text-gray-400"></i>
                                    <span class="ml-3">Settings</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($role == "seller" || $role == "developer"){ ?>
                            <li class="pt-3 border-t border-gray-800/50">
                                <a href="?page=seller-api"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-cyan-600/20 border border-transparent hover:border-blue-500/30 group transition-all duration-300">
                                    <i class="lni lni-key text-blue-400"></i>
                                    <span class="ml-3">Seller API</span>
                                    <span class="ml-auto bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded-full text-xs font-semibold">API</span>
                                </a>
                            </li>
                            <li>
                                <a href="?page=telegram-bot"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-purple-600/20 border border-transparent hover:border-blue-500/30 group transition-all duration-300">
                                    <i class="lni lni-telegram text-blue-400"></i>
                                    <span class="ml-3">Telegram Bot</span>
                                    <span class="ml-auto bg-green-500/20 text-green-400 px-2 py-0.5 rounded-full text-xs font-semibold">Pro</span>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($role != "Manager"){ ?>
                            <li>
                                <a href="?page=audit-logs"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-purple-600/20 hover:to-pink-600/20 border border-transparent hover:border-purple-500/30 group transition-all duration-300">
                                    <i class="lni lni-archive text-purple-400"></i>
                                    <span class="ml-3">Audit Logs</span>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="hidden p-2 rounded-lg" id="account" role="tabpanel" aria-labelledby="account-tab">
                        <ul class="space-y-1 font-medium">
                            <li>
                                <a href="?page=manage-apps"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-purple-600/20 border border-transparent hover:border-blue-500/30 group transition-all duration-300">
                                    <i class="lni lni-package text-blue-400"></i>
                                    <span class="ml-3">Manage Apps</span>
                                </a>
                            </li>
                            <li>
                                <a href="?page=account-settings"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-purple-600/20 hover:to-pink-600/20 border border-transparent hover:border-purple-500/30 group transition-all duration-300">
                                    <i class="lni lni-cog text-purple-400"></i>
                                    <span class="ml-3">Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="?page=account-logs"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white hover:bg-gradient-to-r hover:from-pink-600/20 hover:to-red-600/20 border border-transparent hover:border-pink-500/30 group transition-all duration-300">
                                    <i class="lni lni-archive text-pink-400"></i>
                                    <span class="ml-3">Account Logs</span>
                                </a>
                            </li>
                            <?php if ($role != "tester") { ?>
                            <li class="pt-3 border-t border-gray-800/50">
                                <a href="?page=upgrade"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white bg-gradient-to-r from-blue-600/10 to-purple-600/10 border border-blue-500/30 hover:from-blue-600/30 hover:to-purple-600/30 hover:border-blue-500/50 group transition-all duration-300">
                                    <i class="lni lni-rocket text-blue-400"></i>
                                    <span class="ml-3 font-semibold">View Plans</span>
                                    <span class="ml-auto bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded-full text-xs font-semibold">Upgrade</span>
                                </a>
                            </li>
                            <?php } else { ?>
                            <li class="pt-3 border-t border-gray-800/50">
                                <a href="?page=upgrade"
                                    class="flex items-center p-3 rounded-xl text-gray-300 hover:text-white bg-gradient-to-r from-green-600/10 to-emerald-600/10 border border-green-500/30 hover:from-green-600/30 hover:to-emerald-600/30 hover:border-green-500/50 group transition-all duration-300 animate-pulse">
                                    <i class="lni lni-rocket text-green-400"></i>
                                    <span class="ml-3 font-semibold">Upgrade Now</span>
                                    <span class="ml-auto bg-green-500/20 text-green-400 px-2 py-0.5 rounded-full text-xs font-semibold">Free</span>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>
