<?php

namespace app\library;

class Controller
{
    public static $className = '';
    public function __construct()
    {
        self::$className = get_called_class();
    }

    public static function getRedis($k)
    {
        if (!isset(self::$objects[$k])) {
            self::$objects[$k]= new Redis();
        }
        return self::$objects[$k];
    }

    protected static function render($view, $arr=[])
    {
        //        foreach($arr as $k=>$v){
//            $$k=$v;
//        }
        extract($arr);
        $name = substr(self::$className, strrpos(self::$className, "\\")+1);
        $name = strtolower($name);
        $file = ROOT.'app/view/'."$name/".$view.'.php';

        if (file_exists($file)) {
            include $file;
        } else {
            //echo $file;die;//todo日志
        }
    }

    /**
     * 从缓存取得数据
     * @param unknown $ckey
     * @param unknown $func
     * @param unknown $ttl
     * @return mixed
     */
//    public static function getContentsFromCache($ckey, $func, $ttl = 3600)
//    {
//        $redis = self::getRedis();
//        $jsonstr = $redis->get($ckey);
//
//        if(empty($jsonstr))
//        {
//            $json = $func();//$json 为一个数组
//            $json !== false && $redis->setex($ckey, $ttl, @json_encode($json));
//        }else{
//            $json = json_decode($jsonstr,true);
//        }
//        return $json;
//    }

    public function redirect($arr = ['home/index'], $bool=false)
    { //['home/index','admin1'=>1]
        $url = APPURL.'?r='.$arr[0];

        if (count($arr)>1) {
            foreach ($arr as $k=>$v) {
                if (is_int($k)) {
                    continue;
                }
                $url.='&'.$k.'='.$v;
            }
        }
        if ($bool) {
            //header('HTTP/1.1 301 Moved Permanently');//或发出301头部
            header("Location:".$url, true, 301);//301
        }
        header("Location:".$url);//302
    }
}
