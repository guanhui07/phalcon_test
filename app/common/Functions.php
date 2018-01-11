<?php

function debug($v)
{
    if (is_string($v) || is_int($v)) {
        echo $v;
    } elseif (is_bool($v) || is_resource($v) || is_null($v) || is_object($v)) {
        var_dump($v);
    } elseif (is_array($v)) {
        echo "<pre>";
        print_r($v);
        echo "</pre>";
    } else {
        var_dump($v);
    }
    die;
}

function apiReturnSuccess($outputData = array())
{
    $data['code'] = 200;
    $data['message'] = 'success';
    $data['data'] = $outputData;
    $urldecode_flag = false;
    return apiReturnOutput($data, $urldecode_flag);
}

function curlGetContents($url, $timeout = 3)
{
    $curlHandle = curl_init();
    curl_setopt($curlHandle, CURLOPT_URL, $url);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0); // 让CURL支持HTTPS访问
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($curlHandle);
    curl_close($curlHandle);
    return $result;
}

function curlPostContents($url, $params, $use_http_build_query = true)
{
    if ($use_http_build_query) {
        $params = http_build_query($params);
    }

    $curlHandle = curl_init();
    curl_setopt($curlHandle, CURLOPT_POST, 1);
    curl_setopt($curlHandle, CURLOPT_URL, $url);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0); // 让CURL支持HTTPS访问
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
    curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($curlHandle);
    curl_close($curlHandle);
    return $result;
}

/**
 * 将当前字符串从 BeginString 向右截取
 *
 * @param string $BeginString
 * @param boolean $self
 * @return String
 */
function rightString($String, $BeginString, $self = false)
{
    $Start = strpos($String, $BeginString);
    if ($Start === false) {
        return null;
    }
    if (!$self) {
        $Start += strlen($BeginString);
    }
    $newString = substr($String, $Start);
    return $newString;
}


/**
 * 将当前字符串从 BeginString 向左截取
 *
 * @param string $BeginString
 * @param boolean $self
 * @return String
 */
function leftString($BeginString, $String, $self = false)
{
    $Start = strpos($String, $BeginString);
    if ($Start === false) {
        return null;
    }
    if ($self) {
        $Start += strlen($BeginString);
    }
    $newString = substr($String, 0, $Start);
    return $newString;
}

function subString($String, $BeginString, $EndString = null)
{
    $Start = strpos($String, $BeginString);
    if ($Start === false) {
        return null;
    }
    $Start += strlen($BeginString);
    $String = substr($String, $Start);
    if (!$EndString) {
        return $String;
    }
    $End = strpos($String, $EndString);
    if ($End == false) {
        return null;
    }
    return substr($String, 0, $End);
}

function _mkdir($dir)
{
    if (file_exists($dir)) {
        return true;
    }
    $u = umask(0);
    $r = @mkdir($dir, 0755);
    umask($u);
    return $r;
}

function _mkdirs($dir, $rootpath = '')
{
    if ($rootpath == '.') {
        $rootpath = realpath($rootpath);
    }
    $forlder = explode('/', $dir);
    $path = '';
    for ($i = 0; $i < count($forlder); $i++) {
        if ($current_dir = trim($forlder[$i])) {
            if ($current_dir == '.') {
                continue;
            }
            $path .= '/' . $current_dir;
            if ($current_dir == '..') {
                continue;
            }
            if (file_exists($rootpath . $path)) {
                @chmod($rootpath . $path, 0755);
            } else {
                if (!_mkdir($rootpath . $path)) {
                    return false;
                }
            }
        }
    }
    return true;
}

function isEmail($email)
{
    return preg_match('/^\w[_\-\.\w]+@\w+\.([_-\w]+\.)*\w{2,4}$/', $email);
}

function isMobile($phone)
{
    return preg_match("/^1\d{10}$/", $phone);
}

function isDateValid($str)
{
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $str)) {
        return false;
    }
    $stamp = strtotime($str);
    if (!is_numeric($stamp)) {
        return false;
    }
    if (checkdate(ddate('m', $stamp), ddate('d', $stamp), ddate('Y', $stamp))) {
        return true;
    }
    return false;
}

function isIntval($mixed)
{
    return (preg_match('/^\d+$/', $mixed) == 1);
}

function getIP()
{
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }

    preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
    $onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : null;
    unset($onlineipmatches);
    return $onlineip;
}

