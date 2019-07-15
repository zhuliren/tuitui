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

    public function editGameInfo()
    {
        $all = $this->request->param();

        if (!isset($all['id']) || empty($all['id'])){
            return responseError();
        }
        $res = Db::name('ml_tbl_gameinfo')->where('id',$all['id'])->update($all);
        return responseSuccess();
    }

    //历史活动列表 6.29
    public function gameList()
    {
        $gamedata = Db::table('ml_tbl_gameinfo')
            ->field('name,no,introduce,banner,readytime,starttime,overtime,type')
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
            $sql = "SELECT g.id,g.name,g.head,g.bighead,g.introduction,g.stock,g.price,g.minlimit,g.maxlimit,g.type,g.original FROM  `ml_tbl_game_goodsclass` AS gc LEFT JOIN `ml_tbl_gamegoods` AS g ON gc.goods_id = g.id WHERE gc.class_id = {$class} AND g.no = {$no}  AND g.type != 0 ";
            if ($class == 1){
                $sql = "SELECT g.id,g.name,g.head,g.bighead,g.introduction,g.stock,g.price,g.minlimit,g.maxlimit,g.type,g.original FROM  `ml_tbl_game_goodsclass` AS gc LEFT JOIN `ml_tbl_gamegoods` AS g ON gc.goods_id = g.id WHERE gc.class_id = {$class} AND g.no = {$no}  AND g.type != 0
                      UNION SELECT g.id,g.name,g.head,g.bighead,g.introduction,g.stock,g.price,g.minlimit,g.maxlimit,g.type,g.original FROM `ml_tbl_game_goodsclass`  AS gc LEFT JOIN `ml_tbl_gamegoods` AS g ON gc.goods_id = g.id WHERE gc.class_id != 1 AND g.no = {$no} AND g.weight = 1  AND g.type != 0  ";
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

        $class = $all['class'];
        $banner = $all['banner'];
        unset($all['class']);
        unset($all['banner']);

        $gamegoodsid = Db::table('ml_tbl_gamegoods')->insertGetId($all);
        //  新增分类
        $cls = [];
        foreach ($class as $v){
            $cls[] = [
                'goods_id'=>$gamegoodsid,
                'class_id'=>$v,
            ];
        }
        Db::name('ml_tbl_game_goodsclass')->insertAll($cls);
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
        $upid = $this->request->param('upid');
        $gamegoodsdata = Db::table('ml_tbl_gamegoods')->where('id', $gamegoodsid)->where('type','<>',0)->find();
        $gameLeaderInfo = Db::name('ml_tbl_gameleaderinfo')->where('user_id',$user_id)->find();
        if ($gameLeaderInfo){
            $lead_type = 1;
        }else{
            $lead_type = 0;
        }
        //  查询团长id
        if (isset($upid) && !empty($upid)){
            $up_info = Db::name('ml_tbl_gameleaderinfo')->where('user_id',$upid)->find();
        }else{
            $up_info = Db::name('ml_tbl_gameleaderinfo')->where('user_id',$user_id)->find();
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
                $data = array('status' => 0, 'msg' => '成功', 'data' => array('gamegoodsinfo' => $gamegoodsdata, 'goods_banner'=>$gamegoodsbanner , 'gametype' => $gametype, 'total'=>$total,'class'=>$gamegoodsclass,'leader_id'=>$up_info['id']));
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

                    //  实名和限制时间
                    if ($gamegoodsdata['is_fixtime'] == 1){
                        if (!isset($_REQUEST['fixtime']) || empty($_REQUEST['fixtime'])){
                            return responseError();
                        }
                        $fixtime = $_REQUEST['fixtime'];
                    }else{
                        $fixtime = null;
                    }
                    if ($gamegoodsdata['is_realname'] == 1){
                        if (!isset($_REQUEST['realname']) || empty($_REQUEST['realname'])){
                            return responseError();
                        }
                        if (!isset($_REQUEST['id_card']) || empty($_REQUEST['id_card'])){
                            return responseError();
                        }
                        $realname = $_REQUEST['realname'];
                        $id_card = $_REQUEST['id_card'];
                        if (!preg_id_card($id_card)){
                            return responseError([],2001,'身份证号错误');
                        }
                        if (!namePreg($realname)){
                            return responseError([],2001,'真实姓名错误错误');
                        }
                    }else{
                        $id_card = 0;
                        $realname = 0;

                    }


                    //计算需要支付的金额
                    $payprice = $gamegoodsdata['price'] * $gamegoodsnum;
                    //生成必要参数
                    $order_id = $user_id . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                    //插入数据库表
                    Db::table('ml_tbl_gameorder')->insert(['order_id' => $order_id, 'user_id' => $user_id, 'leaderid' => $leaderid, 'goodsid' => $gamegoodsid,
                        'goodsnum' => $gamegoodsnum, 'payprice' => $payprice, 'creattime' => date("Y-m-d H:i:s", time()), 'phone' => $userphone, 'name' => $username, 'address'=>$address,'order_type' => 0,
                        'realname' => $realname, 'fixtime'=>$fixtime, 'id_card'=>$id_card
                            ]);


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

                //  修改库存
                $gamegoodsdata['stock'] -= $goodsnum;
                Db::name('ml_tbl_gamegoods')->where('id',$goodsid)->setDec('stock',$gamegoodsdata['stock']);

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
        //  当前用id查我的团长id
        $leader_info = Db::name('ml_tbl_gameleaderinfo')->where(['no'=>$all['no'],'user_id'=>$all['user_id']])->find();
        if ($leader_info){
            $list = Db::name('ml_tbl_team_apply t')->join('ml_tbl_gameleaderinfo i','t.user_id =i.user_id')->join('ml_tbl_user u','t.user_id=u.id')->where(['t.no'=>$all['no'],'t.leader_id'=>$leader_info['id'],'t.status'=>0])->field('i.name,i.record,u.headimg,t.status')->select();
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
        $list = Db::name('ml_tbl_game_chart')->where('no',$all['no'])->where('status',2)->find();

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

        $list = Db::name('ml_tbl_user_address')->where('user_id',$all['user_id'])->order('id','desc')->select();
        if (!empty($list)){
            $res = $list[0];
        }else{
            $res = [];
        }
        return responseSuccess($res);
    }

    public function addmyaddress()
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
        if (isset($all['tel']) && !empty($all['tel'])){
            if (!preg_mobile($all['tel'])){
                return responseError();
            }
        }else{
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


    public function gamegoodsrcode()
    {
        $goods_id = $_REQUEST['goodsid'];
        $upid = $_REQUEST['upid'];
        //判断数据库是否存在相同二维码
        $rcodedata = Db::table('ml_tbl_game_rcode')->where('goods_id', $goods_id)->where('upid', $upid)->find();
        if ($rcodedata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('rcodeurl' => $rcodedata['url']));
        } else {
            $fielname = rand(100, 99999) . $goods_id . $upid . '.png';
            // 为二维码创建一个文件
            $fiel = $_SERVER['DOCUMENT_ROOT'] . '/ttgoodssharercode/' . $fielname;
            //获取access_token
            $appid = 'wx0fda8074ccdb716d';
            $srcret = 'bf55d7a720d5bc162621e3901b7645be';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $srcret;
            // get请求获取access_token
            $data = curl_get($url);
            $data = json_decode($data, true);
            //获取二维码
            //参数
            $postdata['scene'] = "goodsid=" . $goods_id . ",upid=" . $upid;
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
            $result = postCurl($url, $post_data);
            // 保存二维码
            file_put_contents($fiel, $result);
            $fileurl = 'https://tuitui.tango007.com/ttgoodssharercode/' . $fielname;
            $intodata = array('goods_id' => $goods_id, 'upid' => $upid, 'url' => $fileurl);
            Db::table('ml_tbl_game_rcode')->insert($intodata);
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('rcodeurl' => $fileurl));
        }
        return json($data);
    }

    //  商品下架/软删除
    public function gamegoodsdown()
    {
        $all = $this->request->param();
        if (!isset($all['goods_id']) || empty($all['goods_id'])){
            return responseError();
        }
        $res = Db::name('ml_tbl_gamegoods')->where('id',$all['goods_id'])->update(['type'=>0]);
        return responseSuccess($res);
    }

    //  活动下架
    public function gamedown()
    {
        $all = $this->request->param();

        if (!isset($all['gameid']) || empty($all['ganemid'])){
            return responseError();
        }
        if (!isset($all['type'])){
            return responseError();
        }
        if ($all['type'] == 1){
            $res = Db::name('ml_tbl_gameinfo')->where('id',$all['gameid'])->update(['type'=>1]);

        }else{
            $res = Db::name('ml_tbl_gameinfo')->where('id',$all['gameid'])->update(['type'=>2]);
        }

        return responseSuccess($res);
    }


    public function leaderstatus()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        if (!isset($all['no']) || empty($all['no'])){
            return responseError();
        }

        $res = Db::name('ml_tbl_gameleaderinfo')->where(['user_id'=>$all['user_id'],'no'=>$all['no']])->find();
        if ($res){
            $type = 1;
        }else{
            $type = 0;
        }

        return responseSuccess(['type'=>$type,'info'=>$res]);
    }

    public function successInfo()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        if (!isset($all['no']) || empty($all['no'])){
            return responseError();
        }

        $user_info = Db::name('ml_tbl_gameleaderinfo')->where(['no'=>$all['no'], 'user_id'=>$all['user_id']])->find();
        if (!$user_info){
            return responseError();
        }
        $res = Db::name('ml_tbl_team_apply')->where(['leader_id'=>$user_info['id'],'no'=>$all['no'],'status'=>1])->select();

        return responseSuccess(['list'=>$res]);
    }

    //  画布


    public function getimg()
    {
        header("Content-type:image/png");
        $a = imagecreate(100,100);
        imagecolorallocate($a,255,0,255);
        imagepng($a);
        imagedestroy($a);
    }

    /**
     * 生成成就图
     */
    public function createsharepng()
    {

        $all = $this->request->param();
        $uid = $all['user_id'];

        $gData = Db::name('ml_tbl_gamegoods')->where('id',$all['gamegoodsid'])->find();
        $fontsize = 12;

        ob_clean ();

        Header("Content-Type: image/png");

        //创建画布
        $im = imagecreatetruecolor(750, 1335);
        //填充画布背景色
        $color = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $color);
        //背景图
        $bacimg = 'static/bg.jpg';

        list($b_w,$b_h) = getimagesize($bacimg);
        $bacimg = $this->createImageFromFile($bacimg);
        imagecopyresized($im, $bacimg, 0, 0, 0, 0, 750, 1335, $b_w, $b_h);

        //字体文件
        $font_file = "static/PINGFANG.TTF";
        //设定字体的颜色
        $font_color_1 = ImageColorAllocate ($im, 18, 18, 18);//黑色
        $font_color_2 = ImageColorAllocate ($im, 153, 153, 153);//灰色
        $font_color_3 = ImageColorAllocate ($im, 239, 239, 239);//灰色
        //二维码
        $ress = self::getQrcodeImage($uid);
        if($ress)
        {
            $logoImg =  $ress;
        }else{
            $logoImg  = 'static/upload/user.jpg';
        }
        //Logo二维码
        list($l_w,$l_h) = getimagesize($logoImg);
        $logoImg = $this->createImageFromFile($logoImg);
        imagecopyresized($im, $logoImg, 50, 980, 0, 0, 170, 170, $l_w, $l_h);
        //个人头像
        $image =  $gData['cover'] ; // 原图

        list($g_w,$g_h) = getimagesize($image);
        $headimg = $this->createImageFromFile($image);
        imagecopyresized($im, $headimg, 80, 580, 0, 0, 79, 79, $g_w, $g_h);

        $nick_name = $gData['nick_name'];
        imagettftext($im, 28,0, 180, 600, $font_color_1 ,$font_file, $nick_name);

        imagettftext($im, $fontsize,0, 490 + (4-$count)*10, 890, $font_color_1 ,$font_file, $mins);
        imagettftext($im, $fontsize,0, 90, 890, $font_color_1 ,$font_file, $bai);
        imagettftext($im, $fontsize,0, 125, 890, $font_color_1 ,$font_file, $shi);
        imagettftext($im, $fontsize,0, 170, 890, $font_color_1 ,$font_file, $ge);
        $per = rand(90,99);
        imagettftext($im, $fontsize,0, 280, 890, $font_color_1 ,$font_file, $per);
        $path2 = 'static/upload/qrcode/'.rand(10000,99999).time().".png";

        imagepng ($im,$path2);
        $imgurl = "https://rwan.org.cn/".$path2;
        $data['promotion_img'] = $imgurl;
        $data['id'] = $uid;
        $this->success("生成成功",$imgurl);
        $res = UserService::save($data,$uid);
        if($res){
            $this->success("生成成功",$imgurl);
        }else{
            $this->error("生成失败");
        }
        exit();

        //释放空间
        imagedestroy($im);
        imagedestroy($goodImg);
        imagedestroy($codeImg);
    }


    /**
     * 从图片文件创建Image资源
     * @param $file 图片文件，支持url
     * @return bool|resource  成功返回图片image资源，失败返回false
     */
    function createImageFromFile($file){
        if(preg_match('/http(s)?:\/\//',$file)){
            $fileSuffix = $this->getNetworkImgType($file);
        }else{
            $fileSuffix = pathinfo($file, PATHINFO_EXTENSION);
        }

        if(!$fileSuffix) return false;

        switch ($fileSuffix){
            case 'jpeg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'jpg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'png':
                $theImage = @imagecreatefrompng($file);
                break;
            case 'gif':
                $theImage = @imagecreatefromgif($file);
                break;
            default:
                $theImage = @imagecreatefromstring(file_get_contents($file));
                break;
        }

        return $theImage;
    }


    /**
     * 获取网络图片类型
     * @param $url 网络图片url,支持不带后缀名url
     * @return bool
     */
    function getNetworkImgType($url)
    {
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //设置需要获取的URL
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //支持https
        curl_exec($ch);//执行curl会话
        $http_code = curl_getinfo($ch);//获取curl连接资源句柄信息
        curl_close($ch);//关闭资源连接

        if ($http_code['http_code'] == 200) {
            $theImgType = explode('/',$http_code['content_type']);

            if($theImgType[0] == 'image'){
                return $theImgType[1];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 分行连续截取字符串
     * @param $str 需要截取的字符串,UTF-8
     * @param int $row 截取的行数
     * @param int $number  每行截取的字数，中文长度
     * @param bool $suffix 最后行是否添加‘...'后缀
     * @return array  返回数组共$row个元素，下标1到$row
     */
    function cn_row_substr($str,$row = 1,$number = 10,$suffix = true)
    {
        $result = array();
        for ($r=1;$r<=$row;$r++){
            $result[$r] = '';
        }

        $str = trim($str);
        if(!$str) return $result;

        $theStrlen = strlen($str);

        //每行实际字节长度
        $oneRowNum = $number * 3;
        for($r=1;$r<=$row;$r++){
            if($r == $row and $theStrlen > $r * $oneRowNum and $suffix){
                $result[$r] = $this->mg_cn_substr($str,$oneRowNum-6,($r-1)* $oneRowNum).'...';
            }else{
                $result[$r] = $this->mg_cn_substr($str,$oneRowNum,($r-1)* $oneRowNum);
            }
            if($theStrlen < $r * $oneRowNum) break;
        }

        return $result;
    }

    /**
     * 按字节截取utf-8字符串
     * 识别汉字全角符号，全角中文3个字节，半角英文1个字节
     * @param $str 需要切取的字符串
     * @param $len 截取长度[字节]
     * @param int $start  截取开始位置，默认0
     * @return string
     */
    function mg_cn_substr($str,$len,$start = 0)
    {
        $q_str = '';
        $q_strlen = ($start + $len) > strlen($str) ? strlen($str) : ($start + $len);

        //如果start不为起始位置，若起始位置为乱码就按照UTF-8编码获取新start
        if ($start and json_encode(substr($str, $start, 1)) === false) {
            for ($a = 0; $a < 3; $a++) {
                $new_start = $start + $a;
                $m_str = substr($str, $new_start, 3);
                if (json_encode($m_str) !== false) {
                    $start = $new_start;
                    break;
                }
            }
        }

        //切取内容
        for ($i = $start; $i < $q_strlen; $i++) {
            //ord()函数取得substr()的第一个字符的ASCII码，如果大于0xa0的话则是中文字符
            if (ord(substr($str, $i, 1)) > 0xa0) {
                $q_str .= substr($str, $i, 3);
                $i += 2;
            } else {
                $q_str .= substr($str, $i, 1);
            }
        }
        return $q_str;
    }

    public function getQrcodeImage( $user_id)
    {
        $access_token = (new MessageModel())->getToken(PublicEnum::WX_APPID,PublicEnum::WX_SECRET);
        $api = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$access_token}";
        header('content-type:image/gif');
        $data = array();
        $data['scene'] = $user_id;//自定义信息，可以填写诸如识别用户身份的字段，注意用中文时的情况
        $data['page'] = "pages/index/index";//扫描后对应的path
        $data['width'] = 170;//自定义的尺寸
        $data['auto_color'] = false;//是否自定义颜色
        $color = array(
            "r"=>"221",
            "g"=>"0",
            "b"=>"0",
        );
        $data['line_color'] = $color;//自定义的颜色值
        $data = json_encode($data);
        $da = postCurl($api,$data);
        $path2 = 'static/upload/qrcode/'.rand(10000,99999).time()."1.jpg";
        file_put_contents($path2, $da);
        return  $path2;

    }

    public function get_http_array($url,$data) {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return $tmpInfo; // 返回数据

    }



    public static function createWxQrcode()
    {

        //配置APPID、APPSECRET
        $APPID = PublicEnum::WX_APPID;
        $APPSECRET = PublicEnum::WX_SECRET;
        //获取access_token
        $access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$APPID&secret=$APPSECRET";

        $params = [
            'gamegoodsid'=>13,
            'upid'=>13,
        ];

        //缓存access_token
        session_start();
        $_SESSION['access_token'] = "";
        $_SESSION['expires_in'] = 0;

        if (!isset($_SESSION['access_token']) || (isset($_SESSION['expires_in']) && time() > $_SESSION['expires_in'])) {

            $json = curl_get($access_token);
            $json = json_decode($json, true);
            // var_dump($json);
            $_SESSION['access_token'] = $json['access_token'];
            $_SESSION['expires_in'] = time() + 7200;
            $ACCESS_TOKEN = $json["access_token"];
        } else {

            $ACCESS_TOKEN = $_SESSION["access_token"];
        }

        //构建请求二维码参数
        //path是扫描二维码跳转的小程序路径，可以带参数?id=xxx
        //width是二维码宽度
        $qcode = "https://api.weixin.qq.com/wxa/getwxacode?access_token=$ACCESS_TOKEN";
        $param = array("path" => "packageA/details/details?id={$params['gamegoodsid']}&recom_uid={$params['upid']}", "width" => 100);


        //POST参数
        $result = curl_post($qcode, $param);
        $filename = ROOT_PATH . 'public/uploads/qrcode/' . $params['gamegoodsid'] . '_' . $params['upid'] . '_qrcode_wx.png';
        //生成二维码
        file_put_contents($filename, $result);
        $image = '/uploads/qrcode/' . $params['gamegoodsid'] . '_' . $params['upid'] . '_qrcode_wx.png';
        $base64_image = "data:image/jpeg;base64," . base64_encode($result);
        #echo $base64_image;
        return $image;
    }

    public static function createMiniWechat()
    {

        $config = array(
            'image' => array(
                array(
                    'url' => "http://hanshantuitui.oss-cn-hangzhou.aliyuncs.com/bz.png",     //素材地址
                    'is_yuan' => false,          //true图片圆形处理
                    'stream' => 0,
                    'left' => 42,               //小于0为小平居中
                    'top' => 34,
                    'right' => 0,
                    'width' => 422,             //图像宽
                    'height' => 483,            //图像高
                    'opacity' => 100            //透明度
                ),
                array(
                    'url' => "https://tuitui.tango007.com/ttgoodssharercode/26468343487.png",     //二维码地址
                    'is_yuan' => true,          //true图片圆形处理
                    'stream' => 0,
                    'left' => 312,               //小于0为小平居中
                    'top' => 570,
                    'right' => 0,
                    'width' => 100,             //图像宽
                    'height' => 100,            //图像高
                    'opacity' => 100            //透明度
                ),

                array(
                    'url' => "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKoBtMSuFfDDHCtuGVMdDcDvnkVvTPLxEyGqVcPBoaeZ2ic7DtRK4GF4Vn8I1AML76iaX56Dic2hEFYQ/132",     //素材地址
                    'is_yuan' => true,          //true图片圆形处理
                    'stream' => 0,
                    'left' => 88,               //小于0为小平居中
                    'top' => 555,
                    'right' => 0,
                    'width' => 80,             //图像宽
                    'height' => 80,            //图像高
                    'opacity' => 100            //透明度
                ),
            ),
            'text' => array(
                array(
                    'text' => '测试文本',            //文字内容
                    'left' => 10,                              //小于0为小平居中
                    'top' => 290,
                    'fontSize' => 14,                         //字号
                    'fontColor' => '0,0,0',                //字体颜色
                    'angle' => 0,
                    'fontPath' => ROOT_PATH . 'public/assets/fonts/FZZJ-KYTJW.TTF',     //字体文件
                ),
                array(
                    'text' => '文字内容',            //文字内容
                    'left' => 10,                              //小于0为小平居中
                    'top' => 340,
                    'fontSize' => 12,                         //字号
                    'fontColor' => '169,169,169',                //字体颜色
                    'angle' => 0,
                    'fontPath' => ROOT_PATH . 'public/assets/fonts/FZZJ-KYTJW.TTF',     //字体文件
                ),
                array(
                    'text' => '￥' . 100,            //文字内容
                    'left' => 10,                              //小于0为小平居中
                    'top' => 365,
                    'fontSize' => 14,                         //字号
                    'fontColor' => '255,20,147',                //字体颜色
                    'angle' => 0,
                    'fontPath' => ROOT_PATH . 'public/assets/fonts/FZZJ-KYTJW.TTF',     //字体文件
                ),
                array(
                    'text' => '长按识别小程序码访问',            //文字内容
                    'left' => 10,                              //小于0为小平居中
                    'top' => 435,
                    'fontSize' => 14,                         //字号
                    'fontColor' => '0,0,0',                //字体颜色
                    'angle' => 0,
                    'fontPath' => ROOT_PATH . 'public/assets/fonts/FZZJ-KYTJW.TTF',     //字体文件
                ),
                array(
                    'text' => '抢购价',            //文字内容
                    'left' => 10,                              //小于0为小平居中
                    'top' => 470,
                    'fontSize' => 13,                         //字号
                    'fontColor' => '169,169,169',                //字体颜色
                    'angle' => 0,
                    'fontPath' => ROOT_PATH . 'public/assets/fonts/FZZJ-KYTJW.TTF',     //字体文件
                )
            ),
            'background' => ROOT_PATH . 'public/assets/img/bj.png',          //背景图
        );
        $params = [
            'line_id'=>12,
            'user_id'=>12,
        ];
        $filename = ROOT_PATH . 'public/uploads/qrcode/' . $params['line_id'] . '_' . $params['user_id'] . '_qrcode.png';

        //echo createPoster($config);
        //$filename为空是真接浏览器显示图片
        $rest = createPoster($config, $filename);
        dd($rest);
        if ($rest) {
            $image = '/uploads/qrcode/' . $params['line_id'] . '_' . $params['user_id'] . '_qrcode.png';
            db('qrcode_record')->insert(['user_id' => $params['user_id'], 'line_id' => $params['line_id'], 'image' => $image, 'create_time' => time()]);
            return $image;
        }
        return false;
    }










}