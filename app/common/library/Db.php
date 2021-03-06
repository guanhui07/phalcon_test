<?php
namespace app\library;

class Db
{
    public static $sqls = array();
    public static $configs = array();
    public static $dbhs = array();
    public static $db_config = array();
    private static $pdo = array();

    public function __construct($db_config = null)
    {
        self::$db_config = $db_config;
    }

    /**
     * 数据库配置
     *
     * @param string $type
     */
    public static function setconfig($type = 'master')
    {
        $db_config = self::$db_config;
        if ($type == 'slave') { // 随机从一台slave服务器读取数据
            $rand_number = array_rand($db_config[$type]);
            $db_config[$type] = $db_config[$type][$rand_number];
        }
        $configs[$type]['dsn'] = "mysql:host={$db_config[$type]['host']}; port={$db_config[$type]['port']}; dbname={$db_config[$type]['dbname']}";
        $configs[$type]['username'] = $db_config[$type]['username'];
        $configs[$type]['password'] = $db_config[$type]['password'];
        self::$configs = $configs;
    }

    /**
     * 获取一个PDO对象
     *
     * @param string $type
     *            类型 [master|slave] 主从
     * @return PDO
     */
    public static function getPdo($type = 'master')
    {
        self::setconfig($type);
        $key = $type;
        if (!isset(self::$dbhs[$key][self::$configs[$type]['dsn']])) {
            $dbh = new \PDO(self::$configs[$type]['dsn'], self::$configs[$type]['username'], self::$configs[$type]['password'], array());
            $dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$dbhs[$key][self::$configs[$type]['dsn']] = $dbh;
        }
        return self::$dbhs[$key][self::$configs[$type]['dsn']];
    }

    public function close()
    {
        if (!self::$dbhs) {
            return false;
        }
        foreach (self::$dbhs as $key => $dbh) {
            unset(self::$dbhs[$key]);
        }
        return true;
    }

    /**
     * 查询出所有的记录
     *
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public static function execQuery($sql, $params = null, $dbtype = 'slave')
    {
        $pdo = self::getPdo($dbtype);

        self::paramParse($sql, $params);
        $sth = self::preExec($pdo, $sql, $params);
        return $sth;
    }

    /**
     * 将数据插入到指定表中
     *
     * @param string $tableName
     * @param string $data
     *            要insert到表中的数据
     * @param string $get_last_insert_id
     *            是否获取最后插入ID
     */
    public static function insert($tableName, $data, $get_last_insert_id = false)
    {
        $sql = "insert into `{$tableName}`(" . join(",", array_keys($data)) . ") values(" . rtrim(str_repeat("?,", count($data)), ",") . ")";
        $params = array_values($data);
        $pdo = self::getPdo('master');
        $sth = self::preExec($pdo, $sql, $params);
        if (is_array($sth) && $sth['error_code']) {
            return false;
        }
        if ($get_last_insert_id) {
            $pdo = self::getPdo('master');
            return $pdo->lastInsertId();
        } else {
            return true;
        }
    }

    /**
     * 将数据批量插入到指定表中
     *
     * @param string $tableName
     * @param string $data_array
     *            要insert到表中的数据
     * @param string $get_last_insert_id
     *            是否获取最后插入ID
     */
    public static function insertMulti($tableName, $data_array)
    {
        $data = current($data_array);
        $current_parameters = null;
        $sql = "insert into `{$tableName}`(" . join(",", array_keys($data)) . ") values(" . rtrim(str_repeat("?,", count($data)), ",") . ")";
        $pdo = self::getPdo('master');
        try {
            $sth = $pdo->prepare($sql);
            reset($data_array);
            foreach ($data_array as $params) {
                $current_parameters = array_values($params);
                $sth->execute($current_parameters);
            }
        } catch (Exception $e) {
            $error_log = $e->getMessage() . '||sql:' . $sql . '||data:' . json_encode($current_parameters);
            writeLog($error_log, 'sql_error');
            return false;
        }
        return true;
    }

