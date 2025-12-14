<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/model/User.php';
require_once __DIR__ . '/../app/model/Artifact.php';

function send_response($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, "Invalid request method.");
}

// Must be logged in
if (!isset($_SESSION["email"])) {
    send_response(false, "Access denied. You must be logged in.");
}

$email = $_SESSION["email"];

// Input
$artifact_id = (int)($_POST['artifact_id'] ?? 0);
$visibility = (int)($_POST['visibility'] ?? -1);

if ($artifact_id <= 0 || ($visibility !== 0 && $visibility !== 1)) {
    send_response(false, "Invalid input data.");
}

// Database objects
$userModel = new User($link);
$artifactModel = new Artifact($link);

// Get user by email
$user = $userModel->get_user_by_email($email);

if (!$user) {
    send_response(false, "User not found.");
}

$user_id = $user['id'];

// Update visibility
$result = $artifactModel->update_visibility_secure($artifact_id, $visibility, $user_id);

if ($result === null) {
    send_response(true, "Visibility updated successfully. $artifact_id, $visibility, $user_id ");
} else {
    send_response(false, $result);
}

mysqli_close($link);
?>
