<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 16:43
 */

namespace app\index\controller;


use think\Db;

class WechatPay
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
        } else {
            $data = array('status' => 1, 'msg' => $result['return_msg'], 'data' => '');
        }
        return json($data);
    }

    public function payNotify()
    {
    }

    public function orderQuery()
    {
        $order_id = $_REQUEST['orderid'];
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
            if ($result['result_code'] == 'SUCCESS') {
                if ($type == 1) {
                    //修改订单状态
                } elseif ($type == 2) {
                    //TODO 判断订单类型为核销的还是发货的
                    //TODO 目前暂定为发货类型订单
                    $order_type = 1;
                    //修改订单状态
                    Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_type' => $order_type, 'pay_time' => date("Y-m-d H:i:s", time())]);
                }
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => $result['err_code_des'], 'data' => '');
            }
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
}