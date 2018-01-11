<?php
namespace app\library;

class SimpleLog
{
    const LEVEL_OFF            = 0;
    const LEVEL_ERROR        = 1;
    const LEVEL_WARN        = 2;
    const LEVEL_NOTICE        = 3;
    const LEVEL_DEBUG        = 9;

    public static function error($data)
    {
        if (LOG_LEVEL >= self::LEVEL_ERROR) {
            if (self::write($data, 'ERROR')) {
                return true;
            }
        }
        return false;
    }

    public static function warn($data)
    {
        if (LOG_LEVEL >= self::LEVEL_WARN) {
            if (self::write($data, 'WARN')) {
                return true;
            }
        }
        return false;
    }

    public static function notice($data)
    {
        if (LOG_LEVEL >= self::LEVEL_NOTICE) {
            if (self::write($data, 'NOTICE')) {
                return true;
            }
        }
        return false;
    }

    public static function debug($data)
    {
        if (LOG_LEVEL >= self::LEVEL_DEBUG) {
            if (self::write($data, 'DEBUG')) {
                return true;
            }
        }
        return false;
    }

    private static function write($data, $level)
    {
        $logdir = LOG_PATH; //日志文件目录
        if (! is_dir($logdir)) {
            echo "Error: log dir is not exists!\n";
            return false;
        } else {
            $filename = $logdir .'/'. date('Y-m-d') . '.log'; //日志文件路径
            $content = '[' . date('H:i:s') . '] [' . $level . '] ' . $data . "\r\n"; //日志内容
            $backtrace = debug_backtrace();
            if (isset($backtrace[1])) {
                $traces = '@File=' . $backtrace[1]['file'] . ',Line=' . $backtrace[1]['line'] . "\r\n\r\n";
                $content .= $traces;
            }

            $encode = mb_detect_encoding($content, array("ASCII",'UTF-8','GB2312',"GBK", 'BIG5'));
            if ($encode != 'UTF-8') {
                $content = iconv($encode, 'UTF-8', $content);
            }
            /*
            if(!is_file($filename)){
                $content .= "\xEF\xBB\xBF";
            }
            */
            return @file_put_contents($filename, $content, FILE_APPEND);
        }
    }
}
