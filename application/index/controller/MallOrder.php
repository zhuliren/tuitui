<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/18
 * Time: 9:25
 */

namespace app\index\controller;


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
        if ($order_type > 5 || $order_type < 0) {
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
                $selectgoodsheadimg = Db::table('ml_view_order_goods')->where('order_zid', $order_zid)->select();
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
        $order_id = $user_id . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //计算订单金额
        $selectgoodsprice = Db::table('ml_tbl_goods')->where('id', $goods_id)->find();
        $goods_stock = $selectgoodsprice['goods_stock'];
        $goods_sell_out = $selectgoodsprice['goods_sell_out'];
        $goods_price = $selectgoodsprice['goods_price'];
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
        $intoorderdata = array('order_zid' => $order_zid, 'goods_id' => $goods_id, 'goods_num' => $goods_num, 'goods_price' => $goods_price);
        Db::table('ml_tbl_order_details')->insert($intoorderdata);
        //修改商品库存
        $new_goods_stock = $goods_stock - $goods_num;
        $new_goods_sell_out = $goods_sell_out + $goods_num;
        Db::table('ml_tbl_goods')->where('id', $goods_id)->update(['goods_stock' => $new_goods_stock, 'goods_sell_out' => $new_goods_sell_out]);
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
        if (preg_mobile($_REQUEST['phone'])){
            $phone = $_REQUEST['phone'];
        }else{
            return json(['status'=>1,'msg'=>'手机号码格式不正确','data'=>'']);
        }
        $coupon_id = $_REQUEST['coupon_id'];
        //生成订单id
        $order_id = $user_id . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //计算订单金额
        $selectgoodsdata = Db::table('ml_tbl_goods')->where('id', $goods_id)->find();
        $goods_stock = $selectgoodsdata['goods_stock'];
        $goods_sell_out = $selectgoodsdata['goods_sell_out'];
        $goods_price = $selectgoodsdata['goods_price'];
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
//            dump($selectcoupon);die;
//            if ($selectcoupon['discount'] != 0) {
//                $discount = $selectcoupon['discount'];
//            }
//            $par_val = $selectcoupon['par_value'];
        }else{
            //计算订单需支付金额
            $pay_price = ($goods_sum - $par_val) * $discount - $freight;
        }
        //创建订单
        $order_type = 0;
        if ($coupon_id != 'no') {
            $orderdata = ['order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $user_id,
                'phone' => $phone, 'coupon_id' => $coupon_id, 'freight' => $freight, 'goods_price' => $goods_sum,
                'pay_price' => $pay_price, 'user_name' => $user_name, 'creat_time' => date("Y-m-d h:i:s", time())];
        } else {
            $orderdata = ['order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $user_id,
                'phone' => $phone, 'freight' => $freight, 'goods_price' => $goods_sum, 'pay_price' => $pay_price,
                'user_name' => $user_name, 'creat_time' => date("Y-m-d h:i:s", time())];
        }
        $order_zid = Db::table('ml_tbl_order')->insertGetId($orderdata);
        //插入订单汇总表
        $orderdatasum = array('order_id' => $order_id, 'type' => 2, 'creat_time' => date("Y-m-d h:i:s", time()));
        Db::table('ml_xm_order_summary')->insert($orderdatasum);
        //插入商品详情表
        $intoorderdata = array('order_zid' => $order_zid, 'goods_id' => $goods_id, 'goods_num' => $goods_num, 'goods_price' => $goods_price, 'type' => $type);
        Db::table('ml_tbl_order_details')->insert($intoorderdata);
        $returndata = array('orderid' => $order_id, 'ordertype' => 2);
        //修改商品库存
        $new_goods_stock = $goods_stock - $goods_num;
        $new_goods_sell_out = $goods_sell_out + $goods_num;
        Db::table('ml_tbl_goods')->where('id', $goods_id)->update(['goods_stock' => $new_goods_stock, 'goods_sell_out' => $new_goods_sell_out]);
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
                    $goodsdata = Db::table('ml_tbl_goods')->where('id', $goods_id)->find();
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
            Db::table('ml_tbl_order_details')->where('order_zid', $order_zid)->where('goods_num')->update(['verify_num' => $new_verify_num]);
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
                Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_type' => 3]);
            }
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        }
        return json($data);
    }
}