function isNeedCheckAccessToken($controller, $action)
{
    global $no_need_check_accesstoken;//不需要checktoken的url
    if (!array_key_exists($controller, $no_need_check_accesstoken)) {
        return true;//需要check
    }
    if (is_array($no_need_check_accesstoken[$controller])) {
        if (!$no_need_check_accesstoken[$controller]) {
            return false;//不需要
        }
        if (in_array($action, $no_need_check_accesstoken[$controller])) {
            return false;
        }
    }
    return true;//需要check
}

//计算多久之前发表
function calculateTime($t)
{
    if (!$t) {
        return false;
    }

    $return = '';
    $lang = array(
        'hour'=>'hour ',
        'hours'=>'hours ',
        'half'=>'Half ',//半小时
        'min'=>'min ',
        'mins'=>'minutes ',
        'sec'=>'seconds ',
        'yday'=>'This day ',
        'day'=>'day',
        'days'=>'days',
        'yesterday'=>'Yesterday ',
        'now'=>'Now ',
        'before'=>'ago ',
    );
    $time = time() - $t;
    //echo '|'.$time;die;
    $split = ' ';
    if ($time>=0 && $time <=86400) { //本天内

        if ($time > 3600) {
            //多少小时前
            $return = intval($time / 3600).$split.$lang['hours'].$lang['before'];
        } elseif ($time > 1800) {
            //半小时前
            //$return = $lang['half'].$lang['hour'].$lang['before'];
            $return = intval($time / 60).$split.$lang['mins'].$lang['before'];
        } elseif ($time > 60) {
            //多少分钟前
            $return = intval($time / 60).$split.$lang['mins'].$lang['before'];
        } elseif ($time > 0) {
            //多少秒前
            $return = $time.$split.$lang['sec'].$lang['before'];
        } elseif ($time == 0) {
            //刚刚
            $return = $lang['now'];
        }
    } elseif (($days = intval($time / 86400)) >= 0 && $days < 365) {//一年内
        //几天前
        if ($days == 0) {
            //本天内发表
            $return = $lang['yday'].$split.ddate('Y-m-d H:i', $t);
        } elseif ($days == 1) {
            //一天前
            //$return = $lang['byday'].$split.ddate('Y-m-d H:i', $t);
            $return = $lang['yesterday']. ddate('H:i', $t);
        } else {
            //多少天前
            //16 sep 22:29   ddate('d M H:i', $t);
            $return = ($days + 1).$split.$lang['days'].' '.$lang['before'];
            //$return = ddate('d M H:i', $t);
        }
    } elseif (($days = intval($time / 86400)) >= 0 && $days < 50000) { //50000day内
        $return = ddate('Y-m-d H:i', $t); //16 sep 22:29   ddate('d M H:i', $t);
    } else {
        $return = $lang['now'];
    }

    return $return;
}


function cutstr($string, $length = 20, $dot = '...', $htmlencode = true, $charset = 'utf-8')
{
    if (strlen($string) <= $length) {
        if ($htmlencode) {
            return htmlspecialchars($string);
        } else {
            return $string;
        }
    }
    $strcut = '';
    if (strtolower($charset) == 'utf-8') {
        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t < 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
    } else {
        for ($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
    }
    $original_strlen = strlen($string);
    $new_strlen = strlen($strcut);
    if ($htmlencode) {
        $strcut = htmlspecialchars($strcut);
    }
    return $strcut . ($original_strlen > $new_strlen ? $dot : '');
}

function highLight($text, $words, $prepend)
{
    $text = str_replace('\"', '"', $text);
    $text = str_replace(array(
        ' ',
        ' '
    ), array(
        '',
        ''
    ), $text);
    $text = preg_replace("/\s(?=\s)/", "\\1", $text);

    if (!is_array($words)) {
        $words = array(
            $words
        );
    }

    foreach ($words as $key => $replaceword) {
        // $text = str_ireplace($replaceword,
        // '<highlight>'.$replaceword.'</highlight>', $text);
        $text = preg_replace("/(" . $replaceword . ")/isU", '<highlight>\\1</highlight>', $text);
    }

    return "$prepend$text";
}

//function getDb(){
//    $config = new Core\Config(ROOT.'config');//传路径
//    $db_servers = $config['db'];
//    $db = new Core\Db($db_servers);
//    return $db;
//}
//
//function fetchAll($sql){
//    $pdo = getDb();
//    $ret = $pdo->queryAll($sql);
//    return $ret;
//
//}
//
//function fetchRow($sql){
//    $pdo = getDb();
//    $ret = $pdo->query($sql);
//    return $ret;
//}
//
//function fetchOne($sql){
//    $pdo = getDb();
//    $ret = $pdo->query($sql);
//    return $ret;
//}
function writeLog($msg, $name = null, $log_dir = null)
{
    if (!$name) {
        $name = date('Y-m-d_H', time());
    } else {
        if ($log_dir === null) {
            $name .= '_' . date('H', time());
        }
    }
    if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR']) {
        $name .= '_' . $_SERVER['SERVER_ADDR'];
    } else {
        if (isset($GLOBALS['local_ip'])) {
            $name .= '_' . $GLOBALS['local_ip'];
        }
    }
    if ($log_dir === null) {
        $log_dir = '/' . 'pre_' . '/' . date('Ym', time()) . '/' . date('d', time());
    }

    _mkdirs($log_dir, ROOT.'app/log/');
    $log_path = ROOT.'app/log/' . $log_dir;
    $log_file = $log_path . "/" . $name . ".log";

    if (is_array($msg)) {
        $msg = json_encode($msg);
    }
    $msg = '[' . date("Y-m-d H:i:s", time()) . '] ' . $msg . "\n";

    return file_put_contents($log_file, $msg, FILE_APPEND);
}

