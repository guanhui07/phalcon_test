<?php

namespace App\Models;
use \Phalcon\Mvc\Model;

class ModelBase extends Model { 
    protected static $_instance = array();
    protected $error;
    protected $primary;
    public $tableName = '';

    /**
     * 实例化一个模型
     * @param string $name 表名
     * @param boolean $external 是否外部数据库模型
     * @return object 模型实例
     */
    public static function instance($name = '', $external = false) {
        $classname = get_called_class();    // 解决继承类的实例化
        $key = $classname . '#' . ($name ? $name : '_');
        if (empty(self::$_instance[$key])) {
            $instance = new $classname;
            if($name) {
                $table_prefix = $external ? '' : \Phalcon\DI::getDefault()->get('config')->database->table_prefix;
                $tablename = $table_prefix . $name;
                $instance->tableName = (DB_TYPE == 'oci') ? strtoupper($tablename) : strtolower($tablename);
                $instance->setSource($instance->tableName);
            } else {
                $instance->tableName = $instance->getSource();
            }
            self::$_instance[$key] = $instance;
        }
        return self::$_instance[$key];
    }
    
    /**
     * 单条插入，批量插入建议使用addAll方法
     * @param array $data 键值数组
     * @return int/boolean 成功返回插入ID值，失败返回false
     */
    public function add($data) {
        $columns = $this->getColumns();
        $fieldnames = $columns['names'];
        $this->primary = $primaryKey = $columns['primary'];
        $fields = array_intersect($fieldnames, array_keys($data));
        if(empty($fields)) {
            $this->error = 'There is no available fields!';
            return false;
        }
        $sql = array();
        foreach ($fields as $field) {
            $sql[$field] = $data[$field];
        }
        try {
            $connection = $this->getWriteConnection();
            $db_type = $connection->getType();
            $tablename = ($db_type == 'oci') ? strtoupper($this->getSource()) : $this->getSource();
            ($db_type == 'oci') && ($fields = array_map('strtoupper', $fields));
            $result = $connection->insert($tablename, array_values($sql), $fields);
            if(!$result) {
                $this->error = $connection->getErrorInfo();
            } else {
                $lastInsertId = $this->lastInsertId();
                // 当无法取得lastInsertId时，返回true，表示插入操作执行成功
                $result = $lastInsertId ? $lastInsertId : true;
            }
        } catch (\PDOException $ex) {
            $this->error = $ex->getMessage();
        }
        return $result ? $result : false;
    }
    
