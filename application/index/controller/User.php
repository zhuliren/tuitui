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
use think\Request;

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
                $userdata = ['wechat_open_id' => $openid, 'created_time' => date("Y-m-d h:i:s", time())];
                $user_id = Db::table('xm_tbl_user')->insertGetId($userdata);
                $returndata = array('user_id' => $user_id, 'openid' => $openid, 'user_type' => '0', 'user_type_msg' => '普通用户', 'user_pwd_type' => 0);
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
                if ($userdetails['user_pwd'] == null) {
                    $user_pwd_type = 0;
                } else {
                    $user_pwd_type = 1;
                }
                $returndata = array('user_id' => $userdetails['id'], 'openid' => $openid, 'user_type' => $user_type, 'user_type_msg' => $user_type_msg, 'user_pwd_type' => $user_pwd_type);
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
        $user_id = $_REQUEST['userid'];
        $user_type = $_REQUEST['type'];
        $user_value = $_REQUEST['value'];
        //判断字段类型
        if ($user_type == 'user_name' || $user_type == 'user_phone' || $user_type == 'user_real_name' || $user_type == 'user_card_id') {
            //直接插入数据
            Db::table('xm_tbl_user')->where('id', $user_id)->update([$user_type => $user_value]);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '字段名不符合规定', 'data' => '');
            return json($data);
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

    public function userPwdSet()
    {
        $user_id = $_REQUEST['userid'];
        $user_pwd = md5(md5($_REQUEST['userpwd']));
        //查询用户是否已设置密码
        $selectuserpwd = Db::table('xm_tbl_user')->where('id', $user_id)->value('user_pwd');
        if ($selectuserpwd) {
            $data = array('status' => 1, 'msg' => '已设置过密码', 'data' => '');
            return json($data);
        } else {
            Db::table('xm_tbl_user')->where('id', $user_id)->update(['user_pwd' => $user_pwd]);
            $data = array('status' => 0, 'msg' => '设置成功', 'data' => '');
            return json($data);
        }
    }

    public function userPwdChange()
    {
        $user_id = $_REQUEST['userid'];
        $old_user_pwd = md5(md5($_REQUEST['olduserpwd']));
        $new_user_pwd = md5(md5($_REQUEST['newuserpwd']));
        //查询用户旧密码是否正确
        $selectuserpwd = Db::table('xm_tbl_user')->where('id', $user_id)->where('user_pwd', $old_user_pwd)->value('user_pwd');
        if ($selectuserpwd) {
            Db::table('xm_tbl_user')->where('id', $user_id)->update(['user_pwd' => $new_user_pwd]);
            $data = array('status' => 0, 'msg' => '设置成功', 'data' => '');
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '原支付密码错误', 'data' => '');
            return json($data);
        }
    }

    public function userBankInfo()
    {
        $user_id = $_REQUEST['userid'];
        //查询用户是否存在
        $selectuser = Db::table('xm_tbl_user')->where('id', $user_id)->find();
        if (isset($selectuser)) {
            //查询用户是否已绑定银行卡
            $selectuserbankcard = Db::table('xm_tbl_user')->where('id', $user_id)->find();
            if (isset($selectuserbankcard['bank_id'])) {
                $bankdata = array('bank_id' => $selectuserbankcard['bank_id'], 'bank_name' => $selectuserbankcard['bank_name']);
                $data = array('status' => 0, 'msg' => '已绑定银行卡', 'data' => $bankdata);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '未绑定银行卡', 'data' => '');
                return json($data);
            }
        } else {
            $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
            return json($data);
        }
    }

    public function userBankSet()
    {
        $user_id = $_REQUEST['userid'];
        $bank_id = $_REQUEST['bankid'];
        $bank_name = $_REQUEST['bankname'];
        $user_pwd = md5(md5($_REQUEST['userpwd']));
        //查询用户是否存在
        $selectuser = Db::table('xm_tbl_user')->where('id', $user_id)->find();
        if (isset($selectuser)) {
            //验证用户密码
            $selectuserpwd = Db::table('xm_tbl_user')->where('id', $user_id)->where('user_pwd', $user_pwd)->value('user_pwd');
            if ($selectuserpwd) {
                //查询用户是否已绑定银行卡
                $selectuserbankcard = Db::table('xm_tbl_user')->where('id', $user_id)->value('bank_id');
                if ($selectuserbankcard) {
                    $data = array('status' => 1, 'msg' => '已绑定过银行卡', 'data' => '');
                    return json($data);
                } else {
                    Db::table('xm_tbl_user')->where('id', $user_id)->update(['bank_name' => $bank_name, 'bank_id' => $bank_id]);
                    $data = array('status' => 0, 'msg' => '绑定成功', 'data' => '');
                    return json($data);
                }
            } else {
                $data = array('status' => 1, 'msg' => '密码错误', 'data' => '');
                return json($data);
            }
        } else {
            $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
            return json($data);
        }
    }

    public function userBankChange()
    {
        $user_id = $_REQUEST['userid'];
        $bank_id = $_REQUEST['bankid'];
        $bank_name = $_REQUEST['bankname'];
        $user_pwd = md5(md5($_REQUEST['userpwd']));
        //查询用户是否存在
        $selectuser = Db::table('xm_tbl_user')->where('id', $user_id)->find();
        if (isset($selectuser)) {
            //查询用户密码是否正确
            $selectuserpwd = Db::table('xm_tbl_user')->where('id', $user_id)->where('user_pwd', $user_pwd)->value('user_pwd');
            if ($selectuserpwd) {
                Db::table('xm_tbl_user')->where('id', $user_id)->update(['bank_id' => $bank_id, 'bank_name' => $bank_name]);
                $data = array('status' => 0, 'msg' => '修改成功', 'data' => '');
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '密码错误', 'data' => '');
                return json($data);
            }
        } else {
            $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
            return json($data);
        }
    }

    public function selUserId()
    {
        $user_id = $_REQUEST['userid'];
        $sel_user_id = $_REQUEST['seluserid'];
        $userModel = new UserModel();
        $user_type = $userModel->userIdentity($user_id);
        switch ($user_type) {
            case -1:
                $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
                return json($data);
            case 0:
                $data = array('status' => 1, 'msg' => '无权限查询', 'data' => '');
                return json($data);
        }
        $sel_user_type = $userModel->userIdentity($sel_user_id);
        switch ($user_type) {
            case -1:
                $data = array('status' => 1, 'msg' => '查询的用户id不存在', 'data' => '');
                return json($data);
            case 0:
                $data = array('status' => 1, 'msg' => '查询的用户id尚未绑定邀请码', 'data' => '');
                return json($data);
        }
        $returndata = array('user_id' => $sel_user_id);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function selUserInfo()
    {
        $user_id = $_REQUEST['userid'];
        $sel_user_id = $_REQUEST['seluserid'];
        $userModel = new UserModel();
        $user_type = $userModel->userIdentity($user_id);
        switch ($user_type) {
            case -1:
                $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
                return json($data);
            case 0:
                $data = array('status' => 1, 'msg' => '无权限查询', 'data' => '');
                return json($data);
        }
        $sel_user_type = $userModel->userIdentity($sel_user_id);
        switch ($user_type) {
            case -1:
                $data = array('status' => 1, 'msg' => '查询的用户id不存在', 'data' => '');
                return json($data);
            case 0:
                $data = array('status' => 1, 'msg' => '查询的用户id尚未绑定邀请码', 'data' => '');
                return json($data);
        }
        //查询用户信息
        $selectuserinfo = Db::table('xm_tbl_user')->where('id', $sel_user_id)->find();
        $returndata = array('user_id' => $selectuserinfo['id'], 'user_name' => $selectuserinfo['user_name'], 'user_phone' => $selectuserinfo['user_phone']);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function myCode()
    {
        $user_id = $_REQUEST['userid'];
        $userdetails = db('xm_tbl_user')->where('id', $user_id)->find();
        $returndata = array('usercode' => $userdetails['user_code']);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function userRegisterWithCode()
    {
        $code = $_REQUEST['code'];
        $up_code = $_REQUEST['upcode'];
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
        //生成用户邀请码
        $userModel = new UserModel();
        $user_code = $userModel->generateCode();
        if (isset($output['openid']) || (isset($output['errcode']) ? $output['errcode'] : 0) == 0) {
            $openid = $output['openid'];
            //查询是否有该openid在数据库中
            $userdetails = Db::query('SELECT * FROM xm_tbl_user WHERE wechat_open_id = ?', [$openid]);
            if (count($userdetails) == 0) {
                //无用户信息，插入用户信息
                $userdata = ['wechat_open_id' => $openid, 'created_time' => date("Y-m-d h:i:s", time()), 'user_code' => $user_code, 'up_code' => $up_code];
                $user_id = Db::table('xm_tbl_user')->insertGetId($userdata);
                $returndata = array('user_id' => $user_id, 'openid' => $openid, 'user_type' => '1', 'user_type_msg' => '被邀请用户', 'user_pwd_type' => 0);
                $data = array('status' => 0, 'msg' => '登录成功', 'data' => $returndata);
                return json($data);
            } else {
                //返回用户信息
                $userdetails = db('xm_tbl_user')->where('wechat_open_id', $openid)->find();
                if ($userdetails['up_code'] == null) {
                    //插入邀请码
                    Db::table('xm_tbl_user')->where('wechat_open_id', $openid)->update(['user_code' => $user_code, 'up_code' => $up_code]);
                }
                $user_type_msg = '被邀请用户';
                $user_type = '1';
                if ($userdetails['user_pwd'] == null) {
                    $user_pwd_type = 0;
                } else {
                    $user_pwd_type = 1;
                }
                $returndata = array('user_id' => $userdetails['id'], 'openid' => $openid, 'user_type' => $user_type, 'user_type_msg' => $user_type_msg, 'user_pwd_type' => $user_pwd_type);
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

    public function myChannel()
    {
        $user_id = $_REQUEST['userid'];
        //判断用户状态
        $userModel = new UserModel();
        $user_type = $userModel->userIdentity($user_id);
        switch ($user_type) {
            case -1:
                $data = array('status' => 1, 'msg' => '查询的用户id不存在', 'data' => '');
                return json($data);
            case 0:
                $data = array('status' => 1, 'msg' => '无权限查询', 'data' => '');
                return json($data);
        }
        //查询我的邀请码
        $selectmycode = Db::table('xm_tbl_user')->where('id', $user_id)->value('user_code');
        //查询我的渠道下级
        $selectmychannel = Db::table('xm_tbl_user')->where('up_code', $selectmycode)->select();
        //数据重组
        $channel_num = 0;
        if ($selectmychannel) {
            foreach ($selectmychannel as $eachchannel) {
                //数据绑定
                $mychanneldetails[$channel_num] = array('user_id' => $eachchannel['id'], 'user_name' => $eachchannel['user_name'], 'user_phone' => $eachchannel['user_phone']);
                $channel_num++;
            }
            $returndata = array('channel_num' => $channel_num, 'channel_details' => $mychanneldetails);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '当前无下级渠道', 'data' => '');
            return json($data);
        }
    }

    public function userInfoGet()
    {
        $user_id = $_REQUEST['userid'];
        $selectuserinfo = Db::table('xm_tbl_user')->where('id', $user_id)->find();
        if ($selectuserinfo) {
            //用户信息绑定
            $returndata = array('userid' => $selectuserinfo['id'], 'username' => $selectuserinfo['user_name'], 'phone' => $selectuserinfo['user_phone'], 'realname' => $selectuserinfo['user_real_name'], 'cardid' => $selectuserinfo['user_card_id']);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
            return json($data);
        }
    }

    public function bindingMalluser()
    {
        $user_id = $_REQUEST['userid'];
        $ml_user_id = $_REQUEST['mluserid'];
        //判断用户是否存在
        $userModel = new UserModel();
        $isbinding = $userModel->mlxmBinding($ml_user_id);
        if ($isbinding) {
            $data = array('status' => 1, 'msg' => '已绑定过推推项目', 'data' => '');
            return json($data);
        } else {
            $user_type = $userModel->userIdentity($user_id);
            $selesmantype = 0;
            switch ($user_type) {
                case -1:
                    $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
                    return json($data);
                //判断用户当前是否为代理商
                case 2:
                    $selesmantype = 1;
            }
            //TODO 目前绑定推推项目均为分销员，到期时间为5月31日
            $selesmantype = 1;
            $salesman_due = '2019-05-31';
            //绑定
            $userdata = ['ml_user_id' => $ml_user_id, 'xm_user_id' => $user_id, 'creat_time' => date("Y-m-d h:i:s", time())];
            Db::table('ml_xm_binding')->insert($userdata);
            //设置用户代理
            Db::table('ml_tbl_user')->where('id', $ml_user_id)->update(['is_salesman' => $selesmantype, 'salesman_due' => $salesman_due]);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            return json($data);
        }
    }

    public function getUserPact()
    {
        $res =  Db::name('ml_tbl_user_pact')->where(['id'=>1])->find();
        return json(['status'=>1001,'msg'=>'成功','data'=>$res]);
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @time: 2019/5/22
     * @autor: duheyuan
     * 新增提现信息
     */
    public function createWithdrawOrder(Request $request)
    {
        if ($request->isPost()){
            $all = $request->param();
            if (isset($all['id']) && !empty($all['id'])){
                $userBank = Db::name('ml_tbl_user_bank_card')->where('uid',$all['id'])->find();
                if (!$userBank){
                    return json(['status'=>3002,'msg'=>'未绑定银行卡,请先绑定银行卡','data'=>'']);
                }
                $userInfo = Db::name('ml_tbl_user')->where('id',$all['id'])->find();
                if ($userInfo <= 0){
                    return json(['status'=>3001,'msg'=>'该用户不存在','data'=>'']);
                }
                if(!isset($all['amount']) || empty($all['amount'])){
                    return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
                }
                $arr = [
                    'uid'=>$all['id'],
                    'order_no'=>randomOrder_no(),
                    'amount'=>$all['amount'],
                    'ctime'=>time(),
                    'desc'=>'提现'
                ];
                $res = Db::name('ml_tbl_withdraw')->insert($arr);
                if ($res >0 ){
                    return json(['status'=>1001,'msg'=>'成功','data'=>'']);
                }else{
                    return json(['status'=>5001,'msg'=>'订单号提交失败,请重新点击','data'=>'']);
                }
            }else{
                return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
            }
        }else{
            return json(['status'=>5001,'msg'=>'请求方法错误','data'=>'']);
        }
    }

    pubLic function insertBankInfo(Request $request)
    {
        if ($request->isPost()){
            $all = $request->param();
            if (empty($all['uid'])){
                return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
            }else{
                if (Db::name('ml_tbl_user_bank_card')->where('uid',$all['uid'])->find()){
                    return json(['status'=>2010,'msg'=>'该用户已绑定银行卡','data'=>'']);
                }
            }
            if (empty($all['name'])){
                return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
            }
            if (empty($all['card_id'])){
                return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
            }

            if(!namePreg($all['name'])){
                return json(['status'=>2005,'msg'=>'用户名不正确','data'=>'']);
            }
            if (!empty($all['tel'])){
                if( !preg_mobile($all['tel'])){
                    return json(['status'=>2001,'msg'=>'手机格式不正确','data'=>'']);
                }
            }
            $url = "https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardBinCheck=true&cardNo=".$all['card_id'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $url);
            $res = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($res, true);
            if (!$result['validated']){
                return json(['status'=>2001,'msg'=>'银行卡号错误错误','data'=>'']);
            }
            $all['ctime'] = time();
            if (Db::name('ml_tbl_user_bank_card')->insert($all)){
                return json(['status'=>1001,'msg'=>'新增成功','data'=>'']);
            }else{
                return json(['status'=>5005,'msg'=>'新增成功','data'=>'']);
            }

        }else{
            return json(['status'=>5001,'msg'=>'方法错误','data'=>'']);
        }

    }

}