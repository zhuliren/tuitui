<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21
 * Time: 16:42
 */

namespace app\index\controller;


use app\index\model\ProModel;
use app\index\model\UserModel;

class ProCard
{
    public function proCardDetails()
    {
        $user_id = $_REQUEST['userid'];
        $pro_card_id = $_REQUEST['procardid'];
        $userModel = new UserModel();
        //判断用户属性
        $user_type = $userModel->userIdentity($user_id);
        switch ($user_type) {
            case -1:
                $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
                return json($data);
            case 0:
                $data = array('status' => 1, 'msg' => '无权限查看', 'data' => '');
                return json($data);
            case 1:
                break;
            case 2:
                break;
        }
        //返回代理权详情
        $selectprocard = db('xm_tbl_pro_cardstage')->where('id', $pro_card_id)->find();
        //判断代理权内容
        $card_info = array();
        if ($selectprocard['card_bonus'] != null) {
            $card_info_bonus = array('享受分红权' . '分红比例为' . $selectprocard['card_bonus']);
            $card_info = array_merge($card_info, $card_info_bonus);
        }
        if ($selectprocard['card_coupon_num'] != null) {
            $card_info_bonus = array('享受优惠券发放' . '优惠券数量为' . $selectprocard['card_coupon_num']);
            $card_info = array_merge($card_info, $card_info_bonus);
        }
        if ($selectprocard['card_discount'] != null) {
            $card_info_bonus = array('享受优惠折扣' . '折扣比例为' . $selectprocard['card_discount']);
            $card_info = array_merge($card_info, $card_info_bonus);
        }
        $card_surplus_num = $selectprocard['agentcard_num'] - $selectprocard['agentcard_used'];
        $returndata = array('card_price' => $selectprocard['card_price'], 'card_surplus_num' => $card_surplus_num, 'card_info' => $card_info);
        $data = array('status' => 0, 'msg' => 'test', 'data' => $returndata);
        return json($data);
    }

    public function proCardBuy()
    {
        $user_id = $_REQUEST['userid'];
        $pro_id = $_REQUEST['proid'];
        $pro_card_id = $_REQUEST['procardid'];
        $pro_card_num = $_REQUEST['procardnum'];
        $userModel = new UserModel();
        //判断用户属性
        $user_type = $userModel->userIdentity($user_id);
        switch ($user_type) {
            case -1:
                $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
                return json($data);
            case 0:
                $data = array('status' => 1, 'msg' => '无权限查看', 'data' => '');
                return json($data);
            case 1:
                $data = array('status' => 1, 'msg' => '无权限购买', 'data' => '');
                return json($data);
            case 2:
                break;
        }
        $proModel = new ProModel();
        $pro_state = $proModel->proStateSel($pro_id);
        //判断项目状态
        if ($pro_state != 1) {
            $data = array('status' => 1, 'msg' => '项目目前无法购买', 'data' => '');
            return json($data);
        }
        //判断代理权状态
        $selectprocard = db('xm_tbl_pro_cardstage')->where('id', $pro_card_id)->find();
        $card_surplus_num = $selectprocard['agentcard_num'] - $selectprocard['agentcard_used'];
        if ($card_surplus_num <= 0) {
            $data = array('status' => 1, 'msg' => '当前代理权已售完', 'data' => '');
            return json($data);
        }else if ($card_surplus_num < $pro_card_num){
            $data = array('status' => 1, 'msg' => '无法购买'.$pro_card_num.'个代理权', 'data' => '');
            return json($data);
        }
        //生成订单

    }
}