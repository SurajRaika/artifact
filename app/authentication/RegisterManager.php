<?php
// app/authentication/RegisterManager.php

// Define variables and initialize with empty values
$user_email_err = $user_password_err = $confirm_password_err = $registration_err = "";
$user_email = $user_password = $confirm_password = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validate Email
    if (empty(trim($_POST["user_email"]))) {
        $user_email_err = "Please enter an email id.";
    } else {
        $user_email = trim($_POST["user_email"]);
    }

    // 2. Validate Password
    if (empty(trim($_POST["password"]))) {
        $user_password_err = "Please enter a password.";
    } else {
        $user_password = trim($_POST["password"]);
    }

    // 3. Validate Confirm Password
    if (empty(trim($_POST["confirm-password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm-password"]);
        if (empty($user_password_err) && ($user_password != $confirm_password)) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // 4. Attempt Registration if no errors
    if (empty($user_email_err) && empty($user_password_err) && empty($confirm_password_err)) {
        
        // Assuming a User class exists with a register method
        $localUser = new User($link); 
        
        // The register method should handle validation, hashing, and database insertion.
        // It returns null on success, or an error message string.
        $registration_err = $localUser->register($user_email,$user_email, $user_password); 

        if (empty($registration_err)) {
            // Registration successful! Redirect or set success message.
            // For simplicity, we'll redirect to the login page after successful registration.
            header("location: /authenticate?action=login&success=registered");
            exit;
        }
    }

    // Close connection (if $link is a mysqli connection)
    // mysqli_close($link); // Usually only closed if there are no more DB operations.
}
?>