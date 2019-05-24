<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/5/22
 * Time: 10:17
 */

namespace app\admin\controller;


use app\index\Controller;
use think\Db;
use think\Loader;

class Excel extends Controller
{
    public function ToExcel()
    {
        $path = dirname(__FILE__);

        Loader::import('PHPExcel.Classes.PHPExcel');
        Loader::import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        $Excel = new \PHPExcel();

        $PHPSheet = $Excel->getActiveSheet();
        $PHPSheet->setTitle("test");

        $PHPSheet->setCellValue("A1", "ID")
        ->setCellValue("B1", "测试")
        ->setCellValue("C1", "PATH")
        ->setCellValue("D1", "PCODE")
        ->setCellValue("E1", "PCODE")
        ->setCellValue("F1", "城市名");
        $data = Db::name('ml_tbl_withdraw')->select();

        $i = 2;
        foreach ($data as $k=>$v) {
            $PHPSheet->setCellValue("A" . $i, $v['id'])
                ->setCellValue("B" . $i, $v['uid'])
                ->setCellValue("C" . $i, $v['order_no'])
                ->setCellValue("D" . $i, $v['amount'])
                ->setCellValue("E" . $i, $v['ctime'])
                ->setCellValue("F" . $i, $v['desc']);
            $i++;
        }

        $PHPWriter = \PHPExcel_IOFactory::createWriter($Excel,'Excel2007');
        header('Content-Disposition: filename="表单数据.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');


        $PHPWriter->save("php://output",'w');
    }


}