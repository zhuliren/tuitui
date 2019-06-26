<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21
 * Time: 13:48
 */

namespace app\index\model;


use app\common\Model\PublicEnum;
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
        $selectuser = Db::table('xm_tbl_user')->where('id', $user_id)->find();
        if (isset($selectuser)) {
            //查询用户表
            $userdetails = db('xm_tbl_user')->where('id', $user_id)->find();
            if ($userdetails['up_code'] == null) {
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

    //  TODO 一级返利,二级未写
    public function getDistributionMoney($user_id)
    {
        $data['useInfo'] = Db::name('ml_tbl_user')->where('id',$user_id)->field('id,user_name,user_phone')->find();

        $xm_id = Db::name('ml_xm_binding')->where('ml_user_id', $user_id)->value('xm_user_id'); // 查询项目ID
        $id_list = Db::name('ml_tbl_channel')->where('xm_user_id', $xm_id)->field('ml_user_id')->select(); // 通过项目id查询 下级
        $ids = '';
        foreach ($id_list as $k => $v) {
            $ids .= $v['ml_user_id'] . ',';
        }
        $ids = rtrim($ids,',');
        $list = Db::name('ml_tbl_order')->whereIn('user_id',$ids)->select();
        $data['orderNum'] = count($list);

        $out_list = Db::name('ml_tbl_order')->whereIn('user_id',$ids)->whereIn('order_type','1,2,3,6')->select();
        $data['out_list'] = count($out_list);
        $goodsId = '';
        foreach ($out_list as $k=>$v){
            $goodsId .= $v['id'] . ',';
        }
        $goodsId = rtrim($goodsId,',');
        $goodsDetail = Db::name('ml_tbl_order_details')->whereIn('order_zid', $goodsId)->field('goods_id,goods_num')->select();
        $data['distriMoney'] = 0;
        foreach ($goodsDetail as $k => $v) {
            $bouns_price = Db::name('ml_tbl_goods')->where('id', $v['goods_id'])->field('bonus_price')->find();
            $data['distriMoney'] += $bouns_price['bonus_price'] * $v['goods_num'];
        }

        return $data['distriMoney'];

    }

    /**
     * @param $uid
     * @return bool
     * @time: 2019/6/18
     * @autor: duheyuan
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 618活动标记
     */
    public function activityMark($uid)
    {
        //  是否有参加活动 0没有
        $user_status = Db::name('ml_tbl_user')->where(['id'=>$uid,'activity_mark'=>0])->find();

        if ($user_status){
            $cpn = Db::name('xm_tbl_coupon')->where(['coup_type'=>1,'use_type'=>PublicEnum::FRUIT])->count();
//            $cpn = 500;
            if ($cpn < 500){
                return true;
            }
            return false;
        }else{
            return false;
        }
    }

    /**
     * @param $uid
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException 写入钱包
     * @throws \think\exception\PDOException
     * @time: 2019/6/18
     * @autor: duheyuan
     */
    public function walletEdit($uid)
    {
        //  一级用户
        $xm_id = Db::name('ml_xm_binding')->where('ml_user_id', $uid)->value('xm_user_id');
        $id_list = Db::name('ml_tbl_channel')->where('xm_user_id', $xm_id)->field('ml_user_id')->select();
        $ids = idsArrayToStr($id_list,'ml_user_id');
        $out_list = Db::name('ml_tbl_order')->whereIn('user_id',$ids)->whereIn('order_type','1,2,3,6')->select();
        $goodsId = idsArrayToStr($out_list);

        $wallet_id = Db::name('ml_tbl_wallet')->where('user_id',$uid)->value('id');
        if (!$wallet_id){
            $wallet_id = Db::name('ml_tbl_wallet')->insertGetId(['user_id'=>$uid,'balance'=>0,'creat_time'=>date('y-m-d H:i:s',time())]);
        }

        foreach ($out_list as $k=>$v){
            $order_wallet = Db::name('ml_tbl_wallet_details')->where(['wallet_id'=>$wallet_id,'remarks'=>'个人佣金','order_num'=>$v['order_id']])->find();
            //  没有钱包记录
            if (!$order_wallet ){
                    //  是否是新商品
                if ($v['format_id'] == 0){
                    $sql = "SELECT g.bonus_price,g.second_bouns,g.third_bouns,d.goods_num FROM `ml_tbl_order_details` AS d JOIN `ml_tbl_goods` AS g ON d.goods_id=g.id WHERE d.order_zid = {$v['id']} ";
                    $res = Db::query($sql);
                    $wallet_info = Db::name('ml_tbl_wallet')->where('id',$wallet_id)->find();
                    $amount = $res[0]['bonus_price'] * $res[0]['goods_num'];
                    $wallet_info['balance'] += $amount;
                    Db::startTrans();
                    $wallet_status = Db::name('ml_tbl_wallet')->where('user_id',$uid)->update(['balance'=>$wallet_info['balance']]);
                    if (!$wallet_status){
                        Db::rollback();
                    }
                    $wallet_detail = Db::name('ml_tbl_wallet_details')->insert(['wallet_id'=>$wallet_id,'time'=>date('Y-m-d H:i:s',time()),'amount'=>$amount,'nowbalance'=>$wallet_info['balance'],'type'=>1,'remarks'=>'个人佣金','order_num'=>$v['order_id'] ]);
                    if (!$wallet_detail){
                        Db::rollback();
                    }
                    Db::commit();
                }else{
                    $sql = "SELECT f.first_bonus,d.goods_num FROM `ml_tbl_order_details` AS d JOIN `ml_tbl_goods_format` AS f ON d.format_id=f.id WHERE d.order_zid = {$v['id']} ";
                    $res = Db::query($sql);
                    if (!empty($res)){
                        $res = $res[0];
                        if ($res['first_bonus'] != 0){
                            $wallet_info = Db::name('ml_tbl_wallet')->where('id',$wallet_id)->find();
                            $amount = $res['first_bonus'] * $res['goods_num'];
                            $wallet_info['balance'] += $amount;
                            Db::startTrans();
                            $wallet_status = Db::name('ml_tbl_wallet')->where('user_id',$uid)->update(['balance'=>$wallet_info['balance']]);
                            if (!$wallet_status){
                                Db::rollback();
                            }
                            $wallet_detail = Db::name('ml_tbl_wallet_details')->insert(['wallet_id'=>$wallet_id,'time'=>date('Y-m-d H:i:s',time()),'amount'=>$amount,'nowbalance'=>$wallet_info['balance'],'type'=>1,'remarks'=>'个人佣金','order_num'=>$v['order_id'] ]);
                            if (!$wallet_detail){
                                Db::rollback();
                            }
                            Db::commit();
                        }
                    }
                }
            }
        }

        //  二级用户
        $second_ids = Db::name('ml_xm_binding')->whereIn('ml_user_id',$ids)->select();
        $second_xm_ids = idsArrayToStr($second_ids,'xm_user_id');
        $second_ids_list = Db::name('ml_tbl_channel')->whereIn('xm_user_id',$second_xm_ids)->select();
        $second_order = Db::name('ml_tbl_order')->whereIn('user_id',idsArrayToStr($second_ids_list,'ml_user_id'))->whereIn('order_type','1,2,3,6')->select();

        foreach ($second_order as $key=>$val){
            $order_wallet = Db::name('ml_tbl_wallet_details')->where(['wallet_id'=>$wallet_id,'remarks'=>'团队返佣','order_num'=>$val['order_id']])->find();
            //  没有钱包记录
            if (!$order_wallet ){
                //  是否是新商品
                if ($val['format_id'] == 0){
                    $sql = "SELECT g.bonus_price,g.second_bouns,g.third_bouns,d.goods_num FROM `ml_tbl_order_details` AS d JOIN `ml_tbl_goods` AS g ON d.goods_id=g.id WHERE d.order_zid = {$val['id']} ";
                    $res = Db::query($sql);
                    if ($res[0]['second_bouns'] != 0){
                        $wallet_info = Db::name('ml_tbl_wallet')->where('id',$wallet_id)->find();
                        $amount = $res[0]['second_bouns'] * $res[0]['goods_num'];
                        $wallet_info['balance'] += $amount;
                        Db::startTrans();
                        $wallet_status = Db::name('ml_tbl_wallet')->where('user_id',$uid)->update(['balance'=>$wallet_info['balance']]);
                        if (!$wallet_status){
                            Db::rollback();
                        }
                        $wallet_detail = Db::name('ml_tbl_wallet_details')->insert(['wallet_id'=>$wallet_id,'time'=>date('Y-m-d H:i:s',time()),'amount'=>$amount,'nowbalance'=>$wallet_info['balance'],'type'=>1,'remarks'=>'团队返佣','order_num'=>$val['order_id'] ]);
                        if (!$wallet_detail){
                            Db::rollback();
                        }
                        Db::commit();
                    }
                }else{
                    $sql = "SELECT f.second_bonus,d.goods_num FROM `ml_tbl_order_details` AS d JOIN `ml_tbl_goods_format` AS f ON d.format_id=f.id WHERE d.order_zid = {$val['id']} ";
                    $res = Db::query($sql);
                    if (!empty($res)){
                        $res = $res[0];
                        if ($res['second_bonus'] != 0){
                            $wallet_info = Db::name('ml_tbl_wallet')->where('id',$wallet_id)->find();
                            $amount = $res['second_bonus'] * $res['goods_num'];
                            $wallet_info['balance'] += $amount;
                            Db::startTrans();
                            $wallet_status = Db::name('ml_tbl_wallet')->where('user_id',$uid)->update(['balance'=>$wallet_info['balance']]);
                            if (!$wallet_status){
                                Db::rollback();
                            }
                            $wallet_detail = Db::name('ml_tbl_wallet_details')->insert(['wallet_id'=>$wallet_id,'time'=>date('Y-m-d H:i:s',time()),'amount'=>$amount,'nowbalance'=>$wallet_info['balance'],'type'=>1,'remarks'=>'团队返佣','order_num'=>$val['order_id'] ]);
                            if (!$wallet_detail){
                                Db::rollback();
                            }
                            Db::commit();
                        }
                    }

                }
            }

            return true;
        }

        //  三级用户
        $third_ids = Db::name('ml_xm_binding')->whereIn('ml_user_id',idsArrayToStr($second_ids_list,'ml_user_id'))->select();
        $third_xm_ids = idsArrayToStr($third_ids,'xm_user_id');
        $third_ids_list = Db::name('ml_tbl_channel')->whereIn('xm_user_id',$third_xm_ids)->select();
        $third_order = Db::name('ml_tbl_order')->whereIn('user_id',idsArrayToStr($third_ids_list,'ml_user_id'))->select();

        foreach ($third_order as $keyTwo=>$valTwo){
            $order_wallet = Db::name('ml_tbl_wallet_details')->where(['wallet_id'=>$wallet_id,'remarks'=>'团队返佣','order_num'=>$valTwo['order_id']])->find();
            //  没有钱包记录
            if (!$order_wallet ){
                //  是否是新商品
                if ($valTwo['format_id'] == 0){
                    $sql = "SELECT g.bonus_price,g.second_bouns,g.third_bouns,d.goods_num FROM `ml_tbl_order_details` AS d JOIN `ml_tbl_goods` AS g ON d.goods_id=g.id WHERE d.order_zid = {$valTwo['id']} ";
                    $res = Db::query($sql);
                    if ($res[0]['third_bouns'] != 0){
                        $wallet_info = Db::name('ml_tbl_wallet')->where('id',$wallet_id)->find();
                        $amount = $res[0]['third_bouns'] * $res[0]['goods_num'];
                        $wallet_info['balance'] += $amount;
                        Db::startTrans();
                        $wallet_status = Db::name('ml_tbl_wallet')->where('user_id',$uid)->update(['balance'=>$wallet_info['balance']]);
                        if (!$wallet_status){
                            Db::rollback();
                        }
                        $wallet_detail = Db::name('ml_tbl_wallet_details')->insert(['wallet_id'=>$wallet_id,'time'=>date('Y-m-d H:i:s',time()),'amount'=>$amount,'nowbalance'=>$wallet_info['balance'],'type'=>1,'remarks'=>'团队返佣','order_num'=>$valTwo['order_id'] ]);
                        if (!$wallet_detail){
                            Db::rollback();
                        }
                        Db::commit();
                    }
                }else{
                    $sql = "SELECT f.third_bonus,d.goods_num FROM `ml_tbl_order_details` AS d JOIN `ml_tbl_goods_format` AS f ON d.format_id=f.id WHERE d.order_zid = {$valTwo['id']} ";
                    $res = Db::query($sql);
                    if (!empty($res)){
                        $res = $res[0];
                        if ($res['third_bonus'] != 0){
                            $wallet_info = Db::name('ml_tbl_wallet')->where('id',$wallet_id)->find();
                            $amount = $res['third_bonus'] * $res['goods_num'];
                            $wallet_info['balance'] += $amount;
                            Db::startTrans();
                            $wallet_status = Db::name('ml_tbl_wallet')->where('user_id',$uid)->update(['balance'=>$wallet_info['balance']]);
                            if (!$wallet_status){
                                Db::rollback();
                            }
                            $wallet_detail = Db::name('ml_tbl_wallet_details')->insert(['wallet_id'=>$wallet_id,'time'=>date('Y-m-d H:i:s',time()),'amount'=>$amount,'nowbalance'=>$wallet_info['balance'],'type'=>1,'remarks'=>'团队返佣','order_num'=>$valTwo['order_id'] ]);
                            if (!$wallet_detail){
                                Db::rollback();
                            }
                            Db::commit();
                        }
                    }

                }
            }
        }
    }

    //  获取上级信息
    public function getMyUpInfo($uid)
    {
        $xm_id = Db::name('ml_tbl_channel')->where(['ml_user_id'=>$uid])->value('xm_user_id');
        $up_user_id = Db::name('ml_xm_binding')->where(['xm_user_id'=>$xm_id])->value('ml_user_id');

        $info = Db::name('ml_tbl_user')->where(['id'=>$up_user_id])->find();
        return $info;


    }
}