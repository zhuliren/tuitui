<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/5/13
 * Time: 17:33
 */

namespace app\index;


use app\common\Model\PublicEnum;

class Controller extends \think\Controller
{

    // 过滤掉emoji表情
    function filter_Emoji($str)
    {
        $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }


    //  签名
    public function getTSign($params)
    {
        ksort($params); //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {  //剔除参数值为空的参数
                $newArr[] = $key . $item; // 整合新的参数数组
            }
        }
        $stringA = implode("", $newArr);  //连接参数
//        $stringSignTemp = $stringA . "&key=A210HOhhog6979ibA89DA0HJO12NNLJL";
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = md5(PublicEnum::TICKET_TRUE_SECRET.$stringA.PublicEnum::TICKET_TRUE_SECRET); //将字符串进行MD5加密
//        $sign = strtoupper($stringSignTemp); //将所有字符转换为大写
        return $stringSignTemp;
    }

    //  签名
    public function Sign($params, $device_num)
    {
        ksort($params); //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {  //剔除参数值为空的参数
                $newArr[] = $key . $item; // 整合新的参数数组
            }
        }
        $stringA = implode("", $newArr);  //连接参数
//        $stringSignTemp = $stringA . "&key=A210HOhhog6979ibA89DA0HJO12NNLJL";
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = md5($params['client_secret'].$stringA .$device_num ); //将字符串进行MD5加密
//        $sign = strtoupper($stringSignTemp); //将所有字符转换为大写
        return  strtoupper($stringSignTemp);
    }


    public function http_url_query($url)
    {
        $info = curl_init();
        curl_setopt($info, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($info, CURLOPT_HEADER, 0);
        curl_setopt($info, CURLOPT_NOBODY, 0);
        curl_setopt($info, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($info, CURLOPT_URL, $url);
        $output = curl_exec($info);
        curl_close($info);

        return $output;
    }

    public function http_send_query($url,$rawData)
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
        curl_setopt($ch,CURLOPT_SSLCERT,ROOT_PATH .'/public/cacert/apiclient_cert.pem'); //这个是证书的位置绝对路径
        curl_setopt($ch,CURLOPT_SSLKEY,ROOT_PATH .'/public/cacert/apiclient_key.pem');
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;


    }

}