<?php
// PreventDirectAcess.php

// If the current file is loaded directly (not included)
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {

    // Detect protocol
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? "https://"
        : "http://";

    // Build full absolute URL
    $root = $protocol . $_SERVER['HTTP_HOST'];

    // Redirect to home page
    header("Location: $root/index.php");
    exit;
}
?>
