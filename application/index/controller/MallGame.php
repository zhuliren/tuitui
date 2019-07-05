<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/29
 * Time: 13:45
 */

namespace app\index\controller;


use app\common\Model\PublicEnum;
use app\index\Controller;
use app\index\model\MessageModel;
use app\index\model\WxPayModel;
use think\Db;
use think\Validate;

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

    //  团长加入活动
    public function joinGame()
    {
        $all = $this->request->param();

        if (!isset($all['no']) || empty($all['no'])){
            return responseError();
        }
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        if ( Db::name('ml_tbl_gameleaderinfo')->where('user_id',$all['user_id'])->find()){
            return responseError([],3001,'已参加,请勿重复参加');
        }
        $user_info = Db::name('ml_tbl_user')->where('id',$all['user_id'])->find();
        if ($user_info){
            if ($user_info['is_salesman'] != 1 ){
                return responseError([],2001,'不是会员');
            }
        }else{
            return responseError([],4001,'没有用户信息');
        }
        if (!isset($all['name']) || empty($all['name'])){
            return responseError();
        }
        if (!isset($all['phone']) || empty($all['phone'])){
            return responseError();
        }
        if (!preg_mobile($all['phone'])){
            return responseError([],2001,'手机格式错误');
        }
        $res = Db::name('ml_tbl_gameleaderinfo')->insert($all);

        if ($res){
            return responseSuccess();
        }else{
            return responseError();
        }
    }

    //活动详情 6.29
    public function gameDetails()
    {
        $all = $this->request->param();
        $no = $all['no'];//活动编号
        $class = $all['class'];//商品分类
        //活动基础信息返回
        $returndata = array();
        $gamedata = Db::table('ml_tbl_gameinfo')->where('no', $no)->find();
        $returndata['gamedata'] = $gamedata;

        if ($gamedata) {

            $readytimestamp = strtotime($gamedata['readytime']);//活动预热时间
            $starttimestamp = strtotime($gamedata['starttime']);//活动开始时间
            $overtimestamp = strtotime($gamedata['overtime']);//活动结束时间
            $nowtimestamp = time();
            if ($nowtimestamp > $overtimestamp) {//判断活动是否结束
                $gametype = 4;
            } elseif ($nowtimestamp >= $starttimestamp) {//活动进行中
                $gametype = 3;
            } elseif ($nowtimestamp >= $readytimestamp) {//判断活动是否处于预热阶段
                $gametype = 2;
            } else {//活动未开始阶段
                $gametype = 1;
            }
            //  判断是否是团长
            $user_status = Db::name('ml_tbl_gameleaderinfo')->where('user_id',$all['user_id'])->find();
            if ($user_status){
                $user_info = 1;
            }else{
                $user_info = 0;
            }
            //活动商品信息返回
            $sql = "SELECT g.id,g.name,g.head,g.bighead,g.introduction,g.stock,g.price,g.minlimit,g.maxlimit FROM  `ml_tbl_game_goodsclass` AS gc LEFT JOIN `ml_tbl_gamegoods` AS g ON gc.goods_id = g.id WHERE gc.class_id = {$class} AND g.no = {$no} ";
            if ($class == 1){
                $sql = "SELECT g.id,g.name,g.head,g.bighead,g.introduction,g.stock,g.price,g.minlimit,g.maxlimit FROM  `ml_tbl_game_goodsclass` AS gc LEFT JOIN `ml_tbl_gamegoods` AS g ON gc.goods_id = g.id WHERE gc.class_id = {$class} 
                      UNION SELECT g.id,g.name,g.head,g.bighead,g.introduction,g.stock,g.price,g.minlimit,g.maxlimit FROM `ml_tbl_game_goodsclass`  AS gc LEFT JOIN `ml_tbl_gamegoods` AS g ON gc.goods_id = g.id WHERE gc.class_id != 1 AND g.no = {$no} AND g.weight = 1   ";
            }
            $goods_list = Db::query($sql);
            return responseSuccess(['goods_list'=>$goods_list,'user_type'=>$user_info, 'gametype'=>$gametype]);

        }else{
            return responseError();
        }
    }

    // 排行榜数据
    public function getrankdata()
    {
        $all = $this->request->param();
        $user_id = $all['user_id'];//用户id
        $no = $all['no'];//活动编号

        $returndata = array();
        $gamedata = Db::table('ml_tbl_gameinfo')->where('no', $no)->find();
        $returndata['gamedata'] = $gamedata;

        if ($gamedata) {
            //判断活动时间 返回对应数据
            $readytimestamp = strtotime($gamedata['readytime']);//活动预热时间
            $starttimestamp = strtotime($gamedata['starttime']);//活动开始时间
            $overtimestamp = strtotime($gamedata['overtime']);//活动结束时间
            $nowtimestamp = time();

//            if (($nowtimestamp > $overtimestamp) || ($nowtimestamp >= $starttimestamp)) {//判断活动是否结束
                //用户信息查询
                // TODO:: 要加上战队战绩
                $myleaderinfo = Db::table('ml_tbl_gameleaderinfo')->where('user_id', $user_id)->where('no', $no)->find();
                if ($myleaderinfo) {
                    //  没有加入战队
                    if ($myleaderinfo['lid'] == 0) {
                        $returndata['mydata'] = $myleaderinfo;
                        //我的团员购买总计
                        $returndata['myteamdata'] = Db::table('ml_tbl_gameorder')->where('leaderid', $myleaderinfo['id'])->where('order_type', '<>', 0)
                            ->order('paysumprice desc')->group('user_id')->field('user_id,name,phone,sum(payprice) as paysumprice')->select();
                        //  加入战队的战绩
                    } else {
                        $returndata['mydata'] = $myleaderinfo;
                        $lead_list = Db::name('ml_tbl_gameleaderinfo')->where('lid', $myleaderinfo['lid'])->select();
                        $ids = idsArrayToStr($lead_list);
                        $returndata['myteamdata'] = Db::table('ml_tbl_gameorder')->whereIn('leaderid', $ids)->where('order_type', '<>', 0)
                            ->order('paysumprice desc')->group('user_id')->field('user_id,name,phone,sum(payprice) as paysumprice')->select();
                    }
                    $sql = "SELECT fina.user_id,fina.rank FROM(
                        SELECT a.user_id, a.record,(@a :=@a + 1) AS rank FROM(
                        SELECT user_id,record AS record FROM ml_tbl_gameleaderinfo WHERE lid=0 AND `no`={$no}
                        UNION 
                        SELECT user_id,SUM(record) AS record FROM ml_tbl_gameleaderinfo WHERE lid!=0 AND `no`={$no} GROUP BY lid ) a,(SELECT @a := 0) t1 ORDER BY a.record DESC                    
                        ) fina WHERE  fina.user_id={$user_id}";
                    $res = Db::query($sql);

                    if ($res){
                        $returndata['mydata']['rank'] = $res[0]['rank'];
                    }else{
                        $returndata['mydata']['rank'] = null;
                    }
                } else {
                    //未参加
                    $returndata['mydata'] = null;
                }
                if (isset($returndata['myteamdata']) &&  !empty($returndata['myteamdata'])){
                    $returndata['myteamdata'] = arraySort($returndata['myteamdata'], 'paysumprice');
                    $num = 1;
                    foreach ($returndata['myteamdata'] as $k=>$v){
                        $returndata['myteamdata'][$k]['name'] = subString_UTF8($v['name'],0,1).'**' ;
                        $returndata['myteamdata'][$k]['head'] = Db::name('ml_tbl_user')->where('id',$v['user_id'])->value('headimg');
                        $returndata['myteamdata'][$k]['sort'] = $num;
                        $num++;
                    }
                }
                //团长排行查询
                $team_list = Db::name('ml_tbl_gameleaderinfo')->where(['no' => $no])->where('lid', '<>', 0)->group('lid')->order('sumtotal', 'desc')->limit(10)->field('id,user_id,name, sum(record) as sumtotal,lid')->select();
                $lead_list = Db::name('ml_tbl_gameleaderinfo')->where(['no' => $no, 'lid' => 0])->order('record', 'desc')->limit(10)->field('id,user_id,name, record as sumtotal,lid')->select();
                foreach ($lead_list as $k => $v) {
                    $team_list[] = $v;
                }
                $team_list = arraySort($team_list, 'sumtotal');
                $leaderranking = array_slice($team_list, 0, 10);
                $num = 1;

                foreach ($leaderranking as $tk => $tv) {
                    if ($tv['lid'] == 0){
                        $leaderranking[$tk]['name'] = subString_UTF8($tv['name'],0,1).'**的队伍';
                        $leaderranking[$tk]['head'] = Db::query("SELECT headimg FROM `ml_tbl_user` WHERE id = {$tv['user_id']}  ");
                    }else{

                        $leaderranking[$tk]['name'] = subString_UTF8((Db::query("SELECT * FROM ml_tbl_gameleaderinfo WHERE id = {$tv['lid']}"))[0]['name'],0,1).'**的战队';
                        $leaderranking[$tk]['head'] = Db::query(" SELECT u.headimg,u.id FROM ml_tbl_gameleaderinfo as l left join ml_tbl_user as u on l.user_id = u.id WHERE l.lid = {$tv['lid']} LIMIT 3   ");

                    }
                    if ($myleaderinfo['id'] == $tv['id']){
                        $returndata['myrank']['rank'] = $num;
                        $returndata['myrank']['sumtotal'] = $num;
                    }
                    $leaderranking[$tk]['sort'] = $num;
                    $num++;
                }
                $returndata['rankingdata'] = $leaderranking;

//            } elseif ($nowtimestamp >= $readytimestamp) {//判断活动是否处于预热阶段
                //TODO 无操作
//            } else {//活动未开始阶段
                //TODO 无操作
//            }
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        }else{
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

        //  新增分类
        $class = $all['class'];
        $cls = [];
        foreach ($class as $v){
            $cls[] = [
                'goods_id'=>$gamegoodsid,
                'class_id'=>$v,
            ];
        }
        Db::name('ml_tbl_game_goodsclass')->insertAll($cls);

        $banner = $all['banner'];
        $arr = [];
        foreach ($banner as $v){
            $arr[] = [
              'url'=>$v,
              'goods_id'=>$gamegoodsid,
            ];
        }
        Db::name('ml_tbl_gamegoodsbanner')->insertAll($arr);
        $data = array('status' => 0, 'msg' => '成功', 'data' => array('gamegoodsid' => $gamegoodsid));
        return json($data);
    }

    //修改商品详情 6.29
    public function updGameGoods()
    {
        $all = $this->request->param();
        if (isset($all['banner']) && !empty($all['banner'])){
            $banner = $all['banner'];
            $arr = [];
            foreach ($banner as $v){
                $arr[] = [
                    'url'=>$v,
                    'goods_id'=>$all['id'],
                ];
            }
            Db::name('ml_tbl_gamegoodsbanner')->where('goods_id',$all['id'])->delete();
            Db::name('ml_tbl_gamegoodsbanner')->insertAll($arr);
            unset($all['banner']);
        }
        if (isset($all['class']) && !empty($all['class'])){
            $class = $all['class'];
            $cls = [];
            foreach ($class as $v){
                $cls[] = [
                    'goods_id'=>$all['id'],
                    'class_id'=>$v,
                ];
            }
            Db::name('ml_tbl_game_goodsclass')->where('goods_id',$all['id'])->delete();
            Db::name('ml_tbl_game_goodsclass')->insertAll($cls);
            unset($all['class']);
        }
        $goods_info = Db::name('ml_tbl_gamegoods')->where('id',$all['id'])->update($all);
        if ($goods_info){
            return  json( $data = array('status' => 0, 'msg' => '成功', 'data' => array('gamegoodsid' => $goods_info)));
        }else{
            return  json( $data = array('status' => 1, 'msg' => '失败', 'data' => array('gamegoodsid' => $goods_info)));

        }

    }

    //活动商品列表 6.29
    public function gameGoodsList()
    {
        $no = $_REQUEST['no'];
        $gamegoodsdata = Db::table('ml_tbl_gamegoods')->where('no', $no)->order('weight asc')
            ->field('id,name,head,bighead,introduction,stock,price,original')->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => array('gamegoodslist' => $gamegoodsdata));
        return json($data);
    }

    //活动商品详情 6.29
    public function gameGoodsInfo()
    {
        $gamegoodsid = $_REQUEST['gamegoodsid'];
        $user_id = $this->request->param('user_id');
        $leader_id = $this->request->param('leaderid');
        $gamegoodsdata = Db::table('ml_tbl_gamegoods')->where('id', $gamegoodsid)->where('type','<>',0)->find();
        $gameLeaderInfo = Db::name('ml_tbl_gameleaderinfo')->where('user_id',$user_id)->find();
        if ($gameLeaderInfo){
            $lead_type = 1;
        }else{
            $lead_type = 0;
        }
        $gamegoodsbanner = Db::name('ml_tbl_gamegoodsbanner')->where('goods_id',$gamegoodsid)->column('url');
        $gamegoodsclass = Db::name('ml_tbl_game_goodsclass g')->join('ml_tbl_game_goods_class gc', 'g.class_id=gc.id')->where('goods_id',$gamegoodsid)->field('g.id,gc.name')->select();

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
                //  查询团购分数
                $total = Db::name('ml_tbl_gameorder')->whereIn('order_type','1,2,3')->where(['goodsid'=>$gamegoodsid,'leaderid'=>$gameLeaderInfo['id']])->field('sum(goodsnum) as totalnum, sum(payprice) as totalprice')->select();
                $data = array('status' => 0, 'msg' => '成功', 'data' => array('gamegoodsinfo' => $gamegoodsdata, 'goods_banner'=>$gamegoodsbanner , 'gametype' => $gametype, 'leader_type'=>$lead_type, 'total'=>$total,'class'=>$gamegoodsclass));
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
        if (isset($_REQUEST['address']) && !empty($_REQUEST['address'])){
            $address =  $_REQUEST['address'];
        }else{
            $address = '';
        }
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
                        'goodsnum' => $gamegoodsnum, 'payprice' => $payprice, 'creattime' => date("Y-m-d H:i:s", time()), 'phone' => $userphone, 'name' => $username, 'address'=>$address,'order_type' => 0]);
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
//                    $url = "https://tuitui.tango007.com/sjht/public/gameUnifiedOrder?gameorderid={$order_id}";
                    $url = "http://127.0.0.1/SZhanshan/public/gameUnifiedOrder?gameorderid={$order_id}";
                    $data = object_array(json_decode(curl_get($url)));
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
        $order_id = $_REQUEST['order_id'];
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
            if (isset($result['trade_state']) && ( $result['trade_state'] == 'SUCCESS')) {
                //修改订单状态
                Db::table('ml_tbl_gameorder')->where('order_id', $order_id)->update(['order_type' => 1, 'paytime' => date("Y-m-d H:i:s", time())]);
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
                //增加到团长战绩里面
                $leaderdata['record'] += $gameorderdata['payprice'];
                Db::name('ml_tbl_gameleaderinfo')->where('id',$leaderid)->update(['record'=>$leaderdata['record']]);
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
                $user_info = Db::name('ml_tbl_user')->where('id',$gameorderdata['user_id'])->find();
                $msg = new MessageModel();
                $msg->sendMessage($user_info['wechat_open_id'],PublicEnum::ORDER_PAY,$user_info['form_id'],'推王争霸赛商品购买成功',$gameorderdata['paytime'],$gameorderdata['payprice'],$order_id);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $gameorderdata = Db::table('ml_tbl_gameorder')->where('order_id', $order_id)->find();
                $user_info = Db::name('ml_tbl_user')->where('id',$gameorderdata['user_id'])->find();
                $msg = new MessageModel();
                $msg->sendMessage($user_info['wechat_open_id'],PublicEnum::ORDER_PAY,$user_info['form_id'],'推王争霸赛商品购买成功',$gameorderdata['paytime'],$gameorderdata['payprice'],$order_id);
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
        $user_id = $_REQUEST['user_id'];
        $gameorderdata = Db::view('ml_tbl_gameorder', 'order_id,goodsnum,payprice,order_type')
            ->view('ml_tbl_gamegoods', 'name,head,bighead,size,price', 'ml_tbl_gameorder.goodsid=ml_tbl_gamegoods.id', 'LEFT')
            ->where('ml_tbl_gameorder.user_id', $user_id)
            ->order('creattime desc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $gameorderdata);
        return json($data);
    }

    //团长的订单列表 6.30
    public function leaderGameOrderList()
    {
        $no = $_REQUEST['no'];
        $user_id = $_REQUEST['user_id'];
        $search = $this->request->param('search');
        //查询团长id
        $gameleaderdata = Db::table('ml_tbl_gameleaderinfo')->where('user_id', $user_id)->where('no', $no)->find();
        if ($gameleaderdata) {
            $leaderid = $gameleaderdata['id'];
            //订单分组排序
            $order_leader = Db::name('ml_tbl_gameorder')->where(['leaderid'=>$leaderid])->whereIn('order_type','1,2,3')->whereLike('phone','%'.$search.'%')->group('user_id')->field('user_id,phone,name')->select();
            foreach ($order_leader as $k=>$v){
                $order_leader[$k]['order'] = Db::name('ml_tbl_gameorder o')->join('ml_tbl_gamegoods g','o.goodsid=g.id')->where('leaderid',$leaderid)->whereIn('order_type','1,2,3')->field('o.goodsnum,g.name,g.head,o.order_type')->select();
            }

            $data = array('status' => 0, 'msg' => '成功', 'data' => $order_leader);
        } else {
            $data = array('status' => 0, 'msg' => '该用户为参加编号' . $no . '的活动', 'data' => '');
        }
        return json($data);
    }

    //订单详情 6.30
    public function gameOrderDetails()
    {
        $order_id = $_REQUEST['order_id'];
        $gameorderdata = Db::view('ml_tbl_gameorder', 'order_id,goodsnum,payprice,order_type,name as username,phone,creattime,paytime,leaderid')
            ->view('ml_tbl_gamegoods', 'name,head,bighead,size,price,introduction,minlimit,maxlimit', 'ml_tbl_gameorder.goodsid=ml_tbl_gamegoods.id', 'LEFT')
            ->where('ml_tbl_gameorder.order_id', $order_id)
            ->order('creattime desc')
            ->find();
        $leader_info = Db::name('ml_tbl_gameleaderinfo')->where('id',$gameorderdata['leaderid'])->find();
        $user_info = ['name'=>$gameorderdata['username'], 'phone'=>$gameorderdata['phone']];

        $data = array('status' => 0, 'msg' => '成功', 'data' =>[ 'goods_info'=>$gameorderdata,'leader_info'=>$leader_info,'user_info'=>$user_info]);
        return json($data);
    }

    //团长确认订单 6.30
    public function gameOrderConfirm()
    {
        $user_id = $_REQUEST['user_id'];
        $no = $_REQUEST['no'];
        $order_user_id = $_REQUEST['order_user_id'];
        $gameleaderdata = Db::table('ml_tbl_gameleaderinfo')->where('user_id', $user_id)->where('no', $no)->find();
        //  当前用户是否是团长
        if ($gameleaderdata) {
            $leaderid = $gameleaderdata['id'];
            //  查询团长下的订单
            $gameorderdata = Db::table('ml_tbl_gameorder')->where(['user_id'=> $order_user_id,'leaderid'=>$leaderid])->whereIn('order_type','1,2,3')->find();
            if ($gameorderdata) {
                if ($leaderid == $gameorderdata['leaderid']) {
                    //跟新订单状态
                    Db::table('ml_tbl_gameorder')->where(['user_id'=>$order_user_id,'leaderid'=>$leaderid])->whereIn('order_type','1,2,3')->update(['order_type' => 3]);
                    $data = array('status' => 0, 'msg' => '成功', 'data' => '');
                } else {
                    $data = array('status' => 1, 'msg' => '团长不匹配无法确认', 'data' => '');
                }
            } else {
                $data = array('status' => 1, 'msg' => '订单信息错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '数据错误', 'data' => '');
        }
        return json($data);
    }

    //全部订单列表 6.30
    public function allGameOrderList()
    {
        $no = $_REQUEST['no'];
        $gameorderdata = Db::view('ml_tbl_gameorder', 'order_id,goodsnum,payprice,order_type,name as username,phone,creattime,paytime')
            ->view('ml_tbl_gamegoods', 'name,head,bighead,size,price', 'ml_tbl_gameorder.goodsid=ml_tbl_gamegoods.id', 'LEFT')
            ->where('ml_tbl_gamegoods.no', $no)
            ->order('creattime desc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $gameorderdata);
        return json($data);
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
                $info['order_id'] = $order_id;
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
    //  加入战队
    public function joinTeam()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        if (!isset($all['leader_id']) || empty($all['leader_id'])){
            return responseError();
        }
        if (!isset($all['no']) || empty($all['no'])){
            return responseError();
        }
        $user_info = Db::name('ml_tbl_gameleaderinfo')->where(['user_id'=>$all['user_id'],'no'=>$all['no']])->find();
        if(!$user_info){
            return responseError([],1,'请先成为团长');
        }
        if ($user_info['lid'] != 0){
            return responseError([],2001,'已经加入战队,请勿重复加入战队!');
        }
        $apply_info = Db::name('ml_tbl_team_apply')->where(['user_id'=>$all['user_id'],'leader_id'=>$all['leader_id'],'no'=>$all['no']])->find();
        if ($apply_info){
            return responseError([],3001,'已申请,请勿重复申请!');
        }
        //  增入申请表
        $res = Db::name('ml_tbl_team_apply')->insert(['user_id'=>$all['user_id'],'leader_id'=>$all['leader_id'],'no'=>$all['no'],'ctime'=>time()]);

        if ($res){
            //  TODO:: 发送模板消息  待封装

            return responseSuccess();
        }else{
            return  responseError();
        }

    }
    //  通过申请或拒绝战队
    public function editTeamInfo()
    {
        $all = $this->request->param();
        if (!isset($all['id']) || empty($all['id'])){
            return responseError();
        }
        if (!isset($all['type']) || empty($all['type'])){
            return responseError();
        }

        $apply_info = Db::name('ml_tbl_team_apply')->where('id',$all['id'])->find();

        if (!$apply_info){
            return responseError();
        }
        if (($apply_info['status'] == 1) ||($apply_info['status'] == 2) ){
            return responseError([],3001, '该消息已处理');
        }
        if ($all['type'] == 1){
            Db::name('ml_tbl_gameleaderinfo')->where(['user_id'=>$apply_info['user_id'],'no'=>$apply_info['no']])->update(['lid'=>$apply_info['leader_id'],'join_time'=>date('Y-m-d H:i:s')]);
            Db::name('ml_tbl_gameleaderinfo')->where(['id'=>$apply_info['leader_id'],'no'=>$apply_info['no']])->update(['lid'=>$apply_info['leader_id'],'join_time'=>date('Y-m-d H:i:s')]);
            Db::name('ml_tbl_team_apply')->where('id',$all['id'])->update(['status'=>$all['type']]);
        }else{

            Db::name('ml_tbl_team_apply')->where('id',$all['id'])->update(['status'=>$all['type']]);
        }
        //  TODO:: 加入战队发送模板消息

        return responseSuccess();
    }
    // 申请信息列表
    public function myTeamInfo()
    {
        $all = $this->request->param();

        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        if (!isset($all['no']) || empty($all['no'])){
            return responseError();
        }

        $leader_info = Db::name('ml_tbl_gameleaderinfo')->where(['no'=>$all['no'],'user_id'=>$all['user_id']])->find();
        if ($leader_info){
            $list = Db::name('ml_tbl_team_apply')->where(['no'=>$all['no'],'leader_id'=>$leader_info['id'],'status'=>0])->select();

            return responseSuccess($list);
        }else{
            return responseError();
        }
    }

    //  活动入口图片接口
    public function gameChatInfo()
    {
        $all = $this->request->param();
        if (!isset($all['no']) || empty($all['no'])){
            return responseError();
        }
        $list = Db::name('ml_tbl_game_chart')->where('no',$all['no'])->select();

        return responseSuccess($list);
    }

    //  新增活动商品分类
    public function addGameClass()
    {
        $all = $this->request->param();
        if (!isset($all['name']) || empty($all['name'])){
            return responseError();
        }
        $all['ctime'] = time();

        $res = Db::name('ml_tbl_game_goods_class')->insert($all);

        return responseSuccess();
    }
    //  查看单个分类
    public function getclassInfo()
    {
        $all = $this->request->param();

        if (!isset($all['id']) || empty($all['id'])){
            return responseError();
        }
        $res = Db::name('ml_tbl_game_goods_class')->where('id',$all['id'])->find();

        return responseSuccess($res);

    }
    //  修改
    public function updateGameClass()
    {
        $all = $this->request->param();
        if (!isset($all['name']) || empty($all['name'])){
            return responseError();
        }
        if (!isset($all['id']) || empty($all['id'])){
            return responseError();
        }

        $res = Db::name('ml_tbl_game_goods_class')->where('id',$all['id'])->update(['name'=>$all['name'],'status'=>$all['status']]);

        return responseSuccess();
    }

    //  获取分类列
    public function getgameclass()
    {
        $list = Db::name('ml_tbl_game_goods_class')->where('status',1)->field('id,name,status')->select();
        return responseSuccess($list);
    }

    //  查看用户会员状态
    public function getmemberstatus()
    {
        $user_id = $this->request->param('user_id');
        if ($user_id == 0){
            return responseError();
        }

        $res = Db::name('ml_tbl_user')->where('id',$user_id)->find();
        if ($res['is_salesman'] == 1){
            $type = 1;
        }else{
            $type = 0;
        }
        return responseSuccess(['type'=>$type]);
    }

    public function getUserAddress()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        if (!isset($all['name']) || empty($all['name'])){
            return responseError();
        }
        if (!isset($all['address']) || empty($all['address'])){
            return responseError();
        }

        $res = Db::name('ml_tbl_user_address')->insert($all);
        if ($res){
            return  responseSuccess();
        }else{
            return responseError();
        }
    }

    //  获取我战队数据
    public function getmyteamdata()
    {
        $all = $this->request->param();
        if (!isset($all['no']) || empty($all['no'])){
            return responseError();
        }
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }

        $leader_info = Db::name('ml_tbl_gameleaderinfo')->where('user_id',$all['user_id'])->find();
        if ((!$leader_info) || ($leader_info['lid'] == 0)){
            return responseError();
        }
        $leader_id = Db::name('ml_tbl_gameleaderinfo')->where('lid',$leader_info['lid'])->field('id')->select();
        $ids = idsArrayToStr($leader_id);

        $user_order = Db::name('ml_tbl_gameorder o')->whereIn('leaderid',$ids)->where('order_type','<>',0)->group('user_id')->order('totalprice','desc')->field('name,user_id,sum(payprice) as totalprice')->select();

        $num = 0;
        foreach ($user_order as $k=>$v){
            if ( Db::name('ml_tbl_gameleaderinfo')->where('user_id',$v['user_id'])->find()){
                $user_order[$k]['type'] = 1;
            }else{
                $user_order[$k]['type'] = 0;
            }

            $num += $v['totalprice'];
        }
        $teaminfo = Db::name('ml_tbl_gameleaderinfo')->where('id',$leader_info['lid'])->find();

        return responseSuccess(['info'=>['team_name'=>$teaminfo['name'],'total'=>$num],'user_list'=>$user_order]);
    }

    //团长排行查询
    public function allteamInfo()
    {
        $all = $this->request->param();
        if (!isset($all['no']) || empty($all['no'])){
            return responseError();
        }
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }

        if ( Db::name('ml_tbl_gameleaderinfo')->where('user_id',$all['user_id'])->find() ){

            //  查询团长和查询
            $team_list = Db::name('ml_tbl_gameleaderinfo')->where(['no' => $all['no']])->where('lid', '<>', 0)->group('lid')->order('sumtotal', 'desc')->field('id,user_id,name, sum(record) as sumtotal,lid')->select();
            $lead_list = Db::name('ml_tbl_gameleaderinfo')->where(['no' => $all['no'], 'lid' => 0])->order('record', 'desc')->field('id,user_id,name, record as sumtotal,lid')->select();

            //  放进一个数组
            foreach ($lead_list as $k => $v) {
                $team_list[] = $v;
            }
            //  进行排序
            $leaderranking = arraySort($team_list, 'sumtotal');

            //  查找头像和排序
            $num = 1;
            foreach ($leaderranking as $tk => $tv) {
                $leaderranking[$tk]['sort'] = $num;
                $num++;
                if ($tv['lid'] == 0){
                    $leaderranking[$tk]['name'] = $tv['name'];
                    $leaderranking[$tk]['head'] = Db::query("SELECT headimg FROM `ml_tbl_user` WHERE id = {$tv['user_id']}  ");
                }else{
                    $leaderranking[$tk]['name'] = (Db::query("SELECT * FROM ml_tbl_gameleaderinfo WHERE id = {$tv['lid']}"))[0]['name'];
                    $leaderranking[$tk]['head'] = Db::query(" SELECT u.headimg,u.id FROM ml_tbl_gameleaderinfo as l left join ml_tbl_user as u on l.user_id = u.id WHERE l.lid = {$tv['lid']} LIMIT 3   ");
                }
            }
            return responseSuccess(['list_info'=>$leaderranking]);
        }else{
            return  responseError();
        }


    }



}