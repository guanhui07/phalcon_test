<?php
namespace app\library;

class Upload
{
    protected $allowExt = array('jpg','jpeg','gif','png','bmp');
    protected $allowSize = 1; // 最大上传大小,单位为M

    protected $errno = 0;
    protected $error = array(
        0=>'上传完成',
        1=>'文件超出upload_max_filesize',
        2=>'文件超出表单中 MAX_FILE_SIZE 选项指定的值',
        3=>'文件只有部分被上传',
        4=>'没有文件被上传',
        6=>'找不到临时目录',
        7=>'文件定入失败',
        8=>'文件大小超出配置文件的限制',
        9=>'不允许的文件类型',
        10=>'创建目录失败',
        11=>'未知错误,反思中'
    );

    // 获取后缀
    protected function getExt($file)
    {
        $ext = strtolower(strrchr($file, '.'));

        return $ext;
    }

    // 检验后缀
    protected function checkExt($ext)
    {
        return in_array(ltrim($ext, '.'), $this->allowExt);
    }

    // 检验大小
    protected function checkSize($size)
    {
        return $size <= $this->allowSize * 1000 * 1000;
    }

    // 按日期生成目录
    protected function mk_dir()
    {
        $dir = date('Y/m/d', time());
        $dir = ROOT . 'app/data/images/' . $dir;

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return false;
            }
        }

        return $dir;
    }

    // 生成随机文件名
    protected function randName($n = 6)
    {
        if ($n <= 0) {
            return '';
        }

        $str = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ0123456789';
        $str = substr(str_shuffle($str), 0, $n);

        return $str;
    }

    public function up($name)
    {
        // $_FILES里面有没有$name指定的单元
        if (!isset($_FILES[$name])) {
            return false;
        }

        $f = $_FILES[$name];

        // 判断错误码,是否上传成功
        if (($this->errno = $f['error']) > 0) {
            return false;
        }

        // 判断大小
        if (!$this->checkSize($f['size'])) {
            $this->errno = 8;
            return false;
        }

        // 判断类型
        $ext = $this->getExt($f['name']);
        if (!$this->checkExt($ext)) {
            $this->errno = 9;
            return false;
        }

        // 上传,返回路径
        $path = $this->mk_dir();
        if (!$path) {
            $this->errno = 10;
            return false;
        }

        $filename = $this->randName(12).date('_YmdHis', time());
        $path = $path . '/' . $filename . $ext;

        if (!move_uploaded_file($f['tmp_name'], $path)) {
            $this->errno = 11;
            return false;
        }

        $path = str_replace(ROOT, '', $path);
        return $path;
    }

    // 获取错误的接口
    public function getErr()
    {
        return $this->error[$this->errno];
    }

    // 设置允许的后缀
    public function setExt($arr)
    {
        $this->allowExt = $arr;
    }

    // 设置最大上传值
    public function setSize($num=2)
    {
        $this->allowSize = $num;
    }
}

/*
$upload = new Upload();

if($path = $upload->up('pic')) {
    echo $path,'<br />';
    echo '上传成功';
} else {
    echo $upload->getErr();
}

*/
