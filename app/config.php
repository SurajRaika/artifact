<?php
require __DIR__ . '/../vendor/autoload.php';


use Dotenv\Dotenv;

// Load .env from project root
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();




if (basename($_SERVER['PHP_SELF']) == 'config.php') {
  header("Location: index.php");
  exit;
}
define("DB_SERVER", $_ENV['DB_HOST']);
define("DB_USERNAME", $_ENV['DB_USER']);
define("DB_PASSWORD", $_ENV['DB_PASS']);
define("DB_NAME", $_ENV['DB_NAME']);







try {  # Connection
  $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
} catch(mysqli_sql_exception $e){
    die("MYSQL ERROR: " . $e->getMessage());
  // header("Location: service-down.php");
}




if (!$link){
  header("Location: service-down.php");
  exit;
}