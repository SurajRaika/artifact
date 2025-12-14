<?php
// components/item-detail-panel.php

// Mock data (Modified for a Handicrafts Marketplace - Added images array)

// Define a simulated user credit balance (e.g., 5000 INR)
$user_credit = 0; 
?>

<aside id="bidding_terminal" onclick="expandTerminal()"
    class="md:w-64 lg:w-96 w-full bg-white shadow-lg border-t-8 md:border-t-0 md:border-l-2 border-black overflow-y-auto shrink-0 z-20 md:z-10 flex flex-col absolute md:static bottom-0 left-0 transition-all duration-300 ease-in-out h-[0%] md:h-full cursor-pointer">

    <div id="empty-terminal"
        class="flex flex-col items-center justify-center h-full p-8 text-center bg-gray-50">
        <i data-lucide="scan" class="w-12 h-12 text-gray-400 mb-4"></i>
        <h3 class="font-bold text-lg uppercase mb-2">Item Detail Panel</h3>
        <p class="text-sm text-gray-500 font-mono">Select an item from the list to view full details and purchase options.</p>
        <div class="mt-4 p-2 bg-white brutalist-border-b text-xs font-mono">
            Market ID: <span class="font-bold">2025-Q4-ALPHA-01</span>
        </div>
    </div>

    <div id="terminal-details" class="flex-1 p-4 md:p-6 flex flex-col justify-between hidden">
        <div class="space-y-4">
            <div class="flex justify-between items-start border-b-2 border-black pb-2">
                <div>
                    <span id="terminal-lot" class="text-sm font-mono uppercase text-gray-500">ITEM XXX</span>
                    <h2 id="terminal-title" class="text-xl font-bold uppercase leading-tight">Handicraft Title Here</h2>
                    <p id="terminal-category" class="text-xs font-mono text-gray-600 mt-1"></p>
                </div>
                <button onclick="event.stopPropagation(); clearSelection()" title="Close Panel"
                    class="w-6 h-6 text-gray-500 hover:text-black z-30 relative md:hidden">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div id="terminal-image-slider"
                x-data="{ 
                    activeImage: 0, 
                    images: [],
                    init() {
                        // Listen for the custom event from Vanilla JS
                        window.addEventListener('update-terminal-images', (e) => {
                            this.images = e.detail || [];
                            this.activeImage = 0;
                        });
                    },
                    next() {
                        this.activeImage = (this.activeImage === this.images.length - 1) ? 0 : this.activeImage + 1;
                    },
                    prev() {
                        this.activeImage = (this.activeImage === 0) ? this.images.length - 1 : this.activeImage - 1;
                    }
                }"
                class="relative w-full brutalist-border-b border-black mb-4 select-none">

                <div class="h-64 overflow-hidden relative bg-gray-100">

                    <template x-for="(img, index) in images" :key="index">
                        <div x-show="activeImage === index"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute inset-0 w-full h-full">
                            <img :src="img" class="w-full h-full object-contain">
                        </div>
                    </template>

                    <div x-show="images.length === 0" class="absolute inset-0 flex items-center justify-center text-gray-400">
                        <div class="text-center">
                            <i data-lucide="image-off" class="w-8 h-8 mx-auto mb-2"></i>
                            <span class="text-xs font-mono">No Preview</span>
                        </div>
                    </div>

                    <div x-show="images.length > 1" class="absolute inset-0 flex items-center justify-between p-2 pointer-events-none">
                        <button @click.stop="prev()"
                            class="bg-white/80 hover:bg-white text-black p-2 border-2 border-black pointer-events-auto transition-transform active:scale-95 shadow-lg">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>

                        <button @click.stop="next()"
                            class="bg-white/80 hover:bg-white text-black p-2 border-2 border-black pointer-events-auto transition-transform active:scale-95 shadow-lg">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <div x-show="images.length > 1" class="flex justify-center space-x-2 p-2 bg-white border-t-2 border-black">
                    <template x-for="(img, index) in images" :key="index">
                        <button @click.stop="activeImage = index"
                            :class="activeImage === index ? 'bg-black w-4' : 'bg-gray-300 w-2 hover:bg-gray-400'"
                            class="h-2 rounded-none transition-all duration-300"></button>
                    </template>
                </div>
            </div>
            <div id="terminal-time-left" class="font-semibold text-lg font-mono text-gray-700 mb-0">2025-07-28</div>

            <p id="terminal-description" class="text-sm text-gray-700 font-mono italic leading-relaxed"></p>
            <div class="space-y-4">
                <div class="p-3 brutalist-border bg-gray-50">
                    <div class="text-[10px] uppercase font-bold text-gray-500">Price</div>
                    <div id="terminal-current-bid" class="font-bold text-2xl font-mono text-black mt-1">₹8,800</div>
                </div>


            </div>
        </div>
    </div>

    <div id="bid-footer" onclick="event.stopPropagation()" class="p-4 md:p-6 brutalist-border-t bg-gray-100 shrink-0 hidden cursor-auto">
        <button onclick="buyNow(<?= $user_credit ?>)"
            class="w-full bg-green-600 text-white py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition-all brutalist-shadow brutalist-active border-2 border-black">
            Buy Now
        </button>
    </div>
