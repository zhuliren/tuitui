<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/6/20
 * Time: 11:15
 */

namespace app\index\controller;


use app\index\Controller;
use think\Db;
use think\Request;

class Event extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    // 活动商品详情
    public function getEventInfo()
    {
        $all = $this->request->param();
        $goods = Db::name('ml_tbl_event_goods')->where(['is_online'=>1])->field('id,goods_name,goods_summary,buy_limit_num,goods_price,goods_stock,head_img,format')->select();
        if(isset($all['user_id']) && !empty($all['user_id'])){
            //  是否参加团购
            $member_info = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->find();
            //  有团购信息
            if ($member_info){
                $order_list = Db::name('ml_tbl_event_order')->where(['lead_id'=>$member_info['id']])->whereIn('order_type','2,3,4')->group('user_id')->select();
                $arr = [];
                foreach ($order_list as $k=>$v){
                    //  用户信息
                    $user_info = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->find();

                    //  该用户下级所有订单
                    $user_order_list = Db::name('ml_tbl_event_order')->where(['user_id'=>$v['user_id'],'lead_id'=>$member_info['id']])->whereIn('order_type','2,3,4')->select();
                    $total = 0;
                    foreach ( $user_order_list as $ok=>$ov){
                        $total += $ov['pay_price'];
                    }
                    $arr[] = [
                        'user_name'=>$user_info['user_name'],
                        'head_img'=>$user_info['head_img'],
                        'but_num'=>$total,
                    ];
                }
                $arr = arraySort($arr,'but_num');
                $num = 1;
                foreach ($arr as $sk=>$sv){
                    $arr[$sk]['sort'] = $num;
                    $num++;
                }
                //  所有团长团购排行
                $lead_list = Db::name('ml_tbl_event_order')->group('lead_id')->select();
                foreach ($lead_list as $lk=>$lv){
                    if ($lv['lead_id'] == 0){
                        unset($lead_list[$lk]);
                    }
                }
                $lead_order_info = [];
                foreach ( $lead_list as $ldk=>$ldv){
                    //  该团长的所有订单
                    $lead_order = Db::name('ml_tbl_event_order')->where('lead_id',$ldv['lead_id'])->whereIn('order_type','2,3,4')->select();
                    $lead_info = Db::name('ml_tbl_event_member')->where('id',$ldv['lead_id'])->find();

                    $pay_total = 0;
                    foreach ($lead_order as $ltk=>$ltv){
                        $pay_total += $ltv['pay_price'];
                    }
                    $lead_order_info[] = [
                        'user_id'=>$lead_info['user_id'],
                        'user_name'=>$lead_info['user_name'],
                        'head_img'=>$lead_info['head_img'],
                        'but_num'=>$pay_total,
                    ];
                }
                $head_list = arraySort($lead_order_info,'but_num');
                $rank = 1;
                foreach ($head_list as $rk=>$rv){
                    $head_list[$rk]['sort'] = $rank;
                    $rank++;
                }
                $data = ['status'=>1001,'msg'=>'','data'=>['goods'=>$goods,'member_list'=>$arr,'head_list'=>$head_list]];
            }else{
                $data = ['status'=>1001,'msg'=>'','data'=>['goods'=>$goods,'member_list'=>[],'head_list'=>[]]];
            }
        }else{
            $data = ['status'=>2001,'msg'=>'用户参数错误','data'=>''];
        }

        return json($data);
    }

    //  参加活动
    public function joinActivity()
    {
        $all = $this->request->param();

        if (isset($all['user_id']) && !empty($all['user_id']) ){
            //  判断是否是分销员
            $user_info = Db::name('ml_tbl_user')->where(['id'=>$all['user_id'],'is_salesman'=>1])->find();
            if (!$user_info){
                return json(['status'=>3001,'msg'=>'只有分销员才可发起活动','data'=>'']);
            }
            //  是否参加了活动
            $activity = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->find();
            if (!$activity){
                $user_info = Db::name('ml_tbl_user')->where('id',$all['user_id'])->find();
//                if (!preg_mobile($all['tel'])){
//                    return json(['status'=>2001,'msg'=>'手机号错误','data'=>'']);
//                }

                if (!empty($user_info['headimg'])){
                    $all['head_img'] = $user_info['headimg'];
                }else{
                    $all['head_img'] = "http://tuitui.tango007.com/ttimg/head_img.png";
                }

                if (!isset($all['user_name']) || empty($all['user_name'])){
                    return responseError();
                }

                if (!isset($all['tel']) || empty($all['tel'])){
                    return responseError();
                }

                $ak = "mC9sPZG8X8Uqq0voMfPp6cc92hk4Cz3T";

                $url = "http://api.map.baidu.com/geocoding/v3/?address={$all['address']}&output=XML&ak=$ak&callback=showLocation";
                $res = curl_get($url);
                $location = FromXml($res);
                if ($location['status'] == 0){
                    if (!empty($location['result'])){
                        $all['lng'] = $location['result']['location']['lng'];
                        $all['lat'] = $location['result']['location']['lat'];
                    }
                }

                $res = Db::name('ml_tbl_event_member')->insertGetId($all);

                Db::name('ml_tbl_event_lead')->insert(['lead_id'=>$res,'event_id'=>1]);
                if ($res ){
                    return json(['status'=>1001,'msg'=>'恭喜成为团长','data'=>'']);
                }else{
                    return json(['status'=>2001,'msg'=>'创建团队失败','data'=>'']);
                }

            }else{

                if (!empty($user_info['headimg'])){
                    $all['head_img'] = $user_info['headimg'];
                }else{
                    $all['head_img'] = "http://tuitui.tango007.com/ttimg/head_img.png";
                }

                if (!isset($all['user_name']) || empty($all['user_name'])){
                    return responseError();
                }

                if (!isset($all['tel']) || empty($all['tel'])){
                    return responseError();
                }
                $ak = "mC9sPZG8X8Uqq0voMfPp6cc92hk4Cz3T";

                $url = "http://api.map.baidu.com/geocoding/v3/?address={$all['address']}&output=XML&ak=$ak&callback=showLocation";
                $res = curl_get($url);
                $location = FromXml($res);
                if ($location['status'] == 0){
                    if (!empty($location['result'])){
                        $all['lng'] = $location['result']['location']['lng'];
                        $all['lat'] = $location['result']['location']['lat'];
                    }
                }
                $all['pid'] = 0;
                $res = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->update($all);
                $lead_id = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->value('id');

                $lead_id_status = Db::name('ml_tbl_event_lead')->where('lead_id',$lead_id)->find();

                if (!$lead_id_status){
                    Db::name('ml_tbl_event_lead')->insert(['lead_id'=>$lead_id,'event_id'=>1]);
                }


                if ($res ){
                    return json(['status'=>1001,'msg'=>'恭喜成为团长','data'=>'']);
                }else{
                    return json(['status'=>2001,'msg'=>'创建团队失败','data'=>'']);
                }
            }
        }
    }

    public function goodsInfo()
    {
        $all = $this->request->param();

        $goods_info = Db::name('ml_tbl_event_goods')->where('id',$all['goods_id'])->find();
        $goods_banner = Db::name('ml_tbl_event_banner')->where('event_id',$all['goods_id'])->column('img');
        $user_info = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->find();
        //  用户是否参加了活动
        if (!$user_info){
            return json(['status'=>3001,'msg'=>'不在团购队列','data'=>'']);
        }
        if (!$goods_info){
            return json(['status'=>3001,'msg'=>'暂无该商品信息','data'=>'']);
        }


        $order_list = Db::name('ml_tbl_event_order')->where(['goods_id'=>$all['goods_id'],'lead_id'=>$user_info['id']])->select();

        $num = 0;
        foreach ($order_list as $k=>$v){
            $num += $v['goods_num'];
        }
        $lead = $user_info['id'];
//        //  查看团队成员订单
//        if ($user_info['pid'] == 0){
//            //  所有成员包含自己
//            $users = Db::name('ml_tbl_event_member')->where('pid',$user_info['id'])->select();
//            $users[] = Db::name('ml_tbl_event_member')->where('id',$user_info['id'])->find();
//            $order_user_id = idsArrayToStr($users,'user_id');
//            //  已完成订单
//            $order_info = Db::name('ml_tbl_event_order')->whereIn('order_type','2,3,4')->whereIn('user_id',$order_user_id)->whereIn('goods_id',$all['goods_id'])->select();
//            //  统计数量
//            $num = 0;
//            foreach ($order_info as $k=>$v){
//                $num += $v['goods_num'];
//            }
//            //  团长id
//            $lead = $user_info['id'];
//        }else{
//
//            $users = Db::name('ml_tbl_event_member')->where('pid',$user_info['pid'])->select();
//            $users[] = Db::name('ml_tbl_event_member')->where('id',$user_info['pid'])->find();
//            $order_user_id = idsArrayToStr($users,'user_id');
//            $order_info = Db::name('ml_tbl_event_order')->whereIn('order_type','2,3,4')->whereIn('user_id',$order_user_id)->whereIn('goods_id',$all['goods_id'])->select();
//            $num = 0;
//            foreach ($order_info as $k=>$v){
//                $num += $v['goods_num'];
//            }
//            $lead = $user_info['pid'];
//        }
        return json(['status'=>1001,'msg'=>'成功','data'=>['goods'=>$goods_info,'banner'=>$goods_banner,'num'=>$num,'lead'=>$lead]]);
    }


    public function goodsShareUrl()
    {
        //  判断活动是否到期
        $time = time();
        $Exdate = strtotime('2019-06-29 18:00:00');
        if ($time >= $Exdate){
            return json(['status'=>3001,'msg'=>'该活动已到期','data'=>'']);
        }
        $all = $this->request->param();
        if (!isset($all['lead_id']) || empty($all['lead_id'])){
            return json(['status'=>2001,'msg'=>'团长id错误','data'=>'']);
        }
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }
        //  查看用户是否存在
        $user_info = Db::name('ml_tbl_user')->where('id',$all['user_id'])->find();
        if (!$user_info){
            return json(['status'=>5001,'msg'=>'用户不存在','data'=>'']);
        }
