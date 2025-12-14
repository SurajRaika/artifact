<?php
session_start();




















require_once __DIR__ . "/../../config.php";














// If the user is NOT logged in, redirect them to /authenticate
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: /authenticate");
    exit; // stop further execution
}

// rest of your protected page code...












// base.php

// Ensure $slot is defined, or set it to an empty string to prevent errors
if (!isset($slot)) {
    $slot = '';
}

// Make sure session is started

// Assuming $link is your mysqli connection

// Get user email from session
$email = $_SESSION["email"]; // email must exist in session

$sql_get_credit = "SELECT credit FROM users WHERE email = ?";
$stmt = mysqli_prepare($link, $sql_get_credit);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    // Store result to get number of rows
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 1) {
        // Bind the result
        mysqli_stmt_bind_result($stmt, $credit);
        mysqli_stmt_fetch($stmt);

        // Update session and local variable
        $_SESSION["credit"] = $credit;
        $availableFunds = $credit;
    } else {
        // No user found
        $_SESSION["credit"] = 0;
        $availableFunds = 0;
    }

    mysqli_stmt_close($stmt);
} else {
    die("Database query failed: " . mysqli_error($link));
}
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
    <script>
    // PHP variables passed to JavaScript
    let availableFunds = <?php echo $availableFunds; ?>;
    const accessFee = <?php echo $accessFee; ?>;

    // NEW MOCK TRANSACTION DATA (Application-wide)
    let transactions = [{
            id: 1,
            type: 'BID PLACED',
            amount: -4500000,
            date: '2025-11-24',
            lot: 103
        },
        {
            id: 2,
            type: 'BID PLACED',
            amount: -1200000,
            date: '2025-11-23',
            lot: 102
        },
        {
            id: 3,
            type: 'DEPOSIT',
            amount: 10000000,
            date: '2025-11-20',
            lot: null
        },
        {
            id: 4,
            type: 'BID PLACED',
            amount: -550000,
            date: '2025-11-19',
            lot: 101
        },
        {
            id: 5,
            type: 'WITHDRAWAL',
            amount: -500000,
            date: '2025-11-18',
            lot: null
        },
    ];


    let selectedArtifactId = null;
    let currentFilter = 'all';
    let currentView = 'live-auctions'; // Tracks which view is currently active (initialized in base.php onload)
    let uploadedFiles = []; // Array to store mock file data
    let privateAccessGranted = false; // New state for private access


    // Helper to format currency
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });

    // Helper to format time (seconds to HH:MM:SS)
    function formatTime(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return [hours, minutes, seconds]
            .map(val => val.toString().padStart(2, '0'))
            .join(':');
    }

    // Function to update the Available Funds display in the header
    function updateFundsDisplay() {
        const fundsDisplay = document.querySelector('#funds-display div:last-child');
        if (fundsDisplay) {
            fundsDisplay.textContent = formatter.format(availableFunds);
            fundsDisplay.classList.remove('opacity-0')
        }
        // Update the fee warning text with the formatted fee (used in private-lots)
        const feeWarning = document.getElementById('fee-warning');
        if (feeWarning) {
            feeWarning.innerHTML = `WARNING: Each attempt to enter a secret code will incur a non-refundable **${formatter.format(accessFee)}** transaction fee. Please confirm the code before attempting.`;
        }
    }

    /**
     * Adds a temporary flash animation to the funds display element.
     */
    function animateFundsDeduction() {
        const fundsDisplay = document.getElementById('funds-display');
        if (fundsDisplay) {
            fundsDisplay.classList.add('funds-deduction-pulse');
            setTimeout(() => {
                fundsDisplay.classList.remove('funds-deduction-pulse');
            }, 500); // Animation duration is 0.5s
        }
    }


