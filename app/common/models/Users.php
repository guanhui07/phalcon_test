<?php
namespace app\models;
use app\models\ModelBase;
//
use Phalcon\Mvc\Model;

class Users extends ModelBase
{
    public $id;

    public $name;

    public $email;

    public $password;

    public function addTest(){ //add
//        $connection = $this->getWriteConnection();
//        $result = connection->insert($tablename,array_values($sql), $fields_array);
//
//        if(!$result) {
//            $this->error = $connection->getErrorInfo();
//        } else {
//            $lastInsertId = $this->lastInsertId();
//            // 当无法取得lastInsertId时，返回true，表示插入操作执行成功
//            $result = $lastInsertId ? $lastInsertId : true;
//        }
//        return $result ? $result : false;

    }

    public function delTest(){
//        $connection = $this->getWriteConnection();
//        $result = $connection->delete($tablename, $where);
//        return $result;
    }

    public function updateTest(){

    }
}
