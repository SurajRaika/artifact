<?php
if (basename($_SERVER['PHP_SELF']) == 'config.php') {
  header("Location: index.php");
  exit;
}

define("DB_SERVER", "localhost");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "password");
define("DB_NAME", "registered");




try {  # Connection
  $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
} catch(mysqli_sql_exception $e){
  header("Location: service-down.php");
  exit;
}




if (!$link){
  header("Location: service-down.php");
  exit;
}