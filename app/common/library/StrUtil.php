<?php
namespace app\library;

class StrUtil
{

    /**
     * 去掉指定的html标签
     * @static
     * @access public
     * @param array $tags html html标记
     * @param string $str  字符串
     * @return string
     * @assert (array('a', 'p'), "<p><a>abc</a></p>") == "abc"
     */
    public static function stripTags($tags, $str)
    {
        $tags = (array)$tags;
        foreach ($tags as $tag) {
            $p[] = "/(<(?:\/" . $tag . "|" . $tag . ")[^>]*>)/i";
        }
        $return_str = preg_replace($p, "", $str);
        return $return_str;
    }
    /**
     * 计算utf8中文字符长度
     * @static
     */
    public static function strlen_utf8($str)
    {
        $i = 0;
        $count = 0;
        $len = strlen($str);
        while ($i < $len) {
            $chr = ord($str[$i]);
            $count++;
            $i++;
            if ($i >= $len) {
                break;
            }
            if ($chr & 0x80) {
                $chr <<= 1;
                while ($chr & 0x80) {
                    $i++;
                    $chr <<= 1;
                }
            }
        }
        return $count;
    }
    /**
     * 计算gbk中文字符长度
     * @static
     */
    public static function strlen_gbk($str)
    {
        $len=strlen($str);
        $i=0;
        for ($j=0;$j<$len;$j++) {
            if (preg_match("/^[".chr(0xa1)."-".chr(0xff)."]+$/", $str[$j])) {
                $j+=1;
            }
            $i++;
        }
        return $i;
    }
    /**
     * 截取中英混编UTF8字符串
     * @static
     */
    public static function utf8SubStr($str, $from, $len, $prefix='')
    {
        $result=preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.

            '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s',

            '$1', $str);
        if (self::strlen_utf8($str)>$len) {
            $result.=$prefix;
        }
        return $result;
    }
    /**
     * 截取UTF8字符串
     */
    public static function cnSubStr($string, $start = 0, $sublen=12, $ellipsis='', $code = 'UTF-8')
    {
        if ($code == 'UTF-8') {
            $tmpstr = '';
            $i = $start;
            $n = 0;
            $str_length = strlen($string);//字符串的字节数
            while (($n+0.5<$sublen) and ($i<$str_length)) {
                $temp_str=substr($string, $i, 1);
                $ascnum=Ord($temp_str);    //得到字符串中第$i位字符的ascii码
                if ($ascnum>=224) {        //如果ASCII位高与224，
                    $tmpstr .= substr($string, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
                    $i=$i+3;            //实际Byte计为3
                    $n++;                //字串长度计1
                } elseif ($ascnum>=192) { //如果ASCII位高与192，
                    $tmpstr .= substr($string, $i, 3); //根据UTF-8编码规范，将2个连续的字符计为单个字符
                    $i=$i+3;            //实际Byte计为2
                    $n++;                //字串长度计1
                } else {                    //其他情况下，包括小写字母和半角标点符号，
                    $tmpstr .= substr($string, $i, 1);
                    $i=$i+1;            //实际的Byte数计1个
                    $n=$n+0.5;            //小写字母和半角标点等与半个高位字符宽...
                }
            }
            if (strlen($tmpstr)<$str_length) {
                $tmpstr .= $ellipsis;//超过长度时在尾处加上省略号
            }
            return $tmpstr;
        } else {
            $strlen = strlen($string);
            if ($sublen != 0) {
                $sublen = $sublen*2;
            } else {
                $sublen = $strlen;
            }
            $tmpstr = '';
            for ($i=0; $i<$strlen; $i++) {
                if ($i>=$start && $i<($start+$sublen)) {
                    if (ord(substr($string, $i, 1))>129) {
                        $tmpstr.= substr($string, $i, 2);
                    } else {
                        $tmpstr.= substr($string, $i, 1);
                    }
                }
                if (ord(substr($string, $i, 1))>129) {
                    $i++;
                }
            }
            if (strlen($tmpstr)<$strlen) {
                $tmpstr.= $ellipsis;
            }
            return $tmpstr;
        }
    }

    //UNICODE编码
    public static function unicodeEncode($name)
    {
        $name = iconv('UTF-8', 'UCS-2', $name);
        $len = strlen($name);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2) {
            $c = $name[$i];
            $c2 = $name[$i + 1];
            if (ord($c) > 0) {   //两个字节的文字
                $str .= '%u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
            } else {
                $str .= $c2;
            }
        }
        return $str;
    }


    //将UNICODE编码后的内容进行解码
    public static function unicodeDecode($name)
    {
        //转换编码，将Unicode编码转换成可以浏览的utf-8编码
        $pattern = '/([\w]+)|(\%u([\w]{4}))/i';
        preg_match_all($pattern, $name, $matches);
        if (!empty($matches)) {
            $name = '';
            for ($j = 0; $j < count($matches[0]); $j++) {
                $str = $matches[0][$j];
                if (strpos($str, '%u') === 0) {
                    $code = base_convert(substr($str, 2, 2), 16, 10);
                    $code2 = base_convert(substr($str, 4), 16, 10);
                    $c = chr($code).chr($code2);
                    $c = iconv('UCS-2', 'UTF-8', $c);
                    $name .= $c;
                } else {
                    $name .= $str;
                }
            }
        }
        return $name;
    }
}
