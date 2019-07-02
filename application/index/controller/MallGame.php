<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/29
 * Time: 13:45
 */

namespace app\index\controller;


use app\index\Controller;
use app\index\model\WxPayModel;
use think\Db;

class MallGame extends Controller
{
    //创建活动 6.29
    public function creatGame()
    {
        $all = $this->request->param();

        //基础数据
        $name = $all['name'];
        $no = date("Ymd", time());;
        $introduce = $all['introduce'];
        $banner = $all['banner'];
        $readytime = $all['readytime'];
        $starttime = $all['starttime'];
        $overtime = $all['overtime'];
        Db::table('ml_tbl_gameinfo')->insert(['name' => $name, 'no' => $no,
            'introduce' => $introduce, 'banner' => $banner, 'readytime' => $readytime,
            'starttime' => $starttime, 'overtime' => $overtime]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => array('gameno' => $no));
        return json($data);
    }

    //历史活动列表 6.29
    public function gameList()
    {
        $gamedata = Db::table('ml_tbl_gameinfo')
            ->field('name,no,introduce,banner,readytime,starttime,overtime')
            ->order('no desc')->select();
        if ($gamedata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $gamedata);
        } else {
            $data = array('status' => 1, 'msg' => '暂无活动', 'data' => '');
        }
        return json($data);
    }

    //活动详情 6.29
    public function gameDetails()
    {
        $all = $this->request->param();
        $user_id = $all['user_id'];//用户id
        $no = $all['no'];//活动编号
        //活动基础信息返回
        $returndata = array();
        $gamedata = Db::table('ml_tbl_gameinfo')->where('no', $no)->find();
        $returndata['gamedata'] = $gamedata;

        if ($gamedata) {
            //活动商品信息返回
            $goodadata = Db::table('ml_tbl_gamegoods')->where('no', $no)->where('type', '<>', '0')
                ->order('weight asc')->field('name,head,bighead,introduction,stock,price')->select();
            $returndata['goodadata'] = $goodadata;
            //判断活动时间 返回对应数据
            $readytimestamp = strtotime($gamedata['readytime']);//活动预热时间
            $starttimestamp = strtotime($gamedata['starttime']);//活动开始时间
            $overtimestamp = strtotime($gamedata['overtime']);//活动结束时间
            $nowtimestamp = time();

            if (($nowtimestamp > $overtimestamp) || ($nowtimestamp >= $starttimestamp)) {//判断活动是否结束
                //用户信息查询
                $myleaderinfo = Db::table('ml_tbl_gameleaderinfo')->where('user_id', $user_id)->where('no', $no)->find();
                if ($myleaderinfo) {
                    $returndata['mydata'] = $myleaderinfo;
                    //我的团员购买总计
                    $returndata['myteamdata'] = Db::table('ml_tbl_gameorder')->where('leaderid', $myleaderinfo['id'])->where('order_type', '<>', 0)
                        ->order('paysumprice desc')->group('user_id')->field('name,phone,sum(payprice) as paysumprice')->select();
                } else {
                    //未参加
                    $returndata['mydata'] = null;
                }
                //团长排行查询
                $leaderranking = Db::table('ml_tbl_gameleaderinfo')->where('no', $no)->where('sort', '<>', 0)->order('sort asc')
                    ->limit(0, 10)->field('name,record,sort')->select();
                $returndata['rankingdata'] = $leaderranking;
            } elseif ($nowtimestamp >= $readytimestamp) {//判断活动是否处于预热阶段
                //TODO 无操作
            } else {//活动未开始阶段
                //TODO 无操作
            }
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '活动编号错误', 'data' => '');
        }
        return json($data);
    }

    //增加活动商品 6.29
    public function creatGameGoods()
    {
        $all= $this->request->param();
        $no = $all['no'];
        $name = $all['name'];
        $head = $all['head'];
        $bighead = $all['bighead'];
        $introduction = $all['introduction'];
        $size = $all['size'];
        $maxlimit = $all['maxlimit'];
        $minlimit = $all['minlimit'];
        $stock = $all['stock'];
        $bonus = $all['bonus'];
        $price = $all['price'];
        $original = $all['original'];
        $cost = $all['cost'];
        $notice = $all['notice'];
        $details = $all['details'];
        $type = $all['type'];
        $business_id = $all['business_id'];
        $weight = $all['weight'];
        $gamegoodsid = Db::table('ml_tbl_gamegoods')->insertGetId(['no' => $no, 'name' => $name, 'head' => $head,
            'bighead' => $bighead, 'introduction' => $introduction, 'size' => $size, 'maxlimit' => $maxlimit,
            'minlimit' => $minlimit, 'stock' => $stock, 'bonus' => $bonus, 'price' => $price, 'original' => $original,
            'cost' => $cost, 'notice' => $notice, 'details' => $details, 'type' => $type, 'business_id' => $business_id, 'weight' => $weight]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => array('gamegoodsid' => $gamegoodsid));
        return json($data);
    }

    //修改商品详情 6.29
    public function updGameGoods()
    {
        $all = $this->request->param();

        $name = $all['name'];
        $head = $all['head'];
        $bighead = $all['bighead'];
        $introduction = $all['introduction'];
        $size = $all['size'];
        $maxlimit = $all['maxlimit'];
        $minlimit = $all['minlimit'];
        $stock = $all['stock'];
        $bonus = $all['bonus'];
        $price = $all['price'];
        $original = $all['original'];
        $cost = $all['cost'];
        $notice = $all['notice'];
        $details = $all['details'];
        $type = $all['type'];
        $business_id = $all['business_id'];
        $weight = $all['weight'];

        $gamegoodsid = Db::table('ml_tbl_gamegoods')->where(['id'=>$all['id']])->update(['name' => $name, 'head' => $head,
            'bighead' => $bighead, 'introduction' => $introduction, 'size' => $size, 'maxlimit' => $maxlimit,
            'minlimit' => $minlimit, 'stock' => $stock, 'bonus' => $bonus, 'price' => $price, 'original' => $original,
            'cost' => $cost, 'notice' => $notice, 'details' => $details, 'type' => $type, 'business_id' => $business_id, 'weight' => $weight]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => array('gamegoodsid' => $gamegoodsid));
        return json($data);
    }

    //活动商品列表 6.29
    public function gameGoodsList()
    {
        $no = $_POST['no'];
        $gamegoodsdata = Db::table('ml_tbl_gamegoods')->where('no', $no)->order('weight asc')
            ->field('id,name,head,bighead,introduction,stock,price,original')->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => array('gamegoodslist' => $gamegoodsdata));
        return json($data);
    }

    //活动商品详情 6.29
    public function gameGoodsInfo()
    {
        $gamegoodsid = $_REQUEST['gamegoodsid'];
        $gamegoodsdata = Db::table('ml_tbl_gamegoods')->where('id', $gamegoodsid)->find();
        if ($gamegoodsdata) {
            $no = $gamegoodsdata['no'];
            $gamedata = Db::table('ml_tbl_gameinfo')->where('no', $no)->find();
            if ($gamedata) {
                $readytimestamp = strtotime($gamedata['readytime']);//活动预热时间
                $starttimestamp = strtotime($gamedata['starttime']);//活动开始时间
                $overtimestamp = strtotime($gamedata['overtime']);//活动结束时间
                $nowtimestamp = time();//当前时间
                if ($nowtimestamp > $overtimestamp) {//判断活动是否结束
                    $gametype = 4;
                } elseif ($nowtimestamp >= $starttimestamp) {//活动进行中
                    $gametype = 3;
                } elseif ($nowtimestamp >= $readytimestamp) {//判断活动是否处于预热阶段
                    $gametype = 2;
                } else {//活动未开始阶段
                    $gametype = 1;
                }
                $data = array('status' => 0, 'msg' => '成功', 'data' => array('gamegoodsinfo' => $gamegoodsdata, 'gametype' => $gametype));
            } else {
                $data = array('status' => 1, 'msg' => '数据出错', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '商品不存在', 'data' => '');
        }
        return json($data);
    }

    //团长信息查询 6.30
    public function gameLeaderInfo()
    {
        $leaderid = $_REQUEST['leaderid'];
        $leaderdata = Db::table('ml_tbl_gameleaderinfo')->where('id', $leaderid)->find();
        if ($leaderdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $leaderdata);
        } else {
            $data = array('status' => 1, 'msg' => '团长id错误', 'data' => '');
        }
        return json($data);
    }

    //创建订单 6.30
    public function creatGameOrder()
    {
        $leaderid = $_REQUEST['leaderid'];
        $user_id = $_REQUEST['user_id'];
        $gamegoodsid = $_REQUEST['gamegoodsid'];
        $gamegoodsnum = $_REQUEST['gamegoodsnum'];
        $username = $_REQUEST['username'];
        $userphone =  $_REQUEST['userphone'];
        //查询商品价格
        $gamegoodsdata = Db::table('ml_tbl_gamegoods')->where('id', $gamegoodsid)->find();

        if ($gamegoodsdata) {
            if ($gamegoodsdata['type'] == 1) {
                if ($gamegoodsnum > $gamegoodsdata['stock']) {
                    $data = array('status' => 1, 'msg' => '商品库存不足', 'data' => '');
                } else {
                    //计算需要支付的金额
                    $payprice = $gamegoodsdata['price'] * $gamegoodsnum;
                    //生成必要参数
                    $order_id = $user_id . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                    //插入数据库表
                    Db::table('ml_tbl_gameorder')->insert(['order_id' => $order_id, 'user_id' => $user_id, 'leaderid' => $leaderid, 'goodsid' => $gamegoodsid,
                        'goodsnum' => $gamegoodsnum, 'payprice' => $payprice, 'creattime' => date("Y-m-d H:i:s", time()), 'phone' => $userphone, 'name' => $username, 'order_type' => 0]);
                    //扣除商品库存
                    $stock = $gamegoodsdata['stock'];
                    $type = $gamegoodsdata['type'];
                    $newstock = $stock - $gamegoodsnum;
                    if ($newstock == 0) {
                        $type = 2;
                    }
                    //更新商品状态
                    Db::table('ml_tbl_gamegoods')->where('id', $gamegoodsid)->update(['stock' => $newstock, 'type' => $type]);
                    //调用预下单
                    $wxpaymodel = new WxPayModel();
                    $data = array('gameorderid' => $order_id);
                    $url = 'https://api.mch.weixin.qq.com/pay/gameUnifiedOrder';

                    $data = $wxpaymodel->http_request($url, $data);
                }
            } else {
                if ($gamegoodsdata['type'] == 0) {
                    $data = array('status' => 1, 'msg' => '商品不存在', 'data' => '');
                } else {
                    $data = array('status' => 1, 'msg' => '商品已售罄', 'data' => '');
                }
            }
        } else {
            $data = array('status' => 1, 'msg' => '商品id错误', 'data' => '');
        }
        return json($data);
    }

    //订单支付 6.30
    public function gameOrderPay()
    {
        //TODO 前端调用支付
    }

    //订单支付完成确认 6.30
    public function gameOrderPayConfirm()
    {
        $order_id = $_POST['order_id'];
        $wxpaymodel = new WxPayModel();
        $appid = 'wx0fda8074ccdb716d';
        $data = array(
            'appid' => $appid,//小程序appid
            'mch_id' => '1501953711',//商户号
            'out_trade_no' => $order_id,//商户订单号
            'nonce_str' => $wxpaymodel->nonce_str(),//随机字符串
        );
        $sign = $wxpaymodel->getSign($data);//签名
        $data['sign'] = $sign;
        $xmldata = $wxpaymodel->ToXml($data);//数组转化为xml
        $url = 'https://api.mch.weixin.qq.com/pay/orderquery';
        $res = $wxpaymodel->http_request($url, $xmldata);
        $result = $wxpaymodel->FromXml($res);
        //判断返回结果
        if ($result['return_code'] == 'SUCCESS') {
            if (isset($result['trade_state']) == 'SUCCESS') {
                //修改订单状态
                Db::table('ml_tbl_order')->where('order_id', $order_id)->update(['order_type' => 1, 'paytime' => date("Y-m-d H:i:s", time())]);
                $gameorderdata = Db::table('ml_tbl_gameorder')->where('order_id', $order_id)->find();
                //进行返佣
                $goodsid = $gameorderdata['goodsid'];
                $leaderid = $gameorderdata['leaderid'];
                $goodsnum = $gameorderdata['goodsnum'];
                //查询商品返佣
                $gamegoodsdata = Db::table('ml_tbl_gamegoods')->where('id', $goodsid)->find();
                $bonus = $gamegoodsdata['bonus'];
                $sum = $bonus * $goodsnum;
                //查询团长的用户id
                $leaderdata = Db::table('ml_tbl_gameleaderinfo')->where('id', $leaderid)->find();
                $leaderuserid = $leaderdata['user_id'];
                //查询团长钱包余额
                $walletdata = Db::table('ml_tbl_wallet')->where('user_id', $leaderuserid)->find();
                $balance = $walletdata['balance'];
                $wallet_id = $walletdata['id'];
                $newbalance = $balance + $sum;
                //更新钱包余额
                Db::table('ml_tbl_wallet')->where('user_id', $leaderuserid)->update(['balance' => $newbalance]);
                //插入钱包明细
                Db::table('ml_tbl_wallet_details')->insert(['wallet_id' => $wallet_id, 'time' => date("Y-m-d H:i:s", time()), 'amount' => $sum,
                    'nowbalance' => $newbalance, 'type' => 1, 'remarks' => '推王争霸赛推王分销返佣', 'order_num' => $order_id]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '订单未支付', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => $result['err_code_des'], 'data' => '');
        }
        return json($data);
    }

    //我的订单列表 6.30
    public function myGameOrderList()
    {
        $user_id = $_POST['user_id'];
        $gameorderdata = Db::view('ml_tbl_gameorder', 'order_id,goodsnum,payprice,order_type')
            ->view('ml_tbl_gamegoods', 'name,head,bighead,size,price', 'ml_tbl_gameorder.goodsid=ml_tbl_gamegoods.id', 'LEFT')
            ->where('ml_tbl_gameorder.user_id', $user_id)
            ->order('creattime desc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $gameorderdata);
        return $data;
    }

    //团长的订单列表 6.30
    public function leaderGameOrderList()
    {
        $no = $_POST['no'];
        $user_id = $_POST['user_id'];
        //查询团长id
        $gameleaderdata = Db::table('ml_tbl_gameleaderinfo')->where('user_id', $user_id)->where('no', $no)->find();
        if ($gameleaderdata) {
            $leaderid = $gameleaderdata['id'];
            //订单分组排序
            $gameorderdata = Db::view('ml_tbl_gameorder', 'order_id,goodsnum,payprice,order_type')
                ->view('ml_tbl_gamegoods', 'name,head,bighead,size,price', 'ml_tbl_gameorder.goodsid=ml_tbl_gamegoods.id', 'LEFT')
                ->where('ml_tbl_gameorder.leaderid', $leaderid)
                ->where('ml_tbl_gameorder.order_type', '<>', 0)
                ->order('creattime desc')
                ->select();
            $data = array('status' => 0, 'msg' => '成功', 'data' => $gameorderdata);
            return $data;
        } else {
            $data = array('status' => 0, 'msg' => '该用户为参加编号' . $no . '的活动', 'data' => '');
        }
        return $data;
    }

    //订单详情 6.30
    public function gameOrderDetails()
    {
        $order_id = $_REQUEST['order_id'];
        $gameorderdata = Db::view('ml_tbl_gameorder', 'order_id,goodsnum,payprice,order_type,name,phone,creattime,paytime')
            ->view('ml_tbl_gamegoods', 'name,head,bighead,size,price', 'ml_tbl_gameorder.goodsid=ml_tbl_gamegoods.id', 'LEFT')
            ->where('ml_tbl_gameorder.order_id', $order_id)
            ->order('creattime desc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $gameorderdata);
        return $data;
    }

    //团长确认订单 6.30
    public function gameOrderConfirm()
    {
        $user_id = $_POST['user_id'];
        $no = $_POST['no'];
        $order_id = $_POST['order_id'];
        $gameleaderdata = Db::table('ml_tbl_gameleaderinfo')->where('user_id', $user_id)->where('no', $no)->find();
        if ($gameleaderdata) {
            $leaderid = $gameleaderdata['id'];
            $gameorderdata = Db::table('ml_tbl_gameorder')->where('order_id', $order_id)->find();
            if ($gameorderdata) {
                if ($leaderid == $gameorderdata['leaderid']) {
                    //跟新订单状态
                    Db::table('ml_tbl_gameorder')->where('order_id', $order_id)->update(['order_type' => 3]);
                    $data = array('status' => 0, 'msg' => '成功', 'data' => '');
                } else {
                    $data = array('status' => 1, 'msg' => '团长不匹配无法确认', 'data' => '');
                }
            } else {
                $data = array('status' => 1, 'msg' => '订单号错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '数据错误', 'data' => '');
        }
        return $data;
    }

    //全部订单列表 6.30
    public function allGameOrderList()
    {
        $no = $_POST['no'];
        $gameorderdata = Db::view('ml_tbl_gameorder', 'order_id,goodsnum,payprice,order_type,name,phone,creattime,paytime')
            ->view('ml_tbl_gamegoods', 'name,head,bighead,size,price', 'ml_tbl_gameorder.goodsid=ml_tbl_gamegoods.id', 'LEFT')
            ->where('ml_tbl_gamegoods.no', $no)
            ->order('creattime desc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $gameorderdata);
        return $data;
    }

    //微信预下单接口
    public function gameUnifiedOrder()
    {
        //订单号
        $order_id = $_REQUEST['gameorderid'];
        //创建微信model
        $wxpaymodel = new WxPayModel();
        $gameorderdata = Db::table('ml_tbl_gameorder')->where('order_id', $order_id)->find();
        if (!$gameorderdata) {
            $data = array('status' => 1, 'msg' => '订单错误', 'data' => '');
            return json($data);
        }
        $order_price = $gameorderdata['payprice'] * 100;
        $appid = 'wx0fda8074ccdb716d';
        $body = '推推优享商城推王争霸赛商品购买';
        //查询用户openid
        $selectopenid = Db::table('ml_tbl_user')->where('id', $gameorderdata['user_id'])->find();
        $openid = $selectopenid['wechat_open_id'];
        $data = array(
            'appid' => $appid,//小程序appid
            'body' => $body,  //商品描述
            'mch_id' => '1501953711',//商户号
            'nonce_str' => $wxpaymodel->nonce_str(),//随机字符串
            'notify_url' => 'https://tuitui.tango007.com/sjht/public/gamePayNotify',//通知地址
            'out_trade_no' => $order_id,//商户订单号
            'spbill_create_ip' => '192.168.0.2',//终端IP
            'total_fee' => $order_price,//标价金额
            'trade_type' => 'JSAPI',//交易类型
            'openid' => $openid//用户openid
        );
        $sign = $wxpaymodel->getSign($data);//签名
        $data['sign'] = $sign;
        $xmldata = $wxpaymodel->ToXml($data);//数组转化为xml
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $res = $wxpaymodel->http_request($url, $xmldata);
        $result = $wxpaymodel->FromXml($res);
        dd($result);

        //判断返回结果
        if ($result['return_code'] == 'SUCCESS') {
            if ($result['result_code'] == 'SUCCESS') {
                $time = time();
                $info = array(
                    'appId' => $appid,
                    'timeStamp' => "" . $time . "",
                    'nonceStr' => $wxpaymodel->nonce_str(),
                    'package' => 'prepay_id=' . $result['prepay_id'],
                    'signType' => 'MD5',
                );
                $paySign = $wxpaymodel->getSign($info);
                $info['paySign'] = $paySign;
                $data = array('status' => 0, 'msg' => '成功', 'data' => $info);
            } elseif ($result['result_code'] == 'FAIL') {
                if ($result['err_code'] == 'ORDERPAID') {
                    //修改订单状态
                    Db::table('ml_tbl_gameorder')->where('order_id', $order_id)->update(['order_type' => 1, 'paytime' => date("Y-m-d H:i:s", time())]);
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

    //活动异步回调通知
    public function gamePayNotify()
    {

    }
}