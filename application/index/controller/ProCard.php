<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21
 * Time: 16:42
 */

namespace app\index\controller;

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

    public function myCardList()
    {
        $user_id = $_REQUEST['userid'];
        $userModel = new UserModel();
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
        $mycardlist = array();
        $pro_num = 0;
        //分组查询，先查询项目类型
        $selectprotype = db('xm_tbl_pro_card')->field('pro_id,count(id)')->where('user_id', $user_id)->group('pro_id')->select();
        if ($selectprotype) {
            foreach ($selectprotype as $eachprocard) {
                $pro_num++;
                //数据绑定
                $selectproname = db('xm_tbl_pro')->where('id', $eachprocard['pro_id'])->find();
                $procarddetails = array('card_name' => $selectproname['pro_name'], 'card_num' => $eachprocard['count(id)']);
                $mycardlist = array_merge($mycardlist, $procarddetails);
            }
            $returndata = array('pro_num' => $pro_num, 'pro_card_details' => $mycardlist);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '当前无代理权', 'data' => '');
            return json($data);
        }
    }
}