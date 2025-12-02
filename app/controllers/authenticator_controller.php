    <?php

session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === TRUE) {
    header("Location: ./");
    exit;
}

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