</aside>

<script>
    // Note: The artifacts array is defined in the PHP block.

    console.log("Item detail panel loaded");

    function formatRupeesJS(amount) {
        if (typeof Intl === 'object' && Intl.NumberFormat) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                minimumFractionDigits: 0
            }).format(amount);
        }
        return '₹' + amount.toLocaleString('en-IN');
    }

    function expandTerminal() {
        const biddingTerminal = document.getElementById('bidding_terminal');
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

            // Reset Slider to empty
            window.dispatchEvent(new CustomEvent('update-terminal-images', {
                detail: []
            }));

            const existingOverlay = document.getElementById('terminal-overlay');
            if (existingOverlay) existingOverlay.remove();
            return;
        }

        const artifact = artifacts.find(a => a.id === selectedArtifactId);
        if (!artifact) return;

        emptyState.classList.add('hidden');
        details.classList.remove('hidden');
        footer.classList.remove('hidden');

        // Populate Text Details
        document.getElementById('terminal-lot').textContent = artifact.lot;
        document.getElementById('terminal-title').textContent = artifact.title;
        document.getElementById('terminal-category').textContent = `Category: ${artifact.category.toUpperCase()}`;
        document.getElementById('terminal-current-bid').textContent = formatRupeesJS(artifact.price);
        document.getElementById('terminal-time-left').textContent = artifact.dateOfCreation;
        document.getElementById('terminal-description').textContent = artifact.description;

        // --- UPDATE SLIDER ---
        // We dispatch an event that the Alpine component is listening for.
        // This is much cleaner than injecting HTML strings.
        window.dispatchEvent(new CustomEvent('update-terminal-images', {
            detail: artifact.images || []
        }));

        // Re-initialize icons for any new content (mostly static now, but good practice)
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    selectArtifact = function(el,id) {
            document.querySelectorAll('.selected-artifact')
        .forEach(e => e.classList.remove('selected-artifact'));

    // add class to the clicked element
    el.classList.add('selected-artifact');
        // add this class to the element which called the selectArtifact func  selected-artifact
        const biddingTerminal = document.getElementById('bidding_terminal');
        const layoutMain = document.getElementById('layout_main');

        if (id === selectedArtifactId) {
            if (biddingTerminal.style.height === "40%") {
                biddingTerminal.style.height = "100%";
                return;
            }
        }

        selectedArtifactId = id;

        if (biddingTerminal.style.height === "0%") {
            biddingTerminal.style.height = "40%";
        }

        biddingTerminal.style.opacity = "1";

        // Overlay Logic
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

        if (existingOverlay) existingOverlay.remove();

        selectedArtifactId = null;
        renderTerminal();
    }

    /**
     * Attempts to purchase the selected artifact with a credit check.
     * @param {number} userCredit The simulated credit balance of the user.
     */
    function buyNow(userCredit) {
        if (!selectedArtifactId) return;
        const artifact = artifacts.find(a => a.id === selectedArtifactId);

        const btn = document.querySelector('#bid-footer button');
        const oldText = btn.innerText;
        btn.innerText = "PROCESSING...";
        btn.disabled = true;

        setTimeout(() => {
            const artifactPrice = artifact.price; // Assuming the price is in the artifact object

            // --- SIMULATED CREDIT CHECK LOGIC ---
            if (artifactPrice > userCredit) {
                // Insufficient Funds - ERROR Scenario
                const formattedPrice = formatRupeesJS(artifactPrice);
                const formattedCredit = formatRupeesJS(userCredit);
                
                const errorMessage = `Purchase failed for ${artifact.title}. Insufficient funds! Item price is ${formattedPrice}, but your credit is only ${formattedCredit}.`;
                
                // Use showToast for error if available, otherwise alert
                if (typeof showToast === 'function') {
                    // Assuming showToast supports an error/red style
                    showToast(errorMessage, 'alert-octagon', 'red'); 
                } else {
                    alert(errorMessage);
                }
                
                // Do NOT clear selection or close the panel on failure
                btn.innerText = oldText; // Restore button text
                btn.disabled = false; // Enable button for re-try
                
            } else {
                // Purchase Successful - SUCCESS Scenario (Original Logic)
                if (typeof showToast === 'function') {
                    showToast(`Purchase initiated for ${artifact.title}`, 'package', 'green');
                } else {
                    alert(`Purchase initiated for ${artifact.title}`);
                }

                // Clear selection and restore button state on success
                clearSelection();
                btn.innerText = oldText;
                btn.disabled = false;
            }

        }, 1500); // Simulate network delay
    }
</script>