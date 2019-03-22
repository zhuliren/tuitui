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
        $user_id = $_REQUEST['userid'];
        $pro_id = $_REQUEST['proid'];
        //昨日时间和今日时间点
        $stardatetime = date("Y-m-d", strtotime("-1 day"));
        $enddatetime = date("Y-m-d");
        //项目基础数据
        $pro_data = Db::table('xm_tbl_pro_data')
            ->where('pro_id', $pro_id)
            ->whereTime('pro_datatime', 'between', [$stardatetime, $enddatetime])
            ->select();
        //昨日数据绑定
        $pro_datadetails = array();
        $n = 0;
        $daygrow = 1;
        $weekgrow = 0.5;
        $mongrow = -2.7;
        foreach ($pro_data as $arr) {
            $pro_datadetails[$n]['pro_dataname'] = Db::table('xm_tbl_dictionary')->where('id', $arr['pro_dataname'])->value('value');
            $pro_datadetails[$n]['pro_datavalue'] = $arr['pro_datavalue'];
            $pro_datadetails[$n]['pro_daygrow'] = $daygrow;
            $pro_datadetails[$n]['pro_weekgrow'] = $weekgrow;
            $pro_datadetails[$n]['pro_mongrow'] = $mongrow;
            $n++;
        }
        $pro_yesterday_datadetails = array('pro_datadetails_num' => $n, 'pro_datadetails' => $pro_datadetails);
        //项目信息
        $pro_finance = Db::query('SELECT a.`pro_name`,a.`pro_originator`,a.`pro_company`,a.`pro_created_time`,b.`value` AS pro_state,c.`pro_value`,c.`pro_agentcard_used`,c.`pro_agentcard_num`  FROM xm_tbl_pro a 
LEFT JOIN xm_tbl_dictionary b ON a.`pro_state`= b.`id` 
LEFT JOIN xm_tbl_pro_finance c ON a.`id` = c.`pro_id` where a.id= ? ', [$pro_id]);
        //项目基础信息绑定
        $pro_info = isset($pro_finance[0]) ? $pro_finance[0] : array();
        //重组数据
        $userdetails = db('xm_tbl_user')->where('id', $user_id)->find();
        //判断用户权限
        if ($userdetails['up_code'] == null) {
            //普通用户
            $pro_cardsellinfo = array();
        } else {
            //被邀请用户
            $pro_cardinfo = Db::table('xm_tbl_pro_cardstage')->where('pro_id', $pro_id)->order('card_stage desc')->find();
            $pro_card_surplus = $pro_cardinfo['agentcard_num'] - $pro_cardinfo['agentcard_used'];
            $pro_card_id = $pro_cardinfo['id'];
            $pro_cardsellinfo = array('pro_card_price' => $pro_cardinfo['card_price'], 'pro_card_surplus' => $pro_card_surplus,'pro_card_id'=>$pro_card_id);
        }
        $prodetails = array('pro_yetdata' => $pro_yesterday_datadetails, 'pro_baseinfo' => $pro_info, 'pro_cardsellinfo' => $pro_cardsellinfo);
        $returndata = array('status' => 0, 'msg' => '成功', 'data' => $prodetails);
        return json($returndata);
    }
}