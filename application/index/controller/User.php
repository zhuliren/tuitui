<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 12:38
 */

namespace app\index\controller;


use app\index\model\UserModel;
use think\Db;

class user
{
    public function userRegister()
    {
        $code = $_REQUEST['code'];
        $appid = "wx4473d33d20a8d3b3";
        $secret = "a1904ad7e0ab761657a294bc00352c3d";
        $URL = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
        $header[] = "Cookie: " . "appver=1.5.0.75771;";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, '');
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, true);
        if (isset($output['openid']) || (isset($output['errcode']) ? $output['errcode'] : 0) == 0) {
            $openid = $output['openid'];
            //查询是否有该openid在数据库中
            $userdetails = Db::query('SELECT * FROM xm_tbl_user WHERE wechat_open_id = ?', [$openid]);
            if (count($userdetails) == 0) {
                //无用户信息，插入用户信息
                $userdata = ['wechat_open_id' => $openid, 'created_time' => date("Y-m-d H:i:s", time())];
                $user_id = Db::table('xm_tbl_user')->insertGetId($userdata);
                $returndata = array('user_id' => $user_id, 'openid' => $openid, 'user_type' => '0', 'user_type_msg' => '普通用户');
                $data = array('status' => 0, 'msg' => '登录成功', 'data' => $returndata);
                return json($data);
            } else {
                //返回用户信息
                $userdetails = db('xm_tbl_user')->where('wechat_open_id', $openid)->find();
                if ($userdetails['up_code'] == null) {
                    $user_type_msg = '普通用户';
                    $user_type = '0';
                } else {
                    $user_type_msg = '被邀请用户';
                    $user_type = '1';
                }
                $returndata = array('user_id' => $userdetails['id'], 'openid' => $openid, 'user_type' => $user_type, 'user_type_msg' => $user_type_msg);
                $data = array('status' => 0, 'msg' => '登录成功', 'data' => $returndata);
                return json($data);
            }
        } else if ($output['errcode'] == 40029) {
            $data = array('status' => 1, 'msg' => 'code无效', 'data' => '');
            return json($data);
        } else if ($output['errcode'] == 45011) {
            $data = array('status' => 1, 'msg' => '频率限制，每个用户每分钟100次', 'data' => '');
            return json($data);
        } else if ($output['errcode'] == -1) {
            $data = array('status' => 1, 'msg' => '微信系统繁忙稍后再试', 'data' => '');
            return json($data);
        } else if ($output['errcode'] == 40163) {
            $data = array('status' => 1, 'msg' => 'code已经被使用了', 'data' => '');
            return json($data);
        }
    }

    public function userInfoSet()
    {
        $selectcardhistory = db('xm_tbl_pro_card_history')->where('last_user_id',1)->find();
        if($selectcardhistory){
            return 1;
        }else{
            return 2;
        }
    }

    public function userUpCodeSet()
    {
        $user_id = $_REQUEST['userid'];
        $upcode = $_REQUEST['upcode'];
        //查询用户是否存在
        $selectuser = Db::table('xm_tbl_user')->where('id', $user_id)->find();
        if (isset($selectuser)) {
            //检查是否已经绑定过了
            $selectcode = Db::table('xm_tbl_user')->where('id', $user_id)->value('user_code');
            if (isset($selectcode)) {
                $data = array('status' => 1, 'msg' => '已绑定邀请码', 'data' => '');
                return json($data);
            } else {
                //检查upcode是否存在
                $selectupcode = Db::table('xm_tbl_user')->where('user_code', $upcode)->find();
                if (isset($selectupcode)) {
                    //生成邀请码
                    $userModel = new UserModel();
                    do {
                        //生成邀请码
                        $code = $userModel->generateCode();
                        $isBeCode = Db::table('xm_tbl_user')->where('user_code', $code)->find();
                        //判断邀请码是否重复
                    } while ($isBeCode != null);
                    //插入邀请码
                    Db::table('xm_tbl_user')->where('id', $user_id)->update(['up_code' => $upcode, 'user_code' => $code]);
                    $data = array('status' => 0, 'msg' => '绑定成功', 'data' => '');
                    return json($data);
                } else {
                    $data = array('status' => 1, 'msg' => '邀请码错误', 'data' => '');
                    return json($data);
                }
            }
        } else {
            $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
            return json($data);
        }
    }
}