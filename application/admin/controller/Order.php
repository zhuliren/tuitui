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
    protected $request;
    protected $table;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->request = $request;
        $this->table = 'ml_tbl_order';
    }

    public function cutOrder(Request $request)
    {
        $all = $request->param();
        if (isset($all['order_id']) && !empty($all['order_id'])){
            $res = Db::name('ml_tbl_order')->where('order_id',$all['order_id'])->find();
            if ( !$res ){
                return json(['status'=>5001,'msg'=>'订单状态不存在','data'=>'']);
            }
            Db::startTrans();
            $order_detail = Db::name('ml_tbl_order_details')->where('order_zid',$res['id'])->find();
            $goods_info = Db::name('ml_tbl_goods_format')->where('id',$order_detail['format_id'])->field('goods_name,first_bonus,is_online,goods_price,second_bonus,third_bonus')->find();

            $channeldata = Db::table('ml_tbl_channel')->where('ml_user_id', $res['user_id'])->find();

            if ($channeldata){
                if ($goods_info['first_price'] != 0){
                    $up_id = Db::name('ml_xm_binding')->where('xm_user_id',$channeldata['xm_user_id'])->value('ml_user_id');
                    if ($up_id){
                        $one_total = $order_detail['goods_num'] * $goods_info['first_bonus'];
                        $remark = '个人佣金';
                        $wallet_status = $this->walletOperate($up_id,$one_total,$remark,$all['order_id']);
                        if (!$wallet_status){
                            Db::rollback();
                        }
                        if ( $goods_info['second_bonus'] != 0){
                            $up_up_xmid = Db::name('ml_tbl_channel')->where('ml_user_id',$up_id)->value('xm_user_id');
                            if ($up_up_xmid){
                                $up_up_mlid = Db::name('ml_xm_binding')->where('xm_user_id',$up_up_xmid)->value('ml_user_id');
                                $second_total = $order_detail['goods_num'] * $goods_info['second_bonus'];
                                $second_remark = '团队返佣';
                                $wallet_status = $this->walletOperate($up_up_mlid,$second_total,$second_remark,$all['order_id']);
                                if (!$wallet_status){
                                    Db::rollback();
                                }
                            }
                            if ($goods_info['third_bonus'] != 0){
                                $the_last_xmid = Db::name('ml_tbl_channel')->where('ml_user_id',$up_up_mlid)->value('xm_user_id');
                                if ($the_last_xmid){
                                    $the_last_mlid = Db::name('ml_xm_binding')->where('xm_user_id',$the_last_xmid)->value('ml_user_id');
                                    $third_total = $order_detail['goods_num'] * $goods_info['second_bonus'];
                                    $third_remark = '团队返佣';
                                    $wallet_status = $this->walletOperate($the_last_mlid,$third_total,$third_remark,$all['order_id']);
                                    if (!$wallet_status){
                                        Db::rollback();
                                    }
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

    /**
     * @return \think\response\Json
     * @time: 2019/6/17
     * @autor: duheyuan
     * 获取订单列表
     */
    public function getOrderList()
    {
        $all = $this->request->param();

        $limit = $all['limit'];
        $page = $all['page'];
        $start = $page * $limit;
        $order_type = $all['order_type'];

        // 订单号 下单时间 姓名手机号 状态 地址 门牌号 商品名 商品规格  商品头像,购买数量,支付金额
        $sql = " SELECT  o.id,o.order_id ,o.order_type, o.user_name, o.phone, o.address, o.house_num, o.creat_time , o.pay_time, d.goods_num, o.pay_price,d.express_no, g.head_img,g.goods_name,f.goods_name as format_name,o.fixtime
            FROM `ml_tbl_order` AS o LEFT JOIN `ml_tbl_order_details` AS d ON o.id = order_zid LEFT JOIN `ml_tbl_goods_format` AS f ON o.format_id=f.id LEFT JOIN `ml_tbl_goods_two` AS g ON d.goods_id=g.id WHERE ";

        if ( ($order_type >= 0) && $order_type != 5  ){
            $sql .= " o.order_type = $order_type  AND ";

        }
        if (isset($all['time'][0]) && !empty($all['time'][0])){

            $sql .= " o.creat_time >= '{$all['time'][0]}' AND  o.creat_time <= '{$all['time'][1]}' ";
        }else{
            $start_date = date('Y-m-d'.' 00:00:00',time());
            $end_date = date('Y-m-d'.' 23:59:59',time());
            $sql .= " o.creat_time >= '{$start_date}' AND  o.creat_time <= '{$end_date}' ";
        }
        $data['count'] = count(Db::query($sql));

        $sql .= " ORDER BY o.id DESC LIMIT $start,$limit ";

        $data['list'] = Db::query($sql);
        foreach ($data['list'] as $k=>$v){
            $data['list'][$k]['realname'] = Db::name('ml_tbl_order_realname')->where('order_id',$v['id'])->select();
        }


        return json(['status'=>1001,'smg'=>'成功','data'=>$data]);
    }


}