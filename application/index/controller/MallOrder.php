<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/18
 * Time: 9:25
 */

namespace app\index\controller;


use app\common\Model\PublicEnum;
use app\index\Controller;
use app\index\model\MallBonus;
use app\index\model\MallUserWallet;
use think\Db;

class MallOrder extends Controller
{
    public function shopCarDetails()
    {
        $user_id = $_REQUEST['userid'];
        $selectshopcar = Db::table('ml_view_shopcar')->where('user_id', $user_id)->select();
        if ($selectshopcar) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $selectshopcar);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '购物车无商品', 'data' => '');
            return json($data);
        }
    }

    public function operateShopCar()
    {
        $user_id = $_REQUEST['userid'];
        $goods_id = $_REQUEST['goodsid'];
        $goods_num = $_REQUEST['num'];
        //操作 0为减 1为加
        $operate = $_REQUEST['operate'];
        //查询商品是否存在
        $selectgoods = Db::table('ml_tbl_shopcar')->where('user_id', $user_id)->where('goods_id', $goods_id)->find();
        $id = $selectgoods['id'];
        if ($selectgoods) {
            //获取商品当前数量
            $now_goods_num = $selectgoods['goods_num'];
            //判断操作
            if ($operate == 1) {
                $new_goods_num = $now_goods_num + $goods_num;
                Db::table('ml_tbl_shopcar')->where('id', $id)->update(['goods_num' => $new_goods_num]);
            } elseif ($operate == 0) {
                //判断数量不能减为1以下
                if ($now_goods_num > $goods_num) {
                    $new_goods_num = $now_goods_num - $goods_num;
                    Db::table('ml_tbl_shopcar')->where('id', $id)->update(['goods_num' => $new_goods_num]);
                } else {
                    $data = array('status' => 1, 'msg' => '购物车商品数量不能为0，可以选择删除', 'data' => '');
                    return json($data);
                }
            }
        } else {
            //判断操作
            if ($operate == 1) {
                //增加商品
                $insertdata = ['user_id' => $user_id, 'goods_id' => $goods_id, 'goods_num' => $goods_num];
                Db::table('ml_tbl_shopcar')->insert($insertdata);
            }
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function delShapCar()
    {
        $user_id = $_REQUEST['userid'];
        $goods_id = $_REQUEST['goodsid'];
        //查询商品是否存在
        $selectgoods = Db::table('ml_tbl_shopcar')->where('user_id', $user_id)->where('goods_id', $goods_id)->find();
        $id = $selectgoods['id'];
        if ($selectgoods) {
            Db::table('ml_tbl_shopcar')->delete($id);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '商品不存在', 'data' => '');
            return json($data);
        }
    }

    public function orderList()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $start = $page * $limit;
        $user_id = $_REQUEST['userid'];
        //订单状态 0.待支付1.待收货2.待核销3.已完成4.已取消 5.全部订单
        $order_type = $_REQUEST['order_type'];
        //判断查询订单类型
        if ($order_type > 6 || $order_type < 0) {
            $data = array('status' => 1, 'msg' => '参数错误', 'data' => '');
            return json($data);
        } else {
            if ($order_type == 5) {
                //查询订单
                $selectorderlist = Db::table('ml_tbl_order')->where('user_id', $user_id)->order('id desc')->limit($start, $limit)->select();
            } else {
                $selectorderlist = Db::table('ml_tbl_order')->where('user_id', $user_id)->where('order_type', $order_type)->order('id desc')->limit($start, $limit)->select();
            }
        }
        if ($selectorderlist) {
            //遍历查询订单图片
            $flag_num = 0;
            foreach ($selectorderlist as $eachorder) {
                //用订单表自增id查询订单商品情况
                $order_zid = $eachorder['id'];
                $gid = Db::name('ml_tbl_order_details')->where('order_zid',$order_zid)->value('goods_id');
//                $selectgoodsheadimg = Db::name('ml_tbl_goods_two')->where('id',$gid)->field('head_img')->select();
                $sql = "SELECT g.head_img,g.goods_name,o.pay_price,o.creat_time,o.pay_time FROM ml_tbl_order_details  d  JOIN ml_tbl_goods_two  g ON d.goods_id = g.id JOIN ml_tbl_order o ON d.order_zid = o.id WHERE d.order_zid = $order_zid";
                $selectgoodsheadimg = Db::query($sql);
                if (empty($selectgoodsheadimg)){
                    $selectgoodsheadimg = Db::name('ml_tbl_goods_two')->where('id',$gid)->field('head_img')->select();
                }

                $goods_num = count($selectgoodsheadimg);
                //数据绑定
                $returndata[$flag_num] = array('order_id' => $eachorder['order_id'], 'order_type' => $eachorder['order_type'], 'goods_num' => $goods_num, 'pay_price' => $eachorder['pay_price'], 'goods_head' => $selectgoodsheadimg);
                $flag_num++;
            }
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '没有订单', 'data' => '');
            return json($data);
        }
    }

    public function creatMallOrder()
    {
        //TODO 购物车下单未修改库存
        $user_id = $_REQUEST['userid'];
        $goods_list_string = $_REQUEST['goodslist'];
        $user_name = filter_Emoji($_REQUEST['username']);
        if (preg_mobile($_REQUEST['phone'])){
            $phone = $_REQUEST['phone'];
        }else{
            return json(['status'=>1,'msg'=>'手机号码格式不正确','data'=>'']);
        }
        $address = $_REQUEST['address'];
        $house_num = $_REQUEST['house_num'];
        $coupon_id = $_REQUEST['coupon_id'];
        //生成订单id
        $order_id = $user_id . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //分离数组
        $goods_list = explode(",", $goods_list_string);
        //计算订单金额
        $goods_sum = 0;
        foreach ($goods_list as $eachgoods) {
            $selecteachgoodssum = Db::table('ml_view_shopcar')->where('id', $eachgoods)->find();
            $goods_sum_price = $selecteachgoodssum['goods_price'] * $selecteachgoodssum['goods_num'];
            $goods_sum += $goods_sum_price;
        }
        $discount = 1;//折扣
        $par_val = 0;//减免金额
        if ($coupon_id != 'no') {
            //查询优惠券优惠价格
            $selectcoupon = Db::table('xm_tbl_coupon')->where('id', $coupon_id)->find();
            //判断优惠券类型
            if ($selectcoupon['discount'] != 0) {
                $discount = $selectcoupon['discount'];
            }
            $par_val = $selectcoupon['par_value'];
        }
        //运费计算
        $freight = 0;
        //计算订单需支付金额
        $pay_price = ($goods_sum - $par_val) * $discount - $freight;
        //创建订单
        $order_type = 0;
        if ($coupon_id != 'no') {
            $orderdata = ['order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $user_id,
                'phone' => $phone, 'address' => $address, 'house_num' => $house_num,
                'coupon_id' => $coupon_id, 'freight' => $freight, 'goods_price' => $goods_sum,
                'pay_price' => $pay_price, 'user_name' => $user_name, 'creat_time' => date("Y-m-d h:i:s", time())];
        } else {
            $orderdata = ['order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $user_id,
                'phone' => $phone, 'address' => $address, 'house_num' => $house_num, 'freight' => $freight, 'goods_price' => $goods_sum,
                'pay_price' => $pay_price, 'user_name' => $user_name, 'creat_time' => date("Y-m-d h:i:s", time())];
        }
        $order_zid = Db::table('ml_tbl_order')->insertGetId($orderdata);
        //插入订单汇总表
        $orderdatasum = array('order_id' => $order_id, 'type' => 2, 'creat_time' => date("Y-m-d h:i:s", time()));
        Db::table('ml_xm_order_summary')->insert($orderdatasum);
        //转换购物车商品到优惠券
        foreach ($goods_list as $eachshopcarid) {
            $selectshopcar = Db::table('ml_view_shopcar')->where('id', $eachshopcarid)->find();
            $intoorderdata = array('order_zid' => $order_zid, 'goods_id' => $selectshopcar['goods_id'], 'goods_num' => $selectshopcar['goods_num'], 'goods_price' => $selectshopcar['goods_price']);
            Db::table('ml_tbl_order_details')->insert($intoorderdata);
            Db::table('ml_tbl_shopcar')->delete($eachshopcarid);
        }
        $returndata = array('orderid' => $order_id, 'ordertype' => 2);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function mallBuyNow()
    {
        $user_id = $_REQUEST['userid'];
        $goods_id = $_REQUEST['goodsid'];
        $goods_num = $_REQUEST['goodsnum'];
        $user_name = filter_Emoji($_REQUEST['username']);
        if (preg_mobile($_REQUEST['phone'])){
            $phone = $_REQUEST['phone'];
        }else{
            return json(['status'=>1,'msg'=>'手机号码格式不正确','data'=>'']);
        }
        $address = $_REQUEST['address'];
        $house_num = $_REQUEST['house_num'];
        $coupon_id = $_REQUEST['coupon_id'];
        //生成订单id
        $sizeid = $_REQUEST['sizeid'];
        if (empty($sizeid)) {
            return json(['status'=>2001,'msg'=>'参数错误,请重新提交','data'=>'']);
        }
        $order_id = $user_id . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //计算订单金额
        $selectgoodsprice = Db::table('ml_tbl_goods_two')->where('id', $goods_id)->find();
        $format_info = Db::name('ml_tbl_goods_format')->where(['goods_id'=>$goods_id,'id'=>$sizeid])->find();
        if ( $goods_num != 0){
            $stock =$format_info['goods_stock'] - $goods_num;
            if ($stock < 0){
                return json(['status'=>2001,'msg'=>'库存不足','data'=>'']);
            }
        }
        $goods_stock = $format_info['goods_stock'];
        $goods_sell_out = $selectgoodsprice['goods_sell_out'];
        $goods_price = $format_info['goods_price'];
        $goods_sum_price = $goods_price * $goods_num;
        $goods_sum = $goods_sum_price;
        $discount = 1;//折扣
        $par_val = 0;//减免金额
        $freight = 0;
        if ($coupon_id != 'no') {
            //查询优惠券优惠价格
            $selectcoupon = Db::table('xm_tbl_coupon')->where('id', $coupon_id)->find();
            //判断优惠券类型
            if ($selectcoupon['coupon_type'] == 1){
                $pay_price = $goods_sum * ($selectcoupon['discount'] / 10);
                if (isset($_REQUEST['pay_price']) && !empty($_REQUEST['pay_price']) ){
                    $pay_price = number_format($pay_price,2);
                    $sum_price = number_format($_REQUEST['pay_price'],2);
                    if ($pay_price !==  $sum_price){
                        return json(['status'=>0,'msg'=>'计算金额出错','data'=>'']);
                    }
                }else{
                    return json(['status'=>0,'msg'=>'参数出错','data'=>'']);
                }
            }else{
                $pay_price = $goods_sum - $selectcoupon['par_value'];
                if (isset($_REQUEST['pay_price']) && !empty($_REQUEST['pay_price']) ){
                    $pay_price = number_format($pay_price,2);
                    $sum_price = number_format($_REQUEST['pay_price'],2);
                    if ($pay_price !==  $sum_price){
                        return json(['status'=>0,'msg'=>'计算金额出错','data'=>'']);
                    }
                }else{
                    return json(['status'=>0,'msg'=>'参数出错','data'=>'']);
                }
            }
            //判断优惠券类型
//            if ($selectcoupon['discount'] != 0) {
//                $discount = $selectcoupon['discount'];
//            }
//            $par_val = $selectcoupon['par_value'];
        }else{
            //运费计算
            //计算订单需支付金额
            $pay_price = ($goods_sum - $par_val) * $discount - $freight;
        }
        //创建订单
        $order_type = 0;
        if ($coupon_id != 'no') {
            $orderdata = ['order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $user_id,
                'phone' => $phone, 'address' => $address, 'house_num' => $house_num,
                'coupon_id' => $coupon_id, 'freight' => $freight, 'goods_price' => $goods_sum,
                'pay_price' => $pay_price, 'user_name' => $user_name, 'creat_time' => date("Y-m-d h:i:s", time()),'format_id'=>$sizeid];
        } else {
            $orderdata = ['order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $user_id,
                'phone' => $phone, 'address' => $address, 'house_num' => $house_num, 'freight' => $freight, 'goods_price' => $goods_sum,
                'pay_price' => $pay_price, 'user_name' => $user_name, 'creat_time' => date("Y-m-d h:i:s", time()),'format_id'=>$sizeid];
        }
        $order_zid = Db::table('ml_tbl_order')->insertGetId($orderdata);
        //插入订单汇总表
        $orderdatasum = array('order_id' => $order_id, 'type' => 2, 'creat_time' => date("Y-m-d h:i:s", time()));
        Db::table('ml_xm_order_summary')->insert($orderdatasum);
        //转换购物车商品到优惠券
        $intoorderdata = array('order_zid' => $order_zid, 'goods_id' => $goods_id, 'goods_num' => $goods_num, 'goods_price' => $goods_price,'type'=>1,'format_id'=>$sizeid);
        Db::table('ml_tbl_order_details')->insert($intoorderdata);
        //修改商品库存
        $new_goods_stock = $goods_stock - $goods_num;
        $new_goods_sell_out = $goods_sell_out + $goods_num;
        $format_sell_out = $format_info['goods_sell_out'] + $goods_num;
        Db::table('ml_tbl_goods_two')->where('id', $goods_id)->update(['goods_sell_out' => $new_goods_sell_out]);
        Db::table('ml_tbl_goods_format')->where('id', $sizeid)->update(['goods_stock' => $new_goods_stock, 'goods_sell_out' => $format_sell_out]);
        $returndata = array('orderid' => $order_id, 'ordertype' => 2);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function creatOrderOnce()
    {
        $user_id = $_REQUEST['userid'];
        $goods_id = $_REQUEST['goodsid'];
        $goods_num = $_REQUEST['goodsnum'];
        $user_name = filter_Emoji($_REQUEST['username']);
        $sizeid = $this->request->param('sizeid');

        if ($goods_id == 80){
            $sql = "SELECT o.id,o.order_type,d.goods_num,o.user_id FROM ml_tbl_order AS o JOIN ml_tbl_order_details AS d ON o.id=d.order_zid WHERE o.user_id = {$user_id} AND d.goods_id = {$goods_id}  AND o.order_type != 4 ";
            $limit_user_buy = Db::query($sql);

            if ($limit_user_buy){
                $num = 0;
                foreach ($limit_user_buy as $k=>$v){
                    $num += $v['goods_num'];
                }
                if ( ($num+$goods_num) > 4 ){
                    return responseError([],4001,'该商品最多购买四张');
                }
            }
        }

        if (preg_mobile($_REQUEST['phone'])){
            $phone = $_REQUEST['phone'];
        }else{
            return json(['status'=>1,'msg'=>'手机号码格式不正确','data'=>'']);
        }
        $coupon_id = $_REQUEST['coupon_id'];
        if (isset($_REQUEST['fixtime']) && !empty($_REQUEST['fixtime'])){
            $fixtime = $_REQUEST['fixtime'];
        }else{
            $fixtime = null;
        }
        //生成订单id
        $order_id = $user_id . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //计算订单金额
        $selectgoodsdata = Db::table('ml_tbl_goods_two')->where('id', $goods_id)->find();
        $format_info = Db::name('ml_tbl_goods_format')->where(['goods_id'=>$goods_id,'id'=>$sizeid])->find();
        if ($selectgoodsdata['is_realname']  == 1){
            if (isset($_REQUEST['realname']) && !empty($_REQUEST)){
                $nameInfo = $_REQUEST['realname'];
            }else{
                return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
            }
        }
        if ( $goods_num != 0){
            $stock =$format_info['goods_stock'] - $goods_num;
            if ($stock < 0){
                return json(['status'=>2001,'msg'=>'库存不足','data'=>'']);
            }
        }
        $goods_stock = $format_info['goods_stock'];
        $goods_sell_out = $selectgoodsdata['goods_sell_out'];
        $goods_price = $format_info['goods_price'];
        $type = $selectgoodsdata['type'];
        $goods_sum_price = $goods_price * $goods_num;
        $goods_sum = $goods_sum_price;
        $discount = 1;//折扣
        $par_val = 0;//减免金额
        //运费计算
        $freight = 0;
        if ($coupon_id != 'no') {
            //查询优惠券优惠价格
            $selectcoupon = Db::table('xm_tbl_coupon')->where('id', $coupon_id)->find();
            //判断优惠券类型
            if ($selectcoupon['coupon_type'] == 1){
                $pay_price = $goods_sum * $selectcoupon['discount'];
                if (isset($_REQUEST['pay_price']) && !empty($_REQUEST['pay_price']) ){
                    $pay_price = number_format($pay_price,2);
                    $sum_price = number_format($_REQUEST['pay_price'],2);
                    if ($pay_price !==  $sum_price){
                        return json(['status'=>0,'msg'=>'计算金额出错','data'=>'']);
                    }
                }else{
                    return json(['status'=>0,'msg'=>'参数出错','data'=>'']);
                }
            }else{
                $pay_price = $goods_sum - $selectcoupon['par_value'];
                if (isset($_REQUEST['pay_price']) && !empty($_REQUEST['pay_price']) ){
                    $pay_price = number_format($pay_price,2);
                    $sum_price = number_format($_REQUEST['pay_price'],2);
                    if ($pay_price !==  $sum_price){
                        return json(['status'=>0,'msg'=>'计算金额出错','data'=>'']);
                    }
                }else{
                    return json(['status'=>0,'msg'=>'参数出错','data'=>'']);

                }
            }
        }else{
            //计算订单需支付金额
            $pay_price = ($goods_sum - $par_val) * $discount - $freight;
        }
        //创建订单
        $order_type = 0;
        if ($coupon_id != 'no') {
            $orderdata = ['order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $user_id,
                'phone' => $phone, 'coupon_id' => $coupon_id, 'freight' => $freight, 'goods_price' => $goods_sum,
                'pay_price' => $pay_price, 'user_name' => $user_name, 'creat_time' => date("Y-m-d h:i:s", time()) , 'fixtime'=>$fixtime,'format_id'=>$sizeid];
        } else {
            $orderdata = ['order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $user_id,
                'phone' => $phone, 'freight' => $freight, 'goods_price' => $goods_sum, 'pay_price' => $pay_price,
                'user_name' => $user_name, 'creat_time' => date("Y-m-d h:i:s", time()), 'fixtime'=>$fixtime,'format_id'=>$sizeid];
        }
        $order_zid = Db::table('ml_tbl_order')->insertGetId($orderdata);
        if (isset($nameInfo) && !empty($nameInfo)){
            $arr = [];
            $nameInfo = json_decode($nameInfo,true);
            foreach ($nameInfo as $k=>$v){
                if ( !namePreg($v['name'])){
                    return json(['status'=>2001,'msg'=>'不是正确姓名格式','data'=>'']);

                }
                if (!preg_id_card($v['id_card'])){
                    return json(['status'=>2001,'msg'=>'不是正确身份证格式','data'=>'']);
                }
                $arr[] = [
                    'order_id' => $order_zid,
                    'realname' => $v['name'],
                    'id_card' => $v['id_card']
                ];
            }
            Db::name('ml_tbl_order_realname')->insertAll($arr);
        }
        //插入订单汇总表
        $orderdatasum = array('order_id' => $order_id, 'type' => 2, 'creat_time' => date("Y-m-d h:i:s", time()));
        Db::table('ml_xm_order_summary')->insert($orderdatasum);
        //插入商品详情表
        $intoorderdata = array('order_zid' => $order_zid, 'goods_id' => $goods_id, 'goods_num' => $goods_num, 'goods_price' => $goods_price, 'type' => $type,'format_id'=>$sizeid);
        Db::table('ml_tbl_order_details')->insert($intoorderdata);
        $returndata = array('orderid' => $order_id, 'ordertype' => 2);
        //修改商品库存
        $new_goods_stock = $goods_stock - $goods_num;
        $new_goods_sell_out = $goods_sell_out + $goods_num;
        $format_sell_out = $format_info['goods_sell_out'] + $goods_num;
        Db::table('ml_tbl_goods_two')->where('id', $goods_id)->update(['goods_sell_out' => $new_goods_sell_out]);
        Db::table('ml_tbl_goods_format')->where('id', $sizeid)->update(['goods_stock' => $new_goods_stock, 'goods_sell_out' => $format_sell_out]);
//        Db::table('ml_tbl_goods')->where('id', $goods_id)->update(['goods_stock' => $new_goods_stock, 'goods_sell_out' => $new_goods_sell_out]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function orderDetails()
    {
        $order_id = $_REQUEST['orderid'];
        //查询商品
        $orderdata = Db::table('ml_tbl_order')->where('order_id', $order_id)->find();
        if ($orderdata) {
            $name = $orderdata['user_name'];//收货人姓名
            $address = $orderdata['address'];//收货地址
            $house_num = $orderdata['house_num'];//收货地址门牌号
            $goods_price_sum = $orderdata['goods_price'];//商品总价
            $pay_price = $orderdata['pay_price'];//订单支付金额
            $creat_time = $orderdata['creat_time'];//订单时间
            $order_type = $orderdata['order_type'];//订单状态0.待支付1.待收货2.待核销3.已完成4.已取消
            $phone = $orderdata['phone'];//订单手机号
            $order_zid = $orderdata['id'];
            $order_freight = $orderdata['freight'];//运费
            //查询商品
            $orderdetailsdata = Db::table('ml_tbl_order_details')->where('order_zid', $order_zid)->select();
            if ($orderdetailsdata) {
                $ordergoodsdetails = array();
                $num = 0;
                foreach ($orderdetailsdata as $eachdata) {
                    $goods_price = $eachdata['goods_price'];//商品价格
                    $goods_num = $eachdata['goods_num'];//商品数量;
                    $goods_id = $eachdata['goods_id'];
                    $goodsdata = Db::table('ml_tbl_goods_two')->where('id', $goods_id)->find();
                    $goods_head = $goodsdata['head_img'];
                    $goods_name = $goodsdata['goods_name'];
                    $goods_format = $goodsdata['goods_format'];
                    $verify_num = $eachdata['verify_num'];
                    $goods_info = array(
                        'goods_price' => $goods_price,
                        'goods_num' => $goods_num,
                        'verify_num' => $verify_num,
                        'goods_id' => $goods_id,
                        'goods_head' => $goods_head,
                        'goods_name' => $goods_name,
                        'goods_format' => $goods_format
                    );
                    $ordergoodsdetails[$num] = $goods_info;
                    $num++;
                }
                //查询优惠券减免价格 商品总价-运费-付款金额=优惠价
                $coupon_price = $goods_price_sum - $order_freight - $pay_price;
                $returndata = array(
                    'name' => $name,
                    'address' => $address,
                    'house_num' => $house_num,
                    'goodslist' => $ordergoodsdetails,
                    'goods_sum_price' => $goods_price_sum,
                    'coupon_price' => $coupon_price,
                    'order_price' => $pay_price,
                    'order_id' => $order_id,
                    'creat_time' => $creat_time,
                    'order_type' => $order_type,
                    'clerk_string' => $order_id,
                    'phone' => $phone
                );
                $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            } else {
                $data = array('status' => 1, 'msg' => '订单数据错误，请联系客服核查', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '订单号错误', 'data' => '');
        }
        return json($data);
    }

    public function orderClerk()
    {
        $user_id = $_REQUEST['userid'];
        $clerk_string = $_REQUEST['clerkstring'];
        //TODO 目前核销数据为订单号
        $order_id = $clerk_string;
        //判断用户是否有该订单的核销权
        $clerkdata = Db::table('ml_tbl_business_clerk')->where('user_id', $user_id)->where('type', 1)->find();
        if ($clerkdata) {
            $orderdata = Db::table('ml_tbl_order')->where('order_id', $order_id)->where('order_type', 2)->find();
            if ($orderdata) {
                //查看订单下能够匹配该核销员商户的商品
                $business_id = $clerkdata['business_id'];
                $order_zid = $orderdata['id'];
                $goodsdata = Db::table('ml_tbl_order_details')->where('order_zid', $order_zid)->find();
                //TODO 平台目前为单订单单商品 后续需要修改为单订单多商户多商品 此处的核销逻辑需要修改
                $goods_num = $goodsdata['goods_num'];
                $verify_num = $goodsdata['verify_num'];
                if ($goods_num <= $verify_num) {
                    //修改订单状态
                    Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_type' => 3]);
                    $data = array('status' => 1, 'msg' => '商品已核销完', 'data' => '');
                    return json($data);
                }
                $can_verify_num = $goods_num - $verify_num;
                $goods_id = $goodsdata['goods_id'];
                $goodsdata = Db::table('ml_tbl_goods')->where('id', $goods_id)->find();
                $head_img = $goodsdata['head_img'];
                $goods_name = $goodsdata['goods_name'];
                $goods_format = $goodsdata['goods_format'];
                $goods_region = $goodsdata['goods_region'];
                $goods_business_id = $goodsdata['business_id'];
                if ($goods_business_id == $business_id) {
                    $returngoodsdata = array(
                        'goods_id' => $goods_id,
                        'head_img' => $head_img,
                        'goods_name' => $goods_name,
                        'goods_format' => $goods_format,
                        'goods_region' => $goods_region,
                        'can_verify_num' => $can_verify_num
                    );
                    $returndata = array('goodsdata' => $returngoodsdata);
                    $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
                } else {
                    $data = array('status' => 1, 'msg' => '非本商户商品，无法核销', 'data' => '');
                }
            } else {
                $data = array('status' => 1, 'msg' => '核销码错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '无核销权', 'data' => '');
        }
        return json($data);
    }

    public function orderGoodsClerk()
    {
        //TODO 此处未判断用户是否拥有核销权
        $user_id = $_REQUEST['userid'];
        $goods_id = $_REQUEST['goodsid'];
        $goods_num = $_REQUEST['num'];
        $order_id = $_REQUEST['orderid'];
        //查询订单自增id
        $orderdata = Db::table('ml_tbl_order')->where('order_id', $order_id)->find();
        //查询订单的用户
        $order_user_id = $orderdata['user_id'];
        $order_zid = $orderdata['id'];
        //获取子订单详细数据
        $orderdetailsdata = Db::table('ml_tbl_order_details')->where('order_zid', $order_zid)->where('goods_id', $goods_id)->find();
        if (($orderdetailsdata['goods_num'] - $orderdetailsdata['verify_num']) < $goods_num) {
            $data = array('status' => 1, 'msg' => '核销数量错误', 'data' => '');
        } else {
            $new_verify_num = $goods_num + $orderdetailsdata['verify_num'];
            Db::table('ml_tbl_order_details')->where('order_zid', $order_zid)->where('goods_num',$orderdetailsdata['goods_num'])->update(['verify_num' => $new_verify_num]);
            //根据商品金额进行返佣 查询用户上级
            $channeldata = Db::table('ml_tbl_channel')->where('ml_user_id', $order_user_id)->find();
            if ($channeldata) {
                $xm_up_user_id = $channeldata['xm_user_id'];
                //查询上级在商城的id
                $bindingdata = Db::table('ml_xm_binding')->where('xm_user_id', $xm_up_user_id)->find();
                if ($bindingdata) {
                    $up_ml_user_id = $bindingdata['ml_user_id'];
                    //查询商品分销价格
                    $bonusdata = Db::table('ml_tbl_goods_bonus')->where('goods_id', $goods_id)->find();
                    //TODO 目前只做一级分销
                    if ($bonusdata['first'] != 0) {
                        //获得的返佣
                        $amount = $bonusdata['first'] * $goods_num;
                        $remarks = '分销奖励：' . $amount . '元';
                        $mallBonus = new MallUserWallet();
                        $mallBonus->walletOperation(1, $amount, $up_ml_user_id, $remarks);
                    }
                }
            }
            //TODO 判断订单是否核销完成，此处为单订单单商品后期改为单订单多商品是需要修改
            if (($orderdetailsdata['goods_num'] - $new_verify_num) == 0) {
                Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_type' => 3,'clerk_id'=>$user_id,'clerk_time'=>time()]);
            }
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        }
        return json($data);
    }


    public function renewalDisTri()
    {
        $uid = $this->request->param('uid');
        $name = $this->request->param('name');
        $tel = $this->request->param('phone');
        $price = $this->request->param('price');


        if ($price < 398){
            return json(['status'=>5001,'msg'=>'价格错误','data'=>'']);
        }
        if (isset($uid) && !empty($uid)){
            $user_info = Db::name('ml_tbl_user')->where('id',$uid)->find();

            if (empty($user_info)){
                return json(['status'=>2001,'msg'=>'用户不存在','data'=>'']);
            }

            if ($user_info['is_salesman'] != 1){
                return json(['status'=>3010,'msg'=>'请先绑定商城项目','data'=>'']);

            }
        }
        $order_num = randomOrder_no();
        $order_status = Db::name('ml_tbl_distributor')->where('order_num', $order_num)->find();
        if ($order_status) {
            $order_num = randomOrder_no();
            $order_status = Db::name('ml_tbl_distributor')->where('order_num', $order_num)->find();

        }
        $arr = [
            'u_id' => $uid,
            'u_name' => $name,
            'tel' => $tel,
            'price' => $price,
            'c_time' => time(),
            'order_type' => PublicEnum::ORDER_UNRECEIVED,
            'order_num' => $order_num,
        ];
        $order_id = Db::name('ml_tbl_distributor')->insertGetId($arr);

        if ($order_id ){
            return json(['status'=>1001,'msg'=>'成功','data'=>$order_id]);
        }else{
            return json(['status'=>2001,'msg'=>'新增失败','data'=>'']);
        }
    }


    //  对外银联接口
    public function UnionPayOpenAPI()
    {
        $all = $this->request->param();
        //  参数是否在
        if (!isset($all['clerkstr']) || empty($all['clerkstr'])){
            return responseError([],400,'缺少必要参数');
        }
        if (!isset($all['time']) || empty($all['time'])){
            return responseError([],400,'缺少时间戳');
        }
        if (!isset($all['device_num']) || empty($all['device_num'])){
            return responseError([],400,'缺少设备号');
        }

        if ( strlen($all['sign']) != 32){
            return responseError([],500,'签名错误');
        }

//        $timsatmp =  strtotime($all['time']);
//        if ( ( $timsatmp > (time() + 10))  || ( $timsatmp < (time()-10) )){
//            return  responseError([],402,'时间允许值在10s范围内');
//        }
        //  查看订单是否在
        $order_status = Db::name('ml_tbl_order')->where('order_id',$all['clerkstr'])->find();
        if (!$order_status){
            return responseError([],404,'订单不存在');
        }
        if ($order_status['order_type'] == 3){
            return responseError([],402,'订单已完成');
        }

        //  签名生成参数
        $timestring = str_replace('%20', ' ', $all['time']);
        $arr = [
            'device_num'=>$all['device_num'],
            'timestamp'=>$timestring,
            'clerkstr'=>$all['clerkstr'],
        ];
        //  验证签名            TODO: 传输过来为小写,编译对比为大写对比
        $sign = $this->Sign($arr,$all['device_num']);
        if ($sign !==  strtoupper($all['sign'])){
            return responseError([],401,'签名错误');
        }

        //  通过订单查询设备号是否存在
        $sql  = "SELECT b.id,b.device_num,d.goods_num,d.verify_num FROM `ml_tbl_order_details` AS d LEFT JOIN `ml_tbl_goods_two` AS g ON d.goods_id=g.id LEFT JOIN `ml_tbl_business` AS b ON g.business_id = b.id  WHERE d.order_zid = {$order_status['id']} ";
        $res = Db::query($sql);

        if ($res){
            if (empty($res[0])){
                return responseError([],404,'未找到详情信息');
            }
            if ($all['device_num'] != $res[0]['device_num']  ){
                return responseError([],403,'不是允许的设备');
            }

        }else{
            return responseError([],404,'商户不存在');
        }
        /* 通过设备号查询
        $device_status = Db::name('ml_tbl_business')->where('device_num',$all['device_num'])->find();
        if (!$device_status){
            return responseError([],400,'不是允许的设备');
        }
        */
        //  TODO:进行订单操作  暂时写死核销所有券码

        if (isset($all['clerk_num']) && !empty($all['clerk_num'])){
            $clerk_num = $all['clerk_num'];
        }else{
            $clerk_num = $res[0]['goods_num'];
        }
        //  验证 购买数量-核销数量 是否 小于 核销数量  如果小于错误
        if (($res[0]['goods_num'] - $res[0]['verify_num'] ) < $clerk_num ){
            return responseError([],500,'核销数量错误');
        }else{
            $now_clerk_num = $clerk_num + $res[0]['verify_num'];
        }
        //  修改订单状态
        Db::startTrans();
        //  核销数量等于购买数量完成订单
        if ($now_clerk_num == $res[0]['goods_num']){
            $edit_order = Db::name('ml_tbl_order')->where('order_id',$all['clerkstr'])->update(['order_type'=>3]);
            if (!$edit_order){
                Db::rollback();
                return responseError([],500,'核销失败');
            }
            $edit_details = Db::name('ml_tbl_order_details')->where('order_zid',$order_status['id'])->update(['verify_num'=>$now_clerk_num]);
            if (!$edit_details){
                Db::rollback();
                return responseError([],500,'核销失败');
            }
        }else{
            //  核销数量小于购买数量还可以核销
            $edit_order = Db::name('ml_tbl_order')->where('order_id',$all['clerkstr'])->update(['order_type'=>2]);
            if (!$edit_order){
                Db::rollback();
                return responseError([],500,'核销失败');
            }
            $edit_details = Db::name('ml_tbl_order_details')->where('order_zid',$order_status['id'])->update(['verify_num'=>$now_clerk_num]);
            if (!$edit_details){
                Db::rollback();
                return responseError([],500,'核销失败');
            }
        }
        Db::commit();


        $returnData = [
            '核销分数'=>$now_clerk_num,
            '购买分数'=>$res[0]['goods_num']
        ];
        return responseSuccess($returnData,200,'成功');
    }



    public function bindDevice()
    {
        $all = $this->request->param();
        //  参数是否在
        if (!isset($all['clerkstr']) || empty($all['clerkstr'])){
            return responseError([],400,'缺少必要参数');
        }
        if (!isset($all['time']) || empty($all['time'])){
            return responseError([],400,'缺少时间戳');
        }
        if (!isset($all['device_num']) || empty($all['device_num'])){
            return responseError([],400,'缺少设备号');
        }
        if ( strlen($all['sign']) != 32){
            return responseError([],500,'签名长度错误');
        }

        //  签名生成参数
        $timestring = str_replace('%20', ' ', $all['time']);
        $arr = [
            'device_num'=>$all['device_num'],
            'time'=>$timestring,
            'clerkstr'=>$all['clerkstr'],
        ];
        //  验证签名            TODO: 传输过来为小写,编译对比为大写对比
        $sign = $this->Sign($arr,$all['device_num']);
        if ($sign !==  strtoupper($all['sign'])){
            return responseError([],401,'签名错误');
        }

        //  查看店铺是否存在
        $business_status = Db::name('ml_tbl_business')->where('id',$all['clerkstr'])->find();
        if (!$business_status){
            return responseError([],404,'商户不存在');
        }
        if (!empty($business_status['device_num']) ){
            return responseError([],402,'该设备已被绑定,是否重新绑定');
        }
        //  修改商户设备号
        Db::startTrans();
        $result = Db::name('ml_tbl_business')->where('id',$all['clerkstr'])->update(['device_num'=>$all['device_num']]);
        if ($result){
            Db::commit();
            return responseSuccess([],200,'绑定成功!');
        }else{
            Db::rollback();
            return responseError([],500, '绑定失败');
        }
    }

    public function againBindDevice()
    {
        $all = $this->request->param();
        //  参数是否在
        if (!isset($all['clerkstr']) || empty($all['clerkstr'])){
            return responseError([],400,'缺少必要参数');
        }
        if (!isset($all['time']) || empty($all['time'])){
            return responseError([],400,'缺少时间戳');
        }
        if (!isset($all['device_num']) || empty($all['device_num'])){
            return responseError([],400,'缺少设备号');
        }
        if ( strlen($all['sign']) != 32){
            return responseError([],500,'签名长度错误');
        }

        //  签名生成参数
        $timestring = str_replace('%20', ' ', $all['time']);
        $arr = [
            'device_num'=>$all['device_num'],
            'time'=>$timestring,
            'clerkstr'=>$all['clerkstr'],
        ];
        //  验证签名            TODO: 传输过来为小写,编译对比为大写对比
        $sign = $this->Sign($arr,$all['device_num']);
        if ($sign !==  strtoupper($all['sign'])){
            return responseError([],401,'签名错误');
        }

        //  查看店铺是否存在
        $business_status = Db::name('ml_tbl_business')->where('id',$all['clerkstr'])->find();
        if (!$business_status){
            return responseError([],404,'商户不存在');
        }
        if (empty($business_status['device_num']) ){
            return responseError([],402,'该设备还未被绑定过!');
        }

        //  修改商户设备号
        $status = Db::name('ml_tbl_business')->where('id',$all['clerkstr'])->update(['device_num'=>$all['device_num']]);
        if (!$status){
            return responseError([],500,'绑定失败');
        }
        return responseSuccess([],200,'绑定成功!');
    }
}