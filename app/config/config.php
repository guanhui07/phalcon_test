<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

return new \Phalcon\Config([
    'database' => [
//        'adapter'     => 'Mysql',
//        'host'        => 'localhost',
//        'username'    => 'root',
//        'password'    => '123456',
//        'dbname'      => 'test',
//        'charset'     => 'utf8',

        'adapter'     => 'Postgresql',
        'host'        => 'localhost',
        'username'    => 'postgres',
        'password'    => '123456',
        'dbname'      => 'test',
        'charset'     => 'utf8',
        'table_prefix'     => ' ',
    ],
    'application' => [
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers/',
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'pluginsDir'     => APP_PATH . '/plugins/',
        'libraryDir'     => APP_PATH . '/library/',
        'cacheDirView'       => BASE_PATH . '/cache/',
        'baseUri'        => '/',
        'lifeTime' =>300,
        'cacheFileDir'=>'/runtime/cache/',
    ],

    'session' =>
        [
            'session_crypt_key' =>'#$&123&($%1',
        ],
    'views' =>
    [
        'path'=>[
            'home'=>APP_PATH.'/home/views/',
            'api'=>APP_PATH.'/api/views/',
            'task'=>APP_PATH.'/task/views/',
        ],
    ],
    'site' =>[
        'url' =>'http://localhost/',
        'static_uri' =>'/static/',
        'base_uri' =>'/rmht/',
        'name' =>'tile name',
    ],
]);


//$this->config->site->static_uri;
//$this->config->site->base_uri