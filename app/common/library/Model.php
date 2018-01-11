<?php

namespace app\library;

use app\library\Redis;
use Medoo\Medoo;

class Model extends Medoo
{
    public $db = null;
    public static $ins = null;
    public static $objects=[];

    public function __construct()
    {
        $config = new Config(ROOT.'config');//传路径
        $db_servers = $config['db'];
        $this->db = new Db($db_servers);
    }

    public function getIns()
    {
        //        if($this->ins !instanceof self){
//            $this->ins = new self();
//        }
//        return $this->ins;
        $config = new Config(ROOT.'config');//传路径
        $db_servers = $config['db'];
        $this->db = new Db($db_servers);
        return $this->db;
    }

    public function getLimit($page, $count)
    {
        $offset = ($page - 1) * $count;
        $limit = ' LIMIT ' . $offset . ',' . $count . ' ';
        return $limit;
    }

    public static function getRedis()
    {
        $redis= new Redis();
        return $redis;
    }

    public function autoCreateSql($table, $data, $act='insert', $where='')
    {
        if ($act == 'insert') {//$act  $_REQUEST['act']
            $sql = 'insert into ' . $table . ' (';
            $sql .= implode(',', (array_keys($data)));   // 拼接
            $sql .= ') values (\'';
            $sql .= implode("','", array_values($data));
            $sql .= "')";
        } elseif ($act == 'update') {
            if (!trim($where)) {
                return false;
            }

            $sql = 'update ' . $table . ' set ';
            foreach ($data as $k=>$v) {
                $sql .= $k;
                $sql .= '=';
                $sql .= "'".$v."',";
            }

            $sql = substr($sql, 0, -1);
            $sql .= ' where ';
            $sql .= $where;
        } else {
            return false;
        }

        //return $sql;
        return $this->query($sql);
    }

    public static function getDb()
    {
        $config = new Config(ROOT.'config');//传路径
        $db_servers = $config['db'];
        $db = new Db($db_servers);
        return $db;
    }

    public function getMedoo($type='slave')
    { //master为主 slave从库
        $config = new Config(ROOT.'config');//传路径
        $db_servers = $config['db'];
        if ($type=='master') {
            $config = $db_servers['master'];
        } else {
            $rand = array_rand($db_servers[$type]);
            $config = $db_servers[$type][$rand];
        }
        $db = new Medoo([
            // 必须配置项
            'database_type' => 'mysql',
            'database_name' => $config['dbname'],
            'server' => $config['host'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => 'utf8',

            // 可选参数
            'port' => $config['port'],

//            // 可选，定义表的前缀
//            'prefix' => 'PREFIX_',
//
//            // 连接参数扩展, 更多参考 http://www.php.net/manual/en/pdo.setattribute.php
            'option' => [
                \PDO::ATTR_CASE => \PDO::CASE_NATURAL
            ]
        ]);
        return $db;
    }

    public function fetchAll($sql)
    {
        $pdo = $this->getDb();
        $ret = $pdo->queryAll($sql);
        return $ret;
    }

    public function fetchRow($sql)
    {
        $pdo = $this->getDb();
        $ret = $pdo->query($sql);
        return $ret;
    }

    public function fetchOne($sql)
    {
        $pdo = $this->getDb();
        $ret = $pdo->query($sql);
        return $ret;
    }


    public function insertData($table, $data)
    {
        $pdo = $this->getDb();
        return $pdo->insert($table, $data, true);
    }
    public function updateData($table, $data, $where, $whereparam)
    {
        $pdo = $this->getDb();
        //$_pdo = $pdo->getPdo();//获取原生pdo
        return $pdo->update($table, $data, $where, $whereparam);
    }

    public function deleteData($tableName, $where, $whereParam = null)
    {
        $pdo = $this->getDb();
        //$_pdo = $pdo->getPdo();//获取原生pdo
        return $pdo->delete($tableName, $where, $whereParam);
    }
}
