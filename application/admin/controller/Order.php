<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/5/21
 * Time: 13:17
 */

namespace app\admin\controller;


use app\admin\Controller;
use app\common\Model\PublicEnum;
use think\Db;
use think\Request;

class Order extends Controller
{
    public function cutOrder(Request $request)
    {
        $all = $request->param();
        if (isset($all['order_id']) && !empty($all['order_id'])){
            $res = Db::name('ml_tbl_order')->where('order_id',$all['order_id'])->find();
            if ( !$res ){
                return json(['status'=>5001,'msg'=>'订单状态不存在','data'=>'']);
            }
            Db::startTrans();
            $order_type = Db::name('ml_tbl_order')->where(['order_id'=>$all['order_id'],'order_type'=>PublicEnum::ORDER_UNRECEIVED])->update(['order_type'=>PublicEnum::ORDER_COMPLETED]);
            if (!$order_type){
                Db::rollback();
            }
            $order_detail = Db::name('ml_tbl_order_details')->where('order_zid',$res['id'])->find();
            $goods_info = Db::name('ml_tbl_goods')->where('id',$order_detail['goods_id'])->field('goods_name,bonus_price,is_online,goods_price,buy_limit,buy_limit_num,second_bouns,third_bouns')->find();

            $channeldata = Db::table('ml_tbl_channel')->where('ml_user_id', $res['user_id'])->find();

            if ($channeldata){
                if (!empty($goods_info['bonus_price'])){
                    $up_id = Db::name('ml_xm_binding')->where('xm_user_id',$channeldata['xm_user_id'])->value('ml_user_id');
                    $one_total = $order_detail['goods_num'] * $goods_info['bonus_price'];
                    $remark = '个人佣金';
                    $wallet_status = $this->walletOperate($up_id,$one_total,$remark,$all['order_id']);
                    if (!$wallet_status){
                        Db::rollback();
                    }
                    if ( !empty($goods_info['second_bouns'])){
                        $up_up_xmid = Db::name('ml_tbl_channel')->where('ml_user_id',$up_id)->value('xm_user_id');
                        if ($up_up_xmid){
                            $up_up_mlid = Db::name('ml_xm_binding')->where('xm_user_id',$up_up_xmid)->value('ml_user_id');
                            $second_total = $order_detail['goods_num'] * $goods_info['second_bouns'];
                            $second_remark = '团队返佣';
                            $wallet_status = $this->walletOperate($up_up_mlid,$second_total,$second_remark,$all['order_id']);
                            if (!$wallet_status){
                                Db::rollback();
                            }
                        }
                        if (!empty($goods_info['third_bouns'])){
                            $the_last_xmid = Db::name('ml_tbl_channel')->where('ml_user_id',$up_up_mlid)->value('xm_user_id');
                            if ($the_last_xmid){

                                $the_last_mlid = Db::name('ml_xm_binding')->where('xm_user_id',$the_last_xmid)->value('ml_user_id');
                                $third_total = $order_detail['goods_num'] * $goods_info['third_bouns'];
                                $third_remark = '团队返佣';
                                $wallet_status = $this->walletOperate($the_last_mlid,$third_total,$third_remark,$all['order_id']);
                                if (!$wallet_status){
                                    Db::rollback();
                                }
                            }
                        }
                    }
                }
                Db::commit();
                return json(['status'=>1001,'msg'=>'成功','data'=>'']);
            }
            Db::commit();
            return json(['status'=>1001,'msg'=>'成功','data'=>'']);

        }

    }


    public function walletOperate($up_id,$total,$remark,$order_id)
    {
        //  查询是否有钱包
        $wallet_type = Db::name('ml_tbl_wallet')->where('user_id',$up_id)->find();
        if ($wallet_type){
            $wallet_id = $wallet_type['id'];
            $now_balance = $total + $wallet_type['balance'];

        }else{
            $wallet_id = Db::name('ml_tbl_wallet')->insertGetId(['user_id'=>$up_id,'creat_time'=>date('Y-m-d H:i:s',time())]);
            $now_balance = $total;
        }
        $arr = [
            'wallet_id'=>$wallet_id,
            'time'=>date('Y-m-d H:i:s',time()),
            'amount'=>$total,
            'nowbalance'=>$now_balance,
            'type'=>1,
            'remarks'=>$remark,
            'order_num'=>$order_id
        ];
        Db::name('ml_tbl_wallet')->where('user_id',$up_id)->update(['balance'=>$now_balance]);
        Db::name('ml_tbl_wallet_details')->insert($arr);
        return true;

    }

}