function cException($exception)
{
    $log_data = '';
    $log_data .= date("Y-m-d H:i:s") . ' ' . $exception->__toString();
    writeLog($log_data, 'exception_error');
    echo 'exception_error';
    //$error_code = 10008;
    //echo apiReturnError($error_code);
    die();
}

/**
 * @param $arr 要排序的数组
 * @param $str 根据哪个字符串
 * @param string $type SORT_ASC - 默认。按升序排列 (A-Z)。SORT_DESC - 按降序排列 (Z-A)。
 * @return mixed
 */
function array_value_sort($arr, $str, $type='SORT_ASC')
{
    foreach ($arr as $v) {
        $flag[] = $v[$str];
    }
    array_multisort($flag, $type, $arr);
    return $arr;
}



/**
 * 弹出对话框
 * @static
 * @access pubilc
 * @param strng $msg 消息内容
 * @param strng $url 跳转地址
 * @param bool $isSelf
 */
function alert($msg, $url=null, $isSelf=true)
{
    $output = "<script>";
    $output .= "alert('" . $msg . "'); ";
    if ($url) {
        if ($isSelf) {
            $output .= "location.href='" . $url . "';";
        } else {
            $output .= "top.location.href='" . $url . "';";
        }
    }
    $output .= "</script>";
    die($output);
}

/**
 * JS页面跳转
 * @static
 * @access pubilc
 * @param string $url 跳转地址
 * @param string $isSelf
 */
function goUrl($url, $isSelf)
{
    $top = $isSelf ? '' : 'top';
    die("<script type='text/javascript'>" . $top . ".location.href ='" . $url . "';</script>");
}


/**
 * 为实现ajax跨域，进行jsonp编码，此方法会从$_GET里读取callback
 * @static
 * @access public
 * @param object $data 数据
 * @return string callback(33);
 */
function jsonp_encode($data)
{
    $callback = isset($_GET["callback"]) ? $_GET["callback"] : "callback";
    $callback = urlencode($callback);
    return $callback . "(". json_encode($data). ");";
}


/**
 * cdn 头部
 *
 * @access global
 * @param mixed $time
 * @return string | void
 */
function cdnHeader($time)
{
    $time   = intval($time);
    $nowGmt = gmdate("D, d M Y H:i:s", time());
    $expiresGMT = gmdate("D, d M Y H:i:s", time()+$time);
    header("Date: $nowGmt"." GMT");
    header("Last-Modified: $nowGmt"." GMT");
    header("Expires: $expiresGMT"." GMT");
    header('Cache-Control: max-age='.$time);
}
/**
 * 压缩html : 清除换行符,清除制表符,去掉注释标记
 * @param	$string
 * @return  压缩后的$string
 * */
