<?php $page = "Handicrafts Marketplace";

ob_start(); ?>

<?php
// Mock data (Modified for a Handicrafts Marketplace - Added images array)
$artifacts = [
    [
    'id' => 101,
    'lot' => 'ITEM 101',
    'category' => 'wooden sculpture',
    'title' => 'Antique Wooden Elephant Figurine with Fine Inlay Work',
    'description' => 'A beautiful example of traditional Indian decorative art, this antique wooden elephant figurine is skillfully hand-carved with a graceful shape and adorned with very fine inlay work. The piece is well-preserved with a rich aged patina. Dimensions: 18.5 x 10 x 15.5 cms (7.4 x 4 x 6.2 inches). Weight: 0.95 kgs (2.09 pounds).',
    'price' => 17202.20, // Price in Rupees
    'dateOfCreation' => null,
    'images' => [
        'https://www.karigaristore.com/cdn/shop/files/TG671_1_-Photoroom.jpg?v=1749551828&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG671_2.jpg?v=1749551836&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG671_3.jpg?v=1749551836&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG671_4.jpg?v=1749551837&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG671_5.jpg?v=1749551836&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG671_6.jpg?v=1749551837&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG671_7.jpg?v=1749551837&width=1946',
    ],
],
    [
    'id' => 102,
    'lot' => 'ITEM 102',
    'category' => 'brass sculpture',
    'title' => 'Indian Brass Handicraft Musician Set – 4 Pc Fine Inlay Décor Statue',
    'description' => 'An exquisite Indian brass handicraft musician set consisting of four finely crafted figurines. Each statue features detailed workmanship with fine inlay décor, representing traditional Indian musicians. The set stands approximately 14 inches tall and makes an elegant decorative showpiece for interiors, collections, or cultural décor displays.',
    'price' => 50000, // Price in Rupees
    'dateOfCreation' => null,
    'images' => [
        'https://www.statuestudio.com/cdn/shop/products/459905_1_3.jpg?v=1674842586&width=1080',
        'https://m.media-amazon.com/images/I/714uKOIn5oL._AC_UF894,1000_QL80_.jpg',
        'https://ashopi.com/cdn/shop/files/AA2066ST2a.jpg?v=1764235016&width=1946',
        'https://ashopi.com/cdn/shop/files/AA2066ST1-Photoroom.jpg?v=1764235019&width=533',
    ],
],

   [
    'id' => 103,
    'lot' => 'ITEM 103',
    'category' => 'wooden sculpture',
    'title' => 'Antique Wooden Lady Figurine on Stand – Hand Carved',
    'description' => 'An original antique wooden lady (woman) figurine mounted on a stand, showcasing fine traditional hand carving. The sculpture reflects classic Indian folk artistry with elegant form and aged character, making it a charming collectible and decorative display piece.',
    'price' => 9000, // Price in Rupees
    'dateOfCreation' => '1950-01-01', // Estimated mid-20th century
    'images' => [
        'https://www.karigaristore.com/cdn/shop/files/TE725_1.jpg?v=1723783322&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TE725_2.jpg?v=1723783322&width=1445',
        'https://www.karigaristore.com/cdn/shop/files/TE725_3.jpg?v=1723783322&width=1445',
        'https://www.karigaristore.com/cdn/shop/files/TE725_4.jpg?v=1723783322&width=1445',
        'https://www.karigaristore.com/cdn/shop/files/TE725_5.jpg?v=1723783322&width=1445',
    ],
],
  [
    'id' => 104,
    'lot' => 'ITEM 104',
    'category' => 'brass sculpture',
    'title' => 'Antique Brass Shiva Nataraj Idol – Hand Crafted Engraved',
    'description' => 'An exquisite antique brass idol depicting Lord Shiva as Nataraj, the cosmic dancer symbolizing creation and destruction. Meticulously hand crafted in India, this figurine features intricate engraved detailing that highlights Shiva’s dynamic posture and serene expression. The rich aged patina enhances its authentic vintage character, reflecting timeless artistry, devotion, and cultural significance. Ideal for collectors, spiritual spaces, or decorative display.',
    'price' => 7333.57, // Price in Rupees
    'dateOfCreation' => '1940-01-01', // Estimated mid-20th century
    'images' => [
        'https://www.karigaristore.com/cdn/shop/files/TG138_1_-Photoroom.jpg?v=1755686377&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG138_2.jpg?v=1755686377&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG138_3.jpg?v=1755686377&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG138_4.jpg?v=1755686377&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG138_5.jpg?v=1755686377&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG138_6.jpg?v=1755686377&width=1946',
    ],
],
[
    'id' => 105,
    'lot' => 'ITEM 105',
    'category' => 'brass sculpture',
    'title' => 'Antique Brass Shiva Linga Idol – Hand Crafted Engraved',
    'description' => 'An exquisite antique brass Shiva Linga idol representing the profound spiritual essence of Lord Shiva. This original piece is finely hand crafted with intricate engraved details that reflect traditional Indian artistry and skilled workmanship. The rich aged patina enhances its authenticity and timeless beauty, making it ideal for a sacred space, devotional setting, or antique collection.',
    'price' => 8420.02, // Price in Rupees
    'dateOfCreation' => '1935-01-01', // Estimated early–mid 20th century
    'images' => [
        'https://www.karigaristore.com/cdn/shop/files/TG141_1_-Photoroom.jpg?v=1755686596&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG141_2.jpg?v=1755686596&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG141_3.jpg?v=1755686596&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG141_4.jpg?v=1755686596&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG141_6.jpg?v=1755686596&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG141_5.jpg?v=1755686596&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG141_7.jpg?v=1755686596&width=1946',
    ],
],
[
    'id' => 106,
    'lot' => 'ITEM 106',
    'category' => 'wooden sculpture',
    'title' => 'Antique Solid Wood Watchman Statue – Large Hand Carved Figurine',
    'description' => 'An antique solid wood large-size watchman figurine from India, finely hand carved with detailed workmanship and original painted finish. The statue is in good condition and displays a rich aged patina, reflecting its authenticity and traditional craftsmanship. A striking decorative and collectible piece showcasing classic Indian folk artistry.',
    'price' => 34313.86, // Price in Rupees
    'dateOfCreation' => '1930-01-01', // Estimated early–mid 20th century
    'images' => [
        'https://www.karigaristore.com/cdn/shop/files/Screenshot2025-04-08113627.png?v=1744092470&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/Screenshot2025-04-08113637.png?v=1744092470&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/Screenshot2025-04-08113643.png?v=1744092470&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/Screenshot2025-04-08113652.png?v=1744092470&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/Screenshot2025-04-08113659.png?v=1744092470&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/Screenshot2025-04-08113705.png?v=1744092470&width=1946',
    ],
],
[
    'id' => 107,
    'lot' => 'ITEM 107',
    'category' => 'wooden carving',
    'title' => 'Antique Wooden Floral Carving Panel Plaque – Hand Carved',
    'description' => 'A beautiful antique wooden panel plaque featuring exceptionally fine floral carvings, entirely hand carved with remarkable detail. The piece displays a rich aged patina and is in good condition, mounted for display. Originating from India, it reflects traditional craftsmanship and timeless decorative artistry, ideal for collectors or interior décor.',
    'price' => 19918.33, // Price in Rupees
    'dateOfCreation' => '1925-01-01', // Estimated early 20th century
    'images' => [
        'https://www.karigaristore.com/cdn/shop/files/TG741_1_-Photoroom.jpg?v=1750838848&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG741_4.jpg?v=1750838858&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG741_2.jpg?v=1750838859&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG741_3.jpg?v=1750838859&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG741_5.jpg?v=1750838859&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG741_6.jpg?v=1750838859&width=1946',
        'https://www.karigaristore.com/cdn/shop/files/TG741_7.jpg?v=1750838859&width=1946',
    ],
],

];

