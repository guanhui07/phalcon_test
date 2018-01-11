<?php
namespace App\Home\Controllers;

use app\controllers\ControllerCommon;
use App\Library\Commons;
use App\Models\Accounts;
use App\Library\Account;
use App\Models\Attachments;
use App\Models\Catalogs;
use App\Models\Datamodel;
use App\Models\Data;
use App\Models\Pkhs;
use App\Models\Qyc;
use App\Models\Xdcp;
use App\Models\Xymds;
use App\Models\Yhzc;


class ControllerBase extends ControllerCommon {
	
	protected $guest = null;
	protected $guest_url = null;
    protected $controller_name = null;

	
	/*
	 * 初始化
	 */
	protected function initialize() {
		parent::initialize();

        $this->redirect('admin/Index/index');

		$static_url = $this->config->site->static_uri;
		$this->view->setVars(array(
				'home_css_path'        => $static_url . 'home/css/',
				'home_js_path'         => $static_url . 'home/js/',
				'home_images_path'	   => $static_url . 'home/img/',
				'home_libs_path'	   => $static_url . 'home/libs/',
                'home_wechat_path'	   => $static_url . 'home/wechat/',
                'admin_images_path'	   => $static_url . 'admin/default/images/',
		));

		$this->controller_name = $this->dispatcher->getControllerName();
		$this->guest_url = $this->config->site->base_uri . 'Guest/';
		$this->view->setVars(array(
				'controller'	=> $this->controller_name,
				'admin_url'		=> $this->config->site->base_uri . 'admin/'
		));

        ##Account::instance()->autoLogout();
        //$controllers = array('Index','Register','Product');
        $controllers = array('Guest');
        if(!in_array($this->controller_name,$controllers)){
            ##$zxggs = \App\Models\Data::instance('news')->getDatas(10,5,0,'status = 1'); //通知公告
            ##$this->view->setVar('zxggs',$zxggs);
        }

        ##$this->guest = Account::instance()->getLogin();
        $this->guest = '';#
        $aid = (int)$this->request->get('aid');

        if($aid){
            ##$this->guest = \App\Models\Accounts::instance()->findFirst($aid);//25 27
        }
        $this->session->set('guest_account',$this->guest);

        if($this->guest){
            $new_acccountinfo = Accounts::findFirst($this->guest->id);
            if (empty($new_acccountinfo) || $this->guest->password !== $new_acccountinfo->password || $this->guest->mobile !== $new_acccountinfo->mobile){
                $this->guest = null;
                $this->session->remove('guest_account');
                $this->session->remove('guest_logintime');
            }
        }

        if($this->guest){
            $welname = $this->guest->username;
            if($this->guest->isPerson() && $this->guest->realname){
                $welname = $this->guest->realname;
            }
            if(!$this->guest->isPerson() && $this->guest->enterprise_name){
                $welname = $this->guest->enterprise_name;
            }
            $this->view->setVar('welname',$welname);
        }

        //金融信用信息基础数据库业务--央行业务链接
        $jrxy = '';
        #$jrxy= Catalogs::findFirst("name = 'jrxyxxjcsjkyw' ");//征信中心信用报告业务
        $jrxy ? $this->view->setVar('jr_catid',$jrxy->id): '';
    }
	

	/**
	 *
	 * @检测密码强度
	 * @param string $password
	 * @return int
	 *
	 */
	protected function testPassword($password)
	{
		return Accounts::testPassword($password);
	}

	/**
	 * 获取数据，并检查sql注入跟xss攻击
	 */
	protected function getPostCheck(){
		$data = $this->request->getPost();
		foreach ($data as $key => $value){
			if(is_string($value) && Commons::instance()->checkSqlXss(trim($value))){
				$result = array();
				$result['status'] = 'n';
				$result['info'] = '数据包括非法字符';
				//$result['key'] = $key;
				//$result['value'] = $value;
				$this->ajaxReturn($result);
			};
		}
		return $data;
	}

	protected function getGrxxinfo($account){
        if (!empty($account->card_photo)) {
            $attachments = Attachments::findFirst('id in (' . $account->card_photo . ')');
            $this->view->setVar('attachment', $attachments);
        }

        $this->view->setVar('jobs',Accounts::$jobs);
        $this->view->setVar('educations',Accounts::$educations);
        $this->view->setVar('gjjs',Accounts::$gjjs);
        $this->view->setVar('shebaos',Accounts::$shebaos);
        $this->view->setVar('fang_types',Accounts::$fang_types);
        $this->view->setVar('cars',Accounts::$cars);
        $this->view->setVar('credit_types',Accounts::$credit_types);
        $this->view->setVar('marrieds',Accounts::$married);

    }

    protected function getQyxxinfo($account){

        $this->view->setVar('up', unserialize($account->up_customer));
        $this->view->setVar('down', unserialize($account->down_customer));
    }

    /** 企业优惠政策 */
    public function  yhzcAction(){
        $result = Yhzc::instance()->get_qyyhzc($this->guest);

        $this->view->setVar('attachment_array', $result['attachment_array']);
            $this->view->setVar('yhzc_list', $result['yhzc_list']);
            $this->view->setVar('username', $this->guest->enterprise_name);

            $this->product_recommendAction(true);
    }

    /** 个人优惠政策 */
    public function  gryhzcAction(){
        $result = Yhzc::instance()->get_gryhzc($this->guest);

        $this->view->setVar('cun_name', $result['cun']);
        $this->view->setVar('yhzc_list', $result['yhzc_list']);
        $this->view->setVar('username', $this->guest->realname);
        $this->view->pick(CONTROLLER_NAME . '/yhzc');

        $this->product_recommendAction(true);
    }

    /** 智能产品推荐 */
    public function product_recommendAction($only_data = false)
    {
        $catid = Xdcp::instance()->getCatid();
        $catalog = Catalogs::findFirst($catid);
        $catalog || $this->error('指定的栏目不存在');
        $datamodel = Datamodel::findFirst($catalog->model_id);
        $datamodel || $this->error('指定的数据模型不存在');
        $model = Data::instance('c_' . $datamodel->name);

        $product_recommends = $this->guest->product_recommend();
        $product_recommends = $model->getDataList($product_recommends, $catalog->model_id);
        $this->view->setVar('lists', $product_recommends);

        if(!$only_data){
            $this->view->pick("$this->controller_name/cpsc_xdcp");
        }

    }
}