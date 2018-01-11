<?php

$router = $di->getRouter();
if (!file_exists(dirname(dirname(__DIR__)) . $_SERVER['REQUEST_URI'])) {
    $_GET['_url'] = explode('?', $_SERVER['REQUEST_URI'])[0];
    //$di['router']->setUriSource(\Phalcon\Mvc\Router::URI_SOURCE_GET_URL);
}


// Define your routes here


$router->add('/:controller/:action/:params',
    array(
        'controller' => 1, 'action' => 2,
        'params' => 3
    )
);

//指定特定url访问特定uri
$router->add(
    "/admin/users/change-password",
    [
        "controller" => "test2",
        "action"     => "upload",
    ]
);

//  设置默认 module为home
$router->add('/', array('module' => 'home', 'controller' => 'Index', 'action' => 'index'));
$router->add('/:controller[/]?', array('module' => 'home',
    'controller' => 1, 'action' => 'index'));
$router->add('/:controller/:action/:params', array('module' => 'home',
    'controller' => 1, 'action' => 2, 'params' => 3));



//设置home module
$router->add('/home/:controller/:action/:params',
    array('module' => 'home', 'controller' => 1, 'action' => 2, 'params' => 3 ));
//设置api module
$router->add('/api/:controller/:action/:params',
    array('module' => 'api', 'controller' => 1, 'action' => 2, 'params' => 3 ));

$router->handle();
