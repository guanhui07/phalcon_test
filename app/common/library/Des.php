<?php
namespace app\library;

class Des
{
    //    public static function encrypt($key, $text)
//    {
//        $size = mcrypt_get_block_size('des', 'ecb');
//        $text = self::pkcs5Pad($text, $size);
//        $td = mcrypt_module_open('tripledes', '', 'ecb', '');
//        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
//        mcrypt_generic_init($td, $key, $iv);
//        $data = mcrypt_generic($td, $text);
//        mcrypt_generic_deinit($td);
//        mcrypt_module_close($td);
//        $data = base64_encode($data);
//        return $data;
//    }
//
//    public static function decrypt($key, $encrypted)
//    {
//        $encrypted = base64_decode($encrypted);
//        $td = mcrypt_module_open('des', '', 'ecb', '');
//        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
//        $ks = mcrypt_enc_get_key_size($td);
//        mcrypt_generic_init($td, $key, $iv);
//        $decrypted = mdecrypt_generic($td, $encrypted);
//        mcrypt_generic_deinit($td);
//        mcrypt_module_close($td);
//        $plain_text = self::pkcs5Unpad($decrypted);
//        return $plain_text;
//    }

    public function encryptUngeneric($key, $text)
    {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size('tripledes', MCRYPT_MODE_ECB), MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt('tripledes', $key, $text, MCRYPT_MODE_ECB, $iv);
        $des3 = bin2hex($encrypted_string);
        return $des3;
    }

    public function decryptUngeneric($key, $encrypted)
    {
        $encrypted_string = @pack("H*", $encrypted);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size('tripledes', MCRYPT_MODE_ECB), MCRYPT_RAND);
        $plain_txt = mcrypt_decrypt('tripledes', $key, $encrypted_string, MCRYPT_MODE_ECB, $iv);

        return $plain_txt;
    }

    public static function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    /**
     * 描述 : 加密
     * 参数 : params : 加密数组
     * 返回 : 密文
     */
    public static function encrypt($params)
    {
        if (is_array($params)) {
            $params += array('time' => time());
            $params = json_encode($params);
            $params = base64_encode($params);
            $len = 5;
            $temp = array();
            for ($i = $j = 0; $i < (ceil(strlen($params) / $len) * $len + 1); ++$i) {
                $temp += array($j => '');
                $temp[$j] .= isset($params[$i]) ? $params[$i] : '&';
                if ($i && $i % $len === 0) {
                    $temp[$j] = $temp[$j] . substr('&&&&&', strlen($temp[$j]));
                    ++$j;
                }
            }
            $params = '';
            for ($i = 0; $i < $len; $i++) {
                for ($j = 0; $j < count($temp); $j++) {
                    $params .= $temp[$j][$i];
                }
            }
            if (isset($temp[0][$len])) {
                $params .= $temp[0][$len];
                return $params;
            }
            return false;
        }
        return null;
    }

    /**
     * 描述 : 解密
     * 参数 : sign : 密文
     * 返回 : 原始数组
     */
    public static function decrypt($sign)
    {
        if (is_string($sign)) {
            $len = 5;
            $params = array();
            $count = (strlen($sign) - 1) / $len;
            for ($i = 0; $i < $count; $i++) {
                for ($j = 0; $j < $len; $j++) {
                    $params += array($i => '');
                    $params[$i] .= $sign[$j * $count + $i];
                }
            }
            $params[0] .= $sign[strlen($sign) - 1];
            $params = base64_decode(str_replace('&', '', implode('', $params)));
            $params = @json_decode($params, true);
            if (is_array($params)) {
                return $params;
            }
            return false;
        }
        return null;
    }
}
