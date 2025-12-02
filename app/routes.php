<?php
// app/routes.php
$router->get('', function () {

    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === TRUE) {
        require 'home.php';
    } else {
        require 'Landing_page.php';
    }
});

$router->get('about', function () {
    require 'about.php';
});
