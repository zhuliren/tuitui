<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/9
 * Time: 10:31
 */

namespace app\index\controller;


use app\index\model\UserModel;
use think\Db;

class Coupon
{
    public function myCouponList()
    {
        $user_id = $_REQUEST['userid'];
        $userModel = new UserModel();
        $user_type = $userModel->userIdentity($user_id);
        switch ($user_type) {
            case -1:
                $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
                return json($data);
            case 0:
                break;
            case 1:
                break;
            case 2:
                break;
        }
        //查询优惠券列表
        $pro_num = 0;
        //分组查询，先查询项目类型
        $selectprotype = db('xm_tbl_coupon')->field('pro_id,count(id)')->where('user_id', $user_id)->group('pro_id')->select();
        if ($selectprotype) {
            foreach ($selectprotype as $eachprocoupon) {
                //数据绑定
                $selectproname = db('xm_tbl_pro')->where('id', $eachprocoupon['pro_id'])->find();
                $procoupondetails[$pro_num] = array('pro_id' => $eachprocoupon['pro_id'], 'pro_name' => $selectproname['pro_name'], 'coupon_num' => $eachprocoupon['count(id)']);
                $pro_num++;
            }
            $returndata = array('pro_num' => $pro_num, 'pro_coupon_details' => $procoupondetails);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '当前无优惠券', 'data' => '');
            return json($data);
        }
    }

    public function couponDetails()
    {
        $user_id = $_REQUEST['userid'];
        $pro_id = $_REQUEST['proid'];
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
        //查询该项目下的优惠券
        $selectmyprocard = db('xm_tbl_coupon')->where(['user_id' => $user_id, 'pro_id' => $pro_id])->select();
        if ($selectmyprocard) {
            $coupon_num = 0;
            foreach ($selectmyprocard as $eachcoupon) {
                //数据绑定
                $procarddetails[$coupon_num] = array('coupon_id' => $eachcoupon['id'], 'coupon_type' => $eachcoupon['coupon_type'],
                    'discount' => $eachcoupon['discount'], 'par_value' => $eachcoupon['par_value'],
                    'last_time' => $eachcoupon['last_time'], 'coupon_name' => $eachcoupon['coupon_name'],
                    'coupon_value' => $eachcoupon['coupon_value'], 'selection' => false);
                $coupon_num++;
            }
            $returndata = array('coupon_num' => $coupon_num, 'mycouponlist' => $procarddetails);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '当前无优惠券', 'data' => '');
            return json($data);
        }
    }

    public function couponGiveTo()
    {
        $user_id = $_REQUEST['userid'];
        $to_user_id = $_REQUEST['touserid'];
        $coupon_list_string = $_REQUEST['couponlist'];
        $user_pwd = md5(md5($_REQUEST['userpwd']));
        //判断用户密码
        $selectuserpwd = Db::table('xm_tbl_user')->where('id', $user_id)->where('user_pwd', $user_pwd)->value('user_pwd');
        if ($selectuserpwd) {
            //分离数组
            $coupon_list = explode(",", $coupon_list_string);
            //遍历转让代理权
            foreach ($coupon_list as $eachcouponid) {
                //单条转让代理 查询id
                //插入历史表
                $data = ['coupon_id' => $eachcouponid, 'last_user_id' => $to_user_id, 'prev_user_id' => $user_id, 'creat_time' => date("Y-m-d H:i:s", time())];
                Db::table('xm_tbl_coupon_history')->insert($data);
                //修改代理权状态
                Db::table('xm_tbl_coupon')->where('id', $eachcouponid)->update(['user_id' => $to_user_id]);
            }
            $data = array('status' => 0, 'msg' => '转让成功', 'data' => '');
            return json($data);

        } else {
            $data = array('status' => 1, 'msg' => '密码错误', 'data' => '');
            return json($data);
        }
    }
}