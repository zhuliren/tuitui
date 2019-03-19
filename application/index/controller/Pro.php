<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 13:10
 */

namespace app\index\controller;


use think\Db;

class Pro
{
    public function proList()
    {
        $prolist = Db::query('SELECT a.`id`,a.`pro_name`,a.`pro_originator`,a.`pro_headimg`,b.`value` AS pro_state FROM xm_tbl_pro a LEFT JOIN xm_tbl_dictionary b ON a.`pro_state`=b.`id`');
        $data = array('status' => 0, 'msg' => '成功', 'data' => $prolist);
        return json($data);
    }

    public function proDetails()
    {
        $pro_id = $_REQUEST["pro_id"];
        $prodetails = Db::query('select * from xm_tbl_pro where id = ?', [$pro_id]);
        return json($prodetails);
    }
}