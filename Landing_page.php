<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUCTION.COM | Secure Asset Exchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Lucide Icons for Black & White Aesthetics -->
    <script src="https://unpkg.com/lucide@latest"></script> 
    <style>
        /* Brutalist High-Contrast Theme */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f0f0; /* Light background */
            color: #000000; /* Black text */
        }
        .text-accent-red {
            color: #ef4444; /* Retained red accent */
        }
        .bg-accent-red {
            background-color: #ef4444; /* Retained red accent */
        }
        /* New Brutalist Utility Classes for High Contrast */
        .brutalist-border {
            border: 2px solid #000;
        }
        .brutalist-shadow {
            box-shadow: 4px 4px 0px #000;
        }
        .brutalist-active:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px #000;
        }
        .brutalist-hover:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px #000;
        }
    </style>
</head>
<body class="min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header/Navigation -->
        <header class="flex justify-between items-center py-6 brutalist-border-b border-b-2 border-black">
            <!-- Updated Logo: AUCTION with a black block .COM -->
            <div class="font-bold text-2xl tracking-tight flex items-center gap-1.5">
                <span class="text-4xl font-semibold sm:inline uppercase">AUCTION</span>
                <div class="bg-black text-white px-2 py-0.5 text-sm font-semibold tracking-widest brutalist-border">
                    .COM
                </div>
            </div>
            <!-- End Updated Logo -->
            
            <a href="/authenticate" class="text-xs font-semibold tracking-wider uppercase px-4 py-2 brutalist-border brutalist-shadow bg-white hover:bg-gray-100 brutalist-active">
                Join Now
            </a>
        </header>

        <!-- Hero Section -->
        <section class="text-center py-20 md:py-32 space-y-8">
            <h2 class="text-5xl sm:text-7xl font-bold tracking-tighter max-w-4xl mx-auto">
                ASSETS.<span class="text-gray-400"> EXCHANGE. SECURE.</span>
            </h2>
            <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                The dedicated, high-security platform for the transparent and discreet transfer of exceptional, investment-grade assets globally.
            </p>
            <div class="pt-4">
                <a href="#" class="inline-flex items-center justify-center px-8 py-3 text-base font-bold leading-6 text-white bg-accent-red brutalist-border brutalist-shadow brutalist-active hover:bg-red-700 transition-all uppercase tracking-wider">
                    View Current Lots
                </a>
            </div>
        </section>

        <!-- Stats Section (Using a stark, contrasting look) -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-8 py-12 md:py-20 border-t-2 border-b-2 border-black">
            <div class="text-center p-4 brutalist-border bg-white brutalist-shadow">
                <p class="text-5xl font-extrabold tracking-tighter">1,200+</p>
                <p class="text-sm text-gray-700 uppercase mt-1">Total Lots Managed</p>
            </div>
            <div class="text-center p-4 brutalist-border bg-white brutalist-shadow">
                <p class="text-5xl font-extrabold tracking-tighter">$850M+</p>
                <p class="text-sm text-gray-700 uppercase mt-1">Value Transferred</p>
            </div>
            <div class="text-center p-4 brutalist-border bg-white brutalist-shadow">
                <p class="text-5xl font-extrabold tracking-tighter">15K+</p>
                <p class="text-sm text-gray-700 uppercase mt-1">Active Users</p>
            </div>
        </section>

        <!-- Security Protocol Features -->
        <section class="py-16 md:py-24">
            <div class="flex items-baseline mb-12 border-b-2 border-black pb-2">
                <p class="text-xs font-mono uppercase text-accent-red mr-4">Protocol 001.03</p>
                <h3 class="text-2xl font-bold tracking-tight">Security is the only policy.</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Feature 1: Escrow Protected -->
                <div class="p-6 brutalist-border bg-white brutalist-shadow brutalist-hover">
                    <!-- Icon: Lock -->
                    <i data-lucide="lock" class="w-8 h-8 text-black mb-3"></i>
                    <h4 class="text-xl font-bold mb-2">Escrow Protected</h4>
                    <p class="text-gray-700 text-sm">
                        All transactions utilize audited escrow accounts for full fund fidelity and guaranteed buyer/seller protection.
                    </p>
                </div>
                
                <!-- Feature 2: Digital Provenance -->
                <div class="p-6 brutalist-border bg-white brutalist-shadow brutalist-hover">
                    <!-- Icon: File Text -->
                    <i data-lucide="file-text" class="w-8 h-8 text-black mb-3"></i>
                    <h4 class="text-xl font-bold mb-2">Digital Provenance</h4>
                    <p class="text-gray-700 text-sm">
                        Permanent ledger tracking for ownership history, condition, and verifiable authentication certificates.
                    </p>
                </div>
                
                <!-- Feature 3: Mandatory Audit -->
                <div class="p-6 brutalist-border bg-white brutalist-shadow brutalist-hover">
                    <!-- Icon: Shield Check -->
                    <i data-lucide="shield-check" class="w-8 h-8 text-black mb-3"></i>
                    <h4 class="text-xl font-bold mb-2">Mandatory Audit</h4>
                    <p class="text-gray-700 text-sm">
                        Security protocols are subjected to quarterly, mandatory third-party audits to ensure data integrity.
                    </p>
                </div>
            </div>
        </section>

        <!-- Asset Classes / Exchange -->
        <section class="py-16 md:py-24 border-t-2 border-black">
            <h3 class="text-3xl font-bold tracking-tight mb-10 border-b border-gray-400 pb-2">The Exchange: Exclusive Asset Classes.</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 md:gap-8">
                <div class="p-4 bg-gray-200 brutalist-border text-center brutalist-shadow hover:bg-gray-300 transition duration-300">
                    <p class="text-2xl font-bold uppercase tracking-wider">Artifacts</p>
                </div>
                <div class="p-4 bg-gray-200 brutalist-border text-center brutalist-shadow hover:bg-gray-300 transition duration-300">
                    <p class="text-2xl font-bold uppercase tracking-wider">Materials</p>
                </div>
                <div class="p-4 bg-gray-200 brutalist-border text-center brutalist-shadow hover:bg-gray-300 transition duration-300">
                    <p class="text-2xl font-bold uppercase tracking-wider">Digital Assets</p>
                </div>
                <div class="p-4 bg-gray-200 brutalist-border text-center brutalist-shadow hover:bg-gray-300 transition duration-300">
                    <p class="text-2xl font-bold uppercase tracking-wider">Real Estate</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 mt-8 text-center">
                All lots are vetted by the AUCTION.COM Compliance Board. <a href="#" class="underline hover:text-black transition duration-200 font-bold">Review Full Criteria.</a>
            </p>
        </section>

        <!-- Participate/Protocol in Action -->
        <section class="py-16 md:py-24 border-t-2 border-black">
            <h3 class="text-3xl font-bold tracking-tight mb-12 border-b border-gray-400 pb-2">Participate: The Protocol in Action.</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <!-- Step 1 -->
                <div class="space-y-3 p-4 bg-white brutalist-border brutalist-shadow">
                    <div class="text-3xl font-extrabold text-accent-red">01.</div>
                    <h4 class="text-xl font-bold">Register Access</h4>
                    <p class="text-gray-700 text-sm">
                        Complete mandatory KYC and AML verification to activate full bidding and selling privileges.
                    </p>
                </div>
                
                <!-- Step 2 -->
                <div class="space-y-3 p-4 bg-white brutalist-border brutalist-shadow">
                    <div class="text-3xl font-extrabold text-gray-600">02.</div>
                    <h4 class="text-xl font-bold">Vetting & Review</h4>
                    <p class="text-gray-700 text-sm">
                        Detailed digital documentation, condition reports, and provenance ledgers are provided for every lot.
                    </p>
                </div>
                
                <!-- Step 3 -->
                <div class="space-y-3 p-4 bg-white brutalist-border brutalist-shadow">
                    <div class="text-3xl font-extrabold text-gray-600">03.</div>
                    <h4 class="text-xl font-bold">Secure Engagement</h4>
                    <p class="text-gray-700 text-sm">
                        Place bids via the secure bidding terminal. All bids are legally binding and irrevocable.
                    </p>
                </div>
                
                <!-- Step 4 -->
                <div class="space-y-3 p-4 bg-white brutalist-border brutalist-shadow">
                    <div class="text-3xl font-extrabold text-gray-600">04.</div>
                    <h4 class="text-xl font-bold">Title Transfer</h4>
                    <p class="text-gray-700 text-sm">
                        Escrow releases funds, and the legal title and physical asset delivery are managed securely.
                    </p>
                </div>
            </div>
        </section>

        <!-- Final Call to Action -->
        <section class="text-center py-16 md:py-24 bg-white brutalist-border brutalist-shadow my-16">
            <h3 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">
                Begin Your Exchange.
            </h3>
            <p class="text-gray-700 max-w-2xl mx-auto mb-8">
                Activate your account now to access private lots and secure your status on the leading global asset platform.
            </p>
            <a href="#" class="inline-flex items-center justify-center px-8 py-3 text-base font-bold leading-6 text-white bg-accent-red brutalist-border brutalist-shadow brutalist-active hover:bg-red-700 transition-all uppercase tracking-wider">
                Register Account Now
            </a>
        </section>

        <!-- Footer -->
        <footer class="text-center py-8 border-t-2 border-black">
            <p class="text-xs text-gray-600 uppercase tracking-widest">
                AUCTION.COM // Dedicated to verified provenance and financial security. EST. 2025
            </p>
            <div class="mt-2 space-x-4">
                <a href="#" class="text-xs text-gray-600 hover:text-black transition duration-200 font-bold">SECURITY AUDIT</a>
                <a href="#" class="text-xs text-gray-600 hover:text-black transition duration-200 font-bold">DISCLOSURES</a>
            </div>
        </footer>

    </div>

    <!-- Initialize Lucide icons -->
    <script>
        lucide.createIcons();
    </script>

</body>
</html>