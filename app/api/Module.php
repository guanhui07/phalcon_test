<?php
namespace app\api;

use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{
    public function registerAutoloaders(\Phalcon\DiInterface $di = null)
    {
        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces(array(
            'app\api\controllers' => __DIR__ . '/controllers/',
        ))->register();
    }

    public function registerServices(\Phalcon\DiInterface $di)
    {
        // 调度
        $di->set('dispatcher', function () {
            $eventsManager = new \Phalcon\Events\Manager();
            $eventsManager->attach("dispatch", function ($event, $dispatcher, $exception) {
                // 如果控制器或方法不存在
                if ($event->getType() == 'beforeException') {
                    switch ($exception->getCode()) {
                        case \Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                        case \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                            $dispatcher->forward(array( 'controller' => 'Index', 'action' => 'notFound' ));
                            return false;
                    }
                }
            });

            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setDefaultNamespace("app\api\controllers");
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });

        $di->set('view', function () use ($di) {
            $view = new \Phalcon\Mvc\View();
            $config = $di->get('config');
            if (isset($config->views->path->api)) {
                $view->setViewsDir($config->views->path->api);
            } else {
                $theme = isset($config->views->theme->api) ? $config->views->theme->api : 'default';
                $template_path = __DIR__ . '/views/' . $theme . '/';
                $view->setViewsDir($template_path);
            }
            $view->registerEngines(array(
                ".volt"     => function ($view, $di) {
                    $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
                    $volt->setOptions(array(
                        'compiledPath' => function ($template_path) {
                            $template_path = strstr($template_path, '/views');
                            $template_cache =  APP_PATH . '/runtime/cache/volt/api' . dirname($template_path);
                            is_dir($template_cache) || mkdir($template_cache, 0777, true);
                            return $template_cache . '/' . basename($template_path, '.volt') . '.php';
                        },

                        'compileAlways' => true
                    ));
//                    $compiler = $volt->getCompiler();
//                    $compiler->addExtension(new \App\Extension\VoltPHPFunctions());
//                    $compiler->addExtension(new \App\Extension\CmsPHPFunctions());
//                    $compiler->addExtension(new \App\Extension\PagePHPFunctions());
                    return $volt;
                },
                ".phtml" => "Phalcon\\Mvc\\View\\Engine\\Php",
                ".php" => "Phalcon\\Mvc\\View\\Engine\\Php",
            ));
            return $view;
        });
    }
}
