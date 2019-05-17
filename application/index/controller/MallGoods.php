<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/24
 * Time: 12:35
 */

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

class MallGoods extends Controller
{

    public function creatGoods()
    {
        $head_img = $_REQUEST['head_img'];//头像
        $goods_name = $_REQUEST['goods_name'];//商品名称
        $goods_summary = $_REQUEST['goods_summary'];//商品简介
        $goods_format = $_REQUEST['goods_format'];//商品规格
        $goods_region = $_REQUEST['goods_region'];//商品地区
        $goods_stock = $_REQUEST['goods_stock'];//商品库存
        $goods_details = $_REQUEST['goods_details'];//商品详情
        $creat_time = date("Y-m-d h:i:s", time());//创建时间
        $ex_time = $_REQUEST['ex_time'];//到期时间
        $bonus_price = 0;//分销价格（默认为0不参与分销）
        $is_online = 0;//是否上架默认为0不上架 1上架
        $goods_class = $_REQUEST['goods_class'];//商品分类
        $goods_price = $_REQUEST['goods_price'];//商品售格
        $goods_original_price = $_REQUEST['goods_original_price'];//商品原价
        $type = $_REQUEST['type'];//核销类型1、配送 2、扫码核销
        $business_id = $_REQUEST['business_id'];//商户id


    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/5/14
     * @autor: duheyuan
     * 商品上传
     */
    public function goodsAdd(Request $request)
    {
        $all = $request->param();
        if (!isset($all['goods_name']) || empty($all['goods_name'])){
            return json(['status'=>0,'msg'=>'商品名不可为空','data'=>'']);
        }
        if (!isset($all['goods_stock']) || empty($all['goods_stock'])){
            return json(['status'=>0,'msg'=>'商品库存不可为空','data'=>'']);
        }
        if (!isset($all['goods_class']) || empty($all['goods_class'])){
            return json(['status'=>0,'msg'=>'商品分类不可为空','data'=>'']);
        }
        if (!isset($all['goods_original_price']) || empty($all['goods_original_price'])){
            $all['goods_original_price'] = $all['goods_price'];
        }
        if (isset($all['goods_price']) && empty($all['goods_price'])){
            return json(['status'=>0,'msg'=>'商品价格不可为空','data'=>'']);
        }
        if (!isset($all['bonus_price']) || empty($all['bonus_price'])){
            return json(['status'=>0,'msg'=>'分销价格不可为空','data'=>'']);
        }
        if (!isset($all['head_img']) || empty($all['head_img'])){
            return json(['status'=>0,'msg'=>'商品头像不可为空','data'=>'']);
        }
        if (!isset($all['goods_format']) || empty($all['goods_format'])){
            return json(['status'=>0,'msg'=>'商品规格不可为空','data'=>'']);
        }
        if (!isset($all['type']) || empty($all['type'])){
            return json(['status'=>0,'msg'=>'商品核销类型不可为空','data'=>'']);
        }
        if (!isset($all['ex_time']) || empty($all['ex_time'])){
            return json(['status'=>0,'msg'=>'商品到期时间不可为空','data'=>'']);
        }

        if (isset($all['banner']) && !empty($all['banner'])) {
            $banner = $all['banner'];
            unset($all['banner']);
        }
        if (!isset($all['goods_details']) || empty($all['goods_details'])){
            return json(['status'=>0,'msg'=>'商品详情不可为空','data'=>'']);
        }
        $all['creat_time'] = date('Y-m-d H:m:s' ,time());
        $last_id = Db::name('ml_tbl_goods')->insertGetId($all);
        if (isset($banner) && !empty($banner)){
            $arr = [];
            foreach ( $banner as $k=>$v){
                $arr[] = [
                    'img_url'=>$v,
                    'goods_id'=>$last_id,
                ];
            }
        $res = Db::name('ml_tbl_goods_banner')->insertAll($arr);
        }
        if (isset($res)){
            if ($last_id && $res){
                return json(['status'=>0,'msg'=>'成功','data'=>'']);
            }else{
                return json(['status'=>1,'msg'=>'商品上传失败','data'=>'']);
            }
        }else{
            return json(['status'=>1,'msg'=>'商品banner上传失败','data'=>'']);
        }
    }





    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/5/8
     * @autor: duheyuan
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 分享文案
     */
    public function shareGoods(Request $request)
    {
        if ($request->isPost()){
            $all = $request->param();
            if (isset($all['goods_id']) && !empty($all['goods_id'])){
                $data['goods_content'] = Db::name('ml_tbl_goods_content')->where('gid',$all['goods_id'])->value('content');
                $goods_img = Db::name('ml_tbl_goods_img')->where('gid',$all['goods_id'])->limit(9)->order('id','desc')->select();
                $data['goods_img'] = [];
                foreach ( $goods_img as $k=>$v){
                    if ($v['url']){
                        $v['url'] = ltrim($v['url'],' ');
                    }
                    $data['goods_img'][] = $v;
                }
                return json(['status'=>1001,'msg'=>'成功','data'=>$data]);
            }else{
                return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
            }
        }else{
            return json(['status'=>2002,'msg'=>'请求方法错误','data'=>'']);
        }
    }

    public function shareImgUpload(Request $request)
    {
        $type = 1;
        $url = $request->param('img');

        $ext=strrchr($url,'.');
        if($ext!='.gif'&&$ext!='.jpg'&& $ext != '.png' && $ext != '.jpeg' ){
            return array('file_name'=>'','save_path'=>'','error'=>3);
        }
        $filename= rand(1000,9999).time().$ext;

//        if(0!==strrpos($save_dir,'/')){
//            $save_dir.='/';
//        }
        $save_dir = $_SERVER['DOCUMENT_ROOT'] . '/ttimg/';
        //创建保存目录
        if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
            return array('file_name'=>'','save_path'=>'','error'=>5);
        }
        //获取远程文件所采用的方法
        if($type){
            $ch=curl_init();
            $timeout=5;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            $img=curl_exec($ch);
            curl_close($ch);
        }else{
            ob_start();
            readfile($url);
            $img=ob_get_contents();
            ob_end_clean();
        }
        //文件大小
        $fp2=@fopen($save_dir.$filename,'a');
        fwrite($fp2,$img);
        fclose($fp2);
        unset($img,$url);
        return json(array('file_name'=>$filename,'save_path'=>'https://tuitui.tango007.com/ttimg/'.$filename,'error'=>0));
    }


    public function getRichText(Request $request)
    {
        $all = $request->param();
        return $all;
    }

}