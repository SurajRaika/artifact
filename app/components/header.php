<?php
// components/header.php

// Note: This component assumes $page is defined in the script that includes app/components/layouts/base.php.
// It also relies on the JavaScript functions defined in footer.php.

?>
<header class="flex flex-col items-center justify-between z-20 w-full brutalist-border-b">
    <div class="h-16 bg-white flex items-center justify-between px-4 z-20 shrink-0 w-full border-b-2 border-black ">
        <div class="flex items-center gap-4">
            <div class="font-bold text-2xl tracking-tight flex items-center gap-1.5">
                <span class="logotype text-4xl font-semibold sm:inline">AUCTION</span>
                <div class="bg-black text-white px-2 py-0.5 text-sm font-semibold tracking-widest ">
                    .COM
                </div>
            </div>
        </div>

        <nav id="main-nav-desktop" class="hidden md:flex gap-6 font-medium text-sm uppercase tracking-wide">
            <button data-view="live-auctions" onclick="changeView('live-auctions')"
                class="nav-button hover:underline decoration-2 underline-offset-4 opacity-50 ">Live Auctions</button>
            <button data-view="private-lots" onclick="changeView('private-lots')"
                class="nav-button hover:underline decoration-2 underline-offset-4 opacity-50">Private Lots</button>
            <button data-view="sell-artifact" onclick="changeView('sell-artifact')"
                class="nav-button hover:underline decoration-2 underline-offset-4 opacity-50">Sell Artifact</button>
            <button data-view="my-bids" onclick="changeView('my-bids')"
                class="nav-button hover:underline underline-offset-4 decoration-2 opacity-50">My Bids</button>
            <button data-view="search" onclick="changeView('my-search')"
                class="nav-button hover:underline decoration-2 underline-offset-4 opacity-50">Search</button>
        </nav>

        <div class="flex items-center gap-3 relative">

            <div id="profile-modal"
                class="absolute top-full right-0 mt-2 brutalist-border brutalist-shadow bg-white z-50 w-72 hidden">
                <div class="flex items-center p-4 border-b-2 border-black">
                    <div
                        class="bg-black text-white w-10 h-10 rounded-full flex items-center justify-center font-mono font-bold text-lg mr-3">
                        D9
                    </div>
                    <div>
                        <div class="font-bold uppercase text-sm">User:D9F3</div>
                        <div class="text-xs text-gray-600 font-mono">Status: Active Bidder</div>
                    </div>
                </div>

                <div class="p-4">
                    <h4 class="font-bold uppercase text-xs mb-2 border-b border-gray-200 pb-1">Recent Transactions
                    </h4>
                    <ul id="transaction-list" class="space-y-2 text-xs font-mono">
                    </ul>
                    <button
                        class="w-full mt-4 bg-black text-white py-2 text-xs font-bold uppercase hover:bg-gray-800 brutalist-shadow-sm brutalist-active cursor-pointer brutalist-border">
                        View Full History
                    </button>
            <button onclick="window.location.href='/authenticate?action=register'"
        class="w-full mt-4 hover:bg-red-600 text-black py-2 text-xs font-bold uppercase cursor-pointer border-red-300 brutalist-shadow-sm brutalist-active brutalist-border">
    Log OUT
</button>
                </div>
            </div>

            <div id="funds-display" onclick="toggleProfileModal()" title="View Profile & Transactions"
                class="text-right cursor-pointer p-2 rounded hover:bg-gray-100 transition-colors sm:block sm:w-36">
                <div class="text-[10px] uppercase font-bold text-gray-500">Available Funds</div>
                <div class="font-bold font-mono text-sm sm:text-base transition-opacity opacity-0">$0</div>
            </div>

        </div>
    </div>

    <nav id="mobile-nav"
        class=" md:hidden w-full bg-white flex justify-center text-sm uppercase tracking-wide font-medium">
        <button data-view="live-auctions" onclick="changeView('live-auctions'); "
            class="nav-button p-3 border-b border-gray-100 hover:bg-gray-50 text-left ">Live Auctions</button>
        <button data-view="private-lots" onclick="changeView('private-lots');"
            class="nav-button p-3 border-b border-gray-100 hover:bg-gray-50 text-left opacity-50">Private
            Lots</button>
        <button data-view="sell-artifact" onclick="changeView('sell-artifact');"
            class="nav-button p-3 border-b border-gray-100 hover:bg-gray-50 text-left opacity-50">Sell
            Artifact</button>
        <button data-view="my-bids" onclick="changeView('my-bids'); "
            class="nav-button p-3 border-b border-gray-100 hover:bg-gray-50 text-left opacity-50">My Bids</button>
    </nav>
</header>