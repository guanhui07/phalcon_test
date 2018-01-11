<?php
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

ini_set('default_charset', 'UTF-8');
define('BASE_PATH', dirname(__DIR__));
defined('ROOT_PATH') || define('ROOT_PATH', BASE_PATH);
define('ROOT', ROOT_PATH.'/');
define('APP_PATH', BASE_PATH . '/app');

define("DEBUG", true); //调试模式true

date_default_timezone_set('Asia/Chongqing');

if (DEBUG) {
    error_reporting(E_ALL); //E_ALL & ~E_NOTICE
    ini_set('display_error', 'On');
} else {
    ini_set('display_error', 'Off');
}

include '../vendor/autoload.php'; //加载composer autoload  php-cs-fixer fix .

try {

    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new \Phalcon\Di\FactoryDefault();

    /**
     * Handle routes
     */
    include APP_PATH . '/config/router.php'; //配置路由

    /**
     * Read services
     */
    include APP_PATH . '/config/services.php';//公共属性共享在控制器访问

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();
    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php'; ////注册自动加载的 命名空间,设置默认命名空间

    set_exception_handler('cException');

    require APP_PATH . "/L.php"; // 继承Application  增加注册服务模块

    /**
     * Handle the request
     */
    //$application = new \Phalcon\Mvc\Application($di);
    //增加module 主要 L.php 和 router.php 增加注册服务模块,以及配置路由
    //todo 默认控制器 视图 去掉 ,在路由指定 在无module 在路由上 设置默认 module
    //todo 去掉默认视图,对应module 视图编译在对应 module 文件夹
    $application = new L($di);

    echo str_replace(["\n","\r","\t"], '', $application->handle()->getContent());
} catch (\Exception $e) {
    exceptionLog($e);
} catch (\Phalcon\Exception $e) {
    exceptionLog($e);
} catch (\PDOException $e) {
    exceptionLog($e);
}

function exceptionLog($e)
{
    if (DEBUG) {
        //echo $e->__toString();die;
        echo $e->getMessage() . '<br>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
        cException($e);
    } else {
        cException($e);
    }
}
