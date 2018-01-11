<?php

namespace app\library;

/**
 * 获取Cookie信息
 * @package util
 * @example
 *  Cookie::set('a', time(), 1000);
echo Cookie::get('a');
 *
 */

class Cookie
{

    /**
     * 判断Cookie是否存在
     * @static
     * @access public
     * @param string $name
     * @return bool
     */
    public static function is_set($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * 获取某个Cookie值
     * @static
     * @access public
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        if (isset($_COOKIE[$name])) {
            $value = $_COOKIE[$name];
            $value = unserialize(base64_decode($value));
            return $value;
        }
        return null;
    }

    /**
     * 设置某个Cookie值
     * @static
     * @access public
     * @param string $name
     * @param mixed $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     */
    public static function set($name, $value, $expire='', $path='/', $domain='')
    {
        $expire = !empty($expire) ? time() + $expire : 0;
        $value = base64_encode(serialize($value));
        setcookie($name, $value, $expire, $path, $domain);
        $_COOKIE[$name] = $value;
    }

    /**
     * 删除某个Cookie值
     * @static
     * @access public
     * @param string $key
     */
    public static function delete($key)
    {
        Cookie::set($key, '', time() - 3600);
        unset($_COOKIE[$key]);
    }

    /**
     * 清空所有Cookie值
     * @static
     * @access public
     */
    public static function clear()
    {
        unset($_COOKIE);
    }
}
