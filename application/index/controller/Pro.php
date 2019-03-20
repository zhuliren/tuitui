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
        //项目列表
        $prolist = Db::query('SELECT a.`id`,a.`pro_name`,a.`pro_originator`,a.`pro_headimg`,b.`value` AS pro_state ,COUNT(c.id) AS pro_innum FROM xm_tbl_pro a LEFT JOIN xm_tbl_dictionary b ON a.`pro_state`=b.`id` LEFT JOIN xm_tbl_pro_card c ON a.`id`=c.`pro_id`');
        $prolistnum = count($prolist);
        $datadetails = array('listnum' => $prolistnum, 'listdata' => $prolist);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $datadetails);
        return json($data);
    }

    public function proDetails()
    {
//        $user_id = $_REQUEST['userid'];
//        $pro_id = $_REQUEST['proid'];
        $user_id = 1;
        $pro_id = 1;
        //昨日时间和今日时间点
        //项目基础数据
        $stardatetime = date("Y-m-d", strtotime("-1 day"));
        $enddatetime = date("Y-m-d");
        $pro_data = Db::table('xm_tbl_pro_data')
            ->where('pro_id', $pro_id)
            ->whereTime('pro_datatime', 'between', [$stardatetime, $enddatetime])
            ->select();
        //昨日数据绑定
        $pro_datadetails = array();
        $n = 0;
        foreach ($pro_data as $arr) {
            $pro_datadetails[$n]['pro_dataname'] = Db::table('xm_tbl_dictionary')->where('id', $arr['pro_dataname'])->value('value');
            $pro_datadetails[$n]['pro_datavalue'] = $arr['pro_datavalue'];
            $pro_datadetails[$n]['pro_daygrow'] = 0;
            $pro_datadetails[$n]['pro_weekgrow'] = 0;
            $pro_datadetails[$n]['pro_mongrow'] = 0;
            $n++;
        }
        $pro_yesterday_datadetails = array('pro_datadetails_num' => $n, 'pro_datadetails' => $pro_datadetails);
        //项目信息
        $pro_finance = Db::table('xm_tbl_pro_finance')->where('pro_id', $pro_id)->find();
        #编写位置注释
        $prolist = Db::query('SELECT a.`pro_name`,a.`pro_originator`,b.`value` AS pro_state FROM xm_tbl_pro a LEFT JOIN xm_tbl_dictionary b ON a.`pro_state`= b.`id` where a.id= ? ',[$pro_id]);
        return json($pro_finance);
        //重组数据
        $userdetails = db('xm_tbl_user')->where('id', $user_id)->find();
        //判断用户权限
        if ($userdetails['up_code'] == null) {
            //普通用户

        } else {
            //被邀请用户
        }
        $pro_id = $_REQUEST["pro_id"];
        $prodetails = Db::query('select * from xm_tbl_pro where id = ?', [$pro_id]);
        $prodetails = array();
        array('status' => 0, 'msg' => '成功', 'data' => $prodetails);
        return json($prodetails);
    }
}