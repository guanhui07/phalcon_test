<?php

namespace app\library;

class Dir
{

    // 遍历的目录数组
    public $mFolders = array();
    // 遍历的文件数组
    public $mFiles = array();
    public $mDateTime = "Y-m-d H-i-s";
    public $mTimeOffset = 8;
    public $aa = 0;

    /**
     * 创建多重目录
     * @access public
     * @param string $dir  目录的绝对路径
     * @param int $mode  八进制，要确保正确操作，需要给 mode 前面加上 0
     * @return bool
     */
    public function mkDirs($pathname, $mode=0775)
    {
        $pathname = str_replace("\\", "/", $pathname);
        if (file_exists($pathname)) {
            return true;
        }
        return mkdir($pathname, $mode, true);
    }

    /**
     * 删除多重目录及文件
     * @param string $dir 目录的绝对路径
     * @param bool $rmself 如果$rmself=false,则不删除本目录,否则删除本目录,默认$rmself=true
     * @return bool
     * @todo
     */
    public function delDirs($dir, $rmself = true)
    {
        //如果给定路径末尾包含"/",先将其删除
        if (substr($dir, -1) == "/") {
            $dir = substr($dir, 0, -1);
        }
        //如给出的目录不存在或者不是一个有效的目录，则返回
        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        } elseif (!is_readable($dir)) {
            return false;
        } else {
            $dirHandle = opendir($dir);
            //当目录不空时，删除目录里的文件
            while (false !== ($entry = readdir($dirHandle))) {
                if ($entry != "." && $entry != "..") { //过滤掉表示当前目录的"."和表示父目录的".."
                    $path = $dir . "/" . $entry;
                    if (is_dir($path)) {
                        $this->delDirs($path); //为子目录，则递归调用本函数
                    } else {
                        unlink($path); //为文件直接删除
                    }
                }
            }
            closedir($dirHandle);
            if ($rmself) {
                if (!rmdir($dir)) {
                    return false;
                }
                return true;
            }
            return true;
        }
    }

    /**
     * 删除文件,删除失败返回false,否则返回true
     * @access public
     * @param string $file 文件路径
     * @return bool
     */
    public function delFile($file)
    {
        if (!is_file($file)) {
            return false;
        }
        @unlink($file);
        return true;
    }

    /**
     * 浏览目录
     * @access public
     * @param string $dir
     * @return array
     */
    public function getFolders($dir)
    {
        $this->mFolders = array();
        //如果给定路径末尾包含"/",先将其删除
        if (substr($dir, -1) == "/") {
            $dir = substr($dir, 0, -1);
        }
        //如给出的目录不存在或者不是一个有效的目录，则返回
        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        }
        //打开目录，
        $dirs = opendir($dir);
        //把目录下的目录信息写入数组
        $i = 0;
        while (false !== ($entry = readdir($dirs))) {
            //过滤掉表示当前目录的"."和表示父目录的".."
            if ($entry != "." && $entry != "..") {
                $path = $dir . "/" . $entry;
                //为子目录，则采集信息
                if (is_dir($path)) {
                    $filetime = @filemtime($path);
                    $filetime = @date($this->mDateTime, $filetime + 3600 * $this->mTimeOffset);
                    // 目录名
                    $this->mFolders[$i]['name'] = $entry;
                    // 目录最后修改时间
                    $this->mFolders[$i]['filetime'] = $filetime;
                    // 目录大小,不计,设为0
                    $this->mFolders[$i]['filesize'] = 0;
                    $i++;
                }
            }
        }
        return $this->mFolders;
    }

    /**
     * 浏览文件
     * @access public
     * @param string $dir
     * @return array
     */
    public function getFiles($dir)
    {
        $this->mFiles = array();
        //如果给定路径末尾包含"/",先将其删除
        if (substr($dir, -1) == "/") {
            $dir = substr($dir, 0, -1);
        }
        //如给出的目录不存在或者不是一个有效的目录，则返回
        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        }
        //打开目录，
        $dirs = opendir($dir);
        //把目录下的文件信息写入数组
        $i = 0;
        while (false !== ($entry = readdir($dirs))) {
            //过滤掉表示当前目录的"."和表示父目录的".."
            if ($entry != "." && $entry != "..") {
                $path = $dir . "/" . $entry;
                //为子目录，则采集信息
                if (is_file($path)) {
                    $filetime = @filemtime($path);
                    $filetime = @date($this->mDateTime, $filetime + 3600 * $this->mTimeOffset);
                    $filesize = $this->getFileSize($path);
                    // 文件名
                    $this->mFiles[$i]['name'] = $entry;
                    // 文件最后修改时间
                    $this->mFiles[$i]['filetime'] = $filetime;
                    // 文件的大小
                    $this->mFiles[$i]['filesize'] = $filesize;
                    $i++;
                }
            }
        }
        return $this->mFiles;
    }

    /**
     * 获取目录大小
     * @access public
     * @param string $dir
     * @return int
     */
    public function getFolderSize($dir)
    {
        $handle = opendir($dir);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($dir . "/" . $file)) {
                        $this->getFolderSize($dir . "/" . $file);
                    } else {
                        $this->aa+=filesize($dir . "/" . $file);
                    }
                }
            }
        }
        return $this->aa;
    }

    /**
     * 获取文件的大小:字节,KB,MB,GB
     * @access public
     * @param string $file
     * @return string
     */
    public function getFileSize($file)
    {
        if (!is_file($file)) {
            return 0;
        }
        $f1 = $f2 = "";
        $filesize = @filesize("$file");
        // 大于1GB以上的文件
        if ($filesize > 1073741824) {
            // 大于1MB以上的文件
        } elseif ($filesize > 1048576) {
            $filesize = $filesize / 1048576;
            list($f1, $f2) = explode(".", $filesize);
            $filesize = $f1 . "." . substr($f2, 0, 2) . "MB";
            // 大于1KB小于1MB的文件
        } elseif ($filesize > 1024) {
            $filesize = $filesize / 1024;
            list($f1, $f2) = explode(".", $filesize);
            $filesize = $f1 . "." . substr($f2, 0, 2) . "KB";
            // 小于1KB的文件
        } else {
            $filesize = $filesize . "字节";
        }
        return $filesize;
    }
}
