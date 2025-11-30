<?php
// components/bidding-terminal.php

// Mock data (keep local for terminal rendering)
// NOTE: In a real app, this should be included/fetched from a shared state/DB
?>

<aside id="bidding_terminal" onclick="expandTerminal()" 
    class="md:w-64 lg:w-96  w-full bg-white shadow-lg border-t-8 md:border-t-0 md:border-l-2 border-black overflow-y-auto shrink-0 z-20 md:z-10 flex flex-col absolute md:static  bottom-0 left-0 transition-all duration-300 ease-in-out h-[0%] md:h-full cursor-pointer">
    
    <div id="empty-terminal"
        class="flex flex-col items-center justify-center h-full p-8 text-center bg-gray-50">
        <i data-lucide="scan" class="w-12 h-12 text-gray-400 mb-4"></i>
        <h3 class="font-bold text-lg uppercase mb-2">Bidding Terminal</h3>
        <p class="text-sm text-gray-500 font-mono">Select an artifact from the list to view live details and
            place a bid.</p>
        <div class="mt-4 p-2 bg-white brutalist-border-b text-xs font-mono">
            Session ID: <span class="font-bold">2025-Q4-ALPHA-01</span>
        </div>
    </div>

    <div id="terminal-details" class="flex-1 p-4 md:p-6 flex flex-col justify-between hidden">
        <div class="space-y-4">
            <div class="flex justify-between items-start border-b-2 border-black pb-2">
                <div>
                    <span id="terminal-lot" class="text-sm font-mono uppercase text-gray-500">LOT XXX</span>
                    <h2 id="terminal-title" class="text-xl font-bold uppercase leading-tight">Artifact Title Here</h2>
                    <p id="terminal-category" class="text-xs font-mono text-gray-600 mt-1"></p>
                </div>
                
                <button onclick="event.stopPropagation(); clearSelection()" title="Close Terminal"
                    class="w-6 h-6 text-gray-500 hover:text-black z-30 relative md:hidden">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="w-full h-40 border-2 border-dashed border-gray-300 bg-gray-100 flex items-center justify-center mb-4">
                <i data-lucide="camera-off" class="w-8 h-8 text-gray-400"></i>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="p-3 brutalist-border bg-gray-50">
                    <div class="text-[10px] uppercase font-bold text-gray-500">Current High Bid</div>
                    <div id="terminal-current-bid" class="font-bold text-2xl font-mono text-black mt-1">$0</div>
                </div>
                <div class="p-3 brutalist-border bg-gray-50">
                    <div class="text-[10px] uppercase font-bold text-gray-500">Time Remaining</div>
                    <div id="terminal-time-left" class="font-bold text-2xl font-mono text-red-600 mt-1">00:00:00</div>
                </div>
            </div>

            <div>
                <h4 class="font-bold uppercase text-sm mt-4 mb-2 border-b border-gray-200 pb-1">Bidding History (3 Latest)</h4>
                <ul id="terminal-history" class="text-xs font-mono space-y-1">
                </ul>
            </div>
        </div>
    </div>

    <div id="bid-footer" onclick="event.stopPropagation()" class="p-4 md:p-6 brutalist-border-t bg-gray-100 shrink-0 hidden cursor-auto">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs uppercase font-bold text-gray-500">Minimum Next Bid</div>
            <div id="terminal-min-bid" class="font-bold text-lg font-mono text-black">$5,000</div>
        </div>

        <input type="number" id="bid-input" placeholder="Enter your bid"
            onkeydown="if(event.key === 'Enter') placeBid()"
            class="w-full px-3 py-2 border-2 border-black font-mono text-lg focus:outline-none focus:ring-2 focus:ring-black mb-3">
        <button onclick="placeBid()"
            class="w-full bg-red-600 text-white py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition-all brutalist-shadow brutalist-active border-2 border-black">
            Place Bid
        </button>
    </div>
</aside>

