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
    case 'register':
        $page_title = "Create New Account";
        require_once __DIR__ . "/RegisterManager.php";
    case 'logout':
        session_destroy();
        // Redirect to login after logout
        header('Location: ./');
        exit;
    default:
        // Handle invalid 'action' values, default to login
        $page = 'login';
        $page_title = "Access Terminal";
        require_once __DIR__ . "/LoginManager.php";
};











// NOTE: The PHP logic files (RegisterManager and LoginManager) will handle form submissions
// and set error variables, which can be checked in the main index.php if needed.
// The current front-end uses JS submission, so the PHP logic will need to handle AJAX/POST data
// or the front-end will need to be changed to a standard POST submission.
// For now, I will assume a standard POST for the Manager files.
