<?php
namespace app\library;

class Helpers
{
    public static function toGetUrl($arr = ['home/index'])
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
        return $url;
    }
}
