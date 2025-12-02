<?php $page = "Process Payment";
ob_start(); ?>
<div id="payment-view" class="view-content p-4 md:p-8 max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold uppercase tracking-tight brutalist-border-b text-center pb-2 mb-4"> Payment Dashboard</h1>

   
    <div id="payment-form-container" class="max-w-2xl mx-auto p-6 brutalist-border brutalist-shadow bg-white mt-8">
        <h2 class="text-xl font-bold uppercase mb-4">Enter Card Details (Indian Banks)</h2>
        <p class="text-sm text-gray-700 mb-6 font-mono">
            We accept all major credit and debit cards issued by Indian banks.
        </p>

        <form id="card-payment-form" onsubmit="event.preventDefault(); processPayment();">
            <div class="space-y-4">
                <div>
                    <label for="cardholder-name" class="block text-sm font-bold uppercase mb-1">Card Holder Name</label>
                    <input type="text" id="cardholder-name" placeholder="Name on Card" class="w-full px-3 py-2 border-2 border-black font-mono focus:outline-none" required>
                </div>

                <div>
                    <label for="card-number" class="block text-sm font-bold uppercase mb-1">Card Number (16 Digits)</label>
                    <input type="text" id="card-number" placeholder="XXXX XXXX XXXX XXXX" pattern="[0-9]{16}" maxlength="16" class="w-full px-3 py-2 border-2 border-black font-mono focus:outline-none" required>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label for="expiry-date" class="block text-sm font-bold uppercase mb-1">Expiry Date (MM/YY)</label>
                        <input type="text" id="expiry-date" placeholder="MM/YY" pattern="(0[1-9]|1[0-2])\/?([0-9]{2})$" maxlength="5" class="w-full px-3 py-2 border-2 border-black font-mono focus:outline-none" required>
                    </div>
                    <div class="col-span-1">
                        <label for="cvv" class="block text-sm font-bold uppercase mb-1">CVV</label>
                        <input type="password" id="cvv" placeholder="XXX" pattern="[0-9]{3,4}" maxlength="4" class="w-full px-3 py-2 border-2 border-black font-mono focus:outline-none" required>
                    </div>
                </div>

                <div class="p-3 brutalist-border brutalist-shadow-sm bg-gray-50">
                    <label for="upi-option" class="block text-sm font-bold uppercase mb-1">UPI / Net Banking (Alternative)</label>
                    <select id="upi-option" class="w-full px-3 py-2 border-2 border-black font-mono focus:outline-none">
                        <option value="disabled">Card Payment Only for this Transaction</option>
                    </select>
                </div>

                <button type="submit" id="pay-btn" class="w-full bg-black text-white py-3 font-bold uppercase tracking-widest hover:bg-red-600 transition-all brutalist-shadow brutalist-active border-2 border-black mt-4">
                    CREDIT MONEY NOW 
                </button>
            </div>
        </form>
    </div>

    <div id="payment-error-container" class="hidden max-w-2xl mx-auto p-6 brutalist-border brutalist-shadow bg-red-800 text-white mt-8">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="server-crash" class="lucide lucide-server-crash w-8 h-8 text-yellow-400 mb-3"><path d="M6 10h12"></path><path d="M6 14h12"></path><rect width="18" height="8" x="3" y="2" rx="2"></rect><rect width="18" height="8" x="3" y="14" rx="2"></rect><path d="m10 18-2 3h8l-2-3"></path><path d="m15 6-1 2-2-4-2 4-1-2"></path></svg>
        <h2 class="text-xl font-bold uppercase mb-4 text-yellow-400">SERVER ERROR: Transaction Failed</h2>
        <p class="text-sm font-mono mb-4">
            **Error Code: 503 SERVICE UNAVAILABLE**
            <br>
            The payment gateway is currently experiencing a critical outage and is temporarily down. Your payment could not be processed.
        </p>

        <div class="p-3 brutalist-border bg-black text-xs font-mono mb-6">
            <p>Please contact the organization's support team immediately to arrange for an alternative payment method (e.g., direct bank transfer or third-party link).</p>
        </div>

        <button onclick="document.getElementById('payment-error-container').classList.add('hidden'); document.getElementById('payment-form-container').classList.remove('hidden');" class="w-full bg-white text-black py-2 text-xs font-bold uppercase hover:bg-gray-200 brutalist-shadow-sm brutalist-active brutalist-border">
            Try Card Payment Again (Not Recommended)
        </button>
    </div>
</div>

<script>
    // This is a dummy function to simulate the server-side error
    function processPayment() {
        // 1. Hide the payment form
        document.getElementById('payment-form-container').classList.add('hidden');
        
        // 2. Simulate a server error (e.g., via an AJAX call failure)
        setTimeout(() => {
            // 3. Show the error container
            document.getElementById('payment-error-container').classList.remove('hidden');
        }, 500); // 0.5 second delay to simulate processing
    }

    // Input formatting for Expiry Date (MM/YY) - Optional but good practice
    document.getElementById('expiry-date').addEventListener('input', function (e) {
        let input = e.target.value.replace(/\D/g, ''); // Remove all non-numeric characters
        if (input.length > 2) {
            // Insert a slash after the month
            input = input.substring(0, 2) + '/' + input.substring(2, 4);
        }
        e.target.value = input;
    });

</script>

<style>
/* Add the brutalist styles for toggle and shadow, keeping the card payment focus */
.brutalist-border-b { border-bottom: 2px solid #000; }
.brutalist-border { border: 2px solid #000; }
.brutalist-shadow { box-shadow: 4px 4px 0 0 #000; }
.brutalist-shadow-sm { box-shadow: 2px 2px 0 0 #000; }
.brutalist-active:active { box-shadow: 0 0 0 0 #000 !important; transform: translate(2px, 2px) !important; }
</style>

<?php $slot = ob_get_clean();
include 'app/components/layouts/base.php';
?>