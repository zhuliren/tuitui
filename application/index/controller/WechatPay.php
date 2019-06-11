<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 16:43
 */

namespace app\index\controller;


use app\common\Model\PublicEnum;
use app\index\Controller;
use app\index\model\MenPiao;
use think\Db;
use think\Request;

class WechatPay extends Controller
{
    public function unifiedorder()
    {
        //订单号
        $order_id = $_REQUEST['orderid'];
        //订单类别（1.推推项目 2.推推优选商城）
        $order_type = $_REQUEST['ordertype'];
        //随机字符串32位
        if ($order_type == 1) {
            //查询订单价格
            $selectorderprice = Db::table('xm_tbl_order')->where('order_id', $order_id)->find();
            $order_price = $selectorderprice['order_price'] * 100;
            $appid = 'wx4473d33d20a8d3b3';
            $body = '推推项目';
            //查询用户openid
            $selectopenid = Db::table('xm_tbl_user')->where('id', $selectorderprice['user_id'])->find();
            $openid = $selectopenid['wechat_open_id'];
        } elseif ($order_type == 2) {
            $selectorderprice = Db::table('ml_tbl_order')->where('order_id', $order_id)->find();
            $order_price = $selectorderprice['pay_price'] * 100;
            $appid = 'wx0fda8074ccdb716d';
            $body = '推推优享商城';
            //查询用户openid
            $selectopenid = Db::table('ml_tbl_user')->where('id', $selectorderprice['user_id'])->find();
            $openid = $selectopenid['wechat_open_id'];
        }
        $data = array(
            'appid' => $appid,//小程序appid
            'body' => $body,  //商品描述
            'mch_id' => '1501953711',//商户号
            'nonce_str' => $this->nonce_str(),//随机字符串
            'notify_url' => 'https://tuitui.tango007.com/sjht/public/payNotify',//通知地址
            'out_trade_no' => $order_id,//商户订单号
            'spbill_create_ip' => '192.168.0.2',//终端IP
            'total_fee' => $order_price,//标价金额
            'trade_type' => 'JSAPI',//交易类型
            'openid' => $openid//交易类型
        );
        $sign = $this->getSign($data);//签名
        $data['sign'] = $sign;
        $xmldata = $this->ToXml($data);//数组转化为xml
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $res = $this->http_request($url, $xmldata);
        $result = $this->FromXml($res);
        //判断返回结果
        if ($result['return_code'] == 'SUCCESS') {
            if ($result['result_code'] == 'SUCCESS') {
                $time = time();
                $info = array(
                    'appId' => $appid,
                    'timeStamp' => "" . $time . "",
                    'nonceStr' => $this->nonce_str(),
                    'package' => 'prepay_id=' . $result['prepay_id'],
                    'signType' => 'MD5',
                );
                $paySign = $this->getSign($info);
                $info['paySign'] = $paySign;
                $data = array('status' => 0, 'msg' => '成功', 'data' => $info);
            } elseif ($result['result_code'] == 'FAIL') {
                if ($result['err_code'] == 'ORDERPAID') {
                    $order_type = 2;
                    //修改订单状态
                    Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_type' => $order_type, 'pay_time' => date("Y-m-d h:i:s", time())]);
                    //判断否是第三方系统下单

                    $data = array('status' => 1, 'msg' => '订单已支付', 'data' => '');
                } else {
                    $data = array('status' => 1, 'msg' => $result['err_code_des'], 'data' => '');
                }
            }
        } else {
            $data = array('status' => 1, 'msg' => $result['return_msg'], 'data' => '');
        }
        return json($data);
    }


