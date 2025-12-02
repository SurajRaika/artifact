<?php
// app/authentication/index.php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../model/User.php";

// --- Determine Page Mode and Title ---
// Default to 'login'
$page_title = "Access Terminal";


$page = $_GET['action'] ?? 'login';  // default state



switch ($page) {
    case 'login':
        $page_title = "Access Terminal";
        require_once __DIR__ . "/LoginManager.php";
        break;

    case 'register':
        $page_title = "Create New Account";
        require_once __DIR__ . "/RegisterManager.php";
        break;

    case 'logout':
        header('Location: ./');
        exit;

    default:
        $page = 'login';
        $page_title = "Access Terminal";
        require_once __DIR__ . "/LoginManager.php";
        break;
}





