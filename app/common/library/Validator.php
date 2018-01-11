<?php
namespace app\library;

class Validator
{
    /**
     * 验证手机
     *
     * @param string $mobile
     * @return bool
     */
    public static function isMobile($mobile)
    {
        return preg_match('/^(0{0,1}|86{0,1})1[0-9]{10}$/', $mobile);
    }

    /**
     * 验证邮箱
     *
     * @param string $email
     * @return bool
     */
    public static function isEmail($email)
    {
        return preg_match('/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/', $email);
    }

    /**
     * 验证url
     *
     * @param string $url
     * @return bool
     */
    public static function isUrl($url)
    {
        return preg_match('/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i', $url);
    }

    /**
     * 验证ip
     *
     * @param string $ip
     * @return bool
     */
    public static function isIp($ip)
    {
        return preg_match('/^((25[0-5]|2[0-4]\d|1?\d?\d)\.){3}(25[0-5]|2[0-4]\d|1?\d?\d)$/', $ip);
    }

    /**
     * 验证中文
     *
     * @param string $str
     * @param string $encoding
     */
    public static function isChinese($str, $encoding = 'utf-8')
    {
        switch ($encoding) {
            case 'utf-8':
                $pattern = '/[\x{4e00}-\x{9fa5}]+/u';
                break;
            case 'gbk':
                $pattern = '/(['.chr(0xb0).'-'.chr(0xf7).']['.chr(0xa1).'-'.chr(0xfe).'])+/i';
                break;
        }
        return preg_match($pattern, $str);
    }
}