// Helper function to format price as Indian Rupees (Replaces formatter.format)
function formatRupees($amount) {
    return '₹' . number_format($amount, 0, '.', ',');
}
?>


<div id="live-auctions-view" class="view-content p-4 md:p-8 max-w-7xl mx-auto">
  

    <h1 class="text-3xl font-bold uppercase tracking-tight brutalist-border-b pb-2 mb-4">Current Collection</h1>

    <div class="flex flex-wrap gap-2 mb-6 text-sm font-medium uppercase">
        <button onclick="filterCategory('all')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-black text-white hover:bg-gray-800 brutalist-active">All
            Categories</button>
        <button onclick="filterCategory('wooden sculpture')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-white hover:bg-gray-100 brutalist-active">wooden sculpture</button>
        <button onclick="filterCategory('brass sculpture')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-white hover:bg-gray-100 brutalist-active">brass sculpture</button>
        <button onclick="filterCategory('wooden carving')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-white hover:bg-gray-100 brutalist-active">wooden carving</button>
        <button onclick="filterCategory('all')"
            class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm bg-white hover:bg-gray-100 brutalist-active">more</button>
    </div>

    <div id="artifacts-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    </div>
</div>

<script>
    // Component-Specific State (Redefine artifacts for local use)
    const artifacts = <?php echo json_encode($artifacts); ?>;
    
    // --- New Formatting Function (Replaces formatter.format) ---
    function formatRupeesJS(amount) {
        if (typeof Intl === 'object' && Intl.NumberFormat) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                minimumFractionDigits: 0
            }).format(amount);
        }
        // Fallback for older browsers
        return '₹' + amount.toLocaleString('en-IN');
    }

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
            const isSelected = artifact.id === selectedArtifactId;
            // Check for multiple images to show the correct icon
            const imageIcon = (artifact.images && artifact.images.length > 1) ? 'image' : 'image-off';

            // Adjusted card to use relative padding and font sizing
            const cardHtml = `
            <div onclick="selectArtifact(this,${artifact.id})" 
                 class="bg-white brutalist-shadow-sm hover:translate-x-1 transition-transform duration-200 cursor-pointer p-4 brutalist-border ">
                
                <span class="text-xs font-mono uppercase ${isSelected ? 'text-white/70' : 'text-gray-500'}">${artifact.lot}</span>
                <h3 class="font-bold text-lg uppercase leading-tight mt-1 mb-3">${artifact.title}</h3>
                
                <div class="w-full h-32 border-2 border-dashed ${isSelected ? 'border-white/50' : 'border-gray-300'} bg-gray-100 mb-3 flex items-center justify-center">
                    <i data-lucide="${imageIcon}" class="w-6 h-6 ${isSelected ? 'text-white/50' : 'text-gray-400'}"></i>
                </div>
                
                <p class="text-xs ${isSelected ? 'text-white/80' : 'text-gray-600'} mb-3 truncate">${artifact.description}</p>
                
                <div class="flex justify-between items-center border-t ${isSelected ? 'border-white/20' : 'border-gray-200'} pt-2">
                    <div>
                        <div class="text-[10px] uppercase font-bold ${isSelected ? 'text-white/70' : 'text-gray-500'}">Price</div>
                        <div class="font-bold text-lg font-mono">${formatRupeesJS(artifact.price)}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold ${isSelected ? 'text-white/70' : 'text-gray-500'}">Created</div>
                        <div class="font-bold font-lg text-black">${artifact.dateOfCreation}</div>
                    </div>
                </div>
            </div>
        `;
            container.insertAdjacentHTML('beforeend', cardHtml);
        });
        
    }
    window.renderArtifacts = renderArtifacts;

    function filterCategory(category) {
        currentFilter = category;
        renderArtifacts(category);
        lucide.createIcons();
    }

    // Initializer function called from app/components/layouts/base.php onload
    function initIndexPage() {
        renderArtifacts(currentFilter);
        renderTerminal(); 
        lucide.createIcons();
    }
