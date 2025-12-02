<?php
// session_start();

// include_once("config.php");

?>




<?php $page = "Live Auctions";



ob_start(); ?>

<?php
// Mock data (also needed here for the main artifact list)
$artifacts = [
    [
        'id' => 101,
        'lot' => 'LOT 101',
        'category' => 'archaeology',
        'title' => 'Roman Denarius Coin Set',
        'description' => 'A collection of 12 silver coins from the reign of Emperor Hadrian.',
        'currentBid' => 550000,
        'time' => 7200, // 2 hours in seconds
        'bids' => [
            ['amount' => 550000, 'user' => 'User:B82C', 'time' => '5m ago'],
            ['amount' => 500000, 'user' => 'User:F1A0', 'time' => '12m ago'],
            ['amount' => 480000, 'user' => 'User:B82C', 'time' => '1h ago'],
        ]
    ],
    [
        'id' => 102,
        'lot' => 'LOT 102',
        'category' => 'fineart',
        'title' => 'Monochrome Sketch No. 4',
        'description' => 'Original ink drawing by an early 20th-century Russian modernist.',
        'currentBid' => 1200000,
        'time' => 14400, // 4 hours in seconds
        'bids' => [
            ['amount' => 1200000, 'user' => 'User:F1A0', 'time' => '20m ago'],
            ['amount' => 1100000, 'user' => 'User:C9D3', 'time' => '1h 30m ago'],
        ]
    ],
    [
        'id' => 103,
        'lot' => 'LOT 103',
        'category' => 'historical',
        'title' => 'Original Apollo 11 Flight Plan',
        'description' => 'A signed working copy of the ascent burn checklist.',
        'currentBid' => 4500000,
        'time' => 3600, // 1 hour in seconds
        'bids' => [
            ['amount' => 4500000, 'user' => 'User:D77E', 'time' => '1m ago'],
            ['amount' => 4200000, 'user' => 'User:F1A0', 'time' => '1m ago'],
        ]
    ],
    [
        'id' => 104,
        'lot' => 'LOT 104',
        'category' => 'rarebooks',
        'title' => 'First Edition "Principia"',
        'description' => 'A 1687 first edition of Isaac Newton\'s seminal work.',
        'currentBid' => 8800000,
        'time' => 86400, // 24 hours in seconds
        'bids' => [
            ['amount' => 8800000, 'user' => 'User:C9D3', 'time' => '1d ago'],
        ]
    ]
];
?>


<div id="live-auctions-view" class="view-content p-4 md:p-8 max-w-7xl mx-auto">












<div id="featured-lot" class="mb-8 brutalist-border bg-white brutalist-shadow p-4 sm:p-6 flex flex-col md:flex-row justify-between items-center gap-6">
                        <div class="md:w-1/3 w-full border-2 border-gray-200 bg-gray-100 p-4 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="image-off" class="lucide lucide-image-off w-10 h-10 mx-auto text-gray-400 mb-2"><line x1="2" x2="22" y1="2" y2="22"></line><path d="M10.41 10.41a2 2 0 1 1-2.83-2.83"></path><line x1="13.5" x2="6" y1="13.5" y2="21"></line><line x1="18" x2="21" y1="12" y2="15"></line><path d="M3.59 3.59A1.99 1.99 0 0 0 3 5v14a2 2 0 0 0 2 2h14c.55 0 1.052-.22 1.41-.59"></path><path d="M21 15V5a2 2 0 0 0-2-2H9"></path></svg>
                            <p class="font-mono text-xs text-gray-600">LOT 001 IMAGE PREVIEW</p>
                        </div>
                        <div class="md:w-2/3 w-full">
                            <span class="bg-black text-white px-2 py-0.5 text-xs font-bold uppercase mb-2 inline-block">Featured Lot</span>
                            <!-- Responsive Typography for Title -->
                            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold uppercase leading-tight tracking-tighter">The Lost Codex of Alexandria</h1>
                            <p class="font-mono mt-3 text-sm text-gray-600">A preserved 4th-century parchment containing early scientific sketches.</p>
                            <div class="flex items-center justify-between mt-4 border-t border-black pt-3">
                                <div class="text-xs uppercase font-bold text-gray-500">Current Bid</div>
                                <div class="font-bold text-xl sm:text-2xl font-mono text-black" id="featured-bid">$3,500,000</div>
                            </div>
                            <button onclick="selectArtifact(artifacts[0].id)" class="bg-black text-white px-8 py-3 font-bold uppercase w-full mt-4 hover:bg-white hover:text-black border-2 border-black transition-all brutalist-shadow-hover brutalist-active">
                                Place Bid Now
                            </button>
                        </div>
                    </div>

























    <h1 class="text-3xl font-bold uppercase tracking-tight brutalist-border-b pb-2 mb-4">Live Auctions</h1>

    <div class="flex flex-wrap gap-2 mb-6 text-sm font-medium uppercase">
        <button onclick="filterCategory('all')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-black text-white hover:bg-gray-800 brutalist-active">All
            Categories</button>
        <button onclick="filterCategory('archaeology')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-white hover:bg-gray-100 brutalist-active">Archaeology</button>
        <button onclick="filterCategory('fineart')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-white hover:bg-gray-100 brutalist-active">Fine Art</button>
        <button onclick="filterCategory('historical')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-white hover:bg-gray-100 brutalist-active">Historical</button>
        <button onclick="filterCategory('rarebooks')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-white hover:bg-gray-100 brutalist-active">Rare Books</button>
    </div>

    <div id="artifacts-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    </div>
</div>

