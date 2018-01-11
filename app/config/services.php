<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;

/**
 * Shared configuration service
 */

//同set
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();
    $url = new \Phalcon\Mvc\Url();
    $url->setBaseUri($config->application->baseUri);// /tutorial/
    return $url;
});

/**
 * Setting up the view component
 * 根据什么后缀 选什么 模板引擎
 */
$di->set('view', function () {
    $config = $this->getConfig();

    $view = new \Phalcon\Mvc\View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);
    $view->registerEngines([
        '.volt' => function ($view) {
            $config = $this->getConfig();
            $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $this);
            $volt->setOptions([
                'compiledPath' => $config->application->cacheDirView,
                'compiledSeparator' => '_',
                'compileAlways' => true
            ]);
            $compiler = $volt->getCompiler();
            //$compiler->addExtension(new \App\Extension\VoltPHPFunctions());
            //$compiler ->addFunction();
            return $volt;
        },
        '.phtml' => \Phalcon\Mvc\View\Engine\Php::class,
        '.php' =>\Phalcon\Mvc\View\Engine\Php::class

    ]);

    return $view;
});

$di->setShared('profiler', function () {
    return new \Phalcon\Db\Profiler();
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {

//    $eventsManager = new \Phalcon\Events\Manager();
//    $profiler = $this->getProfiler();//
//    $eventsManager->attach('db', function($event, $connection) use ($profiler) {
//        if ($event->getType() == 'beforeQuery') {
//            $profiler->startProfile($connection->getSQLStatement());
//        }
//        if ($event->getType() == 'afterQuery') {
//            $profiler->stopProfile();
//        }
//    });

    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);
    return $connection;
});


/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new \Phalcon\Mvc\Model\Metadata\Memory();
});

/**
 * Register the flash service with the Twitter Bootstrap classes
 */
$di->set('flash', function () {
    return new \Phalcon\Flash\Direct([
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ]);
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {
    $session = new \Phalcon\Session\Adapter\Files();
    $session->start();

    return $session;
});

$di->setShared('cache', function () {
    $config = \Phalcon\DI::getDefault()->getShared('config')->toArray();//获取配置
    $frontCache = new \Phalcon\Cache\Frontend\Data(array('lifetime' => $config['application']['lifeTime'])); //配置文件读取
    //$cache_path = APP_PATH . '/runtime/cache/';//配置文件 读取
    $cache_path = APP_PATH . $config['application']['cacheFileDir'];
    is_dir($cache_path) || mkdir($cache_path, 0777, true);
    $cache = new \Phalcon\Cache\Backend\File($frontCache, array('cacheDir' => $cache_path));
    return $cache;
});


$di->setShared('cookie', function () {
    $cookies = new Phalcon\Http\Response\Cookies();
    return $cookies;
});


$di->setShared('crypt', function () {
    $config = \Phalcon\DI::getDefault()->getShared('config');
    $crypt = new \Phalcon\Crypt();
    $crypt->setKey($config->session->session_crypt_key); // 使用你自己的key！
    return $crypt;
});

//$di->setShared('tag', function () {
//    //$config = \Phalcon\DI::getDefault()->getShared('config');
//    return new \App\Library\Tags();
//});


//$di->setShared('router', function () {
//    $router = new \Phalcon\Mvc\Router(FALSE);
//    $router->setDefaults(array( 'module' => 'home', 'controller' => 'Test', 'action' => 'index'));
//});


/*
 多个公共属性在控制器访问, 像: $this->flash, $this->db 或者 $this->session.
$this->view ,
$this->config
$this->url
--cache  cookie log
些是先前在服务容器中定义的服务 (app/config/services.php).
当它们第一次访问的时候, 它们被注入作为控制器的一部分
 *
 *  */
