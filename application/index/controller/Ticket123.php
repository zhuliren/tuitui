<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/5/13
 * Time: 15:12
 */

namespace app\index\controller;


use app\common\Model\PublicEnum;
use app\index\Controller;
use app\index\model\MenPiao;
use think\Db;
use think\Request;

class Ticket123 extends Controller
{
    protected $model;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new MenPiao();
    }


    public function getInsertInfo(Request $request)
    {


    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/6/27
     * @autor: duheyuan
     * 查看商品列表
     */
    public function getMenPiaoGoodslist(Request $request)
    {
        $page = $request->param('page');
        $client_id = PublicEnum::TICKET_TRUE_ID;
        $time = date('Y-m-d H:i:s',time());
        $timestr = str_replace(' ','%20',$time);
        $arr = [
            'client_id'=>$client_id,
            'timestamp'=>$time,
            'page'=>$page
        ];
        $getsign = $this->getTSign($arr);


//        $url = 'http://test.123wlx.cn/vapi/v2/distributor/products?client_id='.$client_id.'&timestamp='.$timestr.'&signature='.$getsign.'&page='.$page;
        $url = 'http://api.123menpiao.com/vapi/v2/distributor/products?timestamp=' . $timestr . '&client_id=' . $client_id . '&page=' . $page . '&signature=' . $getsign;

        $info = $this->http_url_query($url);
        $list = object_array(json_decode($info));

        if ($list['code'] == 200){
            $goods_list = $list['response'];
            return responseSuccess($goods_list);
        }else{
            return responseError([],$list['code'],$list['message']);
        }
    }

    /**
     * @return \think\response\Json
     * @time: 2019/6/27
     * @autor: duheyuan
     * 获取单个商品信息
     */
    public function getSingleGoods()
    {
        $all = $this->request->param();
        if (isset($all['number']) && !empty($all['number'])){

            $time = date('Y-m-d H:i:s',time());
            $timestring = str_replace(' ', '%20', $time);
            $arr = [
                'client_id'=>PublicEnum::TICKET_TRUE_ID,
                'timestamp'=>$time,
                'id'=>$all['number']
            ];
            $sign = $this->getTSign($arr);

//            $url = "http://test.123wlx.cn/vapi/v1/distributor/products/{$all['number']}?timestamp=$timestring&client_id=".PublicEnum::TICKET_TRUE_ID."&&signature=$sign&id={$all['number']}";
            $url = "http://api.123menpiao.com/vapi/v2/distributor/products/{$all['number']}?timestamp=$timestring&client_id=".PublicEnum::TICKET_TRUE_ID."&&signature=$sign&id={$all['number']}";
            $info = $this->http_url_query($url);

            $res = object_array(json_decode($info));
            if ($res['code'] == 200){
                $goods_info = $res['response']['body'];

                return responseSuccess($goods_info);
            }else{

                return responseError([],$res['code'],$res['message']);
            }
        }else{
            return responseError();
        }
    }

    /**
     * @return \think\response\Json
     * @time: 2019/6/27
     * @autor: duheyuan
     * 获取景点信息
     */
    public function getViewPointInfo()
    {
        $all = $this->request->param();
        if (isset($all['ids']) && !empty($all['ids'])){
            $time = date('Y-m-d H:i:s',time());
            $timestring = str_replace(' ', '%20', $time);
            $arr = [
                'client_id'=>PublicEnum::TICKET_TRUE_ID,
                'timestamp'=>$time,
                'ids'=>$all['ids']
            ];
            $sign = $this->getTSign($arr);

//            $url = "http://test.123wlx.cn/vapi/v1/distributor/products/{$all['number']}?timestamp=$timestring&client_id=".PublicEnum::TICKET_TRUE_ID."&&signature=$sign&id={$all['number']}";
            $url = "http://api.123menpiao.com//vapi/v2/distributor/scenics?timestamp=$timestring&client_id=".PublicEnum::TICKET_TRUE_ID."&&signature=$sign&ids={$all['ids']}";
            $info = $this->http_url_query($url);

            $res = object_array(json_decode($info));
            if ($res['code'] == 200){
                $viewPointInfo = $res['response']['body'];

                return responseSuccess($viewPointInfo);
            }else{
                return responseError([],$res['code'],$res['message']);
            }
        }else{
            return responseError();
        }
    }

    public function getGoodsInfo()
    {

//        $all = $this->request->param();


        $time = date('Y-m-d H:i:s',time());
        $timestamp = str_replace(' ','%20',$time);
        $order_id = 29;
        $arr = [
            'clerkstr'=>$order_id,
            'time'=>$time,
            'device_num'=>'adasdasda',
            'client_id'=>'YLMTIzNDU2',
            'client_secret'=>'59abbe56e',
        ];

        $sign = $this->Sign($arr,$arr['device_num']);

        $url = "http://tuitui.tango007.com/thstt/public/v1/clerkrcode?clerkstr={$order_id}&time={$timestamp}&device_num=adasdasda&sign={$sign}&client_id=YLMTIzNDU2";
        $res =  object_array(json_decode(curl_get($url)));
        dd($res);


        dd($res);




    }










}