(function () {
    function showToast(message, iconName, color) {
        const container = document.getElementById('toast-container');
        if (!container) {
            console.warn('Toast container not found');
            return;
        }

        const el = document.createElement('div');
        el.className = `
            bg-black text-white px-6 py-4 border-2 border-white shadow-lg pointer-events-auto
            flex items-center gap-3 toast-enter min-w-[300px] border-${color}-400
        `.trim();

        el.innerHTML = `
            <i data-lucide="${iconName}" class="w-5 h-5 text-${color}-400"></i>
            <div class="font-bold uppercase text-sm">${message}</div>
        `;

        container.appendChild(el);

        if (window.lucide) {
            lucide.createIcons();
        }

        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateX(100%)';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    }

    // Make it globally available
    window.showToast = showToast;
})();
    // --- Navigation Logic (Crucial for the layout to work) ---
    function changeView(viewId) {
        // Navigates to the corresponding PHP file.
        const pageMap = {
            'live-auctions': '/',
            'private-lots': 'private-lots.php',
            'sell-artifact': 'sell-artifact.php',
            'my-bids': 'bids.php',
            'my-search': 'my-search.php',
        };

        const targetFile = pageMap[viewId];
        if (targetFile) {
            const currentPage = window.location.pathname.split('/').pop();
            // Allow client-side logic updates without full reload if already on page
            if (currentPage === targetFile) {
                currentView = viewId;
                // Add page-specific function calls here if needed (e.g., resetAppraisalForm())
                return;
            }

            // Navigate to the correct PHP file
            window.location.href = targetFile;
        } else {
            console.error(`Navigation target not found for view: ${viewId}`);
        }
    }

    // --- Profile Modal & Transactions (Header Component) ---
    function renderTransactions() {
        const list = document.getElementById('transaction-list');
        if (!list) return;

        list.innerHTML = '';

        transactions.slice(0, 4).forEach(tx => { // Show up to 4 recent transactions
            const isDebit = tx.amount < 0;
            const sign = isDebit ? '' : '+';
            const color = isDebit ? 'text-red-600' : 'text-green-600';
            const lotInfo = tx.lot ? ` (Lot ${tx.lot})` : '';
            const date = new Date(tx.date).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });

            list.innerHTML += `
                <li class="flex justify-between items-center p-1 bg-gray-50 border border-gray-200">
                    <div class="truncate">
                        <span class="font-bold">${tx.type}</span><span class="text-gray-500 text-[10px]">${lotInfo}</span>
                    </div>
                    <span class="${color} font-bold">${sign}${formatter.format(tx.amount)}</span>
                </li>
            `;
        });
    }

    function toggleProfileModal() {
           window.location.href = "process_payment.php";

    }

    // Add a global click listener to close the modal when clicking outside
    document.addEventListener('click', (e) => {
        const modal = document.getElementById('profile-modal');
        const fundsDisplay = document.getElementById('funds-display');

        if (modal && fundsDisplay && !modal.classList.contains('hidden')) {
            const isClickInsideModal = modal.contains(e.target);
            const isClickOnFundsDisplay = fundsDisplay.contains(e.target);

            if (!isClickInsideModal && !isClickOnFundsDisplay) {
                modal.classList.add('hidden');
            }
        }
    });

</script>
</head>

<body class="h-screen flex flex-col overflow-hidden">

    <?php include __DIR__ . '/../header.php'; // HEADER COMPONENT ?>

    <div class="flex flex-col md:flex-row flex-1 overflow-hidden">
        <div id="layout_main" class="flex flex-col md:flex-row flex-1 relative overflow-hidden">

            <main class="flex-1 bg-gray-50 overflow-y-auto relative">
                <?php echo $slot; // MAIN CONTENT SLOT ?>
            </main>
<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '') {
    include __DIR__ . '/../bidding-terminal.php'; // BIDDING TERMINAL COMPONENT
}
?>

        </div>
    </div>

    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <?php include __DIR__ . '/../footer.php'; // JAVASCRIPT/GLOBAL LOGIC COMPONENT ?>

</body>

</html>