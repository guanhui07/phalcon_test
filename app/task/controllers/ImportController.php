<?php
/* 数据交换控制器 */
namespace App\Task\Controllers;

use App\Models\Catalogs;
use App\Models\Compare;
use App\Models\Data;
use App\Models\Import;
use App\Models\Tasks;
use App\Models\ImportReport;
use App\Models\Datamodel;
use App\Models\DatamodelFields;
use App\Library\Input;

class ImportController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    /*
     * 导入
     * 
     */
    public function importAction()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '4048M');
        //获取需要的数据
        $import_id = $this->request->get('importid', 'int', 0);
        $input_data = Input::instance()->getInputData($import_id);

        if (!$input_data) {
            $this->ajaxReturn(array('status' => true, 'info' => '数据已导入完成或正在导入中！'));
        }

        $import = $input_data['import'];
        $catalog = $input_data['catalog'];
        $datamodel = $input_data['datamodel'];
        $attachment = $input_data['attachment'];

        $filepath = ROOT_PATH . "/files/" . $attachment['filepath'];//文件路径

        //检查字段,并获取所需要的字段
        $field_name_list = Input::instance()->checkField($import_id, $datamodel, $filepath);
        if (!$field_name_list) {
            $this->ajaxReturn(array('status' => false, 'info' => '导入文件字段不完整'));
        }

        $execl_data = get_excel_data($filepath, 2, $field_name_list);//读取数据

        if (empty($execl_data)) { //没有数据导入
            Input::instance()->updateData($import_id, array('status' => 5, 'msg' => '没有可导入数据'));
            $this->ajaxReturn(array('status' => false, 'info' => '导入文件缺少数据'));
        }

        //数据检查，获取检查后的数据
        $data = Input::instance()->checkData($import_id, $datamodel, $execl_data, $filepath);

        if ($data) {
            //设置数据源
            $update_import = ImportReport::findFirst($import['id']);
            $compare_table = Compare::instance()->createCompareTable($catalog['id']);
            //准备工作
            Compare::instance()->updateSequence($catalog['id']);

            $import_num = $signid_num = 0;
            $import_datas = array();
            foreach ($data as $key => $value) {
                $check_data = array_filter($value);
                if (empty($check_data)) {
                    continue;
                }

                $value['catid'] = $catalog['id'];
                $value['addtime'] = $import['add_time'] ? $import['add_time'] : time();
                $ukey = Compare::makeUKey($catalog['id'], $value);
                $signid = $catalog['client_id'] . $catalog['id'] . ($catalog['signid'] + $signid_num + 1);

                $import_datas[$ukey] = array(
                    'catid' => $catalog['id'],
                    'department_code' => $import['department_code'],
                    'userid' => $import['user_id'],
                    'pkey' => $key,
                    'ukey' => $ukey,
                    'data' => serialize($value),
                    'signid' => $signid,
                    'batches' => $import['id'],
                );

                $import_num++;
                $signid_num++;
                if ($import_num == 500) {
                    $result = Compare::instance($compare_table, true)->addAll($import_datas);
                    if ($result) {
                        $update_import->success_import_num = $update_import->success_import_num + $result;
                        $update_import->repeat_num = $import_num - $result;
                        $update_import->save();
                    } else {
                        $update_import->repeat_num = $import_num - $result;
                        $update_import->save();
                    }
                    $import_num = 0;
                    $import_datas = array();
                }
            }

            if ($import_num) {
                $result = Compare::instance($compare_table, true)->addAll($import_datas);
                if ($result) {
                    $update_import->success_import_num = $update_import->success_import_num + $result;
                    $update_import->repeat_num = $import_num - $result;
                    $update_import->save();
                    //更新signid
                    $catalog_sign = Catalogs::findFirst($catalog['id']);
                    $catalog_sign->signid = $catalog_sign->signid + $signid_num;
                    $catalog_sign->save();
                } else {
                    $update_import->repeat_num = $import_num - $result;
                    $update_import->save();
                }
            }
        }
        

        Input::instance()->updateData($import_id, array(
            'edd_time' => time(),
            'status' => 1,
            'checking' => 0
        ));

        Tasks::instance()->addTasks('task/Import/insert', 'catid=' . $catalog['id']);
        $this->ajaxReturn(array('status' => true, 'info' => '导入数据成功'));
    }

    /*
     * 从对比库拉取数据
     * 
     */
    public function insertAction()
    {
        ini_set('memory_limit', '4048M');
        $catid = $this->request->get('catid', 'int', 0);
        $id = $this->request->get('id', 'int', 0);

        if (!$catid) {
            $this->ajaxReturn(array('status' => false, 'info' => '目录ID不存在'));
        }

        if ($id) {
            $where = 'id > ' . $id;
        }

        $catalog = Catalogs::instance()->findFirst($catid);

        if (!$catalog) {
            $this->ajaxReturn(array('status' => false, 'info' => '目录信息不存在'));
        }

        $datamodel = Datamodel::instance()->findFirst($catalog->model_id);
        $data = Import::instance()->getIncrement($catid, 4000, 0, $where);

        if (empty($data)) {
            $this->ajaxReturn(array('status' => true, 'info' => '没有需要导入的数据'));
        }
        $fields = DatamodelFields::instance()->getFields($catalog->model_id);

        foreach ($data as $value) {
            $row = '';
            $single_data = unserialize($value['data']);
            $row['catid'] = $single_data['catid'] ? $single_data['catid'] : $value['catid'];
            $row['userid'] = $value['userid'];
            $row['department_code'] = $value['department_code'];
            $row['addtime'] = $single_data['addtime'] ? $single_data['addtime'] : time();
            $row['status'] = $value['status'] ? $value['status'] : 0;
            $row['signid'] = $value['signid'];
            foreach ($fields as $field) {
                $row[$field['name']] = $single_data[$field['name']] !== '' ? $single_data[$field['name']] : '';
            }
            $insert_data[] = $row;
            $last_id = $value['id'];
        }

        //进入基础库
        $result = Data::instance('c_' . $datamodel->name)->addAll($insert_data);
        if (!$result) {
            $error = Data::instance($catalog->model_name)->getError();
            $this->ajaxReturn(array('status' => false, 'info' => '导入基础数据库失败：' . $error));
        }

        if ($result) {
            Import::instance()->resetIncrement($catalog->id, $last_id, 0);
            $this->ajaxReturn(array('status' => true, 'info' => '拉取数据完成,继续', 'continue' => 1));
        }
    }
}
