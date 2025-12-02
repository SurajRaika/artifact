<?php

// app/Core/Router.php
class Router
{
    
    protected $routes=[];
    public function get($url     , $callback)
    {
        $this->routes[trim($url,'/')]=$callback;
    }



    public function dispatch($url){
        $url = trim(parse_url($url, PHP_URL_PATH), '/');
        if (isset($this->routes[$url])){
            return call_user_func($this->routes[$url]);
        }


        http_response_code(404);
        require '404.php';
    }




}