//        if ($user_info['pid'] != $user_info['id']){
        Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->update(['pid'=>$all['lead_id']]);
//        }

        //  商品信息
        $goods_info = Db::name('ml_tbl_event_goods')->where(['id'=>$all['goods_id'],'is_online'=>1])->find();
        $goods_info['banner'] = Db::name('ml_tbl_event_banner')->where('event_id',$all['goods_id'])->column('img');
        if (empty($goods_info)) {
            return json(['status' => 2001, 'msg' => '商品已下架', 'data' => '']);
        }
        //  查看是否参加了活动
        $event_status = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->find();

        if (($event_status['pid'] != $event_status['id']) ){
            if ($event_status['pid'] ==  $event_status['id']){
                Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->update(['pid'=>0]);
            }
            Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->update(['pid'=>$all['lead_id']]);
        }

        if ($event_status){
            return json(['status'=>1001,'msg'=>'成功','data'=>$goods_info]);
        }
        //  没有参加活动
        if (!empty($user_info['headimg'])){
            $head_img = $user_info['headimg'];
        }else{
            $head_img = "http://tuitui.tango007.com/ttimg/head_img.png";
        }
        $arr = [
            'user_id'=>$all['user_id'],
            'pid'=>$all['lead_id'],
            'head_img'=>$head_img,
        ];
        $insert_info = Db::name('ml_tbl_event_member')->insert($arr);
        if ($insert_info){
            return json(['status'=>1001,'msg'=>'成功','data'=>$goods_info]);
        }else{
            return json(['status'=>5001,'msg'=>'错误,请重新点击链接进入','data'=>'']);
        }
    }

    //  下单页数据
    public function buyGoods()
    {
        $all = $this->request->param();

        $goods_info = Db::name('ml_tbl_event_goods')->where('id',$all['goods_id'])->find();
        $member_info = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->find();
        if ($member_info['pid'] != 0){
            $lead_info = Db::name('ml_tbl_event_member')->where('id',$member_info['pid'])->find();

            return json(['status'=>1001,'msg'=>'成功','data'=>['goods'=>$goods_info,'lead_info'=>$lead_info,'my_info'=>$member_info]]);
        }else{
            return json(['status'=>1001,'msg'=>'成功','data'=>['goods'=>$goods_info,'lead_info'=>$member_info,'my_info'=>$member_info]]);
        }
    }

    public function createEventOrder()
    {
        $all = $this->request->param();

        if (!isset($all['goods_id']) || empty($all['goods_id'])){
            return json(['status'=>2001,'msg'=>'系统错误','data'=>'']);
        }
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return json(['status'=>2001,'msg'=>'系统错误','data'=>'']);
        }
        if (!isset($all['goods_num']) || empty($all['goods_num'])){
            return json(['status'=>2001,'msg'=>'系统错误','data'=>'']);
        }
        if (!isset($all['pay_price']) || empty($all['pay_price'])){
            return json(['status'=>2001,'msg'=>'系统错误','data'=>'']);
        }
        //  身份信息新增
        if (isset($all['tel']) && !empty($all['tel'])){
            $tel_status = preg_mobile($all['tel']);
            if ($tel_status){
                Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->update(['user_name'=>$all['user_name'],'tel'=>$all['tel']]);
            }else{
                return responseError([],2001,'手机格式不正确');
            }
        }else{
            return responseError([],2001,'缺少手机号');
        }
        //  计算价格
        $goods_info = Db::name('ml_tbl_event_goods')->where('id',$all['goods_id'])->find();
        $pay_price = number_format($all['pay_price'],2);
        $sum_total = number_format($all['goods_num'] * $goods_info['goods_price'] ,2);
        if ($pay_price !== $sum_total){
            return json(['status'=>2001,'msg'=>'价格错误','data'=>'']);
        }
        //  判断库存
        if (($goods_info['goods_stock'] - $all['goods_num']) < 0){
            return json(['status'=>2001,'msg'=>'库存不足','data'=>'']);
        }
        $order_num = randomOrder_no();
        $order_exits = Db::name('ml_tbl_event_order')->where('order_id',$order_num)->find();
        if ($order_exits){
            $order_num = randomOrder_no();
        }
        //  查询 团长id
        $member_info = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->find();
        if ($member_info['pid'] == 0 ){
            $lead_id = $member_info['id'];
        }else{
            $lead_id = $member_info['pid'];
        }
        //  把用户名和手机号加入进去
        $arr = [
            'order_id'=>$order_num,
            'order_type'=>1,
            'user_id'=>$all['user_id'],
            'goods_id'=>$all['goods_id'],
            'goods_num'=>$all['goods_num'],
            'goods_price'=>$goods_info['goods_price'],
            'pay_price'=>$pay_price,
            'creat_time'=>date('Y-m-d H:i:s'),
            'tel'=>$all['tel'],
            'user_name'=>$all['user_name'],
            'lead_id'=>$lead_id,
        ];

        $insert_order = Db::name('ml_tbl_event_order')->insertGetId($arr);

        if ($insert_order){
            return json(['status'=>1001,'msg'=>'订单创建成功','data'=>$insert_order]);
        }else{
            return json(['status'=>3001,'msg'=>'订单创建失败','data'=>'']);
        }
    }


    /**
     * @return \think\response\Json
     * @time: 2019/6/23
     * @autor: duheyuan
     * 订单列表
     */
    public function eventOrderList()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id']) ){
            return responseError([],2001,'参数错误');
        }

        $sql = "SELECT o.id,g.head_img,g.goods_name,g.goods_summary,o.order_id,o.order_type,o.goods_num,o.pay_price,o.creat_time FROM `ml_tbl_event_order` AS o  RIGHT JOIN `ml_tbl_event_goods` AS g ON o.goods_id = g.id  WHERE o.user_id = {$all['user_id']} AND o.order_type in (2,3) ORDER BY o.id  DESC ";
        $order_list = Db::query($sql);
        return responseSuccess($order_list);
    }

    /**
     * @return \think\response\Json
     * @time: 2019/6/23
     * @autor: duheyuan
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 订单详情
     */
    public function eventOrderDetail()
    {
        $all = $this->request->param();

        if (!isset($all['id']) || empty($all['id'])){
            return responseError();
        }

        $return_data['order_info'] = Db::name('ml_tbl_event_order')->where('id',$all['id'])->find();
        $return_data['goods_info'] = Db::name('ml_tbl_event_goods')->where('id',$return_data['order_info']['goods_id'])->find();


        $user_info = Db::name('ml_tbl_event_member')->where('user_id',$return_data['order_info']['user_id'])->find();
        //  判断用是否是团长信息
        if ($user_info['pid'] == 0){
            $return_data['lead_info'] = $user_info;
            $return_data['user_info'] = $user_info;
        }else{
            $return_data['lead_info'] = Db::name('ml_tbl_event_member')->where('id',$user_info['pid'])->find();
            $return_data['user_info'] = $user_info;
        }
        return responseSuccess($return_data);
    }

    //  分享文本
    public function shareInfo()
    {
        $all = $this->request->param();

        if (!isset($all['goods_id']) || empty($all['goods_id'])){
            return responseError();
        }
        $info = Db::name('ml_tbl_event_goods')->where('id',$all['goods_id'])->field('content,share_img')->find();
        $info['share_img'] = explode("##",$info['share_img']);
        return responseSuccess($info);
    }

    //  查看团队订单
    public function lookTeamOrder()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }

        $user_info  = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->find();
