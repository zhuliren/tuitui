<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/26
 * Time: 9:12
 */

namespace app\index\model;


use think\Db;

class MenPiao
{
    //TODO 123门票对接model

    //创建订单
    public function creatMenPiaoOrder($third_id, $order_id, $user_name, $user_phone, $thrid_number, $goods_num)
    {
        //文档地址http://doc.123menpiao.com/docs/openapi/order
        $client_id = '10518da3';
        $timestamp = date('Y-m-d h:i:s', time());
        $product_number = $third_id;
        $partner_order_number = $order_id;
        $name = $user_name;
        $tel = $user_phone;
        $line_items = "[{\"variant_number\":\"" . $thrid_number . "\",\"quantity\":" . $goods_num . "}]";
        $params = array(
            'client_id' => $client_id,
            'timestamp' => $timestamp,
            'product_number' => $product_number,
            'partner_order_number' => $partner_order_number,
            'name' => $name,
            'tel' => $tel,
            'line_items' => $line_items,
        );
        $signature = $this->getSign($params);
        $params['signature'] = $signature;
        $url = 'http://api.123menpiao.com/vapi/v1/distributor/orders';
        $res = $this->http_request_post($url, $params);
        $result = json_decode($res, true);
        //判断是否下单成功
        if ($result['code'] == 200) {
            return true;
        } elseif ($result['code'] == 400) {
            //下单失败

        }
        return false;
    }

    //获取商品信息接口
    public function getProducts($id)
    {
        //文档地址http://doc.123menpiao.com/docs/openapi/product#%E8%8E%B7%E5%8F%96%E5%8D%95%E4%B8%AA%E5%95%86%E5%93%81%E4%BF%A1%E6%81%AF[v2]
        $client_id = '10518da3';
        $timestamp = date('Y-m-d h:i:s', time());
        $timestring = str_replace(' ', '%20', $timestamp);
        $params = array(
            'client_id' => $client_id,
            'timestamp' => $timestamp,
            'id' => $id
        );
        $signature = $this->getSign($params);
        $url = 'http://api.123menpiao.com/vapi/v2/distributor/products/' . $id . '?timestamp=' . $timestring . '&client_id=' . $client_id . '&id=' . $id . '&signature=' . $signature;
        $res = $this->http_request_get($url);
        return $res;
    }

    public function getPriductsList($page){
        //文档地址http://doc.123menpiao.com/docs/openapi/product#%E8%8E%B7%E5%8F%96%E5%95%86%E5%93%81%E4%BF%A1%E6%81%AF%E5%88%97%E8%A1%A8[v2]
        $client_id = '10518da3';
        $timestamp = date('Y-m-d h:i:s', time());
        $timestring = str_replace(' ', '%20', $timestamp);
        $params = array(
            'client_id' => $client_id,
            'timestamp' => $timestamp,
            'page' => $page
        );
        $signature = $this->getSign($params);
        $url = 'http://api.123menpiao.com/vapi/v2/distributor/products?timestamp=' . $timestring . '&client_id=' . $client_id . '&page=' . $page . '&signature=' . $signature;
        $res = $this->http_request_get($url);
        return $res;
    }

    //生成签名
    public function getSign($params)
    {
        $client_secret = 'fd1a7fec15a5';
        ksort($params); //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {  //剔除参数值为空的参数
                $newArr[] = $key . $item; // 整合新的参数数组
            }
        }
        $stringA = implode("", $newArr);  //连接参数
        $stringSignTemp = $client_secret . $stringA . $client_secret;
        $sign = md5($stringSignTemp); //将字符串进行MD5加密
        return $sign;
    }

    public function http_request_get($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function http_request_post($url, $rawData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}