<?php
?>


<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AUCTION.BW-<?php echo $title; ?></title>
<!-- <link rel="stylesheet" href="/style/style.css"> -->
     <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

<script src="https://unpkg.com/lucide@latest"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
    /* All original CSS styles are kept here for brevity, 
           as moving them to an external CSS file was not requested.
           (Imports, brutalist styles, nav animations, etc.) */
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Teko:wght@500;600;700&display=swap');

    body {
        font-family: 'Outfit', sans-serif;
        background-color: #f0f0f0;
        color: #000;
    }

    .logotype {
        font-family: 'Teko', sans-serif;
        line-height: 1;
    }

    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #fff;
        border-left: 2px solid #000;
    }

    ::-webkit-scrollbar-thumb {
        background: #000;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #333;
    }

    .brutalist-border {
        border: 2px solid #000;
    }

    .brutalist-border-b {
        border-bottom: 2px solid #000;
    }

    .brutalist-border-t {
        border-top: 2px solid #000;
    }

    .brutalist-shadow {
        box-shadow: 4px 4px 0px #000;
    }

    .brutalist-shadow-sm {
        box-shadow: 2px 2px 0px #000;
    }

    .brutalist-shadow-hover:hover {
        transform: translate(-2px, -2px);
        box-shadow: 6px 6px 0px #000;
    }

    .brutalist-active:active {
        transform: translate(2px, 2px);
        box-shadow: 0px 0px 0px #000;
    }

    .nav-button {
        position: relative;
        transition: opacity 0.3s ease;
    }

    .nav-button.active {
        text-decoration: none;
        opacity: 1 !important;
    }

    .nav-button.active::after {
        content: "";
        position: absolute;
        left: 50%;
        bottom: -2px;
        width: 0;
        height: 2px;
        background: currentColor;
        transform: translateX(-50%);
        animation: underlineFade 0.6s ease forwards;
    }

    .nav-button.active {
        animation: softGlow 1.5s ease-in-out infinite alternate;
    }

    @keyframes underlineFade {
        from {
            width: 0;
            opacity: 0;
        }

        to {
            width: 70%;
            opacity: 1;
        }
    }

    @keyframes softGlow {
        from {
            text-shadow: 0 0 0 rgba(0, 0, 0, 0);
        }

        to {
            text-shadow: 0 0 6px rgba(0, 0, 0, 0.25);
        }
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .toast-enter {
        transform: translateY(100%);
        animation: slideIn 0.3s ease-out forwards;
    }

    @keyframes fundsDeductionPulse {
        0% {
            background-color: #f0f0f0;
            transform: scale(1);
            box-shadow: 0 0 0 #000;
        }

        50% {
            background-color: #ffcccc;
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }

        100% {
            background-color: #f0f0f0;
            transform: scale(1);
            box-shadow: 0 0 0 #000;
        }
    }

    .funds-deduction-pulse {
        animation: fundsDeductionPulse 0.5s ease-in-out;
    }

    .drag-over {
        border-color: #ff00ff !important;
        background-color: #ffeeff !important;
    }

    .selected-artifact {
        background-color: #000 !important;
        color: #fff !important;
        border-color: #000 !important;
    }

    .hc-shadow {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    }

    .status-winning {
        background-color: #ccffcc;
        color: #006600;
    }

    .status-outbid {
        background-color: #ffcccc;
        color: #cc0000;
    }

    .status-watching {
        background-color: #ccddff;
        color: #0000cc;
    }
</style>