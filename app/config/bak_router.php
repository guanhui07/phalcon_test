<?php

$router = $di->getRouter();

if (!file_exists(__DIR__ . '/' . $_SERVER['REQUEST_URI'])) {
    $_GET['_url'] = explode('?', $_SERVER['REQUEST_URI'])[0];
}


// Define your routes here otherwise use 默认的路由




$router->handle();
