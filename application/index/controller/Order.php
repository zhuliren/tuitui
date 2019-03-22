<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 16:20
 */

namespace app\index\controller;

use app\index\model\UserModel;
use app\index\model\OrderModel;
use app\index\model\ProModel;
use think\Db;

class Order
{
    public function generateOrder()
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
        //判断同一代理权是否存在未支付订单
        $selectOrder = db('xm_tbl_order')->where('user_id', $user_id)->where('pro_id', $pro_id)->where('pay_state', 0)->where('order_state', 0)->find();
        if($selectOrder){
            $data = array('status' => 1, 'msg' => '本项目有未支付的订单，暂时无法继续购买', 'data' => '');
            return json($data);
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
        } else if ($card_surplus_num < $pro_card_num) {
            $data = array('status' => 1, 'msg' => '无法购买' . $pro_card_num . '个代理权', 'data' => '');
            return json($data);
        }
        //生成订单
        $orderModel = new OrderModel();
        //订单号
        $order_id = $orderModel->orderIdGenerate();
        //订单价格
        $pro_card_price = db('xm_tbl_pro_cardstage')->where('id', $pro_card_id)->value('card_price');
        $order_price = $pro_card_price * $pro_card_num;
        $orderdata = ['order_id' => $order_id, 'user_id' => $user_id, 'pro_id' => $pro_id, 'pro_card_id' => $pro_card_id, 'pro_card_num' => $pro_card_num, 'order_price' => $order_price,];
        Db::table('xm_tbl_order')->insert($orderdata);
        $returndata = array('order_id' => $order_id);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }
}