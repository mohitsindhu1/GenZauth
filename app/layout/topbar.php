<?php
if (isset($_POST["shop"])) {
   dashboard\primary\error("Coming soon!");
}
?>

<nav class="fixed z-30 w-full bg-gradient-to-r from-[#0f0f17] via-[#1a1a24] to-[#0f0f17] border-b border-gray-800/50 backdrop-blur-xl" style="height: 64px;">
    <div class="py-2 px-3 lg:px-5 lg:pl-3" style="height: 100%;">
        <div class="flex justify-between items-center h-full">
            <div class="flex justify-start items-center h-full">
                <div
                    class="hidden p-2 text-white rounded cursor-pointer lg:inline hover:opacity-60 transition duration-200 -ml-8">
                    <div class="w-6 h-6">
                    </div>
                </div>

                <button id="toggleSidebarMobile" aria-expanded="true" aria-controls="sidebar"
                    class="p-2 mr-2 text-white rounded-lg cursor-pointer lg:hidden hover:bg-gray-800/50 hover:opacity-60 focus:ring-0 transition-all duration-300">
                    <svg id="toggleSidebarMobileHamburger" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <svg id="toggleSidebarMobileClose" class="hidden w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>

                <a href="?page=manage-apps" class="flex items-center group">
                    <div class="relative" style="max-height: 40px;">
                        <img src="https://cdn.keyauth.cc/v3/imgs/KeyauthBanner.png" alt="GenZ Auth" class="h-8 w-auto transition-transform duration-300 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/0 via-purple-600/20 to-pink-600/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 blur-xl"></div>
                    </div>
                </a>
            </div>


            <div class="hidden md:flex items-center gap-2 h-full">
                <a href="https://keyauth.readme.io" target="_blank"
                    class="inline-flex items-center text-gray-300 bg-gray-800/50 hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-purple-600/20 hover:text-white hover:border-blue-500/50 focus:ring-0 font-medium rounded-lg text-xs px-3 py-1.5 transition-all duration-300 border border-gray-700/50 backdrop-blur-sm">
                    <i class="lni lni-code mr-2"></i>Docs
                </a>

                <a href="https://github.com/keyauth" target="_blank"
                    class="inline-flex items-center text-gray-300 bg-gray-800/50 hover:bg-gradient-to-r hover:from-purple-600/20 hover:to-pink-600/20 hover:text-white hover:border-purple-500/50 focus:ring-0 font-medium rounded-lg text-xs px-3 py-1.5 transition-all duration-300 border border-gray-700/50 backdrop-blur-sm">
                    <i class="lni lni-github-original mr-2"></i>GitHub
                </a>

                <a href="https://youtube.com/keyauth" target="_blank"
                    class="inline-flex items-center text-gray-300 bg-gray-800/50 hover:bg-gradient-to-r hover:from-red-600/20 hover:to-pink-600/20 hover:text-white hover:border-red-500/50 focus:ring-0 font-medium rounded-lg text-xs px-3 py-1.5 transition-all duration-300 border border-gray-700/50 backdrop-blur-sm">
                    <i class="lni lni-youtube mr-2"></i>YouTube
                </a>

                <a href="https://t.me/keyauth" target="_blank"
                    class="inline-flex items-center text-gray-300 bg-gray-800/50 hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-cyan-600/20 hover:text-white hover:border-blue-500/50 focus:ring-0 font-medium rounded-lg text-xs px-3 py-1.5 transition-all duration-300 border border-gray-700/50 backdrop-blur-sm">
                    <i class="lni lni-telegram-original mr-2"></i>Telegram
                </a>

                <a href="https://vaultcord.com" target="_blank"
                    class="inline-flex items-center text-gray-300 bg-gray-800/50 hover:bg-gradient-to-r hover:from-indigo-600/20 hover:to-blue-600/20 hover:text-white hover:border-indigo-500/50 focus:ring-0 font-medium rounded-lg text-xs px-3 py-1.5 transition-all duration-300 border border-gray-700/50 backdrop-blur-sm">
                    <i class="lni lni-discord-alt mr-2"></i>VaultCord
                </a>
            </div>

            <!-- Mobile Dropdown Menu -->
            <div id="dropdownDotsHorizontal"
                class="z-10 hidden bg-gradient-to-br from-[#0f0f17] to-[#1a1a24] divide-y divide-gray-800/50 border border-blue-500/30 rounded-xl shadow-2xl w-48 backdrop-blur-xl">
                <ul class="py-2 text-white text-sm" aria-labelledby="dropdownMenuIconHorizontalButton">
                    <li>
                        <a href="https://keyauth.readme.io" target="_blank"
                            class="flex items-center px-4 py-3 hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-purple-600/20 transition-all duration-300">
                            <i class="lni lni-code mr-3 text-blue-400"></i>Documentation
                        </a>
                    </li>
                    <li>
                        <a href="https://github.com/keyauth" target="_blank"
                            class="flex items-center px-4 py-3 hover:bg-gradient-to-r hover:from-purple-600/20 hover:to-pink-600/20 transition-all duration-300">
                            <i class="lni lni-github-original mr-3 text-purple-400"></i>Examples
                        </a>
                    </li>
                    <li>
                        <a href="https://youtube.com/keyauth" target="_blank"
                            class="flex items-center px-4 py-3 hover:bg-gradient-to-r hover:from-red-600/20 hover:to-pink-600/20 transition-all duration-300">
                            <i class="lni lni-youtube mr-3 text-red-400"></i>YouTube
                        </a>
                    </li>
                    <li>
                        <a href="https://t.me/keyauth" target="_blank"
                            class="flex items-center px-4 py-3 hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-cyan-600/20 transition-all duration-300">
                            <i class="lni lni-telegram-original mr-3 text-blue-400"></i>Telegram
                        </a>
                    </li>
                    <li>
                        <a href="https://twitter.com/keyauth" target="_blank"
                            class="flex items-center px-4 py-3 hover:bg-gradient-to-r hover:from-sky-600/20 hover:to-blue-600/20 transition-all duration-300">
                            <i class="lni lni-twitter-original mr-3 text-sky-400"></i>Twitter
                        </a>
                    </li>
                    <li>
                        <a href="https://instagram.com/keyauthllc" target="_blank"
                            class="flex items-center px-4 py-3 hover:bg-gradient-to-r hover:from-pink-600/20 hover:to-purple-600/20 transition-all duration-300">
                            <i class="lni lni-instagram-original mr-3 text-pink-400"></i>Instagram
                        </a>
                    </li>
                    <li>
                        <a href="https://vaultcord.com" target="_blank"
                            class="flex items-center px-4 py-3 hover:bg-gradient-to-r hover:from-indigo-600/20 hover:to-blue-600/20 transition-all duration-300 rounded-b-xl">
                            <i class="lni lni-discord-alt mr-3 text-indigo-400"></i>VaultCord
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
