<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JODHPURARTIFACT.COM | Global Artisan Exchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- New Fonts: Playfair Display for headings (premium serif), Inter for body -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script> 
    <style>
        /* Minimalist, Premium Theme */
        body {
            font-family: 'Inter', sans-serif;
            font-weight: 300; /* Use lighter weight for body text */
            background-color: #fcfcfc; 
            color: #1a1a1a; 
            line-height: 1.7; /* Slightly more spacious line height */
        }
        h1, h2, h3, h4 {
            font-family: 'Playfair Display', serif;
            letter-spacing: 0; /* Remove tight letter spacing for elegance */
        }
        /* Custom Muted Accent Colors */
        .text-primary-dark {
            color: #1a1a1a;
        }
        .bg-primary-dark {
            background-color: #1a1a1a;
        }
        .text-accent-muted {
            color: #6b7280; /* Slightly lighter slate gray for subtle accent */
        }
        .bg-accent-muted {
            background-color: #4b5563;
        }
        .minimal-card {
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03); /* Extremely light shadow */
            border: 1px solid #f3f4f6; /* Lighter border */
        }
        .minimal-card:hover {
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05);
            transform: translateY(-0.5px);
        }
        img {
            object-fit: cover;
        }
    </style>
</head>
<body class="min-h-screen">

    <!-- Header/Navigation - FULL WIDTH -->
    <header class="border-b border-gray-100 bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center py-4">
            <!-- Logo: Minimalist Typography -->
            <div class="flex items-center gap-1">
                <div class="text-3xl font-normal tracking-normal" style="font-family: 'Playfair Display', serif;">
                    <span class="text-primary-dark font-semibold">Jodhpur</span>
                    <span class="text-accent-muted font-light">Artifacts</span>
                </div>
            </div>
            <!-- End Logo -->
            
            <a href="/authenticate" class="text-sm font-medium tracking-wide px-4 py-2 rounded-sm bg-primary-dark text-white shadow-md hover:bg-gray-700 transition duration-300">
                Sign In
            </a>
        </div>
    </header>

    
    <!-- Main Content Wrapper (Centered) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- UPDATED Hero Section: Two-Column Minimalist Layout - Reduced Vertical Padding -->
        <section class="py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center">
            <!-- Text Content Column -->
            <div class="space-y-6">
                <h1 class="text-lg font-light uppercase tracking-widest text-accent-muted" style="font-family: 'Inter', sans-serif;">The Artisan's Direct Exchange</h1>
                <h2 class="text-5xl sm:text-6xl font-light text-primary-dark leading-snug">
                    Authentic Craft. <span class="font-normal">Direct Connection.</span>
                </h2>
                <p class="text-lg text-gray-600 font-light pt-2">
                    A curated collection of handcrafted masterpieces from Jodhpur's skilled artisans, ensuring direct provenance and ethical exchange.
                </p>
                <div class="pt-6">
                    <a href="#" class="inline-flex items-center justify-center px-8 py-3 text-base font-medium leading-6 rounded-sm text-white bg-primary-dark shadow-md hover:bg-gray-700 transition-all duration-300 uppercase tracking-widest">
                        View Collection
                    </a>
                </div>
            </div>
            <!-- Image Column -->
            <div>
                <img src="https://images.pexels.com/photos/716107/pexels-photo-716107.jpeg?auto=compress&cs=tinysrgb&w=1200" 
                     alt="View of the Blue City, Jodhpur" 
                     class="w-full h-[60vh] rounded-sm shadow-lg minimal-card object-cover object-center"
                     onerror="this.onerror=null; this.src='https://placehold.co/800x600/f3f4f6/374151?text=Hero+Image';"
                >
            </div>
        </section>

        <!-- UPDATED Stats Section (Minimalist, Qualitative Focus) - Reduced Vertical Padding -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 py-12 border-t border-b border-gray-200">
            <div class="text-center p-6 bg-white minimal-card rounded-sm">
                <p class="text-5xl font-light tracking-tight text-primary-dark" style="font-family: 'Playfair Display', serif;">1200+</p>
                <p class="text-sm text-gray-500 uppercase mt-2 font-medium tracking-widest">Documented Artifacts</p>
            </div>
            <div class="text-center p-6 bg-white minimal-card rounded-sm">
                <p class="text-5xl font-light tracking-tight text-primary-dark" style="font-family: 'Playfair Display', serif;">50+</p>
                <p class="text-sm text-gray-500 uppercase mt-2 font-medium tracking-widest">Artisan Villages Supported</p>
            </div>
            <div class="text-center p-6 bg-white minimal-card rounded-sm">
                <p class="text-5xl font-light tracking-tight text-primary-dark" style="font-family: 'Playfair Display', serif;">6+</p>
                <p class="text-sm text-gray-500 uppercase mt-2 font-medium tracking-widest">Generations of Skill</p>
            </div>
        </section>

        <!-- Category Showcase Section -->
        <section class="py-16 md:py-24 text-center">
            <h3 class="text-3xl font-light text-primary-dark mb-12">
                Explore The Bazaar: Authentic Categories.
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
                
                <!-- Category Card 1: Furniture & Woodwork -->
                <a href="#" class="group block minimal-card rounded-sm overflow-hidden bg-white hover:shadow-lg transition duration-300">
                    <img src="https://content.jdmagicbox.com/comp/saharanpur/d5/9999px132.x132.160903144130.c2d5/catalogue/living-concept-handicrafts-saharanpur-ho-saharanpur-bedroom-furniture-dealers-05adno4oeb.jpg" 
                         alt="Furniture & Woodwork" 
                         class="w-full h-40 object-cover object-center group-hover:scale-[1.03] transition duration-300"
                         onerror="this.onerror=null; this.src='https://placehold.co/300x160/f3f4f6/374151?text=Woodwork';"
                    >
                    <div class="p-4">
                        <p class="text-xs font-medium uppercase tracking-widest text-primary-dark">FURNITURE & WOODWORK</p>
                    </div>
                </a>
                
                <!-- Category Card 2: Textiles & Blockprint -->
                <a href="#" class="group block minimal-card rounded-sm overflow-hidden bg-white hover:shadow-lg transition duration-300">
                    <img src="https://masakalee.com/cdn/shop/articles/printing-453747_1280.jpg?v=1729618773" 
                         alt="Textiles & Blockprint" 
                         class="w-full h-40 object-cover object-center group-hover:scale-[1.03] transition duration-300"
                         onerror="this.onerror=null; this.src='https://placehold.co/300x160/f3f4f6/374151?text=Textiles';"
                    >
                    <div class="p-4">
                        <p class="text-xs font-medium uppercase tracking-widest text-primary-dark">TEXTILES & BLOCKPRINT</p>
                    </div>
                </a>
                
                <!-- Category Card 3: Metal & Pottery -->
                <a href="#" class="group block minimal-card rounded-sm overflow-hidden bg-white hover:shadow-lg transition duration-300">
                    <img src="https://assets.aboutamazon.com/dims4/default/453ed9c/2147483647/strip/true/crop/780x605+0+0/resize/780x605!/quality/90/?url=https%3A%2F%2Famazon-blogs-brightspot.s3.amazonaws.com%2Fcc%2Ff8%2F10c8230943278e51460a852e7c21%2Fimage-5.jpg" 
                         alt="Metal & Pottery" 
                         class="w-full h-40 object-cover object-center group-hover:scale-[1.03] transition duration-300"
                         onerror="this.onerror=null; this.src='https://placehold.co/300x160/f3f4f6/374151?text=Metal';"
                    >
                    <div class="p-4">
                        <p class="text-xs font-medium uppercase tracking-widest text-primary-dark">METAL & POTTERY</p>
                    </div>
                </a>
                
                <!-- Category Card 4: Paintings & Art -->
                <a href="#" class="group block minimal-card rounded-sm overflow-hidden bg-white hover:shadow-lg transition duration-300">
                    <img src="https://cpimg.tistatic.com/5145669/b/4/indian-traditional-village-painting-wooden-handicraft-wall-hanging-home-decor-painting.jpg" 
                         alt="Paintings & Art" 
                         class="w-full h-40 object-cover object-center group-hover:scale-[1.03] transition duration-300"
                         onerror="this.onerror=null; this.src='images.pexels.com/photos/2222744/pexels-photo-2222744.jpeg?auto=compress&cs=tinysrgb&w=600'"
                    >
                    <div class="p-4">
                        <p class="text-xs font-medium uppercase tracking-widest text-primary-dark">PAINTINGS & ART</p>
                    </div>
                </a>
            </div>

            <!-- Footer Text from Screenshot -->
           
        </section>


        <!-- Section 1: Image Left, Text Right (Woodwork) - Reduced Vertical Padding -->
        <section class="py-16 md:py-24">
            <div class="grid md:grid-cols-2 gap-10 md:gap-16 items-start">
                <div class="order-1 md:order-1">
                    <img src="https://images.pexels.com/photos/2222744/pexels-photo-2222744.jpeg?auto=compress&cs=tinysrgb&w=600" 
                         alt="Intricate Wood Carving" 
                         class="w-full h-96 minimal-card rounded-sm object-cover object-center shadow-lg"
                         onerror="this.onerror=null; this.src='https://placehold.co/600x384/f3f4f6/374151?text=Woodwork+Detail';"
                    >
                </div>
                <div class="order-2 md:order-2 pt-4 md:pt-0">
                    <p class="text-sm font-medium uppercase text-accent-muted mb-2 tracking-widest">Craft Focus</p>
                    <h3 class="text-3xl font-light text-primary-dark mb-6">The Enduring Legacy of Intricate Woodwork.</h3>
                    <p class="text-gray-600 mb-8 text-lg font-light">
                        Jodhpur is renowned globally for its detailed, hand-carved furniture and architectural elements. Our platform connects you directly with the master artisans who have preserved these ancient techniques for generations.
                    </p>
                    <ul class="space-y-3 text-gray-700 text-base">
                        <li class="flex items-center gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-accent-muted"></i> Traditional preservation techniques</li>
                        <li class="flex items-center gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-accent-muted"></i> Sustainable, locally sourced timber</li>
                        <li class="flex items-center gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-accent-muted"></i> Guaranteed digital provenance</li>
                    </ul>
                    <a href="#" class="mt-10 inline-block text-base font-medium text-primary-dark border-b-2 border-primary-dark hover:border-gray-500 transition duration-300 uppercase tracking-wider">
                        View Woodwork Collection &rarr;
                    </a>
                </div>
            </div>
        </section>

        <!-- Section 2: Image Right, Text Left (Textiles) - Reduced Vertical Padding -->
        <section class="py-16 md:py-24 border-t border-gray-200">
            <div class="grid md:grid-cols-2 gap-10 md:gap-16 items-start">
                <div class="order-2 md:order-1 pt-4 md:pt-0">
                    <p class="text-sm font-medium uppercase text-accent-muted mb-2 tracking-widest">Material & Design</p>
                    <h3 class="text-3xl font-light text-primary-dark mb-6">The Richness of Rajasthani Textiles.</h3>
                    <p class="text-gray-600 mb-8 text-lg font-light">
                        From vibrant Tie-Dye (Bandhani) to classic Block Printing, the textiles of Rajasthan are a celebration of color and texture, deeply rooted in local traditions and natural dyes.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <span class="px-3 py-1 bg-gray-100 text-sm font-normal text-gray-700 rounded-sm border border-gray-200">BANDHANI TIE-DYE</span>
                        <span class="px-3 py-1 bg-gray-100 text-sm font-normal text-gray-700 rounded-sm border border-gray-200">BLOCK PRINTED</span>
                        <span class="px-3 py-1 bg-gray-100 text-sm font-normal text-gray-700 rounded-sm border border-gray-200">HAND WOVEN SILK</span>
                    </div>
                    <a href="#" class="mt-10 inline-block text-base font-medium text-primary-dark border-b-2 border-primary-dark hover:border-gray-500 transition duration-300 uppercase tracking-wider">
                        Shop Textile Art &rarr;
                    </a>
                </div>
                <div class="order-1 md:order-2">
                    <img src="https://images.pexels.com/photos/1328495/pexels-photo-1328495.jpeg?auto=compress&cs=tinysrgb&w=600" 
                         alt="Block Printed Fabric" 
                         class="w-full h-96 minimal-card rounded-sm object-cover object-center shadow-lg"
                         onerror="this.onerror=null; this.src='https://placehold.co/600x384/f3f4f6/374151?text=Textiles+Detail';"
                    >
                </div>
            </div>
        </section>

        <!-- Security and Provenance Features - Added background, increased padding, and expanded card content -->
        <section class="py-20 md:py-32 border-t border-gray-200 bg-gray-50">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <p class="text-sm font-medium uppercase text-accent-muted mb-2">Our Commitments</p>
                <h3 class="text-4xl font-light tracking-tight text-primary-dark">Authenticity and ethical exchange are our core principles.</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Feature 1: Secure Escrow - Increased Content Density & High Contrast Icon -->
                <div class="p-8 bg-white minimal-card rounded-sm shadow-md">
                    <div class="w-14 h-14 bg-primary-dark text-white rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="lock" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-xl font-medium mb-3 text-primary-dark">Secure Escrow Protection</h4>
                    <p class="text-gray-700 text-base font-normal">
                        We utilize a secure third-party escrow system, ensuring your funds are held safely until you confirm receipt and satisfaction with the artifact. This guarantees a trusted, risk-free exchange for both the global collector and the Jodhpur artisan.
                    </p>
                </div>
                
                <!-- Feature 2: Digital Provenance - Increased Content Density & High Contrast Icon -->
                <div class="p-8 bg-white minimal-card rounded-sm shadow-md">
                    <div class="w-14 h-14 bg-primary-dark text-white rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="file-text" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-xl font-medium mb-3 text-primary-dark">Immutable Digital Provenance</h4>
                    <p class="text-gray-700 text-base font-normal">
                        Each artifact includes a permanent, digital ledger documenting the creator's origin, the raw materials used, and a unique authentication certificate. This verifiable history guarantees the artwork's authenticity and value.
                    </p>
                </div>
                
                <!-- Feature 3: Quality Vetting - Increased Content Density & High Contrast Icon -->
                <div class="p-8 bg-white minimal-card rounded-sm shadow-md">
                    <div class="w-14 h-14 bg-primary-dark text-white rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="shield-check" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-xl font-medium mb-3 text-primary-dark">Mandatory Quality Vetting</h4>
                    <p class="text-gray-700 text-base font-normal">
                        Physical assets undergo an independent, multi-point quality check by the Jodhpur Artisan Council before shipping. This confirms the integrity, craftsmanship standards, and adherence to the original specifications.
                    </p>
                </div>
            </div>
        </section>

        <!-- Final Call to Action - High Contrast to reduce 'empty' feeling -->
        <section class="text-center py-20 md:py-32 bg-primary-dark minimal-card rounded-sm my-20 text-white shadow-xl">
            <h3 class="text-4xl sm:text-5xl font-light tracking-tight mb-4" style="font-family: 'Playfair Display', serif;">
                Begin Your Collection.
            </h3>
            <p class="text-gray-300 max-w-2xl mx-auto mb-8 text-lg font-light">
                Register now to access exclusive new listings and join a global community dedicated to preserving and sharing heritage craft.
            </p>
            <!-- Inverted button: White background, dark text -->
            <a href="#" class="inline-flex items-center justify-center px-10 py-3 text-base font-medium leading-6 rounded-sm text-primary-dark bg-white shadow-lg hover:bg-gray-200 transition-all duration-300 uppercase tracking-widest">
                Register Now
            </a>
        </section>

    </div> <!-- End Main Content Wrapper -->

    <!-- Footer -->
    <footer class="text-center py-10 border-t border-gray-300 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-xs text-gray-500 uppercase tracking-widest font-medium">
                JODHPURARTIFACT.COM // Global gateway for Rajasthani heritage. EST. 2025
            </p>
            <div class="mt-4 space-x-6">
                <a href="#" class="text-sm text-gray-600 hover:text-primary-dark transition duration-200">Provenance Policy</a>
                <a href="#" class="text-sm text-gray-600 hover:text-primary-dark transition duration-200">Legal Disclosures</a>
                <a href="#" class="text-sm text-gray-600 hover:text-primary-dark transition duration-200">Artisan Fund</a>
            </div>
        </div>
    </footer>


    <!-- Initialize Lucide icons -->
    <script>
        lucide.createIcons();
    </script>

</body>
</html>