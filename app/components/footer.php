<?php
// components/footer.php

// This file contains all the common JavaScript logic and state initialization.
// It assumes $availableFunds and $accessFee are defined in base.php.

?>
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
        currency: 'USD',
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


    function showToast(message, iconName, color) {
        const container = document.getElementById('toast-container');
        const el = document.createElement('div');
        el.className = `bg-black text-white px-6 py-4 border-2 border-white shadow-lg pointer-events-auto flex items-center gap-3 toast-enter min-w-[300px] border-${color}-400`;
        el.innerHTML = `
            <i data-lucide="${iconName}" class="w-5 h-5 text-${color}-400"></i>
            <div class="font-bold uppercase text-sm">${message}</div>
        `;
        container.appendChild(el);
        lucide.createIcons();

        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateX(100%)';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    }

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
        const modal = document.getElementById('profile-modal');
        if (modal.classList.contains('hidden')) {
            renderTransactions();
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
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