//        if ($user_info['pid'] != 0){
//            return responseError();
//        }
        if (  isset($all['tel']) && !empty($all['tel'])){
            $tel = $all['tel'];
        }else{
            $tel = '';
        }
        /*
        //  下级购买过的用户
        $user_list = Db::name('ml_tbl_event_order')->where('lead_id',$user_info['id'])->group('user_id')->select();

        $list = [];
        //  通过用户查信息和订单
        foreach ($user_list as $k=>$v){
            $team_info = Db::name('ml_tbl_event_member')->where('user_id',$v['user_id'])->whereLike('tel','%'.$tel.'%')->find();
//            $team_order = Db::name('ml_tbl_event_order')->where(['user_id'=>$v['user_id'],'lead_id'=>$user_info['id']])->select();
            if ($team_info){
                $list[] = [
                    'user_id'=>$team_info['user_id'],
                    'user_name'=>$team_info['user_name'],
                    'tel'=>$team_info['tel'],
                ];

                $list[$k]['order'] = Db::name('ml_tbl_event_order o')->join('ml_tbl_event_goods g','o.goods_id=g.id')->where(['o.user_id'=>$v['user_id'],'lead_id'=>$user_info['id']])->whereIn('order_type','2,3,4')->field('o.id,o.goods_id,o.goods_num,g.head_img')->select();
                $num = 1;
                $total = 0;
                foreach ($list[$k]['order'] as $ok=>$ov){
                    $list[$k]['order'][$ok]['num'] = $num;
                    $total += $ov['goods_num'];
                    $num++;
                }
                $list[$k]['total'] = $total;
            }
        }
        */

        $user_list = Db::name('ml_tbl_event_order')->where('lead_id',$user_info['id'])->group('user_id')->select();

        $list = [];
        //  通过用户查信息和订单
        foreach ($user_list as $k=>$v){
            $team_info = Db::name('ml_tbl_event_member')->where('user_id',$v['user_id'])->whereLike('tel','%'.$tel.'%')->find();
//            $team_order = Db::name('ml_tbl_event_order')->where(['user_id'=>$v['user_id'],'lead_id'=>$user_info['id']])->select();
            if ($team_info){
                $list[] = [
                    'head_img'=>$team_info['head_img'],
                    'user_id'=>$team_info['user_id'],
                    'user_name'=>$team_info['user_name'],
                    'tel'=>$team_info['tel'],
                    'order'=>Db::name('ml_tbl_event_order o')->join('ml_tbl_event_goods g','o.goods_id=g.id')->where(['o.user_id'=>$v['user_id'],'lead_id'=>$user_info['id']])->whereIn('order_type','2,3,4')->field('o.id,o.goods_id,o.goods_num,g.head_img,o.order_type,g.goods_name')->select(),
                ];
                $num = 1;
                $total = 0;
                foreach ($list as $ok=>$ov){
                    foreach ($list[$ok]['order'] as $odk=>$odv){
                        $list[$ok]['order'][$odk]['num'] = $num;
                        $total += $odv['goods_num'];
                        $num++;
                    }
                    $list[$ok]['total'] = $total;
                }
            }
        }

