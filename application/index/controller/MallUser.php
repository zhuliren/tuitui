<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/17
 * Time: 15:03
 */

namespace app\index\controller;


use app\index\model\UserModel;
use think\Db;

class MallUser
{
    public function userMallRegister()
    {
        $code = $_REQUEST['code'];
        $appid = "wx0fda8074ccdb716d";
        $secret = "bf55d7a720d5bc162621e3901b7645be";
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
            $userdetails = Db::query('SELECT * FROM ml_tbl_user WHERE wechat_open_id = ?', [$openid]);
            if (count($userdetails) == 0) {
                //无用户信息，插入用户信息
                $userdata = ['wechat_open_id' => $openid, 'created_time' => date("Y-m-d h:i:s", time())];
                $user_id = Db::table('ml_tbl_user')->insertGetId($userdata);
                $returndata = array('user_id' => $user_id, 'openid' => $openid, 'is_salesman' => '0');
                $data = array('status' => 0, 'msg' => '登录成功', 'data' => $returndata);
                return json($data);
            } else {
                //返回用户信息
                $userdetails = db('ml_tbl_user')->where('wechat_open_id', $openid)->find();
                $returndata = array('user_id' => $userdetails['id'], 'openid' => $openid, 'is_salesman' => $userdetails['is_salesman']);
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

    public function userMallShareRegister()
    {
        $code = $_REQUEST['code'];
        $up_user_id = $_REQUEST['upid'];
        $appid = "wx0fda8074ccdb716d";
        $secret = "bf55d7a720d5bc162621e3901b7645be";
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
            $userdetails = Db::query('SELECT * FROM ml_tbl_user WHERE wechat_open_id = ?', [$openid]);
            if (count($userdetails) == 0) {
                //无用户信息，插入用户信息
                $userdata = ['wechat_open_id' => $openid, 'created_time' => date("Y-m-d h:i:s", time())];
                $user_id = Db::table('ml_tbl_user')->insertGetId($userdata);
                $is_salesman = 0;
                $returndata = array('user_id' => $user_id, 'openid' => $openid, 'is_salesman' => '0');
            } else {
                //返回用户信息
                $userdetails = db('ml_tbl_user')->where('wechat_open_id', $openid)->find();
                $user_id = $userdetails['id'];
                $is_salesman = $userdetails['is_salesman'];
                $returndata = array('user_id' => $user_id, 'openid' => $openid, 'is_salesman' => $is_salesman);
            }
            //判断是否是同一个id
            if ($up_user_id != $user_id) {
                //查询用户是否存在上级
                $selectchanel = Db::table('ml_tbl_channel')->where('ml_user_id', $user_id)->find();
                if ($selectchanel) {
                    //TODO 判断用户上级是否过期 判断条件为当前用户成为下级后30天无已完成单
                    //查询用户订单情况
                    //30天前和今日时间点
                    $stardatetime = date("Y-m-d", strtotime("-30 day"));
                    $enddatetime = date("Y-m-d");
                    //判断用户是否已经成为下级满足30天
                    if ($selectchanel['creat_time'] < $stardatetime) {
                        $pro_data = Db::table('xm_tbl_pro_data')
                            ->where('user_id', $user_id)
                            ->where('order_type', 3)
                            ->whereTime('pro_datatime', 'between', [$stardatetime, $enddatetime])
                            ->select();
                        if (!$pro_data) {
                            //查询上级是否是代理商
                            $selectupid = Db::table('ml_tbl_user')->where('id', $up_user_id)->find();
                            if ($selectupid['is_salesman'] == 1) {
                                //查询代理商在推推项目的id
                                $selectupxmid = Db::table('ml_xm_binding')->where('ml_user_id', $up_user_id)->find();
                                $upxmid = $selectupxmid['xm_user_id'];
                                //更新渠道表
                                Db::table('ml_tbl_channel')->where('ml_user_id', $user_id)->update(['xm_user_id' => $upxmid, 'creat_time' => date("Y-m-d h:i:s", time())]);
                            }
                        }
                    }
                } else {
                    //查询上级是否是代理商
                    $selectupid = Db::table('ml_tbl_user')->where('id', $up_user_id)->find();
                    if ($selectupid['is_salesman'] == 1 && $is_salesman == 0) {
                        //查询代理商在推推项目的id
                        $selectupxmid = Db::table('ml_xm_binding')->where('ml_user_id', $up_user_id)->find();
                        $upxmid = $selectupxmid['xm_user_id'];
                        //插入渠道表
                        $userdata = ['ml_user_id' => $user_id, 'xm_user_id' => $upxmid, 'creat_time' => date("Y-m-d h:i:s", time())];
                        Db::table('ml_tbl_channel')->insert($userdata);
                    }
                }
            }
            $data = array('status' => 0, 'msg' => '登录成功', 'data' => $returndata);
            return json($data);
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

    public function userBindingGet()
    {
        $user_id = $_REQUEST['userid'];
        //查询用户是否绑定推推项目
        $userModel = new UserModel();
        $isbinding = $userModel->mlxmBinding($user_id);
        if ($isbinding) {
            $data = array('status' => 0, 'msg' => '已绑定', 'data' => '');
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '未绑定', 'data' => '');
            return json($data);
        }
    }

    public function userBindingSet()
    {
        $ml_user_id = $_REQUEST['userid'];
        $xm_user_id = $_REQUEST['xmuserid'];
        $xm_user_pwd = md5(md5($_REQUEST['xmpwd']));
        $userModel = new UserModel();
        $isbinding = $userModel->mlxmBinding($ml_user_id);
        if ($isbinding) {
            $data = array('status' => 1, 'msg' => '已绑定过推推项目', 'data' => '');
            return json($data);
        } else {
            //判断用户密码是否正确
            $selectuserpwd = Db::table('xm_tbl_user')->where('id', $xm_user_id)->where('user_pwd', $xm_user_pwd)->value('user_pwd');
            if ($selectuserpwd) {
                $userdata = ['ml_user_id' => $ml_user_id, 'xm_user_id' => $xm_user_id, 'creat_time' => date("Y-m-d h:i:s", time())];
                Db::table('ml_xm_binding')->insert($userdata);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '密码错误', 'data' => '');
                return json($data);
            }
        }
    }

    public function userMoBinding()
    {
        $ml_user_id = $_REQUEST['userid'];
        $userModel = new UserModel();
        $isbinding = $userModel->mlxmBinding($ml_user_id);
        if ($isbinding) {
            $data = array('status' => 1, 'msg' => '已绑定过推推项目', 'data' => '');
            return json($data);
        } else {
            $userdata = ['ml_user_id' => $ml_user_id, 'created_time' => date("Y-m-d h:i:s", time())];
            Db::table('ml_xm_binding')->insert($userdata);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            return json($data);
        }
    }

    public function userCentre()
    {
        $user_id = $_REQUEST['userid'];
        //查询用户是否绑定推推项目
        $userModel = new UserModel();
        $isbinding = $userModel->mlxmBinding($user_id);
        $issalesman = Db::table('ml_tbl_user')->where('id', $user_id)->find();
        if ($isbinding) {
            //获取用户在推推项目的id
            $selectxmuserid = Db::table('ml_xm_binding')->where('ml_user_id', $user_id)->find();
            if ($selectxmuserid['xm_user_id']) {
                //查询用户优惠券数量
                $selectcoupon = Db::table('xm_tbl_coupon')->where('user_id', $selectxmuserid['xm_user_id'])->select();
                $coupon_num = count($selectcoupon);
            } else {
                $coupon_num = 0;
            }
            $returndata = array('coupon_num' => $coupon_num, 'user_id' => $user_id, 'isbinding' => 1, 'issalesman' => $issalesman['is_salesman']);
        } else {
            $returndata = array('coupon_num' => 0, 'user_id' => $user_id, 'isbinding' => 1, 'issalesman' => $issalesman['is_salesman']);
        }
        $data = array('status' => 1, 'msg' => '未绑定', 'data' => $returndata);
        return json($data);
    }

    public function userCouponList()
    {
        $user_id = $_REQUEST['userid'];
        //查询用户是否绑定推推项目
        $userModel = new UserModel();
        $isbinding = $userModel->mlxmBinding($user_id);
        if ($isbinding) {
            //获取用户在推推项目的id
            $selectxmuserid = Db::table('ml_xm_binding')->where('ml_user_id', $user_id)->find();
            if ($selectxmuserid['xm_user_id']) {
                $xm_user_id = $selectxmuserid['xm_user_id'];
            } else {
                $data = array('status' => 1, 'msg' => '当前无优惠券', 'data' => '');
                return json($data);
            }
        } else {
            $data = array('status' => 1, 'msg' => '当前无优惠券', 'data' => '');
            return json($data);
        }
        //查询优惠券列表
        $pro_num = 0;
        //分组查询，先查询项目类型
        $selectprotype = db('xm_tbl_coupon')->field('pro_id,count(id)')->where('user_id', $xm_user_id)->group('pro_id')->select();
        if ($selectprotype) {
            foreach ($selectprotype as $eachprocoupon) {
                //数据绑定
                $selectproname = db('xm_tbl_pro')->where('id', $eachprocoupon['pro_id'])->find();
                $procoupondetails[$pro_num] = array('pro_id' => $eachprocoupon['pro_id'], 'pro_name' => $selectproname['pro_name'], 'coupon_num' => $eachprocoupon['count(id)']);
                $pro_num++;
            }
            $returndata = array('pro_num' => $pro_num, 'pro_coupon_details' => $procoupondetails);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '当前无优惠券', 'data' => '');
            return json($data);
        }
    }

    public function isClerk()
    {
        $user_id = $_REQUEST['userid'];
        $isclerk = Db::table('ml_tbl_business_clerk')->where('user_id', $user_id)->find();
        if ($isclerk) {
            $returndata = array('type' => $isclerk['type'], 'name' => $isclerk['name'], 'cardid' => $isclerk['cardid'],'business_id'=>$isclerk['business_id']);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '非核销员', 'data' => '');
        }
        return json($data);
    }

}