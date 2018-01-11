<?php

namespace app\controllers;

class ControllerCommon extends \Phalcon\Mvc\Controller {

    public static $_instance = array();
    
    /*
     * 初始化
     */
    protected function initialize() {
        // parent::initialize();
        define('MODULE_NAME', $this->router->getModuleName());
        define('CONTROLLER_NAME', $this->router->getControllerName());
        define('ACTION_NAME', $this->router->getActionName());
        define('BASE_URI',  $this->config->site->base_uri);
        define('IS_MSIE', preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT']) ? true : false);
        //默认js,css,image
        $static_url = $this->config->site->static_uri;
        $this->view->setVars(array(
            'base_url'              => $this->config->site->url,	//http://localhost/
        	'base_uri'              => $this->config->site->base_uri, ///rmyh/
            'module_name'           => MODULE_NAME,
            'controller_name'       => CONTROLLER_NAME,
        	'controller_path'       => $this->config->site->base_uri . CONTROLLER_NAME . '/',
            'action_name'           => ACTION_NAME,
            'common_static'         => $static_url . 'common/',
            'common_css_path'       => $static_url . 'common/css/',
            'common_js_path'        => $static_url . 'common/js/',
            'common_images_path'	=> $static_url . 'common/images/',
            'is_ie'                 => (defined('IS_MSIE') && IS_MSIE) ? true : false,
            'config'                => $this->config,
            'site_name'				=> $this->config->site->name,
            'http_referer'          => $this->request->getHTTPReferer(),
        ));
    }
    
    /**
     * 先onConstruct()，后initialize()
     */
    public function onConstruct() {
        $this->session->start();
    }
    
	public static function instance() {
        $classname = get_called_class();
        if (empty(self::$_instance[$classname])) {
            self::$_instance[$classname] = new $classname;
        }
        return self::$_instance[$classname];
    }
    
    /* 当指定m、c、a等参数时，将进行重新调度 */
    protected function reRouter() {
        $module = $this->dispatcher->getModuleName();
        $controller = $this->dispatcher->getControllerName();
        $action = $this->dispatcher->getActionName();
        $m = $this->request->get('m', null, $module);
        $c = $this->request->get('c', null, $controller);
        $a = $this->request->get('a', null, $action);
        if(!(isset($m{0}) && isset($c{0}) && isset($a{0}))) {
            return ;
        }
        if(strtolower("$module/$controller/$action") != strtolower("$m/$c/$a")) {
            $this->forward("$m/$c/$a");
        }
   }
    
    /* 404页面 */
    public function notFoundAction() {
        // 发送一个HTTP 404 响应的header
        $this->response->setStatusCode(404, "Not Found");
        $this->response->setContent("<html><body><h1>HTTP 404 Not Found</h1><h2>抱歉，页面没有找到！</h2><h3>Sorry,The page not found...</h3></body></html>");
        $this->response->send();
    }
    
    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    public function success($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }
    
    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    public function error($message = '', $jumpUrl = '', $ajax = false) {
        $this->dispatchJump($message, 0, $jumpUrl, $ajax);
    }
    
    /* 异常信息输出 */
    public function exception($exception) {
        is_object($exception) && ($error_title = $exception->getMessage());
        $error_title = $error_title ? $error_title : '错误信息';
        if(\Phalcon\DI::getDefault()->get('request')->isAjax()) {
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode(array('status' => 0, 'info' => (string)$exception)));
        }
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
        echo '<head><meta http-equiv="content-type" content="text/html; charset=utf-8" />';
        echo '<title>' . $error_title . '</title>';
        echo '<style type="text/css">' . "\n" . '/* <![CDATA[ */' . "\n";
        echo '* { margin: 0; padding: 0; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #536482; background: #E4EDF0; font-size: 62.5%; margin: 0; } ';
        echo 'a:link, a:active, a:visited { color: #006699; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } ';
        echo '#wrap { padding: 0 20px 15px 20px; min-width: 615px; } #page-header { text-align: right; height: 40px; }';
        echo '.panel { margin: 4px 0; background-color: #FFFFFF; border: solid 1px  #A9B8C2; } ';
        echo '#errorpage #page-header a { font-weight: bold; line-height: 6em; } #errorpage #content { padding: 10px; } #errorpage #content h1 { line-height: 1.2em; margin-bottom: 0; color: #DF075C; } ';
        echo '#errorpage #content div { margin-top: 20px; margin-bottom: 5px; border-bottom: 1px solid #CCCCCC; padding-bottom: 5px; color: #333333; font: bold 1.2em "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%; text-align: left; } ';
        echo "\n" . '/* ]]> */' . "\n";
        echo '</style></head>';
        echo '<body id="errorpage">';
        echo '<div id="wrap"><div id="page-header"></div><div id="acp"><div class="panel"><div id="content">';
        echo '<h1>' . $error_title . '</h1>';
        echo '<div><pre>' . $exception . '</pre></div>';
        echo '</div></div></div></div>';
        echo '</body></html>';
        exit;
    }
    
    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    private function dispatchJump($message, $status = 1, $jumpUrl = '', $ajax = false) {
		
        if(true === $ajax || $this->request->isAjax()) {    // AJAX提交
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
        is_int($ajax) && $this->view->setVar('waitSecond', $ajax);
        empty($jumpUrl) || $this->view->setVar('jumpUrl', $jumpUrl);
        $this->view->setVar('msgTitle', $status ? '操作成功' : '操作失败');
        $this->view->setVar('status', $status);
        $this->view->setViewsDir(APP_PATH . '/common/views/');
        $this->view->start();
        if($status) {
            $this->view->setVar('message', $message); // 提示信息
            is_int($ajax) || $this->view->setVar('waitSecond', 1);
            empty($jumpUrl) && $this->view->setVar("jumpUrl", $_SERVER["HTTP_REFERER"]);
            $this->view->render('default', 'dispatch_jump_success');
        } else {
            $this->view->setVar('error', $message); // 提示信息
            is_int($ajax) || $this->view->setVar('waitSecond', 3);
            empty($jumpUrl) && $this->view->setVar("jumpUrl", "javascript:history.back(-1);");
            $this->view->render('default', 'dispatch_jump_error');
        }
        $this->view->finish();
        echo $this->view->getContent();
        exit;
    }

    // 重定向（推荐）
    protected function redirect($location, $externalRedirect = false, $statusCode = 200) {
        //http://szcg.com/index.php?_url=api/login/submit
        return true;
        if(!$externalRedirect) {
            $arr = explode('/', $location);
            $size = sizeof($arr);
            ($size == 1) && ($location = MODULE_NAME  . '/' .  CONTROLLER_NAME . '/' . $location );
            ($size == 2) && ($location = MODULE_NAME . '/' . $location);
        }
        $this->response->redirect($location, $externalRedirect, $statusCode)->sendHeaders();
    }
    
    // 内部转向（不推荐）
    protected function forward($forward) {
        if(!is_array($forward)) {
            $arr = explode('/', $forward);
            $size = sizeof($arr);
            ($size == 1) && ($arr = array('module' => $this->router->getModuleName(), 'controller' => $this->router->getControllerName(), 'action' => $arr[0]));
            ($size == 2) && ($arr = array('module' => $this->router->getModuleName(), 'controller' => $arr[0], 'action' => $arr[1]));
            ($size == 3) && ($arr = array('module' => $arr[0], 'controller' => $arr[1], 'action' => $arr[2]));
            ($size > 3) && ($arr = array('module' => $arr[0], 'controller' => $arr[1], 'action' => $arr[2], 'params' => array_slice($arr, 3)));
            $forward = $arr;
        }
        $this->dispatcher->forward($forward);
    }

    // 输出AJAX结果到页面
    protected function ajaxReturn($data, $type = 'json', $json_option = 0) {
        switch (strtoupper($type)){
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            case 'JSON' :
            default     :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
        }
    }

    /**
     *
     */
    protected function get($key, $type = null, $default = null)
    {
        $value = $this->request->get($key, 'trim', '');
        switch ($type){
            case 'int':
                $value = intval($value);
                $value = empty($value) ? $default : $value;
                break;
            default:
                $value = empty($value) ? $default : $value;
                break;
        }
        return $value;
    }

    /**
     *
     */
    protected function post($key, $type = null, $default = null)
    {
        $value = $this->request->getPost($key, 'trim', '');
        switch ($type) {
            case 'int':
                $value = intval($value);
                $value = empty($value) ? $default : $value;
                break;
            default:
                $value = empty($value) ? $default : $value;
                break;
        }
        return $value;
    }
}