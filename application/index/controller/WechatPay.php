<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 16:43
 */

namespace app\index\controller;


class WechatPay
{
    public function unifiedorder()
    {
        $order_id = $_REQUEST['orderid'];
        $order_price = 0.1;
        $pro_name = '网约车';
        //随机字符串32位
        $str = "1234567890asdfghjklqwertyuiopzxcvbnmASDFGHJKLZXCVBNMPOIUYTREWQ";
        $str_32 = substr(str_shuffle($str), 0, 32);
        //基础数据
        $appid = 'wx4473d33d20a8d3b3';//公众账号ID
        $mch_id = '1501953711';//商户号
        $nonce_str = $str_32;//随机字符串
        $body = '推推平台' . $pro_name . '商户代理权';//商品描述
        $out_trade_no = $order_id;//商户订单号
        $total_fee = $order_price;//标价金额
        $spbill_create_ip = '192.168.1.154';//终端IP
        $notify_url = 'https://tango007.heeyhome.com/test';//通知地址
        $trade_type = 'JSAPI';//交易类型

        $sign_string = '1';

//        $sign;//签名
    }
}