    /**
     * 批量插入
     * @param array $data 二维数组
     * @return boolean 成功返回true，失败返回false
     */
    public function addAll($data) {
        $firstRow = reset($data);
        if(!is_array($firstRow)) {
            return $this->add($data);
        }
        $columns = $this->getColumns();
        $fieldnames = $columns['names'];
        $this->primary = $columns['primary'];
        
        $fields = array_intersect($fieldnames, array_keys($firstRow));
        if(empty($fields)) {
            $this->error = 'There is no available fields!';
            return false;
        }
        $unseted = array_diff($columns['required'], $fields);
        if(sizeof($unseted)) {
            $this->error = 'Fields (' . implode(',', $unseted) . ') is required';
            return false;
        }
        
        //判断数据库类型。使用不同的拼凑方式
        $connection = $this->getWriteConnection();
        if ($connection->getType() == 'oci') {
            $num = 1;
            $data_count = count($data);
            foreach($data as $row) {
                $value = array();
                foreach($fields as $fieldname) {
                    $value[] = $this->sqlValidateValue($row[$fieldname]);
                }

                if ($data_count == $num) {
                    $values .= 'select ' . implode(',', $value) . ' from dual ';
                } else {
                    $values .= 'select ' . implode(',', $value) . ' from dual union all ';
                }
                $num++;
            }
            $sql = 'INSERT INTO ' . $this->getSource() . ' (' . implode(',', $fields) . ') ' . $values;

        } else {
            $values = array();
            foreach($data as $row) {
                $value = array();
                foreach($fields as $fieldname) {
                    $value[] = $this->sqlValidateValue($row[$fieldname]);
                }
                $values[] = '(' . implode(',', $value) . ')';
            }
            $sql = 'INSERT INTO ' . $this->getSource() . ' (' . implode(',', $fields) . ') VALUES ' . implode(',', $values);
        }
        
        try {
            $result = $connection->execute($sql);
            $result || ($this->error = implode('', $connection->getErrorInfo()));
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $result ? $connection->affectedRows() : false;
    }
    
    /* 删除 */
    public function del($where = '') {
        if($where) {
            try {
                $connection = $this->getWriteConnection();
                $tablename = ($connection->getType() == 'oci') ? strtoupper($this->getSource()) : $this->getSource();
                $result = $connection->delete($tablename, $where);
                $result || ($this->error = implode('', $connection->getErrorInfo()));
            } catch (\PDOException $e) {
                $this->error = $e->getMessage();
            }
        } else {
            $this->error = 'Variable $where is empty';
        }
        return $result ? true : false;
    }
    
    /**
     * 获取字段信息
     * @param bool $nocache 强制更新缓存
     * @return array 包含字段信息的数组
     */
    public function getColumns($nocache = false) {
        static $columns = array();
        $this->tableName && $this->setSource($this->tableName);
        $this->tableName || ($this->tableName = $this->getSource());
        if(isset($columns[$this->tableName]) && $nocache === false) return $columns[$this->tableName];
        $connection_service = $this->getReadConnectionService();
        $cache_key = $connection_service . '_' . $this->tableName . '.cloumns.cache';
        $cache = \Phalcon\DI::getDefault()->get('cache');
        $columns[$this->tableName] = $cache->get($cache_key);
        if($columns[$this->tableName] === null || $nocache === true) {
            $connection = $this->getReadConnection();
            if($connection->getType() == 'oci') {
                $columns[$this->tableName] = $this->_oci_columns();
            } else {
                $fields = $names = $required = $columns[$this->tableName] = array();
                $rows = $connection->describeColumns($this->tableName);
                if(is_array($rows) && sizeof($rows)) {
                    foreach($rows as $row) {
                        $isPrimary = $row->isPrimary();
                        $name = strtolower($row->getName());
                        $col = array(
                            'name'      => $name,
                            'type'      => $row->getType(),
                            'size'      => $row->getSize(),
                            'notnull'   => $row->isNotNull(),
                            'numeric'   => $row->isNumeric(),
                            'default'   => $row->getDefault(),
                            'primary'   => $isPrimary,
                        );
                        $fields[$name] = $col;
                        $names[] = $col['name'];
                        $isPrimary && ($this->primary = $col['name']);
                        ($col['notnull'] && is_null($col['default'])) && ($required[] = $col['name']);
                    }

                    $columns[$this->tableName] = array(
                        'fields'    => $fields, 
                        'names'     => $names, 
                        'required'  => $required, 
                        'primary'   => $this->primary
                    );
                }
            }
            if($columns[$this->tableName]) {
                $cache->save($cache_key, $columns[$this->tableName]);
            }
        }
        return $columns[$this->tableName];
    }
    
    /**
     * Oracle 下获取字段信息
     */
    private function _oci_columns() {
        $tablename = $this->getSource();
        $connection = $this->getReadConnection();
        $rows = $connection->fetchAll('SELECT * FROM USER_TAB_COLUMNS WHERE TABLE_NAME=UPPER(\'' . $tablename . '\')', \Phalcon\Db::FETCH_ASSOC);
        if(empty($rows)) return false;
        $sql = 'select ucc.* from user_cons_columns ucc, user_constraints uc ';
        $sql .= "where ucc.table_name = UPPER('{$tablename}') AND uc.table_name=UPPER('{$tablename}') AND ucc.CONSTRAINT_NAME=uc.CONSTRAINT_NAME AND uc.constraint_type='P'";
        $prow = $connection->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC);
        !empty($prow) && ($this->primary = strtolower($prow['COLUMN_NAME']));
        $fields = $names = $required = array();
        $types = array(
            'INT' => \Phalcon\Db\Column::TYPE_INTEGER,
            'CHAR' => \Phalcon\Db\Column::TYPE_CHAR,
            'NUMBER' => \Phalcon\Db\Column::TYPE_INTEGER,
            'FLOAT' => \Phalcon\Db\Column::TYPE_FLOAT,
            'VARCHAR' => \Phalcon\Db\Column::TYPE_VARCHAR,
            'VARCHAR2' => \Phalcon\Db\Column::TYPE_VARCHAR,
        );
        foreach ($rows as $row) {
            $name = strtolower($row['COLUMN_NAME']);
            $isPrimary = ($name == $this->primary) ? true : false;
            $type = isset($types[$row['DATA_TYPE']]) ? $types[$row['DATA_TYPE']] : \Phalcon\Db\Column::TYPE_TEXT;
            $col = array(
                'name'      => $name,
                'type'      => $type,
                'size'      => $row['DATA_LENGTH'],
                'notnull'   => ($row['NULLABLE'] == 'N') ? true : false,
                'numeric'   => ($type == \Phalcon\Db\Column::TYPE_INTEGER || $type == \Phalcon\Db\Column::TYPE_FLOAT) ? true : false,
                'default'   => $row['DATA_DEFAULT'],
                'primary'   => $isPrimary,
            );
            $fields[$name] = $col;
            $names[] = $name;
            ($col['notnull'] && is_null($col['default'])) && ($required[] = $col['name']);
        }
        $columns = array(
            'fields'    => $fields, 
            'names'     => $names, 
            'required'  => $required, 
            'primary'   => $this->primary
        );
        return $columns;
    }
    
