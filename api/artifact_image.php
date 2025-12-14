<?php
session_start();

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/model/Artifact.php';

// Read query parameters
$artifact_id = isset($_GET["artifact_id"]) ? intval($_GET["artifact_id"]) : null;
$slide       = isset($_GET["slide"]) ? intval($_GET["slide"]) : null;

// Validate parameters
if (!$artifact_id || !$slide) {
    http_response_code(400);
    echo "Missing 'artifact_id' or 'slide' parameter.";
    exit;
}

$artifactModel = new Artifact($link);

// Get the image URL
$previewImage = $artifactModel->getPreviewImage($artifact_id, $slide);

// If no image found
if (!$previewImage) {
    http_response_code(404);
    echo "Image not found.";
    exit;
}

// -------------------------------------------
// SEND THE ACTUAL IMAGE SO <img src=""> WORKS
// -------------------------------------------

// Detect image type
$imageInfo = @getimagesize($previewImage);

if (!$imageInfo) {
    http_response_code(500);
    echo "Unable to load image.";
    exit;
}

// Set correct content type (jpg/png/webp/etc.)
header("Content-Type: " . $imageInfo['mime']);

// Output the actual image file
readfile($previewImage);

exit;
?>
<!-- http://localhost:8000/api/artifact_image.php?artifact_id=15&slide=1 -->