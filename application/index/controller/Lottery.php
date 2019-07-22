<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/7/21
 * Time: 14:26
 */

namespace app\index\controller;


use app\common\Model\PublicEnum;
use app\index\Controller;
use think\Db;
use think\Request;

class Lottery extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function getLottery()
    {
        $all = $this->request->param();
        if (isset($all['id']) && !empty($all['id'])){
            $id = $all['id'];
        }else{
            $id = 1;
        }
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        //  活动信息
        $lottery_info = Db::name('ml_tbl_lottery_info')->where('id',$id)->find();

        $openTime = $lottery_info['openTime'];
        $nowTime = date('Y-m-d H:i:s');
        if ($openTime >= $nowTime){
            $lottey = 1;
            $lottery_list= Db::name('ml_tbl_lottery_record l')->join('ml_tbl_user u','l.userId=u.id')->field('l.id,l.userId,u.headimg,u.user_name')->where('l.result',1)->select();
        }else{
            $lottey = 0;
            $lottery_list = null;
        }
        //  已参加活动
        $lottered = Db::name('ml_tbl_lottery_record l')
            ->join('ml_tbl_user u','l.userId=u.id')
            ->field('l.userId,u.headimg')
            ->group('userId')
            ->order('l.id','desc')
            ->select();

        //  是否参加
        $user_state = Db::name('ml_tbl_lottery_record')->where('userId',$all['user_id'])->find();
        if ($user_state){
            $status = 1;
        }else{
            $status = 0;
        }

        return responseSuccess(['lottery_info'=>$lottery_info,'list'=>$lottered,'status'=>$status,'lottery_list'=>$lottery_list,'lottery_status'=>$lottey]);
    }

    public function joinLottery()
    {
        $all = $this->request->param();
        if (isset($all['user_id']) && !empty($all['user_id'])){
            //  是否参加
            $user_state = Db::name('ml_tbl_lottery_record')->where('userId',$all['user_id'])->find();
            if ($user_state){
                return responseError([],2001,'已参加活动请勿重复参加');
            }

            $is_share = Db::name('ml_tbl_lottery_share')->where('userId',$all['user_id'])->find();

            $user_info = Db::name('ml_tbl_user')->where('id',$all['user_id'])->find();
            if (($user_info['is_salesman'] != 1) && !$is_share ){
                return responseError([],2001,'您还不是会员,请先成为会员或分享');
            }

            $res = Db::name('ml_tbl_lottery_record')->insert(['userId'=>$all['user_id']]);
            if ($res){
                return responseSuccess();
            }else{
                return responseError();
            }
        }else{
            return responseError();
        }
    }
    public function isSalesman()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        $user_state = Db::name('ml_tbl_user')->where('id',$all['user_id'])->find();
        if ($user_state['is_salesman'] == 1){
            $status = 1;
        }else{
            $status = 0;
        }
        return responseSuccess(['status'=>$status]);
    }
    public function isShare()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        $user_info = Db::name('ml_tbl_lottery_share')->where('userId',$all['user_id'])->find();
        if ($user_info){
            $status = 1;
        }else{
            $status = 0;
        }
        return responseSuccess(['state'=>$status]);

    }

    public function createSalesmanOrder()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id']) ){
            return responseError();
        }
        if (!isset($all['name']) || empty($all['name']) ){
            return responseError();
        }
        if (!namePreg($all['name'])){
            return responseError([],2001,'姓名错误');
        }
        if (!isset($all['tel']) || empty($all['tel'])){
            return responseError();
        }
        if (!preg_mobile($all['tel'])){
            return responseError([],2001,'手机格式错误');
        }

        if ($all['price'] < 1){
            return responseError([],2001,'价格错误');
        }
        $order_num = 'H'.randomOrder_no();
        $data = [
            'u_id'=>$all['user_id'],
            'u_name'=>$all['name'],
            'tel'=>$all['tel'],
            'price'=>$all['price'],
            'c_time'=>time(),
            'order_type'=>1,
            'order_num'=>$order_num,
        ];

        Db::name('ml_tbl_distributor')->insert($data);


        $url = "http://127.0.0.1/SZhanshan/public/buySalesman?order_id={$order_num}";
        $returndata = object_array(json_decode(curl_get($url)));

        return  json($returndata);

    }

    public function buySalesman()
    {
        $all = $this->request->param();
        if (!isset($all['order_id']) ||  empty($all['order_id'])){
            return responseError();
        }

        $order_id = $all['order_id'];
        $selectorderprice = Db::table('ml_tbl_distributor')->where('order_num', $order_id)->find();
        $order_price = $selectorderprice['price'] * 100;
        $appid = PublicEnum::WX_APPID;
        $body = '推推优享商城';
        //查询用户openid
        $selectopenid = Db::table('ml_tbl_user')->where('id', $selectorderprice['u_id'])->find();
        $openid = $selectopenid['wechat_open_id'];
        $data = array(
            'appid' => $appid,//小程序appid
            'body' => $body,  //商品描述
            'mch_id' => '1501953711',//商户号
            'nonce_str' => nonce_str(),//随机字符串
            'notify_url' => 'https://tuitui.tango007.com/sjht/public/lotteryNotify',//通知地址
            'out_trade_no' => $all['order_id'],//商户订单号
            'spbill_create_ip' => '192.168.0.2',//终端IP
            'total_fee' => $order_price,//标价金额
            'trade_type' => 'JSAPI',//交易类型
            'openid' => $openid//交易类型
        );

        $sign = $this->getSign($data);//签名
        $data['sign'] = $sign;
        $xmldata = $this->ToXml($data);//数组转化为xml
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $res = $this->http_send_query($url, $xmldata);
        $result = FromXml($res);
        //判断返回结果
        if ($result['return_code'] == 'SUCCESS') {
            if ($result['result_code'] == 'SUCCESS') {
                $time = time();
                $info = array(
                    'appId' => $appid,
                    'timeStamp' => "" . $time . "",
                    'nonceStr' => nonce_str(),
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
                    Db::table('ml_tbl_distributor')->where('id', $order_id)->update(['order_type' => $order_type, 'pay_time' =>  time()]);
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

    public function lotteryNotify()
    {
        $testxml  = file_get_contents("php://input");

        $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));

        $result = json_decode($jsonxml, true);//转成数组，
        if($result) {
            //如果成功返回了
            $out_trade_no = $result['out_trade_no'];
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                //执行业务逻辑
                Db::name('ml_tbl_distributor')->where('order_num',$out_trade_no)->update(['order_type'=>2,'pay_time'=>time()]);

                $order_info = Db::name('ml_tbl_distributor')->where('order_num',$out_trade_no)->find();
                $time = date('Y-m-d',strtotime('+365day'));
                Db::name('ml_tbl_user')->where('id',$order_info['u_id'])->update(['is_salesman'=>1,'salesman_due'=>$time]);
                echo "SUCCESS";
            }
        }
    }

    public function lotteryRcode()
    {
        $user_id = $_REQUEST['user_id'];
        //判断数据库是否存在相同二维码
        $rcodedata = Db::table('ml_tbl_lottery_rcode')->where('user_id', $user_id)->find();
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
            $data = $this->http_url_query($url);
            $data = json_decode($data, true);
            //获取二维码
            //参数
            $postdata['scene'] = "user_id=" . $user_id;
            // 宽度
            $postdata['width'] = 430;
            // 页面
            $postdata['page'] = 'pages/index/index';
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
            $result = $this->http_send_query($url, $post_data);
            // 保存二维码

            file_put_contents($fiel, $result);
            $fileurl = 'https://tuitui.tango007.com/ttgoodssharercode/' . $fielname;
            $intodata = array('user_id' => $user_id,  'url' => $fileurl);
            Db::table('ml_tbl_lottery_rcode')->insert($intodata);
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('rcodeurl' => $fileurl));
        }
        return json($data);
    }


    public function editShare()
    {
        $all = $this->request->param();

        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        $info = Db::name('ml_tbl_lottery_share')->where('userId',$all['user_id'])->find();
        if ($info){
            return responseError([],2001,'已分享,不可重复分享');
        }

        $res = Db::name('ml_tbl_lottery_share')->insert(['userId'=>$all['user_id']]);

        return responseSuccess();
    }









}