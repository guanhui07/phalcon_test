<?php
namespace App\Task\Controllers;

use App\Models\Xdzc;
use App\Models\Xdyq;
use App\Models\Qyxdyq;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        echo 'this is task';
        exit;
    }

    /* 设置信贷政策过期 */
    public function setXdzcExpireAction()
    {
        $count = Xdzc::instance()->setExpire();
        echo '过期' . $count . '条政策';
    }

    /* 个人信贷逾期 */
    public function find_gryqAction()
    {
        Xdyq::instance()->find_gryq();
        $this->success('刷新完成', $this->url->get('admin/' . CONTROLLER_NAME . '/gryq/?refresh=1'));
    }

    /* 企业贷逾期 */
    public function find_qyyqAction()
    {
        Qyxdyq::instance()->find_qyyq();
        $this->success('刷新完成', $this->url->get('admin/' . CONTROLLER_NAME . '/qyyq/?refresh=1'));
    }
}