    public function xmWechatPay(Request $request)
    {
        $all = $request->param();
        if (isset($all['orderid']) && !empty($all['orderid'])){
            $order_id = $all['orderid'];
            $selectorderprice = Db::table('xm_tbl_order')->where('order_id', $order_id)->find();
            $order_price = $selectorderprice['order_price'] * 100;
            $appid = PublicEnum::WX_XM_APPID;
            $body = '推推项目';
            $openid = Db::name('xm_tbl_user')->where('id',$selectorderprice['user_id'])->value('wechat_open_id');
        }else{
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }
        $data = [
            'appid' => $appid,//小程序appid
            'body' => $body,  //商品描述
            'mch_id' => '1501953711',//商户号
            'nonce_str' => $this->nonce_str(),//随机字符串
            'notify_url' => 'https://tuitui.tango007.com/sjht/public/payNotify',//通知地址
            'out_trade_no' => $order_id,//商户订单号
            'spbill_create_ip' => '192.168.0.2',//终端IP
            'total_fee' => $order_price,//标价金额
            'trade_type' => 'JSAPI',//交易类型
            'openid' => $openid//交易类型
        ];

        $sign = $this->getSign($data);//签名
        $data['sign'] = $sign;
        $xmldata = $this->ToXml($data);//数组转化为xml
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $res = $this->http_request($url, $xmldata);
        $result = $this->FromXml($res);

        //判断返回结果
        if ($result['return_code'] == 'SUCCESS') {
            if ($result['result_code'] == 'SUCCESS') {
                $time = time();
                $info = array(
                    'appId' => $appid,
                    'timeStamp' => "" . $time . "",
                    'nonceStr' => $this->nonce_str(),
                    'package' => 'prepay_id=' . $result['prepay_id'],
                    'signType' => 'MD5',
                );
                $paySign = $this->getSign($info);
                $info['paySign'] = $paySign;
                $data = array('status' => 0, 'msg' => '成功', 'data' => $info);
            } elseif ($result['result_code'] == 'FAIL') {
                if ($result['err_code'] == 'ORDERPAID') {
                    $order_type = 2;
                    //修改订单状态
                    Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_type' => $order_type, 'pay_time' => date("Y-m-d h:i:s", time())]);
                    //判断否是第三方系统下单

                    $data = array('status' => 1, 'msg' => '订单已支付', 'data' => '');
                } else {
                    $data = array('status' => 1, 'msg' => $result['err_code_des'], 'data' => '');
                }
            }
        } else {
            $data = array('status' => 1, 'msg' => $result['return_msg'], 'data' => '');
        }
        return json($data);
    }

    //TODO
    public function payNotify()
    {
    }

    public function orderQuery()
    {
        $order_id = $_REQUEST['orderid'];
        if (isset($_REQUEST['order_type']) && !empty($_REQUEST['order_type'])){
            $goods_type = $_REQUEST['order_type'];
        }else{
            $goods_type = 2;
        }
        $order_info = Db::table('ml_xm_order_summary')->where('order_id', $order_id)->find();
        $type = $order_info['type'];
        if ($type == 1) {
            $appid = 'wx4473d33d20a8d3b3';
        } elseif ($type == 2) {
            $appid = 'wx0fda8074ccdb716d';
        }
        $data = array(
            'appid' => $appid,//小程序appid
            'mch_id' => '1501953711',//商户号
            'out_trade_no' => $order_id,//商户订单号
            'nonce_str' => $this->nonce_str(),//随机字符串
        );
        $sign = $this->getSign($data);//签名
        $data['sign'] = $sign;
        $xmldata = $this->ToXml($data);//数组转化为xml
        $url = 'https://api.mch.weixin.qq.com/pay/orderquery';
        $res = $this->http_request($url, $xmldata);
        $result = $this->FromXml($res);
        //判断返回结果
        if ($result['return_code'] == 'SUCCESS') {
            if (isset($result['trade_state']) == 'SUCCESS') {
                if ($type == 1) {
                    if (isset($_REQUEST['procardnum']) && !empty($_REQUEST['procardnum'])){
                        $goods_num = $_REQUEST['procardnum'];
                    }else{
                        return json(['status'=>1,'msg'=>'缺少参数','data'=>'']);
                    }
                    //  插入生成卡记录
                    $xm_order_info = Db::table('xm_tbl_order')->where('order_id', $order_id)->find();
                    $xm_price = Db::name('xm_tbl_pro_cardstage')->where('pro_id',$xm_order_info['pro_id'])->value('card_price');
                    $now_time = date('Y-m-d H:i:s',time());
                    for ($i=0;$i < $goods_num;$i++){
                        $arr = [
                            'user_id'=>$xm_order_info['user_id'],
                            'pro_id'=>$xm_order_info['pro_id'],
                            'pro_stage_id'=>1,
                            'pro_card_oriprice'=>$xm_price,
                            'pro_card_newprice'=>$xm_price,
                            'pro_card_lasttrantime'=>$now_time,
                            'pro_card_firstrantime'=>$now_time,
                            'pro_card_pprice'=>$xm_price
                        ];
                        Db::name('xm_tbl_pro_card')->insert($arr);
                    }
                    //修改订单状态
                    $xm_goods_id = Db::table('xm_tbl_order')->where('order_id', $order_id)->value('pro_id');

                    Db::table('xm_tbl_order')->where('order_id', $order_id)->update(['pay_state' => 1]);
                    $res = Db::table('xm_tbl_pro_cardstage')->where('pro_id',$xm_goods_id)->setInc('agentcard_used',$goods_num);
                    if ($res ){
                        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
                    }else{
                        $data = array('status' => 1, 'msg' => '订单出错,请联系客服!', 'data' => '');
                    }
                } elseif ($type == 2) {
                    //TODO 判断订单类型为核销的还是发货的
                    //TODO 目前暂定为核销类型订单
                    if ($goods_type == 1 ){
                        $order_type = 6;
                    }else{
                        $order_type = 2;
                    }
                    //修改订单状态
                    Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_type' => $order_type, 'pay_time' => date("Y-m-d h:i:s", time())]);
                    $order_data = Db::table('ml_tbl_order')->where('order_id', $order_id)->find();
                    $order_zid = $order_data['id'];
                    //修改商品库存及商品售出

                    //查询商品是否为第三方订单商品
                    $goods_data = Db::table('ml_tbl_order_details')->where('order_zid', $order_zid)->select();
                    foreach ($goods_data as $goodsitem) {
                        $goods_id = $goodsitem['goods_id'];
                        //查询商品是否为第三方商品
                        $goods_item_data = Db::table('ml_tbl_goods_two')->where('id', $goods_id)->find();
                        $format_data = Db::name('ml_tbl_goods_format')->where('id',$goodsitem['format_id'])->find();
                        $order_details_id = $goodsitem['id'];
                        if ($goods_item_data['third_id'] == 1) {
                            //调用123票务下单系统
                            $menPiao = new MenPiao();
                            $third_number = $format_data['third_number'];
                            $user_name = $order_data['user_name'];
                            $user_phone = $order_data['phone'];
                            $third_znumber = $format_data['third_znumber'];
                            $goods_num = $goodsitem['goods_num'];
                            if (isset($order_data['fixtime']) && !empty($order_data['fixtime'])){
                                $fixdate = $order_data['fixtime'];
                            }else{
                                $fixdate = '';
                            }
                            $creatMenPiaoOrder = $menPiao->creatMenPiaoOrder($third_number, $order_id, $user_name, $user_phone, $third_znumber, $goods_num,$fixdate);
                            if ($creatMenPiaoOrder == 'yes') {
                                $third_isconfirm = 1;
                                //成功后修改订单状态
                                Db::table('ml_tbl_order_details')->where('id', $order_details_id)->update(['third_isconfirm' => $third_isconfirm]);
                                Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_state'=>$creatMenPiaoOrder]);
                            } else {
                                Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_state' => $creatMenPiaoOrder]);
                            }
                        }
                    }
                    $data = array('status' => 0, 'msg' => '成功', 'data' => '');
                }
            } else {
                $data = array('status' => 1, 'msg' => '订单未支付', 'data' => '');
            }
        }else{
            $data = array('status' => 1, 'msg' => $result['err_code_des'], 'data' => '');
        }

        return json($data);
    }

