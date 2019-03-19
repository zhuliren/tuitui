<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 12:38
 */

namespace app\index\controller;


class user
{
    public function userRegister()
    {
//        $code  =  $_REQUEST['code'];
        $code = "te6565656st";
        $appid = "wx4473d33d20a8d3b3";
        $secret = "a1904ad7e0ab761657a294bc00352c3d";
        $URL = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
        $header[] = "Cookie: " . "appver=1.5.0.75771;";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, '');
        $output = curl_exec($ch);
        curl_close($ch);
        return json($output);
    }

    public function userLogin()
    {
        $user_id = $_REQUEST['user_id'];
        return $user_id;
    }
}