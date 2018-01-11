<?php
// 验证码 缩略图类
namespace app\library;

class ThumtPic
{
    protected $im;
    protected $img_width;
    protected $img_height;
    protected $img_type;

    // 生成随机数
    public static function randStr($n = 4)
    {
        if ($n <= 0) {
            return '';
        }

        $str = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ0123456789';
        $str = substr(str_shuffle($str), 0, $n);

        return $str;
    }


    // 生成验证码
    public static function chkCode($type=1, $length=4, $pixel=0, $line=0, $sess_name = "verify")
    {
        @session_start();
        //创建画布
        $width = 80;
        $height = 28;
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        //用填充矩形填充画布
        imagefilledrectangle($image, 1, 1, $width - 2, $height - 2, $white);
        $chars = self::randStr();

        //$fontfiles = array ("MSYH.TTF", "MSYHBD.TTF", "SIMLI.TTF", "SIMSUN.TTC", "SIMYOU.TTF", "STZHONGS.TTF" );
        $fontfiles = array("SIMYOU.TTF" );
        $s = '';
        //由于字体文件比较大，就只保留一个字体，如果有需要的同学可以自己添加字体，字体在你的电脑中的fonts文件夹里有，直接运行输入fonts就能看到相应字体
        for ($i = 0; $i < $length; $i ++) {
            $size = mt_rand(14, 18);
            $angle = mt_rand(- 15, 15);
            $x = 5 + $i * $size;
            $y = mt_rand(20, 26);
            $fontfile = "/data1/test/app/view/" . $fontfiles [mt_rand(0, count($fontfiles) - 1)];
            //echo $fontfile;die;
            $color = imagecolorallocate($image, mt_rand(50, 90), mt_rand(80, 200), mt_rand(90, 180));
            $text = substr($chars, $i, 1);
            $s .=$text;
            imagettftext($image, $size, $angle, $x, $y, $color, $fontfile, $text);
        }
        $_SESSION [$sess_name] = $s;
        if ($pixel) {
            for ($i = 0; $i < 50; $i ++) {
                imagesetpixel($image, mt_rand(0, $width - 1), mt_rand(0, $height - 1), $black);
            }
        }
        if ($line) {
            for ($i = 1; $i < $line; $i ++) {
                $color = imagecolorallocate($image, mt_rand(50, 90), mt_rand(80, 200), mt_rand(90, 180));
                imageline($image, mt_rand(0, $width - 1), mt_rand(0, $height - 1), mt_rand(0, $width - 1), mt_rand(0, $height - 1), $color);
            }
        }
        header("content-type:image/gif");
        imagegif($image);
        imagedestroy($image);
    }


    /**
     * @param type $len 字符个数
     * @param type $numberOnly 是否只显示数字
     * @return 输出验证码图像
     */
    public static function makeCode($len=4, $numberOnly=false)
    {
        session_start();

        $randCode = '';
        if ($numberOnly) {
            $chars = '123456789';
        } else {
            $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ23456789';
        }
        for ($i = 0; $i < $len; $i++) {
            $randCode .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        $_SESSION['code'] = strtoupper($randCode);


        $imgWidth = $len * 14;

        $img = imagecreate($imgWidth, 22);
        imagecolorallocate($img, 255, 255, 255); //背景颜色
        $pixColor = imagecolorallocate($img, mt_rand(30, 180), mt_rand(10, 100), mt_rand(40, 250));

        for ($i = 0; $i < $len; $i++) {
            $x = $i * 13 + mt_rand(0, 4) - 2;
            $y = mt_rand(0, 3);
            $text_color = imagecolorallocate($img, mt_rand(30, 180), mt_rand(10, 100), mt_rand(40, 250));
            imagechar($img, 5, $x + 5, $y + 3, $randCode[$i], $text_color);
        }


        for ($j = 0; $j < $imgWidth ; $j++) {
            $x = mt_rand(0, $imgWidth);
            $y = mt_rand(0, 22);
            imagesetpixel($img, $x, $y, $pixColor); //画一个单一像素
        }

        header('Content-Type: image/png');
        imagepng($img);
        imagedestroy($img);
    }

    public static function makeThumb($ori, $w=200, $h=200)
    {
        // 判断原图大小,如果原图比缩略还小,不必处理.

        // 读出大图当画布
        $info = self::getInfo($ori);
        if ($info['func'] === false) {
            return false;
        }

        $createfunc = 'imagecreatefrom' . $info['func']; // 分析出读取大图所用的函数名.
        $src = $createfunc($ori);

        // 创建小画布,并把背景做成灰色
        $small = imagecreatetruecolor($w, $h);
        $gray = imagecolorallocate($small, 255, 255, 255);
        imagefill($small, 0, 0, $gray);

        // 复制大图到小图
        $scale = min($w/$info['width'], $h/$info['height']); // 以更小的缩小比例为准,才能装下

        // 根据比例,算最终复制过去的块的大小.
        $realw = $info['width'] * $scale;
        $realh = $info['height'] * $scale;

        // 生成小图
        /*
        bool imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
        */

        // 计算留白
        $lw = round(($w - $realw)/2); // 计算左侧留的宽度
        $lh = round(($h - $realh)/2); // 计算上部留的高度

        imagecopyresampled($small, $src, $lw, $lh, 0, 0, $realw, $realh, $info['width'], $info['height']);

        /*
        header('content-type: image/jpeg');
        imagejpeg($small);
        */

        // 计算小图片的存储路径
        $thumburl = str_replace('.', '_thumb.', $ori);
        $imagefunc = 'image' . $info['func'];

        if ($imagefunc($small, $thumburl)) {
            return str_replace(ROOT, '', $thumburl);
        } else {
            return false;
        }
    }

    public static function getInfo($ori)
    {
        $arr = getimagesize($ori);

        // 如果原始图片分析不出来,直接false
        if ($arr === false) {
            return false;
        }

        $info = array();

        $info['width'] = $arr[0];
        $info['height'] = $arr[1];

        switch ($arr[2]) {
            case 1:
                $info['func'] = 'gif';
                break;

            case 2:
                $info['func'] = 'jpeg';
                break;

            case 3:
                $info['func'] = 'png';
                break;

            case 6:
                $info['func'] = 'wbmp';
                break;

            default:
                $info['func'] = false;

        }

        return $info;
    }
}