    /**
     * 最后插入ID
     * @param string $sequence 序列名称，PGSQL支持
     * @return int ID值
     */
    public function lastInsertId($sequence = NULL) {
        $connection = $this->getWriteConnection();
        $dbType = $connection->getType();
        if(($dbType == 'pgsql') && $this->primary && !$sequence) {
            $row = $this->getReadConnection()->fetchOne("select pg_get_serial_sequence('{$this->tableName}','{$this->primary}') as seq");
            $sequence = $row['seq'];
            $lastInsertId = $connection->lastInsertId($sequence);
        } elseif ($dbType == 'oracle' || $dbType == 'oci') {
            $row = $this->getReadConnection()->fetchOne("SELECT {$this->tableName}.currval currval FROM dual");
            $lastInsertId = $row->currval;
        }
        return $lastInsertId ? $lastInsertId : $connection->lastInsertId();
    }
    
    /**
     * 获取错误信息
     * @return string 错误信息
     */
    public function getError() {
        return is_array($this->error) ? implode('', $this->error) : $this->error;
    }
    
    /**
     * 获取SQL执行情况
     * @return array 
     */
    public function getSQLStatement() {
        $profiles = \Phalcon\DI::getDefault()->get('profiler')->getProfiles();
        $statement = array();
        if(is_array($profiles)) {
            foreach ($profiles as $profile) {
                $statement['sql'] = $profile->getSQLStatement();
                $statement['start_time'] = $profile->getInitialTime();
                $statement['final_time'] = $profile->getFinalTime();
                $statement['total_time'] = $profile->getTotalElapsedSeconds();
            }
        }
        return $statement;
    }
    
    public function sqlValidateValue($var) {
        $var = $this->sqlEscape($var);
        if (is_null($var)) {
            return 'NULL';
        } else if (is_string($var)) {
            return "'" . $var . "'";
        } else {
            return (is_bool($var)) ? intval($var) : $var;
        }
	}
    
    public function sqlEscape($string) {
        $db_type = $this->getReadConnection()->getType();
        if ($db_type == 'pgsql') {
            return $string;
//            return pg_escape_string($string);
        } else if ($db_type == 'oci') {
            return str_replace(array("'", "\0"), array("''", ''), $string);
        } else {
            return mysql_real_escape_string($string);
        }
    }
    
    /* 
     * 检测数据库连接信息
     * @param array $params 数据库连接信息
     * @return boolean 连接成功返回true，失败返回错误信息
     */
    public static function testConnect($params = array()) {
        try {
            $type = $params['dbtype'];
            $config = \Phalcon\DI::getDefault()->get('config');
            unset($params['dbtype']);
            switch($type) {
                case 'mysql':
                    // $charset = $config->database->charset;
                    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql($params);
                    break;
                case 'postgresql':
                    $params['schema'] = $config->database->schema;
                    $connection = new \Phalcon\Db\Adapter\Pdo\Postgresql($params);
                    break;
                case 'oracle':
                    $params['charset'] = $config->database->charset;
                    $connection = new \Phalcon\Db\Adapter\Pdo\Oracle($params);
                    break;
                case 'sqlite':
                    $connection = new \Phalcon\Db\Adapter\Pdo\Sqlite($params);
                    break;
                case 'mssql':
                    $connection = mssql_connect($params['host'], $params['username'], $params['password']);
                    if(!mssql_select_db($params['dbname'],$connection)) {
                        return false;
                    }
                    break;
                case 'sybase':
                    $connection = @sybase_connect($params['host'], $params['username'], $params['password']);
                    if(!$connection) {
                        return false;
                    }
                break;
            }
            return $connection ? true : false;
        } catch (\PDOException $e) {
            $error = (string)$e->getMessage();
            return $error;
        }
    }
    
    /**
     * 设置数据源连接
     */
    public function setDbSource($dbsource_id) {
        if(!is_numeric($dbsource_id)) {
            $row = Dbsource::findFirst(array('guid=:guid:', 'bind' => array('guid' => $dbsource_id)));
            $dbsource_id = (int)$row->id;
        }
        $connectionService = $this->createConnection($dbsource_id);
        $connectionService && $this->setConnectionService($connectionService);
        return $this;
    }
    
