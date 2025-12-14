<?php
// session_start();
// include_once("config.php");

// --- START: Define artifact data for direct sale (Prices in INR) ---
// Note: 'currentBid' is renamed to 'price' and values are changed to INR equivalents.
$all_artifacts = [
    // Featured Lot: Price changed to a large INR value
    ['id' => 7, 'lot' => 'LOT 001', 'title' => 'The Lost Codex of Alexandria', 'description' => 'A preserved 4th-century parchment.', 'category' => 'archaeology', 'price' => 290000000],
    
    // Regular Lots: Prices changed to smaller INR values
    ['id' => 1, 'lot' => 'LOT 002', 'title' => 'Venus de Milo Sketch', 'description' => 'A rare preparatory sketch by an unknown master.', 'category' => 'fineart', 'price' => 12500000],
    ['id' => 2, 'lot' => 'LOT 003', 'title' => 'Roman Bronze Coin Hoard', 'description' => 'A preserved collection of third-century Roman coins.', 'category' => 'archaeology', 'price' => 7000000],
    ['id' => 3, 'lot' => 'LOT 004', 'title' => 'Declaration of Independence Draft', 'description' => 'An early, signed draft of the historic document.', 'category' => 'historical', 'price' => 415000000],
    ['id' => 4, 'lot' => 'LOT 005', 'title' => 'First Edition Moby Dick', 'description' => 'A pristine first edition of Herman Melville\'s masterpiece.', 'category' => 'rarebooks', 'price' => 3750000],
    ['id' => 5, 'lot' => 'LOT 006', 'title' => 'Ancient Greek Amphora', 'description' => 'A large, intact vessel from the 5th century BC.', 'category' => 'archaeology', 'price' => 10000000],
    ['id' => 6, 'lot' => 'LOT 007', 'title' => 'Modern Abstract Sculpture', 'description' => 'A striking piece by contemporary artist, Ava Chen.', 'category' => 'fineart', 'price' => 25000000],
];
// --- END: Define artifact data for direct sale ---

$page = "Artifacts for Sale"; // Page title updated
$currentFilter = 'all'; // Default filter

ob_start();

// Custom function to format price in INR
function format_inr($amount) {
    // Uses the internal PHP Intl extension for robust currency formatting (requires intl extension)
    if (class_exists('NumberFormatter')) {
        $formatter = new NumberFormatter('en_IN', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, 'INR');
    }
    // Fallback if intl extension is not available (simpler formatting)
    return '₹' . number_format($amount, 0, '.', ',');
}


// 1. --- PHP POST HANDLER FOR FILTERING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['category_filter'])) {
        $submitted_view = $_POST['category_filter'];
        
        $valid_filters = ['all', 'archaeology', 'fineart', 'historical', 'rarebooks'];
        
        if (in_array($submitted_view, $valid_filters)) {
            $currentFilter = $submitted_view;
        }
    }
}

// 2. --- FILTER ARTIFACTS BASED ON CURRENT FILTER ---
$filtered_artifacts = $all_artifacts; 
if ($currentFilter !== 'all') {
    $filtered_artifacts = array_filter($all_artifacts, function($artifact) use ($currentFilter) {
        return $artifact['category'] === $currentFilter;
    });
}

// Separate the featured lot (assuming ID 7)
$featured_lot = array_values(array_filter($all_artifacts, function($a) { return $a['id'] === 7; }))[0] ?? null;

// Remove the featured lot from the main list if present
$display_artifacts = array_filter($filtered_artifacts, function($a) { return $a['id'] !== 7; });
$display_artifacts = array_values($display_artifacts); 
?>


