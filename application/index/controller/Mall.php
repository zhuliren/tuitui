<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/17
 * Time: 14:29
 */

namespace app\index\controller;


use think\Db;
use think\Request;

class Mall
{
    public function mallIndex()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $goods_class = $_REQUEST['class'];
        $start = $page * $limit;
        if ($goods_class == 1) {
            //商品列表
            $selectgoods = Db::table('ml_tbl_goods')->where('is_online', 1)->limit($start, $limit)->order('goods_sort','desc')
                ->field('id,head_img,goods_name,goods_format,goods_region,goods_stock,bonus_price,goods_price,goods_original_price,goods_sell_out,goods_sort')->select();
            if ($selectgoods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $selectgoods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        } else {
            $selectgoods = Db::table('ml_tbl_goods')->where('is_online', 1)->where('goods_class', $goods_class)->limit($start, $limit)->order('goods_sort','desc')
                ->field('id,head_img,goods_name,goods_format,goods_region,goods_stock,bonus_price,goods_price,goods_original_price,goods_sell_out')->select();
            if ($selectgoods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $selectgoods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        }
    }

    public function mallBanner()
    {
        //轮播图
        $selectbanner = Db::table('ml_tbl_banner')->select();
        $banner_num = count($selectbanner);
        $returndata = array('banner_num' => $banner_num, 'banner_img' => $selectbanner);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function goodsDetails()
    {
        $goods_id = $_REQUEST['goodsid'];
        $selectgoods = Db::table('ml_tbl_goods')->where('id', $goods_id)->find();
        $selectgoodsbanner = Db::table('ml_tbl_goods_banner')->where('goods_id', $goods_id)->column('img_url');
        if ($selectgoods) {
            if ($selectgoods['is_online'] == 0) {
                $data = array('status' => 1, 'msg' => '商品已下架', 'data' => '');
                return json($data);
            }
            $returndata = array('goods_details' => $selectgoods, 'goods_banner' => $selectgoodsbanner);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '商品id错误', 'data' => '');
            return json($data);
        }
    }

    public function getGoodsClass()
    {
        $selectgoodsclass = Db::table('ml_tbl_goods_class')->select();
        if ($selectgoodsclass) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $selectgoodsclass);
            return json($data);
        } else {
            $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
            return json($data);
        }
    }

    public function getMustbuyGoods(Request $request)
    {
        if ($request->isPost()){

            $res = Db::name('ml_tbl_goods')->where('is_online',1)->where('goods_sort',99)->limit(5)->field('id,goods_name,goods_stock,ex_time,bonus_price,goods_price,goods_original_price,must_img')->select();

            if ($res > 0){
                return json(['status'=>1001,'msg'=>'成功','data'=>$res]);
            }else{
                return json(['status'=>2001,'msg'=>'无数据','data'=>'']);
            }
        }else{
            return json(['status'=>2002,'msg'=>'方法错误','data'=>'']);
        }

    }
}