<?php
namespace app\library;

class fileCache
{
    public $cache_file;
    public $cache_time;
    public function __construct($cache_file='_index.htm', $cache_time=1)
    {
        $this->cache_file        = $cache_file;
        $this->cache_time    = $cache_time;
    }
    /**
     * 开启缓存
     */
    public function cacheStart($update=false)
    {
        if (!$update) {
            if ($this->cacheIsActive()) {
                include($this->cache_file);
                exit;
            }
        }
        ob_start();
    }
    /**
     * 结束缓存
     */
    public function cacheEnd($output=true)
    {
        $this->makeCache();
        if ($output) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }
    }
    /**
     * 缓存是否有效
     */
    public function cacheIsActive()
    {
        if ($this->cacheIsExist()) {
            if (time() - $this->lastModified() < $this->cache_time) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * 创建缓存
     */
    public function makeCache()
    {
        $content = $this->getCacheContent();
        if (empty($content)) {
            return false;
        }
        if ($this->writeFile($content)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 缓存是否存在
     */
    public function cacheIsExist()
    {
        if (file_exists($this->cache_file)) {
            return true;
        } else {
            return false;
        }
    }
    public function lastModified()
    {
        return @filemtime($this->cache_file);
    }
    /**
     * 获取缓存内容
     */
    public function getCacheContent()
    {
        $contents = ob_get_contents();
        return $contents;
    }
    /**
     * 写缓存
     */

    public function writeFile($content, $mode='w')
    {
        $this->mkDirs($this->cache_file);
        if (!$fp = @fopen($this->cache_file, $mode)) {
            $this->reportError($this->cache_file." 目录或者文件属性无法写入.");
            return false;
        } else {
            @fwrite($fp, $content);
            @fclose($fp);
            @umask($oldmask);
            return true;
        }
    }
    /**
     * 递归创建级联目录
     */
    public function mkDirs()
    {
        $dir    = @explode("/", $this->cache_file);
        $num    = @count($dir)-1;
        $tmp    = '';
        for ($i=0; $i<$num; $i++) {
            $tmp    .= $dir[$i];
            if (!file_exists($tmp)) {
                @mkdir($tmp);
                @chmod($tmp, 0777);
            }
            $tmp    .= '/';
        }
    }

    public function clearCache()
    {
        if (!@unlink($this->cache_file)) {
            $this->reportError('不能清除缓存！');
            return false;
        } else {
            return true;
        }
    }

    public function reportError($message=null)
    {
        if ($message!=null) {
            trigger_error($message);
        }
    }
}
