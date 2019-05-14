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
        if (!isset($all['business_id']) || empty($all['business_id'])){
            return json(['status'=>0,'msg'=>'商户id不可为空','data'=>'']);
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
        if (!isset($all['third_id']) || empty($all['third_id'])){
            return json(['status'=>0,'msg'=>'商品第三方系统编号不可为空','data'=>'']);
        }
        if (!isset($all['third_number']) || empty($all['third_number'])){
            return json(['status'=>0,'msg'=>'商品第三方系统子编号不可为空','data'=>'']);
        }
        if (!isset($all['third_znumber']) || empty($all['third_znumber'])){
            return json(['status'=>0,'msg'=>'商品规格id不可为空','data'=>'']);
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
                    'goodsid'=>$last_id,
                ];
            }
        $res = Db::name('ml_tbl_goods_banner')->insert($arr);
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
                $data['goods_img'] = Db::name('ml_tbl_goods_img')->where('gid',$all['goods_id'])->limit(9)->order('id','desc')->select();
                return json(['status'=>1001,'msg'=>'成功','data'=>$data]);
            }else{
                return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
            }
        }else{
            return json(['status'=>2002,'msg'=>'请求方法错误','data'=>'']);
        }
    }


    public function shareImgUpload()
    {
        $max_size = 1000000;                                    //上传文件最大值
        $allow_type = array('gif','png','jpg','jpeg');
        $file = $_SERVER['DOCUMENT_ROOT'] . '/ttimg/';
        if(!is_dir($file)){
            mkdir($file);
        }
        //判断文件是否上传成功
        if($_FILES['myfile']['error']){
                 echo "文件上传失败<br>";
             switch($_FILES['myfile']['error']){
                   case 1: die('上传的文件超出系统的最大值<br>');break;
                   case 2: die('上传的文件超出表单允许的最大值<br>');break;
                   case 3: die('文件只有部分被上传<br>');break;
                   case 4: die('没有上传任何文件<br>');break;
                   default: die('未知错误<br>');break;
             }
        }

        $hz = array_pop(explode('.',$_FILES['myfile']['name']));
        if(!in_array($hz,$allow_type)){
            die("该类型不允许上传<br>");
        }

        //判断文件是否超过允许的大小
         if($max_size < $_FILES['myfile']['size']){
                 die("文件超出PHP允许的最大值<br>");
         }
        //为了防止文件名重复，在系统中使用新名称
        $save_file_name = date('YmdHis').rand(100,900).'.'.$hz;
        //判断是否为HTTP POST上传的，如果是则把文件从临时目录移动到保存目录，并输出保存的信息
        if(is_uploaded_file($_FILES['myfile']['tmp_name'])){
            if(move_uploaded_file($_FILES['myfile']['tmp_name'],$file.'/'.$save_file_name)){
                echo "上传成功!<br>文件{$_FILES['myfile']['name']}保存在{$file}/{$save_file_name}!<br>";
            }else{
                echo "文件移动失败!<br>";
            }
        }else{
            die("文件{$_FILES['myfile']['name']}不是一个HTTP POST上传的合法文件");
        }

    }

}