<?php

session_start();

// index.php

require_once __DIR__ . '/app/Core/Router.php';


$router = new Router();


require_once __DIR__ . '/app/routes.php';




$router->dispatch($_SERVER['REQUEST_URI']);

?>



 



