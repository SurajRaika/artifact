<?php
// base.php

// Ensure $slot is defined, or set it to an empty string to prevent errors
if (!isset($slot)) {
    $slot = '';
}

// Initial State Variables (Moved here as they are application-wide state)
// Note: In a real app, this should come from a database/session.
$availableFunds = 15450000;
$accessFee = 5000;

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <?php $title=$page; include __DIR__ . '/../global/head.php'; // HEADER COMPONENT ?>
    <script>
        window.onload = function() {
            updateFundsDisplay(); // Set initial funds display
            // On load, apply the 'active' class to the correct button based on the current file
            const currentPageFile = window.location.pathname.split('/').pop();
            let activeView = ''; // Default

            if (currentPageFile === '' || currentPageFile === 'index.php' ) activeView = 'live-auctions';
            else if (currentPageFile === 'private-lots.php') activeView = 'private-lots';
            else if (currentPageFile === 'sell-artifact.php') activeView = 'sell-artifact';
            else if (currentPageFile === 'bids.php') activeView = 'my-bids';
            else if (currentPageFile === 'my-search.php') activeView = 'search';

            document.querySelectorAll('.nav-button').forEach(btn => {
                const buttonViewId = btn.getAttribute('data-view');
                if (buttonViewId === activeView) {
                    btn.classList.add('active');
                    btn.classList.remove('opacity-50');
                } else {
                    btn.classList.remove('active');
                    btn.classList.add('opacity-50');
                }
            });
            currentView = activeView;
            lucide.createIcons();

            // Call specific initializers for the current page (assuming they are defined in index.php, etc.)
            if (typeof initIndexPage === 'function') initIndexPage();
            if (typeof initPrivateLots === 'function') initPrivateLots();
            if (typeof initSellArtifact === 'function') initSellArtifact();
            if (typeof initMyBids === 'function') initMyBids();
        }
    </script>
</head>

<body class="h-screen flex flex-col overflow-hidden">

    <?php include __DIR__ . '/../header.php'; // HEADER COMPONENT ?>

    <div class="flex flex-col md:flex-row flex-1 overflow-hidden">
        <div id="layout_main" class="flex flex-col md:flex-row flex-1 relative overflow-hidden">

            <main class="flex-1 bg-gray-50 overflow-y-auto relative">
                <?php echo $slot; // MAIN CONTENT SLOT ?>
            </main>

            <?php include __DIR__ . '/../bidding-terminal.php'; // BIDDING TERMINAL COMPONENT ?>
        </div>
    </div>

    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <?php include __DIR__ . '/../footer.php'; // JAVASCRIPT/GLOBAL LOGIC COMPONENT ?>

</body>

</html>