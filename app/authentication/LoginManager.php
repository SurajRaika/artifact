<?php
// app/authentication/LoginManager.php (Updated)

// Define variables and initialize with empty values
$user_email_err = $password_err = $login_err = ""; // Renamed $user_password_err to $password_err
$user_email = $password = ""; // Renamed $user_password to $password

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  // 1. Validate Email/User ID
  if (empty(trim($_POST["user_email"]))) {
    $user_email_err = "Please enter your username or an email id.";
  } else {
    $user_email = trim($_POST["user_email"]);
  }

  // 2. Validate Password (The input name is 'password', not 'user_password')
  if (empty(trim($_POST["password"]))) { // Check the correct input name
    $password_err = "Please enter your password.";
  } else {
    $password = trim($_POST["password"]); // Set the correct variable name
  }

  // 3. Validate credentials 
  if (empty($user_email_err) && empty($password_err)) {
    // Assuming a User class exists with a login method
    $localUser = new User($link);
    
    // The login method should find the user, verify the hashed password, and start the session.
    // It returns null on success, or an error message string.
    $login_err = $localUser->login($user_email, $password);

    if (empty($login_err)) {
      // Login successful! Redirect to a protected page.
      header("location: /dashboard/index.php"); // Example protected page
      exit;
    }
  }

  // Close connection (if $link is a mysqli connection)
  // mysqli_close($link);
}
?>