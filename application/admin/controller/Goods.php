<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/5/17
 * Time: 10:54
 */

namespace app\admin\controller;


use app\admin\Controller;
use think\Db;
use think\Request;
use traits\think\Instance;

class Goods extends Controller
{
    public function allgoodsList()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $goods_class = $_REQUEST['class'];
        $start = $page * $limit;

        if ($goods_class == 1) {
            //商品列表
            $goods['total'] = Db::name('ml_tbl_goods')->where('is_online',1)->count();
            $goods['goods_list'] = Db::table('ml_tbl_goods')->where('is_online', 1)->limit($start, $limit)->order('goods_sort','desc')
                ->field('id,head_img,goods_name,goods_stock,bonus_price,goods_price,goods_original_price,goods_sell_out,goods_class,type,ex_time')->select();
            if ($goods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        } else {
            $goods['total'] = Db::name('ml_tbl_goods')->where('is_online',1)->where('goods_class', $goods_class)->count();
            $goods['goods_list'] = Db::table('ml_tbl_goods')->where('is_online', 1)->where('goods_class', $goods_class)->limit($start, $limit)->order('goods_sort','desc')
                ->field('id,head_img,goods_name,goods_stock,bonus_price,goods_price,goods_original_price,goods_sell_out,goods_class,type,ex_time')->select();
            if ($goods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        }
    }

    public function getUnlineGoods(Request $request)
    {


        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $goods_class = $_REQUEST['class'];
        $start = ($page-1) * $limit;

        if ($goods_class == 1) {
            //商品列表
            $goods['total'] = Db::name('ml_tbl_goods')->where('is_online',0)->count();
            $goods['goods_list'] = Db::table('ml_tbl_goods')->where('is_online', 0)->limit($start, $limit)->field('id,head_img,goods_name,goods_stock,bonus_price,goods_price,goods_original_price,goods_sell_out,goods_class,type,ex_time')->select();
            if ($goods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        } else {
            $goods['total'] = Db::name('ml_tbl_goods')->where('is_online',0)->where('goods_class',$goods_class)->count();
            $goods['goods_list'] = Db::table('ml_tbl_goods')->where('is_online', 0)->where('goods_class', $goods_class)->limit($start, $limit)->order('goods_sort','desc')
                ->field('id,head_img,goods_name,goods_stock,bonus_price,goods_price,goods_original_price,goods_sell_out,goods_class,type,ex_time')->select();
            if ($goods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        }
    }


    public function getGoodsDetail(Request $request)
    {
        $id = $request->param('id',0,'int');

        if (isset($id) && !empty($id)){
            $goods_detaile = [];

            $goods_detaile['goods'] = Db::name('ml_tbl_goods')->where('id',$id)->find();
            $goods_detaile['banner'] = Db::name('ml_tbl_goods_banner')->where('goods_id',$id)->select();
            $goods_detaile['goodsshare']['content'] = Db::name('ml_tbl_goods_content')->where('gid',$id)->find();
            $goods_detaile['goodsshare']['share_img'] = Db::name('ml_tbl_goods_img')->where('gid',$id)->select();
            return json(['status'=>1001,'msg'=>'成功','data'=>$goods_detaile]);
        }else{
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }
    }

    public function editGoods(Request $request)
    {
        $all = $request->param();

        if (isset($all['id']) && !empty($all['id'])){
            $res = Db::name('ml_tbl_goods')->where('id',$all['id'])->update($all);

            return json(['status'=>1001,'msg'=>'成功','data'=>$res]);

        }else{
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }
    }


    public function editGoodsBanner(Request $request)
    {
        $all = $request->param();
        if (isset($all['id']) && !empty($all['id'])){
            Db::name('ml_tbl_goods_banner')->where('goods_id',$all['id'])->delete();
            $arr = [];
            foreach ($all['banner'] as $k=>$v){
                $arr[] = [
                  'goods_id'=>$all['id'],
                  'img_url'=>$v
                ];
            }
            $res = Db::name('ml_tbl_goods_banner')->insertAll($arr);
            return json(['status'=>1001,'msg'=>'成功','data'=>$res]);
        }else{
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }
    }

    public function editShareInfo(Request $request)
    {
        $all = $request->param();
        if ( isset($all['id']) && !empty($all['id'])){
            if (isset($all['content']) && !empty($all['content'])){
                $condition = Db::name('ml_tbl_goods_content')->where('gid',$all['id'])->find();
                if ($condition ){
                    $res = Db::name('ml_tbl_goods_content')->where('gid',$all['id'])->update(['content'=>$all['content']]);
                }else{
                    $res = Db::name('ml_tbl_goods_content')->where('gid',$all['id'])->insert(['gid'=>$all['id'],'content'=>$all['content']]);

                }

            }
            if (isset($all['banner']) && !empty($all['banner'])){
                Db::name('ml_tbl_goods_img')->where('gid',$all['id'])->delete();
                $arr = [];
                foreach ($all['banner'] as $k=>$v){
                    $arr [] = [
                      'gid'=>$all['id'],
                        'url'=>$v
                    ];
                }
                $result = Db::name('ml_tbl_goods_img')->insertAll($arr);
            }
            return json(['status'=>1001,'msg'=>'修改成功','data'=>'']);

        }else{
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }
    }


    public function getGoodsShareInfo(Request $request)
    {
        $all = $request->param();
        if (isset($all['id']) && !empty($all['id'])){
            $goods['content'] = Db::name('ml_tbl_goods_content')->where(['gid'=>$all['id']])->find();
            $goods['good_img'] = Db::name('ml_tbl_goods_img')->where(['gid'=>$all['id']])->select();
            return json(['status'=>1001,'msg'=>'成功','data'=>$goods]);

        }else{
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }

    }




}