    /**
     * 创建连接
     * @param int $dbsource_id 数据源ID
     * @param boolean $returnConnection 返回连接
     */
    public static function createConnection($dbsource_id, $returnConnection = false) {
        $connect_name = 'dbSource' . $dbsource_id;
        $dbsource = Dbsource::instance()->findFirst($dbsource_id);
        if(!$dbsource) return false;
        $params = $dbsource->toArray();
        $di = \Phalcon\DI::getDefault();
        $config = $di->get('config');
        if($di->has($connect_name)) {
            return $returnConnection ? $di->get($connect_name) : $connect_name;
        }
        try {
            $di->set($connect_name, function() use ($params, $config) {
                $dbtype = $params['dbtype'];
                unset($params['dbtype'], $params['id'], $params['guid']);
                switch ($dbtype) {
                    case 'mysql':
                        // $charset = $this->config->database->charset;
                        $connection = new \Phalcon\Db\Adapter\Pdo\Mysql($params);
                        break;
                    case 'postgresql':
                        $params['schema'] = $config->database->schema;
						unset($params['title']);
                        $connection = new \Phalcon\Db\Adapter\Pdo\Postgresql($params);
                        break;
                    case 'oracle':
                        $params['charset'] = $config->database->charset;
                        $connection = new \Phalcon\Db\Adapter\Pdo\Oracle($params);
                        break;
                    case 'sqlite':
                        $connection = new \Phalcon\Db\Adapter\Pdo\Sqlite($params);
                        break;
                    case 'mssql':
                        if(!vendor('Mssqldb#class')) return false;
                        $connection = new \Mssql($params['host'], $params['username'], $params['password'], $params['dbname']);
                        if(!$connection->isConnected()) {
                            $connection = false;
                        }
                        break;
                    case 'sybase':
                        if(!vendor('Sybasedb#class')) return false;
                        $connection = new \Sybase($params['host'], $params['username'], $params['password'], $params['dbname']);
                        if(!$connection->isConnected()) {
                            $connection = false;
                        }
                        break;
                }
                return $connection;
            });
            return $returnConnection ? $di->get($connect_name) : $connect_name;
        } catch(\PDOException $e) {
            return false;
        }
    }
    
    /**
     * 重置数据表 
     * @param string $name 数据表
     * @param boolean $external 是否外部数据库模型
     * @return model
     */
    public function resetSource($name = NULL, $external = false) {
        if($name) {
            $table_prefix = $external ? '' : $this->getDI()->get('config')->database->table_prefix;
            $tablename = $table_prefix . $name;
            //
            $this->tableName = strtolower($tablename);
            //$this->tableName = ($this->getReadConnection()->getType() == 'oci') ? strtoupper($tablename) : strtolower($tablename);
        }
        $this->setSource($this->tableName);
        return $this;
    }
    
    /* 清空数据表 */
    public function truncate() {
        $sql = 'TRUNCATE TABLE ' . $this->getSource();
        return $this->getWriteConnection()->execute($sql);
    }
    
    /* 建立column map */
    public function columnMap() {
        $columns = $this->getColumns();
        $map = array();
        if(is_array($columns['names'])) {
            foreach($columns['names'] as $field) {
                $map[$field] = $field;
            }
        }
        if($this->getReadConnection()->getType() == 'oci') {
            $map = array_change_key_case($map, CASE_UPPER);
            $map['PHALCON_RN'] = 'phalcon_rn';
        }
        return $map;
    }
    
    /* 重写sum，以支持oracle数据库 */
    public static function sum($parameters = array()) {
        if(DB_TYPE == 'oci') {
            if(!$parameters['column']) return false;
            $classname = get_called_class();
            $instance = new $classname;
            $tablename = $instance->getSource();
            $sql = "SELECT SUM({$parameters['column']}) AS SUMATORY FROM $tablename";
            if($parameters[0]) {
                $sql .= " WHERE {$parameters[0]}";
            }
            $result = $instance->getReadConnection()->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, $parameters['bind']);
            return $result['SUMATORY'];
        } else {
            return parent::sum($parameters);
        }
    }
    
    /* 重写count，以支持oracle数据库 */
    public static function count($parameters = NULL) {
        if(DB_TYPE == 'oci') {
            $classname = get_called_class();
            $instance = new $classname;
            $tablename = $instance->getSource();
            $sql = "SELECT COUNT(*) AS ROWCOUNT FROM $tablename";
            is_array($parameters) || $parameters = array($parameters);
            if($parameters[0]) {
                $sql .= " WHERE {$parameters[0]}";
            }
            $result = $instance->getReadConnection()->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, $parameters['bind']);
            return (int)$result['ROWCOUNT'];
        } else {
		
            return parent::count($parameters);
        }
    }
	
    /*
    public function getSource() {
        return $this->tableName;
    }*/

    /**
     * 根据$list获得ids
     * @param $list
     * @param string $format 指定返回值为字符串或者数组
     * @param string $id 默认id，或者user_id等
     * @return array|string
     */
    public  function get_ids($list, $format = 'string', $id = 'id'){
        $ids = array();
        foreach ($list as $info) {
            if(is_array($info)){
                $ids[] = $info[$id];
            }else{
                $ids[] = $info -> $id;
            }
        }
        if($format == 'string'){
            return implode(',',$ids);
        }
        return $ids;
    }
    
}