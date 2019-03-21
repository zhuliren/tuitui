<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21
 * Time: 13:48
 */

namespace app\index\model;


use think\Model;

class UserModel extends Model
{
    //生成邀请码
    public function generateCode()
    {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0, 25)]
            . strtoupper(dechex(date('m')))
            . date('d')
            . substr(time(), -5)
            . substr(microtime(), 2, 5)
            . sprintf('%02d', rand(0, 99));
        for (
            $a = md5($rand, true),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            $d = '',
            $f = 0;
            $f < 6;
            $g = ord($a[$f]),
            $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
            $f++
        ) ;
        return $d;
    }

    //判断用户身份
    public function userIdentity($user_id)
    {
        //查询用户表
        $userdetails = db('xm_tbl_user')->where('wechat_open_id', $openid)->find();
        if ($userdetails['up_code'] == null) {
            $user_type_msg = '普通用户';
            $user_type = '0';
        } else {
            $user_type_msg = '被邀请用户';
            $user_type = '1';
        }
    }
}