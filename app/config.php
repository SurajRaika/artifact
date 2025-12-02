<?php
// Load environment class
$db_env = require 'env.php';


// Redirect if someone accesses this config directly
if (basename($_SERVER['PHP_SELF']) === 'config.php') {
    header("Location: index.php");
    exit;
}

// Get DB credentials
$dbHost = $db_env['DB_HOST'];
$dbName = $db_env['DB_NAME'];
$dbUser = $db_env['DB_USER'];
$dbPass = $db_env['DB_PASS'];

// Define constants for compatibility (optional)
define("DB_SERVER", $dbHost);
define("DB_USERNAME", $dbUser);
define("DB_PASSWORD", $dbPass);
define("DB_NAME", $dbName);

// Connect to MySQL
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Handle connection errors
if (!$link) {
    // Optional: log error here
    header("Location: service-down.php");
    exit;
}
?>