    /**
     * 对指定表进行更新操作
     * rareDb::update('tableName',array('title'=>'this is
     * title','content'=>'this is content'),'id=?',array(12));
     *
     * @param string $tableName
     * @param array $data
     *            要进行更新的数据 array('title'=>'this is title','hitNum=hitNum+1')
     * @param string $where
     * @param string $whereParam
     * @param string $dbName
     */
    public static function update($tableName, $data, $where, $whereParam = null)
    {
        if (is_string($data)) {
            $data = array(
                $data
            );
        }
        $sql = "UPDATE `{$tableName}` SET ";
        $tmp = array();
        $param = array();
        foreach ($data as $k => $v) {
            if (is_int($k)) { // 如 hitNum=hitNum+1，可以是直接的函数
                $tmp[] = $v;
            } else { // 其他情况全部使用占位符 'title'=>'this is title'
                $tmp[] = "`{$k}`=:k_{$k}";
                $param[":k_" . $k] = $v;
            }
        }
        $where = self::filters($where);
        self::paramParse($where, $whereParam);
        $param = array_merge($param, $whereParam);
        $sql .= join(",", $tmp) . " {$where}";
        $result = self::exec($sql, $param);
        if (is_array($result) && $result['error_code']) {
            return false;
        }
        if (!$result) {
            $result = true;
        }
        return $result;
    }

    /**
     * 对指定表进行删除操作
     * 如Db::delete('tableName',"id=?",array(1));
     *
     * @param string $tableName
     * @param string $where
     * @param array $whereParam
     * @param string $dbName
     */
    public static function delete($tableName, $where, $whereParam = null)
    {
        self::paramParse($where, $whereParam);
        $param = $whereParam;
        $sql = "delete from `{$tableName}` where {$where}";
        return self::exec($sql, $param);
    }

    /**
     *
     * @param string $sql  获取一行记录 where id=233  或一个count(1)
     * @param array $params
     *            当参数只有一个时也可以直接写参数而不需要写成数组
     */
    public static function query($sql, $params = null, $dbtype = 'slave')
    {
        $fetch_all = false;
        return self::select($sql, $params, $fetch_all, $dbtype);
    }

    /**
     * 查询出所有的记录  获取多行记录 where id>=233
     *
     * @param string $sql
     * @param array $params
     */
    public static function queryAll($sql, $params = null, $dbtype = 'slave')
    {
        return self::select($sql, $params, $dbtype);
    }


    /*
     * @param string $sql @param string|array $params @param string $dbName
     * @param bllean $fetchAll 是否获取全部结果集
     */
    protected static function select($sql, $params = null, $fetchAll = true, $dbtype = 'slave')
    {
        $sth = self::execQuery($sql, $params, $dbtype);
        if (is_array($sth) && isset($sth['error_code'])) {
            return null;
        }
        return $fetchAll ? $sth->fetchAll() : $sth->fetch();
    }

    /**
     *
     * @param string $sql
     * @param array $params
     * @param string $dbName
     * @return int
     */
    public static function exec($sql, $params = null)
    {
        $pdo = self::getPdo('master');
        self::paramParse($sql, $params);
        $sth = self::preExec($pdo, $sql, $params);
        if (is_array($sth) && $sth['error_code']) {
            return $sth;
        }
        return $sth->rowCount();
    }

    /**
     *
     * @param PDO $pdo
     * @param string $sql
     * @param array $params
     * @throws Exception
     * @return PDOStatement
     */
    private static function preExec($pdo, $sql, $params)
    {
        try {
            //echo $sql;die;
            $sth = $pdo->prepare($sql);
            $sth->execute($params);
        } catch (\Exception $e) {
            $error_info = $e->getMessage();
            $data['error_code'] = $error_info[1];
            $data['error'] = $error_info[2];
            if ($data['error_code'] == 2006) {
                self::close();
                $pdo = self::getPdo('master');
                return self::preExec($pdo, $sql, $params);
            }
            $error_log = $e->__toString() . '||sql:' . $sql . '||data:' . json_encode($params);
            writeLog($error_log, 'sql_error');
            return $data;
        }
        return $sth;
    }