</script>
<script>
    // Keeping the second renderArtifacts block consistent with the first one for redundancy/compatibility

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
            const isSelected = artifact.id === selectedArtifactId;
            const imageIcon = (artifact.images && artifact.images.length > 1) ? 'image' : 'image-off';

            const cardHtml = `
                    <div onclick="selectArtifact(this,${artifact.id})" 
                         class="bg-white brutalist-shadow-sm hover:translate-x-1 transition-transform duration-200 cursor-pointer p-4 brutalist-border ${isSelected ? 'selected-artifact' : ''}">
                        
                        <span class="text-xs font-mono uppercase ${isSelected ? 'text-white/70' : 'text-gray-500'}">${artifact.lot}</span>
                        <h3 class="font-bold text-lg uppercase leading-tight mt-1 mb-3">${artifact.title}</h3>
                        
                        <div class="w-full h-32 border-2 border-dashed ${isSelected ? 'border-white/50' : 'border-gray-300'} bg-gray-100 mb-3 flex items-center justify-center">
                            <i data-lucide="${imageIcon}" class="w-6 h-6 ${isSelected ? 'text-white/50' : 'text-gray-400'}"></i>
                            <img src="${artifact.images[0]}" class=" w-full h-full object-contain "  >
                        </div>
                        
                        <p class="text-xs ${isSelected ? 'text-white/80' : 'text-gray-600'} mb-3 truncate">${artifact.description}</p>
                        
                        <div class="flex justify-between items-center border-t ${isSelected ? 'border-white/20' : 'border-gray-200'} pt-2">
                            <div>
                                <div class="text-[10px] uppercase font-bold ${isSelected ? 'text-white/70' : 'text-gray-500'}">Price</div>
                                <div class="font-bold text-base font-mono">${formatRupeesJS(artifact.price)}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] uppercase font-bold ${isSelected ? 'text-white/70' : 'text-gray-500'}">Created</div>
                                <div class="font-bold text-base font-mono text-black">${artifact.dateOfCreation}</div>
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