function compressHtml($string)
{
    $string = str_replace("\r\n", '', $string); //清除换行符
    $string = str_replace("\n", '', $string); //清除换行符
    $string = str_replace("\t", '', $string); //清除制表符
    $pattern = array(
        "/> *([^ ]*) *</", //去掉注释标记
        "/[\s]+/",
        "/<!--[^!]*-->/",
        "/\" /",
        "/ \"/",
        "'/\*[^*]*\*/'"
    );
    $replace = array(
        ">\\1<",
        " ",
        "",
        "\"",
        "\"",
        ""
    );
    return preg_replace($pattern, $replace, $string);
}


/**
 * 获取客户端真实ip
 *
 * @return string
 */
function getRealIp()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $clientIp = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ips as $ip) {
            $ip = trim($ip);
            if (!self::isLan($ip)) { //非局域网
                $clientIp = $ip;
                break;
            }
        }
    }
    return (!empty($clientIp) ? $clientIp : $_SERVER['REMOTE_ADDR']);
}


function download($filename)
{
    if ((isset($filename))&&(file_exists($filename))) {
        header("Content-length: ".filesize($filename));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile("$filename");
    } else {
        echo "Looks like file does not exist!";
    }
}

function logResult($path='log.txt', $str='')
{
    $fp = fopen("", "a");
    flock($fp, LOCK_EX) ;
    fwrite($fp, "执行日期：".strftime("%Y%m%d%H%M%S", time())."\n".$str."\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

function mk_dir($path)
{
    if (is_dir($path)) {
        return true;
    }
    return is_dir(dirname($path))||mk_dir(dirname($path))?mkdir($path):false;
}

function sortArray($arr, $k)
{
    usort($arr, function ($a, $b) {
        $al = $a[$k]+0;
        $bl = $b[$k]+0;
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? -1 : 1;
    });
    return $arr;
}
//生成秘钥
function signatureKey($param)
{
    ksort($param);
    $string = '';
    foreach ($param as $k => $v) {
        $string .= $k . '=' . urlencode($v);
    }
    $newTicket = hash_hmac("md5", strtolower($string), 'pushOrder');
    return $newTicket;
}

function set_token($secretkey, $param, $payload=null)
{
    //    ksort($param);
//    $ret = '';
//    foreach ($param as $k=>$v){
//        $ret .= "{$k}{$v}";
//    }
//
//    $token  = $ret . $secretkey;
//    if ($payload) {
//        $token .= $payload;
//    }
//
//    return md5($token);
}

// 仿DOS的tree命令,给目录层次加缩进
function showdir($path, $lev = 1)
{
    $dh = opendir($path);
    while (($d = readdir($dh)) !== false) {
        if ($d == '.' || $d == '..') {
            continue;
        }

        echo '├' . str_repeat('─', $lev) . $d,'<br />';
        if (is_dir($path . '/' .$d)) {
            showdir($path . '/' . $d, $lev + 1);
        }
    }
}

//求子孙树
function son($arr, $id=0, $lev=1)
{
    static $list=array();
    foreach ($arr as $v) {
        if ($v['parent']==$id) {
            $v['lev']=$lev;
            $list[]=$v;
            son($arr, $v['id'], $lev+1);
        }
    }
    return $list;
    /*
        print_r($arr=son($area));
        echo '<br/>';
        foreach($arr as $v){
        if($v['parent']==0){
            echo '<br />';
        }
        echo str_repeat(' ',$v['lev']),$v['name'],'<br/>';
     **/
}
//子孙树
function subtree($arr, $id=0, $lev=1)
{
    static $sta = array(); //或则用array_merge   或 用 +

    foreach ($arr as $k=>$v) {
        if ($v['parent_id']==$id) {
            $v['lev'] = $lev;
            $sta[] = $v;
        }

        subtree($arr, $v['id'], $lev+1);
    }
}

//家谱树  哨兵
function familytree($arr, $id)
{
    $list = array();
    $arr[] = array('id'=>0,'area'=>0,'pid'=>0); // 硬加一个

    while ($id) {
        foreach ($arr as $v) {
            if ($v['id'] ==$id || $v['id'] ==0) {
                if ($v['id']>0) {
                    $list[] = $v;
                }
                $id = $v['pid'];
                break;
            }
        }
    }
    return $list;
    ////print_r(familytree($area,7));
}


function getUri($query)
{
    //$arr = func_get_args(); //func_num_args();
    $request_uri = $_SERVER["REQUEST_URI"];
    $url = strstr($request_uri, '?') ? $request_uri :  $request_uri.'?';

    if (is_array($query)) {
        $url .= http_build_query($query);
    } elseif ($query != "") {
        $url .= "&".trim($query, "?&");
    }

    $arr = parse_url($url);

    if (isset($arr["query"])) {
        parse_str($arr["query"], $arrs);
        unset($arrs["page"]);
        $url = $arr["path"].'?'.http_build_query($arrs);
    }

    if (strstr($url, '?')) {
        if (substr($url, -1)!='?') {
            $url = $url.'&';
        }
    } else {
        $url = $url.'?';
    }

    return $url;
}

//xml 转数组 xml2array
function toArray($sim)
{
    $arr =(array) $sim;
    foreach ($arr as $k=>$v) {
        if ($v instanceof SimpleXMLElement || is_array($v)) {
            $arr[$k] = toArray($v);
        }
    }
    return $arr;
}

function randStr()
{
    $arr=array_merge(range(0, 9), range("a", "z"), range("A", "Z"));
    shuffle($arr);
    $arr2=array_slice($arr, 0, 4);
    return implode('', $arr2);
}

function delDir($dir)
{
    if (is_dir($dir)) {
        $objs=scandir($dir);
        foreach ($objs as $obj) {
            if ($obj!='.' && $obj!='..') {
                if (is_dir($dir.'/'.$obj)) {
                    delDir($dir.'/'.$obj);
                } else {
                    unlink($dir.'/'.$obj);
                }
            }
        }
        rmdir($dir);
    }
    return true;
}


function scanfiles($dir)
{
    if (! is_dir($dir)) {
        return array();
    }

    // 兼容各操作系统
    $dir = rtrim(str_replace('\\', '/', $dir), '/') . '/';

    // 栈，默认值为传入的目录
    $dirs = array( $dir );

    // 放置所有文件的容器
    $rt = array();

    do {
        // 弹栈
        $dir = array_pop($dirs);

        // 扫描该目录
        $tmp = scandir($dir);

        foreach ($tmp as $f) {
            // 过滤. ..
            if ($f == '.' || $f == '..') {
                continue;
            }

            // 组合当前绝对路径
            $path = $dir . $f;

            // 如果是目录，压栈。
            if (is_dir($path)) {
                array_push($dirs, $path . '/');
            } elseif (is_file($path)) { // 如果是文件，放入容器中
                $rt [] = $path;
            }
        }
    } while ($dirs); // 直到栈中没有目录

    return $rt;
}

function iconv_str($content)
{
    //gbk转utf8
    $content = iconv("GBK", "UTF-8", $content);
    //$content = mb_convert_encoding($content, "UTF-8", "GBK");
    return $content;
}

//获取字符串编码
function getStrCode($str)
{
    //****利用mb_detect_encoding检测，文本较短的时候会检测不到
    $page_code = mb_detect_encoding($str, array('utf-8','gbk','gb2312','CP936','big5','ascii'));
    $page_code = strtolower($page_code);

    if (empty($page_code)) {
        $strLength = strlen($str);
        $countEncoding['utf-8'] = 1;
        $countEncoding['gbk'] = 1;
        $i = 0;
        while ($i < $strLength) {
            $strTMP = substr($str, $i, 1);
            if (ord($strTMP) >= 224) {
                $strTMP = substr($str, $i, 3);
                $i = $i + 3;
                $countEncoding['utf-8']++;
            } elseif (ord($strTMP) >= 192) {
                $strTMP = substr($str, $i, 2);
                $i = $i + 2;
                $countEncoding['gbk']++;
            } else {
                $i = $i + 1;
            }
        }

        if ($countEncoding['gbk'] > 0 && $countEncoding['gbk'] > $countEncoding['utf-8'] * 2) {
            $page_code = 'gbk';
        }
    }

    return $page_code;
}

//获取指定的文件后缀名
function get_file_extension($file)
{
    //SPLFileInfo类的getExtension方法，需要5.3.6+版本
    if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
        $fileInfo = new \splFileInfo($file);
        return $fileInfo->getExtension();
    } else {
        $fileInfo = pathinfo($file);
        return $fileInfo['extension'];
    }
}

//格式化文件大小(file_format_size)
function file_format_size($file, $unitList = array())
{
    $size = filesize($file);
    if (empty($size)) {
        return '';
    }
    if (empty($unitList)) {
        $unitList = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    }

    $i = intval(log($size, 1024));
    return (round($size/pow(1024, $i), 2) . $unitList[$i]);
}