//        $order_list = Db::table('ml_tbl_event_order o')
//            ->join(' ml_tbl_event_member m','o.user_id = m.user_id')
//            ->where('o.lead_id',$user_info['id'])
//            ->whereLike('m.tel','%'.$tel.'%')
//            ->field('m.user_name,m.tel,o.user_id,m.head_img')
//            ->group('o.user_id')
//            ->select();
//        $total = 0;
//        foreach ($order_list as $k=>$v){
//            $order_list[$k]['order'] = Db::name('ml_tbl_event_order o')->join('ml_tbl_event_goods g','o.goods_id=g.id')->where(['o.user_id'=>$v['user_id'],'lead_id'=>$user_info['id']])->whereIn('order_type','2,3,4')->field('o.id,o.goods_id,o.goods_num,g.head_img,o.order_type,g.goods_name')->select();
//            $num = 1;
//            foreach ($order_list[$k]['order'] as $ok=>$ov){
//                $order_list[$k]['order'][$ok]['num'] = $num;
//                $total += $ov['goods_num'];
//                $num++;
//            }
//            $order_list[$k]['total'] = $total;
//        }
        $notime = time();
        $ex_time = strtotime('2019-06-29 22:00:00');
        if ( $notime > $ex_time ){
            $type = 2;
        }else{
            $type = 1;
        }
        return responseSuccess(['orderlist'=>$list,'leadid'=>$user_info['id'],'type'=>$type]);
