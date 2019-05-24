<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// 过滤掉emoji表情
function filter_Emoji($str)
{
    $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);

    return $str;
}

function preg_mobile($mobile) {
    if(preg_match("/^1[34578]\d{9}$/", $mobile)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function randomOrder_no()
{
    $time =date('Ymd',time());
    $rand_no = substr((lcg_value() * 100000000),0,8);
    $rand =  substr((lcg_value() * 10000),0,4);
    $res = $time.$rand_no.$rand;
    $num = rand(1,10);
    if (strstr($res,'.')){
        $res = str_replace('.',$num,$res);
    }
    return $res;
}

function namePreg($name)
{
    if (isset($name) && !empty($name)){
        if (preg_match("/^[\x{4e00}-\x{9fa5}]{3,6}$/u",$name)) {
//                    print("中文");
            return true;
        } else {
            return false;
//                    print("非中文");
        }
    }
}


/**
 * 创建(导出)Excel数据表格
 * @param  array   $list        要导出的数组格式的数据
 * @param  string  $filename    导出的Excel表格数据表的文件名
 * @param  array   $indexKey    $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
 * @param  array   $startRow    第一条数据在Excel表格中起始行
 * @param  [bool]  $excel2007   是否生成Excel2007(.xlsx)以上兼容的数据表
 * 比如: $indexKey与$list数组对应关系如下:
 *     $indexKey = array('id','username','sex','age');
 *     $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24));
 */
function toExcel($list,$filename,$indexKey,$startRow=1,$excel2007=false){
    //文件引入
    include  '../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
    include  '../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
    include  '../vendor/phpoffice/phpexcel/Classes/PHPExcel/Writer/Excel2007.php';

    \think\Loader::import('PHPExcel.PHPExcel');
    \think\Loader::import('PHPExcel.PHPExcel.IOFactory');

    // Loader::import('PHPExcel.PHPExcel.Writer.Excel2007');
    $objPHPExcel = new PHPExcel();


    ob_end_clean();
    if(empty($filename)) $filename = time();
    if( !is_array($indexKey)) return false;

    $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    //初始化PHPExcel()

    //设置保存版本格式
    if($excel2007){
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $filename = $filename.'.xlsx';
    }else{
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $filename = $filename.'.xls';
    }

    //接下来就是写数据到表格里面去
    $objActSheet = $objPHPExcel->getActiveSheet();

    $objPHPExcel->setActiveSheetIndex(0)                                                                            // set table header content
                     ->setCellValue('A1', 'ID')
                     ->setCellValue('B1', '用户id')
                     ->setCellValue('C1', '提现订单单号')
                     ->setCellValue('D1', '提现金额')
                     ->setCellValue('E1', '提现人姓名')
                     ->setCellValue('F1', '银行卡号')
                     ->setCellValue('G1', '联系号码')
                     ->setCellValue('H1', '提现时间')
                     ->setCellValue('I1', '描述');
    $startRow = 2;
    foreach ($list as $row) {
        foreach ($indexKey as $key => $value){
            //这里是设置单元格的内容
            $objActSheet->setCellValueExplicit($header_arr[$key].$startRow,$row[$value]);
        }
        $startRow++;
    }

    // 下载这个表格，在浏览器输出
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/vnd.ms-execl");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");;
    header('Content-Disposition:attachment;filename='.$filename.'');
    header("Content-Transfer-Encoding:binary");
    $objWriter->save('php://output');
}
