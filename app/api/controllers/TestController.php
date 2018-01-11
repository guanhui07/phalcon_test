<?php
namespace app\api\controllers;

//
use app\models\Users;//
use app\library\Test;
use Phalcon\Paginator\Adapter\Model as Paginator;

class TestController extends \Phalcon\Mvc\Controller
{
    /*
     *
     *
     * $this->flash, $this->db 或者 $this->session.
$this->view ,
$this->config
$this->url
     * */
    //http://testph.com/test/getconfig
    public function indexAction()
    {
        //$redirect_url = $this->url->get("admin/Backup/backup?index=1&page=2");
        //echo BASE_PATH;die;
        //debug($this->config->toArray());//获取配置
        $rs = $this->db->fetchAll('select * from users', \Phalcon\Db::FETCH_ASSOC);//多条
        debug('from api');
        $rs = $this->db->fetchOne('select * from users limit 1', \Phalcon\Db::FETCH_ASSOC);//一条

        //$rs = $this->db->execute('INSERT INTO users (name,password,email) VALUES ( \'Nissan Versa\', \'aa\',\'fdsf@gmail.com\')');
        //$lastInsertId = $this->db->lastInsertId();
        //debug($lastInsertId);

        $this->session->set(
            "auth",
            [
                "id"   => 23,
                "name" => 't1',
            ]
        );

//        $this->flash->success(
//            "Welcome " . ' abc'
//        );

        //重定向
//        return $this->dispatcher->forward(
//            [
//                "controller" => "test",
//                "action"     => "t1",
//            ]
//        );


        //debug($this->request->isPost());

        //debug($this->request->getPost("email",'','a@gmail.com'));

        $menu = [
            ['href'=>'http://baidu.com','caption'=>'地址1'],
            ['href'=>'http://baidu2.com','caption'=>'地址2'],
            ['href'=>'http://baidu3.com','caption'=>'地址3'],
            ['href'=>'http://baidu4.com','caption'=>'地址4'],
        ];
        $post = [
            'title'=>'test tile',
            'content'=>'test content',
        ];

        //debug($_SESSION);
        //echo 'test1action';die;
        $this->view->postId = '112';//渲染视图
        $this->view->setVar("menu", 'test menu');
        $this->view->setVar("show_navigation", true);
        $this->view->setVar("menu", $menu);
        $this->view->setVar("title", 'title var');
        $this->view->setVar("post", $post);
        //$this->view->pick("products/search");//默认选择test/index.phtml 视图渲染
//        $this->view->render("products/search",[
//            'id'=>[],
//            'name'=>[],
//        ]);
        //$this->view->disable();//关闭视图渲染 //或则return false;

        //return $this->response->redirect("index/index"); //重定向
    }

    public function t1Action()
    {
        debug('from api');
        //new Test();
        //echo 1;die;
        //$user = new Users();
        //print_r($_GET);
        //$email    = $this->request->getPost("email");
        //echo $email;die;
        //print_r($this->request->getPost());die;
        $email='3rfdsf@163.com ';
        $password = 'fdsfdfdfff';
//        $user = Users::findFirst(
//            [
//                " email = :email AND password = :password ",
//                "bind" => [
//                    "email"    => $email,
//                    "password" => $password,
//                ]
//            ]
//        );
        $user = Users::findFirstById(3)->toArray();
        //WechatTopic:: find(array('article_id=' . intval($article_id), 'order' => 'topic_id ASC'))->toArray();
        echo '<pre>';
        debug($user);
    }

    public function t2Action()
    {
        $id= 4;
        $name='test';
//        $phql = "UPDATE users SET name = :name: WHERE id = :id:";
//        $status = Users::executeQuery(
//            $phql,
//            [
//                "id"   => $id,
//                "name" => $name,
//            ]
//        );
        $robot = Users::findFirst(3);

        $robot->name = "RoboCop";

        $status = $robot->save();
        var_dump($status);
        die;
    }
    public function f1Action()
    {
        $data = Users::find("name='zhangsan'")->toArray();

//        $data = Robots::find(
//            [
//                "name = :name: AND type = :type:",
//                "bind" => [
//                    "name" => "Robotina",
//                    "type" => "maid",
//                ],
//            ]
//        );
        print_r($data);
    }

    public function f2Action()
    {
        $robots = Users::query()
            ->where("name = :name")
            ->andWhere("password ='fdsfdfdfff'")
            ->bind(["name" => "zhangsan"])
            //->order("id")
            ->execute();
        var_dump($robots);
        die;
    }

    public function c1Action()
    {
        $rowcount = Users::count(
            [
                "name" => "zhangsan",
                //"group" => "area",
            ]
        );
        print_r($rowcount);
        die;
    }

    public function c2Action()
    { //error
        $phql = "select sum(id) from users";
        $row  = $this->modelsManager->executeQuery($phql)->getFirst();
        echo $row['summatory'];
    }

    public function insertAction()
    {
        $robot = new Users();


        $robot->name = "Astro Boy".mt_rand(1, 10000);
        $robot->email='test'.mt_rand(10000, 99999).'@163.com';
        $robot->password = md5(mt_rand(1, 1000));

        if ($robot->save() === false) { //create update
            $messages = $robot->getMessages();

            foreach ($messages as $message) {
                echo $message, "\n";
            }
            die;
        }
        echo 'ok';
        die;
    }

    public function insert2Action()
    {
        $phql = "INSERT INTO users VALUES (12, 'Nissan Versa', 'aa','fdsf@gmail.com')";

        $result = $this->modelsManager->executeQuery($phql);
        //$phql = "UPDATE Cars SET price = ?0, type = ?1 WHERE brands_id > ?2";
//        $manager->executeQuery(
//            $phql,
//            [
//                0 => 7000.00,
//                1 => 'Sedan',
//                2 => 5,
//            ]
//        );

        if ($result->success() === false) {
            echo 'ok';
        }
    }

