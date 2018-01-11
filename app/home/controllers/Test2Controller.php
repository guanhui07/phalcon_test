<?php
namespace app\home\controllers;

//
use app\models\Users;//
use app\library\Page;
use Phalcon\Paginator\Adapter\Model as Paginator;


use app\library\Upload;
use app\library\ThumtPic;
use app\library\Curl;

use app\library\Runtime;
//写
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

//读
use PhpOffice\PhpSpreadsheet\IOFactory;

class Test2Controller extends \Phalcon\Mvc\Controller
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
    public function uploadAction()
    {
    }

    //测试上传类
    public function doUploadAction()
    {
        $upload = new Upload();
        if ($path = $upload->up('image')) {
            echo '上传成功','<br />';
            echo "<img src='/".$path."' />",'<br />';
        } else {
            echo $upload->getErr();
        }
    }
    //测试缩略图类
    public function thumtPicAction()
    {
        debug(ThumtPic::makeThumb(ROOT.'app/data/images/2018/01/05/YkJwgV0jh9NZ_20180105145903.png', 100, 100));
    }
    //测试分页类
    public function pageAction()
    {
        //$page=new Page($cnt,$per);
        $page=new Page(100001, 10);
        //$sql="select * from good $page->limit";
        //->findAllBySql();
        $page=$page->fpage(array(1,2,3,4,5,6,7)); //array(3,4,5,6,7,8) 常用
        echo $page;
        die;
    }

    //测试curl类
    public function curlTestAction()
    {
        debug(Curl::curlGetContents('http://localhost'));
        debug(Curl::curlPostContents('http://localhost', ['test'=>1]));
    }

    public function t1Action()
    {
        try {
            if (empty($_GET['uid'])) {
                //
                throw new \Exception('test', 999);
            }
        } catch (\Exception $e) {
            echo $e->getCode();
            echo $e->getMessage();
        }
    }

    public function t2Action()
    {
        $db = $this->db;
        //$db->beginTransaction();
        $db->begin();
        //操作model执行sql
        if (1) {
            //$db->commitTransaction();
            $db->commit();
        } else {
            //$db->rollbackTransaction();
            $db->rollback();
        }
    }


    //将图片转换为base64数据流 test
    public function t9Action()
    {
        $img = './app/data/images/2017/12/26/c3UzSb.png';
        $image_data = fread(fopen($img, 'r'), filesize($img));
        $image_info = getimagesize($img);
        $base64_img = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        echo '<img src="' . $base64_img . '" />';
    }


    public function t3Action()
    {
        echo file_get_contents('php://input');
    }

    //页面静态化
    public function t4Action()
    {
        ob_start();
        echo file_get_contents('http://localhost');
        if (file_exists("./app/data/index.htm")) {//看静态index.htm文件是否存在
            $time=time(); //文件修改时间和现在时间相差?的话，直接导向htm文件，否则重新生成htm
            if ($time-filemtime("./app/data/index.htm")< 600) {
                header("Location:http://localhost");
            }
        }

        $temp=ob_get_contents();//读出缓冲区里的内容
        ob_end_clean();
        $fp=fopen("./app/data/index.htm", 'w');
        if (fwrite($fp, $temp)) {
            header("Location:http://localhost/test/app/data/index.htm");
        }
    }


    //redis乐观锁 test
    public function t8Action()
    {
        header("content-type:text/html;charset=utf-8");
        $redis = new \Redis();
        $result = $redis->connect('127.0.0.1', 6379);
        $mywatchkey = $redis->get("mywatchkey");
        $rob_total = 100;   //抢购数量
        if ($mywatchkey<$rob_total) {
            $redis->watch("mywatchkey");
            $redis->multi();

            //设置延迟，方便测试效果。
            sleep(5);
            //插入抢购数据
            $redis->hSet("mywatchlist", "user_id_".mt_rand(10000, 99999), time());
            $redis->set("mywatchkey", $mywatchkey+1);
            $rob_result = $redis->exec();
            if ($rob_result) {
                $mywatchlist = $redis->hGetAll("mywatchlist");
                echo "抢购成功！<br/>";
                echo "剩余数量：".($rob_total-$mywatchkey-1)."<br/>";
                echo "用户列表：<pre>";
                var_dump($mywatchlist);
            } else {
                echo "手气不好，再抢购！";
                exit;
            }
        }
    }

    //导出txt test
    public function t11Action()
    {
        $imei = [
            ['etldate'=>12,'imei'=>'test'],
            ['etldate'=>12,'imei'=>'test'],
            ['etldate'=>12,'imei'=>'test'],
            ['etldate'=>12,'imei'=>'test'],
        ];
        Header("Content-type:   application/octet-stream ");
        Header("Accept-Ranges:   bytes ");
        header("Content-Disposition:   attachment;   filename=QQ新安装IMEI.txt ");
        header("Expires:   0 ");
        header("Cache-Control:   must-revalidate,   post-check=0,   pre-check=0 ");
        header("Pragma:   public ");
        echo "总数\t\t\t".count($imei)."\r\n";
        echo "日期\t\t\t";
        echo "IMEI\r\n";

//        foreach($imei as $k => $v){
//            echo $v['etldate']."\t\t".$v['imei']."\r\n";
//
//        }
    }

    //测试spl类库
    public function t15Action()
    {
        $s = new SplStack();
        //入栈 出栈 后进先出,先进后出 子弹 煤球
        $s->push("test1\r\n");
        $s->push("test2\r\n");

        echo $s->pop();//test2
        echo $s->pop();

        //队列  先进先出  排队
        $s = new SplQueue();

        $s->enqueue('data1');
        $s->enqueue('data2');

        echo $s->dequeue();
        echo $s->dequeue();

        //固定长度数组
        $s=new SplFixedArray(10);
        $s[0]=11;
        $s[7]=33;
        var_dump($s);
    }

    public function t16Action()
    {
        echo DIRECTORY_SEPARATOR; // /
        echo PHP_SHLIB_SUFFIX;    // so
        echo PATH_SEPARATOR;      // :
        echo PHP_EOL; //换行符号
    }

    public function t19Action()
    {
        $str1=<<<SRC
<meta name="keywords" content="网站收录查询,站长查询,百度排名,百度权重查询,关键词排名查询,百度收录查询" />
SRC;

        echo $str1;
    }


    //写excel test
    public function t21Action()
    {
        //https://phpspreadsheet.readthedocs.io/en/develop/
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'HelloWorld !');

        $writer = new Xlsx($spreadsheet);
        $writer->save(ROOT.'hello_world.xlsx');
        echo 'ok';
    }
    //读excel test
    public function t22Action()
    {
        $inputFileName = ROOT.'/hello_world.xlsx';
        $spreadsheet = IOFactory::load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        var_dump($sheetData);
    }

    //页面执行时间 也可以用xhprof扩展
    public function t20Action()
    {
        $runtime= new Runtime;
        $runtime->start();

        //代码开始
        $a = 0;
        for ($i=0; $i<2; $i++) {
            sleep(1);
            $a += $i;
        }
        //代码结束

        $runtime->stop();
        echo "页面执行时间: ".$runtime->spent()." 毫秒";
    }
}
