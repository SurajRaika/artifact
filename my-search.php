<?php $page="Search" ;
ob_start(); ?>

<div id="search-view" class="view-content p-4 md:p-8 max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold uppercase tracking-tight brutalist-border-b pb-2 mb-4">Artifact Search Terminal</h1>

    <div class="max-w-xl mx-auto p-8 brutalist-border brutalist-shadow bg-white mt-10">
        <h2 class="text-xl font-bold uppercase mb-4">Search Catalog</h2>
        
        <input type="text" id="search-input" placeholder="Enter keyword or Lot ID..."
            class="w-full px-4 py-3 border-2 border-black font-mono text-xl uppercase focus:outline-none focus:ring-2 focus:ring-black mb-4">
        
        <button onclick="searchArtifacts(document.getElementById('search-input').value)"
            class="w-full bg-black text-white py-3 font-bold uppercase tracking-widest hover:bg-red-600 transition-all brutalist-shadow brutalist-active border-2 border-black">
            Execute Search
        </button>

        <div class="mt-8 p-3 brutalist-border border-dashed bg-gray-50">
            <p class="text-sm text-gray-500 font-mono">Results will be displayed here.</p>
        </div>
    </div>
</div>

<script>
    function searchArtifacts(query) {
        // Not fully implemented in the original file, just log/show toast
        showToast(`Searching for: ${query}`, 'search', 'blue');
        console.log(`Searching for: ${query}`);
    }
</script>

<?php $slot = ob_get_clean();
include 'app/components/layouts/base.php';
?>