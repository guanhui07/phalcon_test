<?php
class L extends \Phalcon\Mvc\Application
{
    private $_di;
    private $_config;

    /**
     * 构建网站服务入口(Constructor)
     * @param $di
     */
    public function __construct(\Phalcon\DiInterface $di)
    {
        $this->_di = $di;
        $this->init();
    }

    protected function init()
    {
        //每次增加模块需要在数组配置 模块以及路径
        $modules = array(
            'home' => array('className' => 'app\home\module',
                'path' => APP_PATH . '/home/Module.php'),
            'api' => array('className' => 'app\api\module',
                'path' => APP_PATH . '/api/Module.php'),

        );
        // 注册服务模块
        $this->registerModules($modules);
        // 注册本类为应用服务 Registers a service in the services container 同setShared
        $this->_di->set('app', $this);
        // 调用父类注册入口
        parent::setDI($this->_di);
    }
}
