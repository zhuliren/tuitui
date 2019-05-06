<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/17
 * Time: 15:03
 */

namespace app\index\controller;


use app\common\Model\PublicEnum;
use app\index\model\UserModel;
use think\Db;
use think\Request;
use think\WXBizDataCrypt;


class MallUser
{
    protected $appid;
    protected $secret;

    public function __construct()
    {
        $this->appid = PublicEnum::WX_APPID;
        $this->secret = PublicEnum::WX_SECRET;
    }


    public function userMallRegister()
    {
        $code = $_REQUEST['code'];
        $appid = $this->appid;
        $secret = $this->secret;
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

    /**
     * @param Request $request
     * @return \think\Response|\think\response\Json
     * @time: 2019/5/5
     * @autor: duheyuan
     * @throws \think\Exception
     * 我的订单数
     */
    public function myOrderNum(Request $request)
    {

        $user_id = $request->param('user_id','');
        if (isset($user_id) && !empty($user_id)){
            $user_code = Db::query('SELECT xu.user_code FROM ml_xm_binding as mx join xm_tbl_user as xu WHERE mx.xm_user_id = xu.id and mx.ml_user_id = ?',[$user_id]);
            if (!empty($user_code[0]['user_code'])){
                $res = Db::name('xm_tbl_user')->where('up_code',$user_code[0]['user_code'])->field('id')->select();
                $ids = "";
                foreach ($res as $k=>$v){
                    $ids .= $v['id'] . ",";
                }
                $ids = rtrim($ids,',');
            }else{
                $ids = '';
            }
            if (isset($ids) && !empty($ids)) {
                $user_order_id = Db::name('ml_xm_binding')->whereIn('xm_user_id', $ids)->field('ml_user_id')->select();
                $oids = '';
                foreach ($user_order_id as $k => $v) {
                    $oids .= $v['ml_user_id'] . ',';
                }
                $oids = rtrim($oids, ',');
                $goodsId = Db::name('ml_tbl_order')->whereIn('user_id', $oids)->field('id')->select();
                $orderNum = count($goodsId);

            }else{
                $orderNum = 0;
            }
            $data = array('status'=>1,'msg'=>'成功','data'=>$orderNum);
            return json($data);
        }else{
            return json(array('status'=>0,'msg'=>'参数错误','data'=>''));
        }

    }


    public function myDistribution(Request $request)
    {
        $all = $request->param();
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return json(array('status'=>0,'msg'=>'参数错误','data'=>''));
        }
        $data['useInfo'] = Db::name('ml_tbl_user')->where('id',$all['user_id'])->field('id,user_name,user_phone')->find();
        $user_code = Db::query('SELECT xu.user_code FROM ml_xm_binding as mx join xm_tbl_user as xu WHERE mx.xm_user_id = xu.id and mx.ml_user_id = ?',[$all['user_id']]);
        if (!empty($user_code[0]['user_code'])){
            $res = Db::name('xm_tbl_user')->where('up_code',$user_code[0]['user_code'])->field('id')->select();
            $ids = "";
            foreach ($res as $k=>$v){
                $ids .= $v['id'] . ",";
            }
            $ids = rtrim($ids,',');
        }else{
            $ids = '';
        }
        if (isset($ids) && !empty($ids)) {
            $user_order_id = Db::name('ml_xm_binding')->whereIn('xm_user_id', $ids)->field('ml_user_id')->select();
            $oids = '';
            foreach ($user_order_id as $k => $v) {
                $oids .= $v['ml_user_id'] . ',';
            }
            $oids = rtrim($oids, ',');
            $goodsId = Db::name('ml_tbl_order')->whereIn('user_id', $oids)->field('id,pay_time')->select();
            $data['orderNum'] = count($goodsId);
            $gids = '';
            $pat_time = '';
            foreach ($goodsId as $k => $v) {
                $gids .= $v['id'] . ',';
                if (!empty($v['pay_time']) && ($pat_time < $v['pay_time'])) {
                    $pat_time = $v['pay_time'];
                }
            }
            $data['the_last_order'] = $pat_time;
            $gids = rtrim($gids, ',');
            $goodsDetail = Db::name('ml_tbl_order_details')->whereIn('order_zid', $gids)->field('goods_id,goods_num')->select();
            $data['distriMoney'] = 0;
            foreach ($goodsDetail as $k => $v) {
                $bouns_price = Db::name('ml_tbl_goods')->where('id', $v['goods_id'])->field('bonus_price')->find();
                $data['distriMoney'] += $bouns_price['bonus_price'] * $v['goods_num'];
            }
        }else{
            $data = [];
        }
        return json(['status'=>1001,'msg'=>'成功','data'=>$data]);
    }

    public function registerBefore(Request $request)
    {
            $res['appid'] = PublicEnum::WX_APPID;
            $res['secret'] = PublicEnum::WX_SECRET;
            return json( array('status'=>1,'msg'=>'成功','data'=>$res));
    }

    public function deCryptData(Request $request)
    {
        $all = $request->param();
        if (!empty($all['nickName'])){
            $user_name = $all['nickName'];
        }else{
            return json(['status'=>0,'msg'=>'1失败','data'=>'']);
        }

        if (!empty($all['avatarUrl'])){
            $avatarUrl = $all['avatarUrl'];
        }else{
            return json(['status'=>0,'msg'=>'2失败','data'=>'']);
        }
        $session_key = $this->getAccessToken($this->appid,$this->secret);

        if (isset($all['iv']) && !empty($all['iv'])){
            $iv = $all['iv'];
        }else{
            return json(['status'=>0,'msg'=>'缺少iv参数','data'=>'']);
        }
        if (isset($all['encryptData']) && !empty($all['encryptData'])){
            $encryptedData = $all['encryptData'];
        }else{
            return json(['status'=>0,'msg'=>'缺少encryptData参数','data'=>'']);
        }
        $user_id = $all['user_id'];
        $pc = new WXBizDataCrypt($this->appid,$session_key);
        $errCode = $pc->decryptData($encryptedData, $iv,$data );
        if ( $errCode == 0){
            $first = strpos($data,'1');
            $phone = substr($data,$first,11);
            Db::name('ml_tbl_user')->where('id',$user_id)->update(['user_phone'=>$phone,'user_name'=>$user_name,'headimg'=>$avatarUrl]);
            return json(['status'=>1001,'msg'=>'成功','data'=>$data]);
        }else{
            return json(['status'=>$errCode,'msg'=>'失败','data'=>'']);
        }
    }