<div id="live-auctions-view" class="view-content p-4 md:p-8 max-w-7xl mx-auto">

    <?php if ($featured_lot): ?>
    <div id="featured-lot" class="mb-8 brutalist-border bg-white brutalist-shadow p-4 sm:p-6 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="md:w-1/3 w-full border-2 border-gray-200 bg-gray-100 p-4 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="image-off" class="lucide lucide-image-off w-10 h-10 mx-auto text-gray-400 mb-2"><line x1="2" x2="22" y1="2" y2="22"></line><path d="M10.41 10.41a2 2 0 1 1-2.83-2.83"></path><line x1="13.5" x2="6" y1="13.5" y2="21"></line><line x1="18" x2="21" y1="12" y2="15"></line><path d="M3.59 3.59A1.99 1.99 0 0 0 3 5v14a2 2 0 0 0 2 2h14c.55 0 1.052-.22 1.41-.59"></path><path d="M21 15V5a2 2 0 0 0-2-2H9"></path></svg>
            <p class="font-mono text-xs text-gray-600">LOT 001 IMAGE PREVIEW</p>
        </div>
        <div class="md:w-2/3 w-full">
            <span class="bg-black text-white px-2 py-0.5 text-xs font-bold uppercase mb-2 inline-block">Featured Item</span>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold uppercase leading-tight tracking-tighter"><?= htmlspecialchars($featured_lot['title']) ?></h1>
            <p class="font-mono mt-3 text-sm text-gray-600"><?= htmlspecialchars($featured_lot['description']) ?></p>
            <div class="flex items-center justify-between mt-4 border-t border-black pt-3">
                <div class="text-xs uppercase font-bold text-gray-500">Price</div>
                <div class="font-bold text-xl sm:text-2xl font-mono text-black" id="featured-bid"><?= format_inr($featured_lot['price']) ?></div>
            </div>
            <button onclick="selectArtifact(<?= $featured_lot['id'] ?>)" class="bg-black text-white px-8 py-3 font-bold uppercase w-full mt-4 hover:bg-white hover:text-black border-2 border-black transition-all brutalist-shadow-hover brutalist-active">
                Buy Now
            </button>
        </div>
    </div>
    <?php endif; ?>

    <h1 class="text-3xl font-bold uppercase tracking-tight brutalist-border-b pb-2 mb-4">Artifacts for Sale</h1>

    <div class="flex flex-wrap gap-2 mb-6 text-sm font-medium uppercase">
        
        <?php
        $categories = [
            'all' => 'All Categories',
            'archaeology' => 'Archaeology',
            'fineart' => 'Fine Art',
            'historical' => 'Historical',
            'rarebooks' => 'Rare Books'
        ];

        foreach ($categories as $category_key => $category_label):
            $is_active = ($currentFilter === $category_key);
        ?>
        <form method="POST" class="inline-block" style="margin: 0; padding: 0;">
            <input type="hidden" name="category_filter" value="<?= $category_key ?>">
            
            <button type="submit"
                class="filter-button px-3 py-1 brutalist-border brutalist-shadow-sm brutalist-active 
                <?= $is_active ? 'bg-black text-white' : 'bg-white hover:bg-gray-100 text-black' ?>">
                <?= $category_label ?>
            </button>
        </form>
        <?php endforeach; ?>

    </div>
    <div id="artifacts-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        
        <?php foreach ($display_artifacts as $artifact): 
            $selectedArtifactId = 0; // Placeholder for demonstration
            $isSelected = $artifact['id'] === $selectedArtifactId;
        ?>
            
            <div onclick="selectArtifact(<?= $artifact['id'] ?>)" 
                 class="bg-white brutalist-shadow-sm hover:translate-x-1 transition-transform duration-200 cursor-pointer p-4 brutalist-border <?= $isSelected ? 'selected-artifact' : '' ?>">
                
                <span class="text-xs font-mono uppercase <?= $isSelected ? 'text-white/70' : 'text-gray-500' ?>"><?= htmlspecialchars($artifact['lot']) ?></span>
                <h3 class="font-bold text-lg uppercase leading-tight mt-1 mb-3"><?= htmlspecialchars($artifact['title']) ?></h3>
                
                <div class="w-full h-32 border-2 border-dashed <?= $isSelected ? 'border-white/50' : 'border-gray-300' ?> bg-gray-100 mb-3 flex items-center justify-center">
                    <i data-lucide="image-off" class="w-6 h-6 <?= $isSelected ? 'text-white/50' : 'text-gray-400' ?>"></i>
                </div>
                
                <p class="text-xs <?= $isSelected ? 'text-white/80' : 'text-gray-600' ?> mb-3 truncate"><?= htmlspecialchars($artifact['description']) ?></p>
                
                <div class="flex justify-between items-center border-t <?= $isSelected ? 'border-white/20' : 'border-gray-200' ?> pt-2">
                    <div>
                        <div class="text-[10px] uppercase font-bold <?= $isSelected ? 'text-white/70' : 'text-gray-500' ?>">Price</div>
                        <div class="font-bold text-base font-mono"><?= format_inr($artifact['price']) ?></div>
                    </div>
                    <div class="text-right">
                         <button onclick="selectArtifact(<?= $artifact['id'] ?>)" class="bg-black text-white px-3 py-1 text-xs font-bold uppercase hover:bg-gray-800 brutalist-active">
                            Buy
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($display_artifacts)): ?>
            <p class="col-span-full text-gray-500 italic">No artifacts found in the "<?= htmlspecialchars($currentFilter) ?>" category.</p>
        <?php endif; ?>

    </div>
</div>

<script>
    // Note: The original JavaScript logic (e.g., selectArtifact) may still reference 
    // the variable 'artifacts'. If you are still using that logic, ensure the 
    // PHP data injection is updated to use the new structure (e.g., 'price' instead of 'currentBid').

    // Since you asked for pure PHP filtering, the filtering/rendering JS is omitted.

    // If 'selectArtifact' is still used, you would need to define it 
    // and the dependency 'lucide.createIcons()' would still be necessary 
    // if you want the icons to render after the PHP loop generates the HTML.
</script>


<?php $slot = ob_get_clean();
include 'app/components/layouts/base.php';
?>