<?php

$loader = new \Phalcon\Loader();
//默认注册文件夹
//$loader->registerDirs(
//    [
//        $config->application->controllersDir,
//        $config->application->modelsDir
//    ]
//)->register();



include_once(APP_PATH . '/common/Functions.php');

$loader->registerNamespaces(array(
    //'app\common'        => APP_PATH . '/common/',
    //'app\api'           => APP_PATH . '/api/',
    'app\models'        => APP_PATH . '/common/models/',
    'app\library'       => APP_PATH . '/common/library/',
    'app\controllers'   => APP_PATH . '/common/controllers/',
    //'app\extension'     => APP_PATH . '/extension/'
))->register();

//$di =  FactoryDefault这个对象 入口文件 不同module 设置默认命名空间 不同
$di->set('dispatcher', function () {
    $dispatcher = new Phalcon\Mvc\Dispatcher();
    $dispatcher->setDefaultNamespace('app\controllers');
    return $dispatcher;
});
