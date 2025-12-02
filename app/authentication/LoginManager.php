<?php


# Include connection
# Define variables and initialize with empty values
$user_email_err = $user_password_err = $login_err = "";
$user_email = $user_password = "";

# Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty(trim($_POST["user_email"]))) {
    $user_email_err = "Please enter your username or an email id.";
  } else {
    $user_email = trim($_POST["user_email"]);
  }

  if (empty(trim($_POST["user_password"]))) {
    $user_password_err = "Please enter your password.";
  } else {
    $user_password = trim($_POST["user_password"]);
  }

  # Validate credentials 
  if (empty($user_email_err) && empty($user_password_err)) {
    $localUser=new User($link);
    $user_email_err=$localUser->login($user_email,$user_password);
  }

  # Close connection
  mysqli_close($link);
}
?>