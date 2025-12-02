<?php
// authenticate/index.php , but if i visit the web i can get same by /authenticate
// The required file below now sets $page and $page_title before being included
require_once "../app/authentication/index.php"; 



// Ensure $slot is defined, or set it to an empty string to prevent errors
if (!isset($slot)) {
    $slot = '';
}

// *** New: Server-side message check ***
// This function determines the server-side message to display.
function getAuthMessage() {
    global $user_email_err, $password_err, $confirm_password_err, $login_err, $registration_err, $page;
    
    // Check for success messages first (e.g., after a successful registration redirect)
    if (isset($_GET['success']) && $_GET['success'] === 'registered') {
        return ['type' => 'success', 'message' => 'Registration complete. Please log in.'];
    }

    // Check for errors (only set if form was POSTed and validation failed)
    // The global error variables are set in LoginManager.php or RegisterManager.php
    $errors = [];
    if (!empty($user_email_err)) $errors[] = $user_email_err;
    
    // The password error variable name changes based on the mode
    $current_password_err = ($page === 'register') ? $user_password_err : $password_err;
    if (!empty($current_password_err)) $errors[] = $current_password_err;
    
    if ($page === 'register' && !empty($confirm_password_err)) $errors[] = $confirm_password_err;
    
    // Check main process errors (login_err or registration_err)
    $main_err = ($page === 'register') ? $registration_err : $login_err;
    if (!empty($main_err)) $errors[] = $main_err;

    if (!empty($errors)) {
        return ['type' => 'error', 'message' => implode(' | ', $errors)];
    }

    // Default message when nothing has happened
    return ['type' => 'info', 'message' => 'Enter your credentials to continue.']; 
}

$message_data = getAuthMessage();

// If we have an error, we need to make sure the email/password fields are pre-filled 
// with the POSTed values to be user-friendly.

?>
<!DOCTYPE html>
<html lang="en">

<?php $title = "Authenticate";
// Assuming this includes the required scripts and meta tags
// include 'components/global/head.php'; 
?>

<head>
    <?php $title = $page_title;
    include '../app/components/global/head.php'; // HEADER COMPONENT 
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
            
            <div id="message-area" class="brutalist-border border-2 p-3 mb-4 font-mono text-sm 
                <?php 
                    $message_class = '';
                    if ($message_data['type'] === 'error') {
                        $message_class = 'bg-red-100 border-red-600 text-red-700';
                    } elseif ($message_data['type'] === 'success') {
                        $message_class = 'bg-green-100 border-green-600 text-green-700';
                    } else {
                        // Default/info message is visible with no color classes
                    }
                    echo $message_class;
                ?>">
                <?php echo $message_data['message']; // Display the message 
                ?>
            </div>

            <form id="auth-form" method="POST" action="" class="space-y-4"> 

                <div>
                    <label for="email" class="block text-xs font-bold uppercase mb-1">Email / User ID</label>
                    <input type="email" id="email" name="user_email" required
                        class="brutalist-input" placeholder="user@domain.com"
                        value="<?php echo htmlspecialchars($user_email ?? ''); ?>"> </div>

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
        // *** Simplified Client-Side JS ***
        // The core authentication is now handled by PHP via a standard form POST.
        // The original JS functions (handleLogin/handleSignup) have been removed.
        // This script now only focuses on client-side visual management if needed, 
        // but the PHP logic is the authoritative source for mode and messages.
        
        const messageArea = document.getElementById('message-area');
        
        // Function to make sure the message area is visible if it contains content other than the default.
        if (messageArea.textContent.trim() !== 'Enter your credentials to continue.') {
            // Note: The visibility is now largely controlled by PHP class-setting, 
            // but this is a fail-safe to ensure it's not accidentally hidden.
            messageArea.classList.remove('hidden'); 
        } else {
            // If it's just the default message, hide it until an action happens
            messageArea.classList.add('hidden');
        }
    </script>

</body>

</html>