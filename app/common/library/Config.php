<?php

namespace app\library;

class Config implements \ArrayAccess
{
    protected $path;
    protected $configs = array();
    public function __construct($path) //路劲
    {
        $this->path = $path;
    }
    public function offsetGet($key)
    {
        if (empty($this->configs[$key])) {
            $file_path = $this->path.'/'.$key.'.php';
            $config = require $file_path;
            $this->configs[$key] = $config;
        }
        return $this->configs[$key];
    }
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        throw new \Exception('cannot write config file');
    }
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }
}
