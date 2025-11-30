<?php
// base.php
// If the current file is loaded directly (not included)

// Start the session (essential for session_destroy to work)
session_start();

$page = $_GET['action'] ?? 'login';  // default state

$page_title = "Access Terminal";


switch ($page) {
    case 'login':
        $page_title = "Access Terminal";
        break; // Stop execution here
    case 'register':
        $page_title = "Create New Account";
        break; // Stop execution here
    case 'logout':
        session_destroy();
        // Redirect to login after logout
        header('Location: base.php?action=login');
        exit;
    default:
        // Handle invalid 'action' values, default to login
        $page = 'login';
        $page_title = "Access Terminal";
        break;
};


// Ensure $slot is defined, or set it to an empty string to prevent errors
if (!isset($slot)) {
    $slot = '';
}

?>
<!DOCTYPE html>
<html lang="en">

<?php $title = "Authenticate";
// Assuming this includes the required scripts and meta tags
// include 'components/global/head.php'; 
?>

<head>
    <?php $title = $page_title;
    include '../components/global/head.php'; // HEADER COMPONENT 
    ?>


    <link rel="stylesheet" href="/authenticate/style.css">
    </link>

</head>

<body class="h-screen flex flex-col items-center justify-center py-10 overflow-auto">

    <div class="w-full max-w-md px-4">

        <header class="mb-8 flex items-center justify-center">
            <div class="flex items-center gap-4">
                <div class="font-bold text-2xl tracking-tight flex items-center gap-1.5">
                    <span class="logotype text-4xl font-semibold sm:inline">AUCTION</span>
                    <div class="bg-black text-white px-2 py-0.5 text-sm font-semibold tracking-widest brutalist-border">
                        .COM
                    </div>
                </div>
            </div>
        </header>

        <div class="bg-white brutalist-border brutalist-shadow p-6 sm:p-8">

            <h1 id="auth-title" class="text-2xl font-bold uppercase mb-4 text-center">
                <?php echo $page_title; // Dynamically set title 
                ?>
            </h1>

            <div id="message-area" class="brutalist-border border-2 p-3 mb-4 font-mono text-sm hidden">
            </div>

            <form id="auth-form" onsubmit="event.preventDefault(); handleSubmit();" class="space-y-4">

                <div>
                    <label for="email" class="block text-xs font-bold uppercase mb-1">Email / User ID</label>
                    <input type="email" id="email" name="email" required
                        class="brutalist-input" placeholder="user@domain.com">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="brutalist-input" placeholder="Enter Password">
                </div>


                <?php

                // CONDITIONAL PHP BLOCK for Confirm Password field
                if ($page === 'register') {
                    echo '
                    <div id="confirm-password-group" class="">
                        <label for="confirm-password" class="block text-xs font-bold uppercase mb-1">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm-password" required
                               class="brutalist-input" placeholder="Repeat Password">
                    </div>
                    ';
                } else {
                    // Render the div hidden for consistency and JS reference
                    echo '
                    <div id="confirm-password-group" class="hidden">
                        <label for="confirm-password" class="block text-xs font-bold uppercase mb-1">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm-password" 
                               class="brutalist-input" placeholder="Repeat Password">
                    </div>
                    ';
                }

                ?>


                <button type="submit" id="submit-button" class="brutalist-btn w-full bg-black text-white brutalist-shadow-sm brutalist-shadow-hover brutalist-active">
                    <?php
                    // Dynamically change button text
                    echo ($page === 'register') ? 'Create Account' : 'Secure Login';
                    ?>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t-2 border-black text-center text-sm">
                <?php if ($page === 'login') : ?>
                    <span id="toggle-prompt" class="text-gray-600">Don't have an account?</span>
                    <a href="?action=register" id="toggle-button" class="font-bold text-black hover:underline ml-1">
                        Create Account
                    </a>
                <?php else : ?>
                    <span id="toggle-prompt" class="text-gray-600">Already have an account?</span>
                    <a href="?action=login" id="toggle-button" class="font-bold text-black hover:underline ml-1">
                        Secure Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // State Management - Initialize JS state based on PHP's $page variable
        let currentMode = '<?php echo $page; ?>'; // 'login' or 'register'

        // DOM Elements
        const authTitle = document.getElementById('auth-title');
        const messageArea = document.getElementById('message-area');
        // Note: confirmPasswordGroup is primarily managed by PHP now, but the input is still needed for JS validation
        const confirmPasswordInput = document.getElementById('confirm-password');
        const submitButton = document.getElementById('submit-button');
        const togglePrompt = document.getElementById('toggle-prompt');
        const toggleButton = document.getElementById('toggle-button');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');


        /**
         * Displays a notification message.
         * @param {string} type - 'success' or 'error'
         * @param {string} message - The message content.
         */
        function showMessage(type, message) {
            messageArea.textContent = message;
            messageArea.classList.remove('hidden', 'bg-red-100', 'border-red-600', 'text-red-700', 'bg-green-100', 'border-green-600', 'text-green-700');

            if (type === 'error') {
                messageArea.classList.add('bg-red-100', 'border-red-600', 'text-red-700');
            } else if (type === 'success') {
                messageArea.classList.add('bg-green-100', 'border-green-600', 'text-green-700');
            }
        }

        /**
         * Clears the notification message.
         */
        function clearMessage() {
            messageArea.textContent = '';
            messageArea.classList.add('hidden');
            messageArea.className = 'brutalist-border border-2 p-3 mb-4 font-mono text-sm hidden'; // Reset classes
        }

        /**
         * Handles the form submission based on the current mode.
         */
        function handleSubmit() {
            clearMessage();
            const email = emailInput.value;
            const password = passwordInput.value;

            // Use currentMode initialized by PHP
            if (currentMode === 'login') {
                handleLogin(email, password);
            } else {
                // Ensure confirmPasswordInput exists (it does, even if hidden by PHP)
                const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';
                handleSignup(email, password, confirmPassword);
            }
        }

        // --- Mock Authentication Handlers ---

        function handleLogin(email, password) {
            // Mock Error:
            showMessage('error', 'Mock Error: Authentication failed. Check credentials.');

            // Mock Success condition:
            if (email.toLowerCase().includes('success')) {
                showMessage('success', 'Login successful! Redirecting...');
                // In a real app: window.location.href = 'index.html';
            }
        }

        function handleSignup(email, password, confirmPassword) {
            if (password !== confirmPassword) {
                showMessage('error', 'Passwords do not match.');
                return;
            }

            // Mock registration is always successful
            showMessage('success', 'Registration successful. Please wait...');

            // In a real app, you might redirect after success:
            // setTimeout(() => {
            //     window.location.href = 'base.php?action=login'; 
            // }, 2000);
        }

        // Initial setup - The PHP sets the initial state, no need for complex JS init logic.
    </script>

</body>

</html>