<script>
    console.log("Bidding terminal loaded");

    // NEW FUNCTION: Expands terminal when clicked, but only if it's in "peek" (40%) mode
    function expandTerminal() {
        const biddingTerminal = document.getElementById('bidding_terminal');
        
        // We check inline style. If it is exactly 40%, we expand it.
        if (biddingTerminal.style.height === "40%") {
            biddingTerminal.style.height = "100%";
        }
    }

    function renderTerminal() {
        const details = document.getElementById('terminal-details');
        const footer = document.getElementById('bid-footer');
        const emptyState = document.getElementById('empty-terminal');

        if (!selectedArtifactId) {
            details.classList.add('hidden');
            footer.classList.add('hidden');
            emptyState.classList.remove('hidden');
            const bidInput = document.getElementById('bid-input');
            if (bidInput) bidInput.value = '';
            
            const existingOverlay = document.getElementById('terminal-overlay');
            if (existingOverlay) existingOverlay.remove();
            return;
        }

        const artifact = artifacts.find(a => a.id === selectedArtifactId);
        if (!artifact) return;

        emptyState.classList.add('hidden');
        details.classList.remove('hidden');
        footer.classList.remove('hidden');

        // Populate main details
        document.getElementById('terminal-lot').textContent = artifact.lot;
        document.getElementById('terminal-title').textContent = artifact.title;
        document.getElementById('terminal-category').textContent = `Category: ${artifact.category.toUpperCase()}`;
        document.getElementById('terminal-current-bid').textContent = formatter.format(artifact.currentBid);
        document.getElementById('terminal-time-left').textContent = formatTime(artifact.time);

        const minNextBid = artifact.currentBid + 5000;
        document.getElementById('terminal-min-bid').textContent = formatter.format(minNextBid);

        const bidInput = document.getElementById('bid-input');
        bidInput.value = minNextBid;
        bidInput.min = minNextBid;

        const historyList = document.getElementById('terminal-history');
        historyList.innerHTML = '';
        artifact.bids.slice(0, 3).forEach(bid => { 
            const historyHtml = `
            <li class="flex justify-between">
                <span>${formatter.format(bid.amount)}</span>
                <span class="text-gray-400">${bid.user} (${bid.time})</span>
            </li>`;
            historyList.insertAdjacentHTML('beforeend', historyHtml);
        });

        lucide.createIcons();
    }

    selectArtifact = function(id) {
        const biddingTerminal = document.getElementById('bidding_terminal');
        const layoutMain = document.getElementById('layout_main');
        
        // If clicking the same item that is already open...
        if (id === selectedArtifactId) {
            // If it is currently minimized to 40%, expand it
            if (biddingTerminal.style.height === "40%") {
                biddingTerminal.style.height = "100%";
                return;
            }
            // If it is already 100%, we do nothing (or we could collapse, but UX usually prefers staying open)
            // If you want toggle behavior on the list click, uncomment below:
            /*
            else if (biddingTerminal.style.height === "100%") {
                 biddingTerminal.style.height = "40%";
                 return;
            }
            */
        }
        
        selectedArtifactId = id;

        // Reset to initial "Peek" state
          if (biddingTerminal.style.height === "0%") {
        biddingTerminal.style.height = "40%";
                return;
            }


        biddingTerminal.style.opacity = "1";

        // Create overlay
        if (layoutMain && layoutMain.classList.contains('relative')) {
            let overlay = document.getElementById('terminal-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'terminal-overlay';
                Object.assign(overlay.style, {
                    position: 'absolute',
                    top: '0',
                    left: '0',
                    width: '100%',
                    height: '100%',
                    backgroundColor: 'rgba(0, 0, 0, 0.5)', 
                    zIndex: '10', 
                    cursor: 'pointer'
                });
                overlay.addEventListener('click', (event) => {
                    event.stopPropagation();
                    clearSelection();
                });
                overlay.classList.add("md:hidden")
                layoutMain.appendChild(overlay);
            }
        }
        
        renderTerminal();
    }

    function clearSelection() {
        const biddingTerminal = document.getElementById('bidding_terminal');
        const existingOverlay = document.getElementById('terminal-overlay');

        biddingTerminal.style.height = "0%";
        biddingTerminal.style.opacity = "0";
        
        if (existingOverlay) {
            existingOverlay.remove();
        }

        selectedArtifactId = null;
        if (currentView === 'live-auctions' && typeof renderArtifacts === 'function') {
            renderArtifacts(currentFilter);
        }
        renderTerminal(); 
    }

    function placeBid() {
        if (!selectedArtifactId) return;

        const bidInput = document.getElementById('bid-input');
        const newBidAmount = parseFloat(bidInput.value);
        const artifact = artifacts.find(a => a.id === selectedArtifactId);
        const minBid = artifact.currentBid + 5000;

        if (isNaN(newBidAmount) || newBidAmount < minBid) {
            showToast(`Bid must be at least ${formatter.format(minBid)}!`, 'alert-triangle', 'red');
            return;
        }

        if (availableFunds < newBidAmount) {
            showToast(`Error: Insufficient funds!`, 'x-circle', 'red');
            return;
        }

        const btn = document.querySelector('#bid-footer button');
        const oldText = btn.innerText;
        btn.innerText = "VERIFYING BID...";
        btn.disabled = true;

        setTimeout(() => {
            availableFunds -= newBidAmount;
            transactions.unshift({
                id: Date.now(),
                type: 'BID PLACED',
                amount: -newBidAmount,
                date: new Date().toISOString().slice(0, 10),
                lot: artifact.id
            });

            const currentUserId = 'User:D9F3';
            artifact.currentBid = newBidAmount;
            artifact.bids.unshift({
                amount: newBidAmount,
                user: currentUserId,
                time: 'Just now',
            });

            showToast(`Bid Placed: ${formatter.format(newBidAmount)} on Lot ${artifact.lot}`, 'gavel', 'green');

            animateFundsDeduction();
            updateFundsDisplay();

            if (typeof renderArtifacts === 'function') {
                renderArtifacts(currentFilter);
            }
            renderTerminal();

            btn.innerText = oldText;
            btn.disabled = false;
        }, 1500);
    }

    function manageBid(id, status) {
        const artifact = artifacts.find(a => a.id === id);
        if (!artifact) return;
        window.event.stopPropagation();
        showToast(`Opening Proxy Manager for Lot ${artifact.lot || id}.`, 'settings-2', 'blue');
    }
</script>