<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21
 * Time: 13:48
 */

namespace app\index\model;


use think\Db;
use think\Model;

class UserModel extends Model
{
    //生成邀请码
    public function generateCode()
    {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0, 25)]
            . strtoupper(dechex(date('m')))
            . date('d')
            . substr(time(), -5)
            . substr(microtime(), 2, 5)
            . sprintf('%02d', rand(0, 99));
        for (
            $a = md5($rand, true),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            $d = '',
            $f = 0;
            $f < 6;
            $g = ord($a[$f]),
            $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
            $f++
        ) ;
        return $d;
    }

    //判断用户身份 0:普通用户 1:被邀请用户 2:可购买代理权用户 -1:用户不存在
    public function userIdentity($user_id)
    {
        //判断用户是否存在
        $selectuser = Db::name('xm_tbl_user')->where('id', $user_id)->find();
        if (!empty($selectuser)) {
            //查询用户表
//            $userdetails = db('xm_tbl_user')->where('id', $user_id)->find();
            if ($selectuser['up_code'] == null) {
                return 0;
            } else {
                //查询
                $selectcardhistory = db('xm_tbl_pro_card_history')->where('last_user_id', $user_id)->find();
                if ($selectcardhistory) {
                    return 2;
                } else {
                    return 1;
                }
            }
        }else{
            return -1;
        }
    }

    //查询用户是否绑定推推项目
    public function mlxmBinding($user_id){
        $selectuserbinding = Db::table('ml_xm_binding')->where('ml_user_id',$user_id)->find();
        if($selectuserbinding){
            return true;
        }else{
            return false;
        }
    }


    public function getDistributionMoney($user_id)
    {
        $xm_id = Db::name('ml_xm_binding')->where('ml_user_id', $user_id)->value('xm_user_id');
        $id_list = Db::name('ml_tbl_channel')->where('xm_user_id', $xm_id)->field('ml_user_id')->select();
        $ids = '';
        foreach ($id_list as $k => $v) {
            $ids .= $v['ml_user_id'] . ',';
        }
        $ids = rtrim($ids,',');
        $list = Db::name('ml_tbl_order')->whereIn('user_id',$ids)->whereIn('order_type','1,2,3')->select();

        $goodsId = '';
        foreach ($list as $k=>$v){
            $goodsId .= $v['id'] . ',';
        }
        $goodsId = rtrim($goodsId,',');
        $goodsDetail = Db::name('ml_tbl_order_details')->whereIn('order_zid', $goodsId)->field('goods_id,goods_num')->select();

        $distriMoney = 0;
        foreach ($goodsDetail as $k=>$v){
            $bouns_price = Db::name('ml_tbl_goods')->where('id', $v['goods_id'])->value('bonus_price');
            $distriMoney += $bouns_price * $v['goods_num'];
        }
        return $distriMoney;
    }
}