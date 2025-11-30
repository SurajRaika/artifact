            <div class="flex items-center gap-3 relative">

                <div id="profile-modal"
                    class="absolute top-full right-0 mt-2 brutalist-border brutalist-shadow bg-white z-50 w-72 hidden">
                    <div class="flex items-center p-4 border-b-2 border-black">
                        <div
                            class="bg-black text-white w-10 h-10 rounded-full flex items-center justify-center font-mono font-bold text-lg mr-3">
                            D9
                        </div>
                        <div>
                            <div class="font-bold uppercase text-sm">User:D9F3</div>
                            <div class="text-xs text-gray-600 font-mono">Status: Active Bidder</div>
                        </div>
                    </div>

                    <div class="p-4">
                        <h4 class="font-bold uppercase text-xs mb-2 border-b border-gray-200 pb-1">Recent Transactions
                        </h4>
                        <ul id="transaction-list" class="space-y-2 text-xs font-mono">
                        </ul>
                        <button
                            class="w-full mt-4 bg-black text-white py-2 text-xs font-bold uppercase hover:bg-gray-800 brutalist-shadow-sm brutalist-active brutalist-border">
                            View Full History
                        </button>
                    </div>
                </div>

                <div id="funds-display" onclick="toggleProfileModal()" title="View Profile & Transactions"
                    class="text-right cursor-pointer p-2 rounded hover:bg-gray-100 brutalist-border transition-colors sm:block">
                    <div class="text-[10px] uppercase font-bold text-gray-500">Available Funds</div>
                    <div class="font-bold font-mono text-sm sm:text-base">$0</div>
                </div>

            </div>


                    <script>
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