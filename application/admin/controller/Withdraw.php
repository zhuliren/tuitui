<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/5/22
 * Time: 09:09
 */

namespace app\admin\controller;


use app\admin\Controller;
use think\Db;
use think\Request;

class Withdraw extends Controller
{
    public function getWithdrawList()
    {
        $sql = "SELECT W.id,W.uid,W.order_no,W.amount,BC.name,BC.card_id,BC.tel,W.ctime,W.desc FROM `ml_tbl_withdraw` AS W LEFT  JOIN `ml_tbl_user_bank_card` AS BC ON W.uid =  BC.uid WHERE W.pay_time = 0 ";
        $list = Db::query($sql);
        return json(['status'=>1001,'msg'=>'成功','data'=>$list]);
    }

    public function exportExcel()
    {
        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $data = self::getWithdrawList()->getData()['data'];

        foreach ($data as $k=>$v){
            if ($v['ctime']){
                $data[$k]['ctime'] = date('Y-m-d H:i:s',$v['ctime']);
            }
        }
        $indexKey = array_keys($data[0]);
        $name = date('Ymd',time()).rand(10000,9999);
        $res = toExcel($data,$name,$indexKey);

    }




}