    public function deleteAction()
    {
        $robot = Users::findFirst(7);

        if ($robot !== false) {
            if ($robot->delete() === false) {
                echo "Sorry, we can't delete the robot right now: \n";

                $messages = $robot->getMessages();

                foreach ($messages as $message) {
                    echo $message, "\n";
                }
                die;
            } else {
                echo "The robot was deleted successfully!";
                die;
            }
        }
    }

    public function deleteAllAction()
    {
        $robots = Users::find(
            [
                [
                    "name" => "zhangsan",
                ]
            ]
        );

        foreach ($robots as $robot) {
            if ($robot->delete() === false) {
                echo "Sorry, we can't delete the robot right now: \n";

                $messages = $robot->getMessages();

                foreach ($messages as $message) {
                    echo $message, "\n";
                }
            } else {
                echo "The robot was deleted successfully!";
            }
        }
    }

    public function updateAction()
    {
        $robot = Users::findFirst(8);
        $robot->name='test11';
        if ($robot->save() === false) {
            echo 'update failure';
        } else {
            echo 'update succ';
        }
    }
    //分页
    public function pageAction()
    {
        $products = Users::find("name='zhangsan'");
        $paginator = new Paginator(
            [
                "data"  => $products,   // 分页的数据
                "limit" => 1,           // 每页的行数
                "page"  => 1, // 查看的指定页
            ]
        );

        // 获取分页中当前页面
        $page = $paginator->getPaginate();
        print_r($page);
    }

    //事务
    public function s1Action()
    {
        $this->db->begin();
        //sql1
        $robot = new Users();

        $robot->name       = "WALL·E";
        //if false 回滚
        if ($robot->save() === false) {
            $this->db->rollback();
        }

        //sql2
        $robot = new Users();

        $robot->name       = "WALL·E";
        //if false 回滚
        if ($robot->delete() === false) {
            $this->db->rollback();
        }


        $this->db->commit();//提交
    }

    public function query1Action()
    {
        $query = $this->modelsManager->createQuery("SELECT * FROM users");
        $cars  = $query->execute();
        //print_r($cars);die;
//        $query = $this->modelsManager->createQuery("SELECT * FROM users WHERE name = :name:");
////缓存
//        $query->cache(
//            [
//                "key"      => "cars-by-name1",
//                "lifetime" => 300,
//            ]
//        );
//        $cars  = $query->execute(
//            [
//                "name" => "zhangsan",
//            ]
//        );
        //print_r($cars);die;
        foreach ($cars as $k=>$v) {
            echo $v->name,'<br />';
        }
    }

    public function query2Action()
    {
        $phql = "SELECT c.* FROM users AS c ORDER BY c.name"; //支持连表join

        $cars = $this->modelsManager->executeQuery($phql);
        foreach ($cars as $k=>$v) {
            echo $v->name,'<br />';
        }
    }

    public function query3Action()
    {
        //        $name='zhangsan';
//        $id=2;
//
//        $robots = $this->modelsManager->createBuilder()
//            ->from("users")
//            ->where("name = :name:", ["name" => $name])
//            ->andWhere("id = :id:", ["id" => $id])
//            ->getQuery()
//            ->execute();
//        print_r($robots);die;
    }

    public function cache1Action()
    {
        if ($this->view->getCache()->exists("downloads")) {
            $latest = Users::find(
                [
                    "order" => "id DESC",
                ]
            );

            $this->view->latest = $latest;
        }

        // Enable the cache with the same key "downloads"
        $this->view->cache(
            [
                "key" => "downloads",
            ]
        );
    }

    public function cache2Action()
    {
        $config = \Phalcon\DI::getDefault()->get('config')->toArray();//获取配置
        //debug($config);
        //$cache = $this->cache();
        $cache = $this->cache;

        $cache_key='test_key';
        $token = array(
            'akey'=>1,
            'bkey'=>2,
        );
        $cache->save($cache_key, json_encode($token));
        $token = $cache->get($cache_key);
        debug($token);
    }

    protected function cache()
    {
        $config = new \stdClass();
//        $this->_di->set('cache', function() use ($config) {
//        });

            // Get the parameters
            $frontCache = new \Phalcon\Cache\Frontend\Data(array('lifetime' => 300));
        $cache_path = APP_PATH . '/runtime/cache/';
        is_dir($cache_path) || mkdir($cache_path, 0777, true);
        $cache = new \Phalcon\Cache\Backend\File($frontCache, array('cacheDir' => $cache_path));
        return $cache;
    }
    public function conndbAction()
    {
        $params = array(
            "host" => 'localhost',
            "username" => 'postgres',
            "password" => '123456',
            "dbname" => 'test'
        );
        $connection = new \Phalcon\Db\Adapter\Pdo\Postgresql($params);
        debug($connection);
    }

    public function getConfigAction()
    {
        $config = \Phalcon\DI::getDefault()->get('config')->toArray();//获取配置

        include '/data1/slog.function.php';//nohup socketlog-server > /dev/null &
        //slog('hello ');
        //$config = new Phalcon\Config\Adapter\Php(APP_PATH . '/config/config.php');
        slog(APP_PATH . '/config/config.php');
        $config = include(APP_PATH."/config/config.php");
        debug($config);
    }

    public function getUrlAction()
    {
        $url = new \Phalcon\Mvc\Url();
    }

    public function getsessionAction()
    {
        $session = new \Phalcon\Session\Adapter\Files();
        $session->start();
        return $session;
    }

    public function getcookieAction()
    {
        $cookies = new Phalcon\Http\Response\Cookies();

        return $cookies;
    }
}