    public function myWallet(Request $request)
    {
        if ($request->isPost()){
            $all = $request->param();
            if (isset($all['user_id']) && !empty($all['user_id'])){
                $res = Db::name('ml_tbl_wallet')->where('user_id',$all['user_id'])->find();
                if ($res > 0){
                    $data['balance'] = $res['balance'];
                    $data['wallet_detail'] = Db::name('ml_tbl_wallet_details')->where('wallet_id',$res['id'])->field('time,amount,type,order_num')->select();
                }else{
                    $data['balance'] = 0;
                    $data['wallet_detail'] = [];
                }
                return json(['status'=>1001,'msg'=>'成功','data'=>$data]);
            }else{
                return json(['status'=>2002,'msg'=>'缺少必要参数','data'=>'']);
            }
        }else{
            return json(['status'=>2001,'msg'=>'请求方式错误','data'=>'']);
        }

    }



    public function goodsRcode()
    {
        $user_id = $_REQUEST['user_id'];
        //判断数据库是否存在相同二维码
        $rcodedata = Db::table('ml_tbl_rcode')->where('upid', $user_id)->find();
        if ($rcodedata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('rcodeurl' => $rcodedata['url']));
        } else {
            $fielname = rand(100, 99999) . $user_id . '.png';
            // 为二维码创建一个文件
            $fiel = $_SERVER['DOCUMENT_ROOT'] . '/ttgoodssharercode/' . $fielname;
            //获取access_token
            $appid = PublicEnum::WX_APPID;
            $srcret = PublicEnum::WX_SECRET;
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $srcret;
            // get请求获取access_token
            $data = $this->getCurl($url);
            $data = json_decode($data, true);
            //获取二维码
            //参数
            $postdata['scene'] = "goodsid=" . $user_id;
            // 宽度
            $postdata['width'] = 430;
            // 页面
            $postdata['page'] = 'packageA/details/details';
            // 线条颜色
            $postdata['auto_color'] = false;
            //auto_color 为 false 时生效
            $postdata['line_color'] = ['r' => '0', 'g' => '0', 'b' => '0'];

            // 是否有底色为true时是透明的
            $postdata['is_hyaline'] = false;
            $post_data = json_encode($postdata);
            // 获取二维码
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $data['access_token'];
            // post请求
            $result = $this->postCurl($url, $post_data);
            // 保存二维码

            file_put_contents($fiel, $result);
            $fileurl = 'https://tuitui.tango007.com/ttgoodssharercode/' . $fielname;
            $intodata = array('upid' => $user_id,  'url' => $fileurl);
            Db::table('ml_tbl_rcode')->insert($intodata);
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('rcodeurl' => $fileurl));
        }
        return json($data);
    }

    public function subUserList(Request $request)
    {
        if ($request->isPost()){
            $all = $request->param();
            if (isset($all['user_id']) && !empty($all['user_id'])){

                $data['useInfo'] = Db::name('ml_tbl_user')->where('id',$all['user_id'])->field('id,user_name,user_phone')->find();
                $user_code = Db::query('SELECT xu.user_code FROM ml_xm_binding as mx join xm_tbl_user as xu WHERE mx.xm_user_id = xu.id and mx.ml_user_id = ?',[$all['user_id']]);
                if (!empty($user_code[0]['user_code'])){
                    $res = Db::name('xm_tbl_user')->where('up_code',$user_code[0]['user_code'])->field('id')->select();
                    $ids = "";
                    foreach ($res as $k=>$v){
                        $ids .= $v['id'] . ",";
                    }
                    $ids = rtrim($ids,',');
                }else{
                    $ids = '';
                }
                if (isset($ids) && !empty($ids)) {
                    $user_order_id = Db::name('ml_xm_binding')->whereIn('xm_user_id', $ids)->field('ml_user_id')->select();
                    $oids = '';
                    foreach ($user_order_id as $k => $v) {
                        $oids .= $v['ml_user_id'] . ',';
                    }
                    $oids = rtrim($oids,',');
                    $info = Db::name('ml_tbl_user')->whereIn('id',$oids)->select();
                    return json(['status'=>1001,'msg'=>'成功','data'=>$info]);
                }else{
                    return json(['status'=>2003,'msg'=>'成功','data'=>'']);

                }
            }else{
                return json(['status'=>2002,'msg'=>'参数错误','data'=>'']);
            }
        }else{
            return json(['status'=>2001,'msg'=>'方法错误','data'=>'']);
        }

    }


    function getCurl($url)
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

    function postCurl($url, $data)
    {
        $ch = curl_init();
        $header[] = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        } else {
            return $tmpInfo;
        }
    }


    public function getAccessToken($appid,$secret){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
        $res = $this->curl_get($url);
        $res = json_decode($res,1);
        if (isset($res['errcode'])){
            if($res['errcode']!=0) throw new Exception($res['errmsg']);
        }
        return $res['access_token'];
    }
    public function curl_get($url)
    {
        $headers = array('User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36');
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 20);
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }



}