<script>
    // Component-Specific State (Redefine artifacts for local use)
    const artifacts = <?php echo json_encode($artifacts); ?>;

    function renderArtifacts(filter = 'all') {
        const container = document.getElementById('artifacts-container');
        if (!container) return;

        container.innerHTML = '';

        const filtered = filter === 'all' ? artifacts : artifacts.filter(a => a.category === filter);

        // Update filter button styles
        document.querySelectorAll('.filter-button').forEach(btn => {
            if (btn.textContent.trim().toLowerCase().includes(filter.replace('all', 'categories').toLowerCase())) {
                btn.classList.remove('bg-white', 'text-black');
                btn.classList.add('bg-black', 'text-white');
            } else {
                btn.classList.remove('bg-black', 'text-white');
                btn.classList.add('bg-white', 'text-black');
            }
        });


        filtered.forEach(artifact => {
            // Note: selectedArtifactId is a global variable from app/components/layouts/base.php
            const isSelected = artifact.id === selectedArtifactId;

            // Adjusted card to use relative padding and font sizing
            const cardHtml = `
            <div onclick="selectArtifact(${artifact.id})" 
                 class="bg-white brutalist-shadow-sm hover:translate-x-1 transition-transform duration-200 cursor-pointer p-4 brutalist-border ${isSelected ? 'selected-artifact' : ''}">
                
                <span class="text-xs font-mono uppercase ${isSelected ? 'text-white/70' : 'text-gray-500'}">${artifact.lot}</span>
                <h3 class="font-bold text-lg uppercase leading-tight mt-1 mb-3">${artifact.title}</h3>
                
                <div class="w-full h-32 border-2 border-dashed ${isSelected ? 'border-white/50' : 'border-gray-300'} bg-gray-100 mb-3 flex items-center justify-center">
                    <i data-lucide="image-off" class="w-6 h-6 ${isSelected ? 'text-white/50' : 'text-gray-400'}"></i>
                </div>
                
                <p class="text-xs ${isSelected ? 'text-white/80' : 'text-gray-600'} mb-3 truncate">${artifact.description}</p>
                
                <div class="flex justify-between items-center border-t ${isSelected ? 'border-white/20' : 'border-gray-200'} pt-2">
                    <div>
                        <div class="text-[10px] uppercase font-bold ${isSelected ? 'text-white/70' : 'text-gray-500'}">High Bid</div>
                        <div class="font-bold text-lg font-mono">${formatter.format(artifact.currentBid)}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold ${isSelected ? 'text-white/70' : 'text-gray-500'}">Time Left</div>
                        <div class="font-bold font-lg text-red-600">${formatTime(artifact.time)}</div>
                    </div>
                </div>
            </div>
        `;
            container.insertAdjacentHTML('beforeend', cardHtml);
        });
        lucide.createIcons();
    }
    window.renderArtifacts = renderArtifacts;

    function filterCategory(category) {
        currentFilter = category;
        renderArtifacts(category);
    }

    // Initializer function called from app/components/layouts/base.php onload
    function initIndexPage() {
        renderArtifacts(currentFilter);
        renderTerminal(); // Render the terminal based on initial selectedArtifactId
    }
</script>
<script>
    function renderArtifacts(filter = 'all') {
        const container = document.getElementById('artifacts-container');
        // Only render if we are on the live-auctions view
        if (!container) return;

        container.innerHTML = '';

        const filtered = filter === 'all' ? artifacts : artifacts.filter(a => a.category === filter);

        filtered.forEach(artifact => {
            const isSelected = artifact.id === selectedArtifactId;

            // Adjusted card to use relative padding and font sizing
            const cardHtml = `
                    <div onclick="selectArtifact(${artifact.id})" 
                         class="bg-white brutalist-shadow-sm hover:translate-x-1 transition-transform duration-200 cursor-pointer p-4 brutalist-border ${isSelected ? 'selected-artifact' : ''}">
                        
                        <span class="text-xs font-mono uppercase ${isSelected ? 'text-white/70' : 'text-gray-500'}">${artifact.lot}</span>
                        <h3 class="font-bold text-lg uppercase leading-tight mt-1 mb-3">${artifact.title}</h3>
                        
                        <div class="w-full h-32 border-2 border-dashed ${isSelected ? 'border-white/50' : 'border-gray-300'} bg-gray-100 mb-3 flex items-center justify-center">
                            <i data-lucide="image-off" class="w-6 h-6 ${isSelected ? 'text-white/50' : 'text-gray-400'}"></i>
                        </div>
                        
                        <p class="text-xs ${isSelected ? 'text-white/80' : 'text-gray-600'} mb-3 truncate">${artifact.description}</p>
                        
                        <div class="flex justify-between items-center border-t ${isSelected ? 'border-white/20' : 'border-gray-200'} pt-2">
                            <div>
                                <div class="text-[10px] uppercase font-bold ${isSelected ? 'text-white/70' : 'text-gray-500'}">High Bid</div>
                                <div class="font-bold text-base font-mono">${formatter.format(artifact.currentBid)}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] uppercase font-bold ${isSelected ? 'text-white/70' : 'text-gray-500'}">Time Left</div>
                                <div class="font-bold  text-base font-mono text-red-600">${formatTime(artifact.time)}</div>
                            </div>
                        </div>
                    </div>
                `;
            container.insertAdjacentHTML('beforeend', cardHtml);
        });
        lucide.createIcons();
    }

    function filterCategory(category) {
        currentFilter = category;
        renderArtifacts(category);
    }




</script>

<?php $slot = ob_get_clean(); // Store the output buffer content into $slot
include 'app/components/layouts/base.php'; // Include the layout file
?>