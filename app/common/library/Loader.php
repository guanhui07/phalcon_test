<?php

namespace app\library;

class Loader
{
    public static function autoload($className)
    {
        $file = str_replace('\\', '/', $className).'.php';
        if (file_exists($file)) {
            include $file;
        } else {
            //todo日志
        }
    }
}