//        return responseSuccess(['orderlist'=>$order_list,'leadid'=>$user_info['id'],'type'=>$type]);

    }


    public function editOrderStatus()
    {
        $all = $this->request->param();

        if (!isset($all['id']) && !isset($all['user_id'])){
            return responseError();
        }

        if (isset($all['id']) && !empty($all['id'])){
            $order_status = Db::name('ml_tbl_event_order')->where('id',$all['id'])->update(['order_type'=>4]);
            if (!$order_status){
                return responseError();
            }
        }
        if (isset($all['user_id']) && !empty($all['user_id'])){
            if (isset($all['leadid']) && !empty($all['leadid'])){
                $order_status = Db::name('ml_tbl_event_order')->where(['user_id'=>$all['user_id'],'lead_id'=>$all['leadid']])->whereIn('order_type','2,3')->update(['order_type'=>4]);
                if (!$order_status){
                    return responseError();
                }
            }

        }
        return responseSuccess();
    }


    public function editJoinActivityType()
    {
        $all = $this->request->param();
        if (!isset($all['user_id']) || empty($all['user_id'])){
            return responseError();
        }
        $res = Db::name('ml_tbl_user')->where('id',$all['user_id'])->update(['activity_mark'=>0]);
        if ($res){
            return responseSuccess();
        }
    }

    public function findUserInfo()
    {
        $all = $this->request->param();

        if (isset($all['user_id']) && !empty($all['user_id'])){

            $user_info = Db::name('ml_tbl_event_member')->where('user_id',$all['user_id'])->find();

            if ($user_info){
                return responseSuccess($user_info);
            }else{
                return responseSuccess();
            }
        }else{
            return responseError();
        }

    }


    public function eventShareRcode()
    {
        $goods_id = $_REQUEST['goods_id'];
        $lead_id = $_REQUEST['lead_id'];
        $user_id = $_REQUEST['user_id'];
        //判断数据库是否存在相同二维码
        $rcodedata = Db::table('ml_tbl_event_rcode')->where(['goods_id'=> $goods_id])->where('lead_id', $lead_id)->find();
        if ($rcodedata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('rcodeurl' => $rcodedata['url']));
        } else {
            $fielname = rand(100, 99999) . $goods_id . $lead_id .$user_id .'.png';
            // 为二维码创建一个文件
            $fiel = $_SERVER['DOCUMENT_ROOT'] . '/ttgoodssharercode/' . $fielname;
            //获取access_token
            $appid = 'wx0fda8074ccdb716d';
            $srcret = 'bf55d7a720d5bc162621e3901b7645be';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $srcret;
            // get请求获取access_token
            $data = $this->getCurl($url);
            $data = json_decode($data, true);
            //获取二维码
            //参数
            $postdata['scene'] = "goodsid=" . $goods_id . ",leadid=" . $lead_id . ",userid=".$user_id;
            // 宽度
            $postdata['width'] = 430;
            // 页面
            $postdata['page'] = 'packageD/groupbuy/groupbuy';
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
            $intodata = array('goods_id' => $goods_id, 'lead_id' => $lead_id, 'user_id'=>$user_id,'url' => $fileurl);
            Db::table('ml_tbl_event_rcode')->insert($intodata);
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('rcodeurl' => $fileurl));
        }
        return json($data);
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
}