    public function nonce_str()
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < 32; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function getSign($params)
    {
        ksort($params); //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {  //剔除参数值为空的参数
                $newArr[] = $key . '=' . $item; // 整合新的参数数组
            }
        }
        $stringA = implode("&", $newArr);  //使用 & 符号连接参数
        $stringSignTemp = $stringA . "&key=A210HOhhog6979ibA89DA0HJO12NNLJL";
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = md5($stringSignTemp); //将字符串进行MD5加密
        $sign = strtoupper($stringSignTemp); //将所有字符转换为大写
        return $sign;
    }

    public function FromXml($xml)
    {
        if (!$xml) {
            echo "xml数据异常！";
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    public function http_request($url, $rawData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:text'));
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function ToXml($data = array())
    {
        if (!is_array($data) || count($data) <= 0) {
            return '数组数据异常';
        }
        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }


    /**
     * @param Request $request
     * @time: 2019/5/21
     * @autor: duheyuan
     * 提现--
     */
    //TODO  提现,未完成,等待平台申请
    public function doWithdraw(Request $request)
    {
//        if ($request->isPost()){
        $all = $request->param();
        if (isset($all['order_no']) && !empty($all['order_no'])){
            $order_info = Db::name('ml_tbl_withdraw')->where('order_no',$all['order_no'])->find();
            $openid = Db::name('ml_tbl_user')->where('id',$order_info['uid'])->value('wechat_open_id');
            $data = array(
                'mch_appid' => PublicEnum::WX_APPID ,//小程序appid
                'mchid' => '1501953711',//商户号
                'nonce_str' => $this->nonce_str(),//随机字符串
//                    'notify_url' => 'https://tuitui.tango007.com/sjht/public/payNotify',//通知地址
                'partner_trade_no' => $all['order_no'],//商户订单号
                'spbill_create_ip' => '192.168.0.2',//终端IP
                'amount' => $order_info['amount'] * 100,//标价金额
                'openid' => $openid,
                'check_name' => 'NO_CHECK',//校验用户姓名选项
                'desc' => '用户提现',

            );

            $sign = $this->getSign($data);//签名
            $data['sign'] = $sign;
            $xmldata = $this->ToXml($data);//数组转化为xml
            $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
            $res = $this->http_send_query($url, $xmldata);
            $result = $this->FromXml($res);
            dump($result);die;
        }

//        }

    }

}