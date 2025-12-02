<?php $page="Private Lots" ;
ob_start(); ?>

<div id="private-lots-view" class="view-content p-4 md:p-8 max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold uppercase tracking-tight brutalist-border-b text-center pb-2 mb-4">Private Lots Access</h1>

    <div id="private-code-form" class="max-w-xl mx-auto p-8 brutalist-border brutalist-shadow bg-white mt-10">
        <h2 class="text-xl font-bold uppercase mb-4">Enter Access Code</h2>
        <p class="text-sm text-gray-700 mb-6 font-mono">
            Access to this section is restricted. Please enter the current 8-digit secure code.
        </p>

        <div id="fee-warning" class="bg-red-100 text-red-700 brutalist-border border-red-300 p-3 mb-4 text-xs font-mono hidden">
            </div>

        <input type="text" id="private-code-input" placeholder="ALPHA734"
            class="w-full px-4 py-3 border-2 border-black font-mono text-xl uppercase focus:outline-none focus:ring-2 focus:ring-black mb-4"
            onkeydown="if(event.key === 'Enter') tryPrivateCode()">
        
        <button id="private-code-button" onclick="tryPrivateCode()"
            class="w-full bg-black text-white py-3 font-bold uppercase tracking-widest hover:bg-red-600 transition-all brutalist-shadow brutalist-active border-2 border-black">
            Attempt Access
        </button>
    </div>

    <div id="private-lots-content" class="hidden max-w-xl mx-auto p-8 brutalist-border brutalist-shadow bg-gray-900 text-white mt-10">
        <i data-lucide="shield-check" class="w-8 h-8 text-green-400 mb-3"></i>
        <h2 class="text-xl font-bold uppercase mb-4 text-green-400">ACCESS GRANTED</h2>
        <p class="text-sm font-mono mb-6">
            Welcome, authorized bidder. You now have access to Lot P-001 through P-008. These artifacts are sold via sealed bid only.
        </p>
        <button
            class="bg-white text-black px-4 py-2 text-xs font-bold uppercase hover:bg-gray-200 brutalist-shadow-sm brutalist-active brutalist-border">
            View Sealed Lots
        </button>
    </div>
</div>

<script>
    function initPrivateLots() {
        // No fund display update needed anymore
        const form = document.getElementById('private-code-form');
        const content = document.getElementById('private-lots-content');
        
        // This relies on the global privateAccessGranted variable from app/components/layouts/base.php
        if (typeof privateAccessGranted !== 'undefined' && privateAccessGranted) {
            form.classList.add('hidden');
            content.classList.remove('hidden');
        } else {
            form.classList.remove('hidden');
            content.classList.add('hidden');
        }
    }

    /**
     * Logic for attempting to access Private Lots with a secret code.
     */
    function tryPrivateCode() {
        const codeInput = document.getElementById('private-code-input');
        const warningDiv = document.getElementById('fee-warning');
        const secretCode = codeInput.value.trim();
        
        // Hide previous warnings
        warningDiv.classList.add('hidden');
        warningDiv.innerHTML = '';


        if (secretCode.length === 0) {
            warningDiv.innerHTML = 'Please enter a private access code.';
            warningDiv.classList.remove('hidden');
            return;
        }

        // NO FUNDS CHECK or DEDUCTION IS PERFORMED HERE

        // Disable button and show loading state
        const btn = document.getElementById('private-code-button');
        const oldText = btn.innerHTML;
        btn.innerHTML = 'VALIDATING CODE...';
        btn.disabled = true;

        // Simulate API delay
        setTimeout(() => {

            // Forced Failure State for Prototype
            const errorMessage = `ACCESS RESTRICTED: Only **verified items** are allowed. The code "${secretCode.toUpperCase()}" is **not verified or does not exist**.`;
            
            warningDiv.innerHTML = errorMessage;
            warningDiv.classList.remove('hidden');
            
            codeInput.value = ''; // Clear input on failure
            codeInput.focus();

            // Reset button
            btn.innerHTML = oldText;
            btn.disabled = false;
        }, 1500);
    }
    
    // Call init on load to set initial visibility (assuming this script runs after the DOM is ready)
    document.addEventListener('DOMContentLoaded', initPrivateLots);
</script>

<?php $slot = ob_get_clean();
include 'app/components/layouts/base.php';
?>