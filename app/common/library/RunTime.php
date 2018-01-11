<?php
namespace app\library;

class Runtime
{
    public $StartTime = 0;
    public $StopTime = 0;

    public function get_microtime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    public function start()
    {
        $this->StartTime = $this->get_microtime();
    }

    public function stop()
    {
        $this->StopTime = $this->get_microtime();
    }

    public function spent()
    {
        return round(($this->StopTime - $this->StartTime) * 1000, 1);
    }
}
//
//例子
//$runtime= new runtime;
//$runtime->start();
//
////你的代码开始
//
//$a = 0;
//for($i=0; $i<1000000; $i++)
//{
//$a += $i;
//}
//
////你的代码结束
//
//$runtime->stop();
//echo "页面执行时间: ".$runtime->spent()." 毫秒";
