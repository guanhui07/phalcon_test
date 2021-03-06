<?php
namespace app\library;

class Csv
{
    //csv文件数据数组
    public static $fileArr = array();

    /**
     * 描述 : 操作csv内部数组
     * 注明 :
     *      row, col 为 数字=正负位置, null=结尾位置, false=插入操作, true=替换操作
     * 参数 :
     *      val : 数组=插入数据, false=删除数据
     *      row : 指定行位置, 默认null
     *      col : 指定列位置, 默认false
     */
    public static function addRow($val, $row = null, $col = false)
    {
        //引用数组
        $fileArr = &self::$fileArr;

        //对行操作
        if (is_bool($col)) {
            //整理数据
            $td = &self::arrFill($fileArr, $row < 1 ? 0 : $row, count($val));
            //初始化行
            $row === null && $row = $td['row'];

            array_splice($fileArr, $row, $col, array(&$val));
            //对列操作
        } elseif (is_bool($row)) {
            //整理数据
            $td = &self::arrFill($fileArr, count($val), $col < 1 ? 0 : $col);
            //初始化列
            $col === null && $col = $td['col'];
            //初始数据
            for ($i = $col - count($val); $i > 0; $i--) {
                $val[] = '';
            }

            reset($fileArr);
            foreach ($val as &$v) {
                array_splice($fileArr[key($fileArr)], $col, $row, array(&$v));
                next($fileArr);
            }
        }
    }

    /**
     * 描述 : 整理csv数组成字符串
     * 参数 :
     *      path    : 字符串=保存到磁盘路径,默认=null
     *      charset : 转化的字符集, 默认 "UTF-8"
     * 返回 :
     *      返回 生成的字符串
     */
    public static function &toString($path = null, $charset = 'UTF-8')
    {
        //字符串列
        $result = array();
        //引用数组
        $fileArr = &self::$fileArr;

        self::arrFill($fileArr);
        foreach ($fileArr as $vs) {
            foreach ($vs as &$v) {
                if (is_string($v)) {
                    //数字类型
                    if (is_numeric($v)) {
                        //防止科学记数法
                        $v .= "\t";
                    } else {
                        //字符串替换
                        $v = str_replace('"', '""', $v);
                        //编码转换
                        $charset === 'UTF-8' || $v = iconv('UTF-8', $charset . '//IGNORE', $v);
                    }
                }
            }
            $result[] = '"' . join('","', $vs) . '"';
        }

        $result = join("\r\n", $result);
        //保存到文件
        //$path && of_base_com_disk::file($path, $result);

        return $result;
    }

    /*
     * 描述:以指定的文件名下载csv
     * 参数:
     *      filename : 字符串=文件名
     *      charset  : 转化的字符集, 默认 "UTF-8"
     * 示例:
     *      $ExcelExportObj=new self;
     *      $ExcelExportObj->download('测试.csv');
     *      将弹出'测试.csv'下载框
     */
    public static function download($filename = 'download', $charset = 'UTF-8')
    {
        //需要发送头信息
        static $sendHead = true;
        //引用数组
        $fileArr = &self::$fileArr;
        is_array($filename) && $fileArr[] = $filename;

        if ($sendHead === true) {
            //永不超时
            ini_set('max_execution_time', 0);
            //默认文件名
            is_string($filename) || $filename = 'download';
            //UTF8 文件名
            strpos($_SERVER["HTTP_USER_AGENT"], 'Firefox') || $filename = rawurlencode($filename);
            //字符集
            $sendHead = $charset;

            /*header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream');
            header('Content-type: application/vnd.ms-excel');*/
            //下载头
            header('Content-Type: application/download');
            //二进制
            header('Content-Transfer-Encoding: binary');
            //文件名
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            //不缓存
            header('Pragma:no-cache');
        }

        if (!empty($fileArr)) {
            echo self::toString(null, $sendHead), "\r\n";
            $fileArr = array();
        }
    }



    /**
     * 描述 : 数组填充
     * 参数 :
     *     &arr : 格式数据
     *      row : 最小行数, 默认=0
     *      col : 最小列数, 默认=0
     * 返回 :
     *      {"row" : 最大行数, "col" : 最大列数}
     * 作者 : Edgar.lee
     */
    private static function &arrFill(&$arr, $row = 0, $col = 0)
    {
        //最大行
        for ($i = $row - count($arr); $i > 0; $i--) {
            $arr[] = array();
        }
        //最大值
        $result = array('row' => count($arr), 'col' => 0);

        if (!empty($arr)) {
            //最大列
            $col > ($result['col'] = max(array_map('count', $arr))) && $result['col'] = $col;
            //填充列
            foreach ($arr as &$v) {
                for ($i = $result['col'] - count($v); $i > 0; $i--) {
                    $v[] = '';
                }
            }
        }

        return $result;
    }
}
/*
 *
 header("Content-type:application/csv;charset=utf-8");
        Csv::download('储位导入模板' . date('Ymd', time()) . rand(0, 9999));
        Csv::download(array(
            '储位类型(arrival=到货区, receiving=收货区, inventory=存货区, stockout=出库区)',
            '储位编码(按仓库实际顺序录入)',
            '储位名称',
        ));


//

header("Content-type:application/csv;charset=utf-8");
        Csv::download('order_fedex_' . date('YmdHis', time()));
        $header = 'company,contact,address1,address2,city,state,zip,package weight,customer referrance,customer referrance1,Scustomer referrance2,order_id';
        Csv::download(explode(",", $header));
            $result = self::getPrintList($_GET['shipping']);
            if ($result['state'] == 200 && $result['data']) {
                foreach ($result['data'] as $val) {
                    of_base_com_csv::download($val);
                }
            }


 * */
