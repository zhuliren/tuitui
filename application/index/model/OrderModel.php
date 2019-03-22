<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 15:51
 */

namespace app\index\model;


use think\Db;
use think\Model;

class OrderModel extends Model
{
    public function orderIdGenerate()
    {
        do{
            $order_id = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            $selectodrderid = Db::table('xm_tbl_order')->where('order_id',$order_id)->find();
            //判断id是否重复
        }while($selectodrderid);
        return $order_id;
    }
}