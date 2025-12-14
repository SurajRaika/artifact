<?php



error_reporting(E_ALL);
ini_set('display_errors', 1);
// 1. Configuration and Setup
// Start session to access user ID
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set header for JSON response
header('Content-Type: application/json');

// Include necessary files (assuming these paths are correct in your structure)
// Corrected paths for submit_artifact.php in the 'api' directory
require_once __dir__ . '/../app/config.php'; // Path to config.php (up two levels: api -> php-bet -> app)
require_once __dir__ . '/../app/model/Artifact.php'; // Path to Artifact.php

// Function to send JSON response and exit
function send_response($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// 2. Authentication Check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, "Invalid request method.");
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['id'])) {
    send_response(false, "You must be logged in to submit an artifact.");
}

$seller_id = $_SESSION['id'];

// 3. Collect and Sanitize Input
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$is_private = (int) ($_POST['is_private'] ?? 0);
$password = trim($_POST['password'] ?? ''); // Secret Access Code
$confirm_password = trim($_POST['confirm_password'] ?? ''); // Not used in model, just for server-side check



// 6. Collect Base64 Images
// PHP automatically converts "images[]" from FormData into an array at $_POST['images']
$uploaded_images = $_POST['images'] ?? []; 
$image_count = count($uploaded_images);

// 4. Server-Side Validation
if (empty($name) || empty($description)) {
    send_response(false, "Artifact title and description are required.");
}

$private_code_valid = false;
$error_message = null;

if ($is_private) {
    // Check if code is exactly 6 digits
    if (!preg_match('/^\d{6}$/', $password)) {
        $error_message = "Secret Access Code must be exactly 6 digits.";
    } elseif ($password !== $confirm_password) {
        // This check is mainly for client-side, but good to double-check
        $error_message = "Secret Access Code and confirmation code do not match.";
    } else {
        $private_code_valid = true;
    }

    if ($error_message) {
        send_response(false, $error_message);
    }
}




// 5. Database Interaction
// Assuming $link is the mysqli connection object from 'config/db.php'
if (!isset($link)) {
    send_response(false, "Database connection failed.");
}

$artifactModel = new Artifact($link);

// Prepare the password for the model: use the code if private, otherwise null
$final_password = ($is_private && $private_code_valid) ? $password : null; // $password is the raw 6-digit code
// Call the method to create the artifact
$db_error = $artifactModel->create_artifact(
    $name,
    $description,
    $seller_id,
    $is_private,
        $image_count,

    $final_password
);




// 7. Handle Image Uploads
if ($db_error === null) {
    // Success: Get the newly created artifact ID
    $new_artifact_id = mysqli_insert_id($link);

    // Only process if we actually have images
    if (!empty($uploaded_images) && is_array($uploaded_images)) {
        
        // CRITICAL: Your Artifact model must be able to handle Base64 strings now.
        // If uploadImages() expects URLs, you must update that method in Artifact.php 
        // to decode Base64 and save the file to disk.
        $image_upload_error = $artifactModel->uploadImages($new_artifact_id, $uploaded_images);
        
        if ($image_upload_error !== null) {
            // Log the error but maybe don't kill the response if the artifact was created
            // Or use your existing logic:
            send_response(false, "Artifact submitted, but image upload failed: " . $image_upload_error);
        }
    }

    // Success: Return tracking ID and estimated completion
    $tracking_id = "AR-" . date('Ymd') . "-" . str_pad($new_artifact_id, 3, '0', STR_PAD_LEFT);
    $est_completion = date('Y-m-d H:i T', strtotime('+48 hours'));

    send_response(true, "Artifact submitted and appraisal started.", [
        'tracking_id' => $tracking_id,
        'est_completion' => $est_completion
    ]);
} else {
    // Failure
    send_response(false, "Failed to submit artifact to database: " . $db_error);
}

// Close database connection
mysqli_close($link);
?>