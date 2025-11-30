<?php $page="My Bids" ;
ob_start(); ?>

<?php
// Mock Data (Simulate user's current bids/watches)
// NOTE: In a real app, this should be fetched for the current user.
$myBids = [
    [
        'id' => 103,
        'lot' => 'LOT 103',
        'title' => 'Original Apollo 11 Flight Plan',
        'myBid' => 4200000, // My bid
        'highBid' => 4500000, // Current high bid (different user)
        'status' => 'outbid', // Status: outbid
        'time' => 3600,
    ],
    [
        'id' => 102,
        'lot' => 'LOT 102',
        'title' => 'Monochrome Sketch No. 4',
        'myBid' => 1200000,
        'highBid' => 1200000, // My bid is the current high bid
        'status' => 'winning', // Status: winning
        'time' => 14400,
    ],
    [
        'id' => 101,
        'lot' => 'LOT 101',
        'title' => 'Roman Denarius Coin Set',
        'myBid' => 500000,
        'highBid' => 550000,
        'status' => 'watching', // Status: watching (was outbid, or watching high bid)
        'time' => 7200,
    ],
];
?>

<div id="my-bids-view" class="view-content p-4 md:p-8 max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold uppercase tracking-tight brutalist-border-b pb-2 mb-4">My Bidding Portfolio</h1>

    <div id="my-bids-list" class="space-y-4">
        </div>
    
    <div id="no-bids-state" class="hidden text-center p-12 brutalist-border brutalist-shadow bg-white mt-8">
        <i data-lucide="folder-open" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
        <h3 class="font-bold text-lg uppercase mb-2">No Active Bids</h3>
        <p class="text-sm text-gray-500 font-mono">You haven't placed a bid on any live auctions yet.</p>
    </div>

</div>

<script>
    const myBids = <?php echo json_encode($myBids); ?>;

    function renderMyBids() {
        const container = document.getElementById('my-bids-list');
        const emptyState = document.getElementById('no-bids-state');
        if (!container) return;

        container.innerHTML = '';
        if (myBids.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');

        myBids.forEach(bid => {
            let statusClass = '';
            let statusText = '';
            let statusIcon = '';

            switch (bid.status) {
                case 'winning':
                    statusClass = 'status-winning';
                    statusText = 'WINNING';
                    statusIcon = 'check-circle';
                    break;
                case 'outbid':
                    statusClass = 'status-outbid';
                    statusText = 'OUTBID';
                    statusIcon = 'alert-triangle';
                    break;
                case 'watching':
                    statusClass = 'status-watching';
                    statusText = 'WATCHING';
                    statusIcon = 'eye';
                    break;
            }

            const bidHtml = `
                <div onclick="selectArtifact(${bid.id})" class="brutalist-border brutalist-shadow-sm bg-white hover:bg-gray-50 transition-colors cursor-pointer p-4 flex justify-between items-center">
                    
                    <div>
                        <span class="text-xs font-mono uppercase text-gray-500">${bid.lot}</span>
                        <h3 class="font-bold text-lg uppercase leading-tight mt-1">${bid.title}</h3>
                        <p class="text-xs font-mono text-gray-700 mt-1">Your Bid: ${formatter.format(bid.myBid)}</p>
                    </div>

                    <div class="text-center md:block hidden">
                        <div class="inline-flex items-center px-3 py-1 text-xs font-bold uppercase rounded-sm hc-shadow ${statusClass}">
                            <i data-lucide="${statusIcon}" class="w-3 h-3 mr-1"></i>
                            ${statusText}
                        </div>
                        <p class="font-bold text-lg font-mono mt-2">${formatter.format(bid.highBid)}</p>
                        <p class="text-[10px] uppercase font-bold text-gray-500">Current High Bid</p>
                    </div>

                    <div class="text-right">
                        <div class="font-bold font-mono text-lg text-red-600">${formatTime(bid.time)}</div>
                        <div class="text-[10px] uppercase font-bold text-gray-500 mb-2">Time Left</div>
                        <button onclick="manageBid(${bid.id}, '${bid.status}')"
                            class="bg-black text-white px-3 py-1 text-xs font-bold uppercase hover:bg-gray-800 brutalist-shadow-sm brutalist-active brutalist-border"
                            title="Manage Proxy Bid">
                            Manage Proxy
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', bidHtml);
        });
        lucide.createIcons();
    }
    
    /**
     * Simulates managing the proxy bidding behavior for an artifact.
     * @param {number} id - The artifact ID.
     * @param {string} status - The current bid status.
     */
    function manageBid(id, status) {
        const artifact = myBids.find(a => a.id === id);
        if (!artifact) return;

        // Stop propagation to prevent the parent list item (which navigates to the artifact) 
        // from being clicked simultaneously.
        window.event.stopPropagation();

        // Simulate opening a management modal or panel
        showToast(`Opening Proxy Manager for Lot ${artifact.lot || id}. Current Status: ${status}`, 'settings-2', 'blue');

        console.log(`Managing proxy for Lot ${id}. Behavior management logic goes here.`);
    }

    function initMyBids() {
        renderMyBids();
    }
</script>

<?php $slot = ob_get_clean();
include 'app/components/layouts/base.php';
?>