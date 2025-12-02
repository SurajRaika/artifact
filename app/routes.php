<?php
// app/routes.php
$router->get('', function () {

     if (!empty($_SESSION['logged_in'])) {
                require 'home.php';

            }else{
        require 'Landing_page.php';
    }

});

$router->get('about', function () {
    require 'about.php';
});
