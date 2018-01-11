<?php

namespace app\library;

class Curl
{
    //    public static function curl($url, $data, $method='GET')
//    {
//        $ch  = curl_init();
//        if ($method == 'GET') {
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
//        } else {
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_POST, true);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        }
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $body = curl_exec($ch);
//        $header = curl_getinfo($ch);
//        return json_decode($body, true);
//    }

    public static function curl($url, $postData=null, $timeout=3)
    {
        $arrResult = array();
        $arrResult['status'] = true;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        $arrResult['content'] = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code != 200) {
            $arrResult['status'] = false;
            $arrResult['code'] = $http_code;
        }

        return $arrResult;
    }

    public static function curlGetContents($url, $timeout = 3)
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

    public static function curlPostContents($url, $params, $use_http_build_query = true)
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
     * 读取远程文件
     * @access public static
     * @param
     * @return mixed
     * @example
    echo http::urlfopen('http://topic.kugou.com/2011/xuliang/');
     */
    public static function urlfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = false, $ip = '', $timeout = 15, $block = true, $encodetype = 'URLENCODE')
    {
        $return = '';
        $matches = parse_url($url);
        $host = $matches['host'];
        $path = isset($matches['path']) ? $matches['path'] . (isset($matches['query']) ? '?' . $matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : 80;

        if ($post) {
            $out = "POST $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $boundary = $encodetype == 'URLENCODE' ? '' : ';' . substr($post, 0, trim(strpos($post, "\n")));
            $out .= $encodetype == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data$boundary\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= 'Content-Length: ' . strlen($post) . "\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
            $out .= $post;
        } else {
            $out = "GET $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Referer: \r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
        }
        $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
        if (!$fp) {
            return '';
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);
            if (!$status['timed_out']) {
                while (!feof($fp)) {
                    if (($header = @fgets($fp)) && ($header == "\r\n" || $header == "\n")) {
                        break;
                    }
                }

                $stop = false;
                while (!feof($fp) && !$stop) {
                    $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                    $return .= $data;
                    if ($limit) {
                        $limit -= strlen($data);
                        $stop = $limit <= 0;
                    }
                }
            }
            @fclose($fp);
            return $return;
        }
    }

    //file_get_contents post数据
//    public static function post($url,$data){
//        $query = http_build_query($data);
//        $options['http'] = [
//            'timeout' =>60,
//            'method' =>'POST',
//            'header'=>'Content-type:application/x-www-form-urlencoded',
//            'content'=>$query
//        ];
//        $context = stream_context_create($options);
//        $result = file_get_contents($url,false,$context);
//        return $result;
//    }
}
