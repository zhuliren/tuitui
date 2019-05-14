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


    public function getSingleGoods(Request $request)
    {

        $res = Db::name('ml_tbl_user')->where('id',864)->select();

        dump($res);die;

        $id = $request->param('id');
//        $page = 1;
        $time = date('Y-m-d H:i:s',time());
        $timestring = str_replace(' ', '%20', $time);
        $arr = [
            'client_id'=>PublicEnum::TICKET_ID,
            'timestamp'=>$time,
            'id'=>$id
        ];
        $sign = $this->getSign($arr);

        $url = "http://test.123wlx.cn/vapi/v1/distributor/products/$id?timestamp=$timestring&client_id=".PublicEnum::TICKET_ID."&&signature=$sign&id=$id";
        $info = $this->http_url_query($url);

        return $info;
    }

    public function getInsertInfo(Request $request)
    {


    }


    public function getMenPiaoGoodslist(Request $request)
    {
        $page = $request->param('page');
        $client_id = PublicEnum::TICKET_ID;
        $time = date('Y-m-d H:i:s',time());
        $timestr = str_replace(' ','%20',$time);
        $arr = [
            'client_id'=>$client_id,
            'timestamp'=>$time,
            'page'=>$page
        ];
        $getsign = $this->getsign($arr);

        $url = 'http://test.123wlx.cn/vapi/v1/distributor/products?client_id='.$client_id.'&timestamp='.$timestr.'&signature='.$getsign.'&page='.$page;
        $info = $this->http_url_query($url);

        return $info;
    }






}