    /**
     * 自动生成条件语句
     *
     * @param array $filters
     * @return string
     */
    public static function filters($filters)
    {
        $sql_where = '';
        if (is_array($filters)) {
            foreach ($filters as $f => $v) {
                $f_type = gettype($v);
                if ($f_type == 'array') {
                    $sql_where .= ($sql_where ? " AND " : "") . "(`{$f}` " . $v['operator'] . " '" . $v['value'] . "')";
                } elseif ($f_type == 'string') {
                    $sql_where .= ($sql_where ? " OR " : "") . "(`{$f}` LIKE '%{$v}%')";
                } else {
                    $sql_where .= ($sql_where ? " AND " : "") . "(`{$f}` = '{$v}')";
                }
            }
        } elseif (strlen($filters)) {
            $sql_where = $filters;
        } else {
            return '';
        }
        $sql_where = $sql_where ? " WHERE " . $sql_where : '';
        return $sql_where;
    }

    /**
     * 对sql语句进行预处理，同时对参数进行同步处理 ,以实现在调用时sql和参数多种占位符格式支持
     * 如 $where="id=1" , $params=1 处理成$where="id=:id",$params['id']=1
     *
     * @param string $where
     * @param array $params
     */
    public static function paramParse(&$where, &$params)
    {
        if (is_null($params)) {
            $params = array();
            return;
        }

        if (!is_array($params)) {
            $params = array(
                $params
            );
        }
        $_first = each($params);
        $tmp = array();
        if (!is_int($_first['key'])) {
            foreach ($params as $_k => $_v) {
                $tmp[":" . ltrim($_k, ":")] = $_v;
            }
        } else {
            preg_match_all("/`?([\w_]+)`?\s*[\=<>!]+\s*\?\s+/i", $where . " ", $matches, PREG_SET_ORDER);
            if ($matches) {
                foreach ($matches as $_k => $matche) {
                    $fieldName = ":" . $matche[1]; // 字段名称
                    $i = 0;
                    while (array_key_exists($fieldName, $params)) {
                        $fieldName = ":" . $matche[1] . "_" . ($i++);
                    }
                    $where = str_replace(trim($matche[0]), str_replace("?", $fieldName, $matche[0]), $where);
                    if (array_key_exists($_k, $params)) {
                        $tmp[$fieldName] = $params[$_k];
                    }
                }
            }
        }
        $params = $tmp;

        // ------------------------------------------
        // fix sql like: select * from table_name where id in(:ids)
        preg_match_all("/\s+in\s*\(\s*(\:\w+)\s*\)/i", $where . " ", $matches, PREG_SET_ORDER);

        if ($matches) {
            foreach ($matches as $_k => $matche) {
                $fieldName = trim($matche[1], ":");

                $_val = $params[$matche[1]];

                if (!is_array($_val)) {
                    $_val = explode(",", addslashes($_val));
                }

                $_tmpStrArray = array();
                foreach ($_val as $_item) {
                    $_tmpStrArray[] = is_numeric($_item) ? $_item : "'" . $_item . "'";
                }
                $_val = implode(",", $_tmpStrArray);
                $where = str_replace($matche[0], " In (" . $_val . ") ", $where);

                unset($params[$matche[1]]);
            }
        }
        // ==========================================
    }

    public static function beginTransaction($type = 'master')
    {
        self::$pdo = self::getPdo($type);
        return self::$pdo->beginTransaction();
    }

    public static function commitTransaction($type = 'master')
    {
        self::$pdo = self::getPdo($type);
        return self::$pdo->commit();
    }

    public static function rollbackTransaction($type = 'master')
    {
        self::$pdo = self::getPdo($type);
        return self::$pdo->rollBack();
    }
}
