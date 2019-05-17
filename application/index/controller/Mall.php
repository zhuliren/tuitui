<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/17
 * Time: 14:29
 */

namespace app\index\controller;


use think\Db;
use think\db\exception\ModelNotFoundException;
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

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/5/15
     * @autor: duheyuan
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     * @throws \think\exception\DbException
     * 必买好货接口
     */
    public function getMustbuyGoods(Request $request)
    {
        if ($request->isPost()){
            $res = Db::name('ml_tbl_goods')->where('is_online',1)
                ->where('goods_sort',99)->limit(5)
                ->field('id,goods_name,goods_stock,ex_time,bonus_price,goods_price,goods_original_price,goods_sell_out,head_img')
                ->select();
            if ($res > 0){
                return json(['status'=>1001,'msg'=>'成功','data'=>$res]);
            }else{
                return json(['status'=>2001,'msg'=>'无数据','data'=>'']);
            }
        }else{
            return json(['status'=>2002,'msg'=>'方法错误','data'=>'']);
        }
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/5/15
     * @autor: duheyuan
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     * @throws \think\exception\DbException
     *  商城首页商品列表
     */
    public function getGoodsClassList(Request $request)
    {
        if ($request->isPost()){
            $goodsClass = self::getClassList2();
            $returnarr = [];
            foreach ($goodsClass as $k=>$v){
                if ($v['id'] !== 1){
                    $returnarr[] = [
                    'cid'=>$v['id'],
                    'name'=>$v['class_name'],
                    'as_name'=>$v['as_name'],
                    'goodslist'=> Db::name('ml_tbl_goods')->where('goods_class',$v['id'])->where('is_online',1)->field('id as gid,goods_name,head_img,goods_stock,goods_price,goods_original_price,bonus_price,goods_sell_out,ex_time')->limit(4)->select(),
                    ];
                }
            }
            return json(['status'=>1001,'msg'=>'成功','data'=>$returnarr]);
        }else{
            return json(['status'=>2002,'msg'=>'方法错误','data'=>'']);
        }
    }

    /**
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     * @throws \think\exception\DbException
     * @time: 2019/5/15
     * @autor: duheyuan
     * 获取分类
     */
    public function getClassList()
    {

        $selectgoodsclass = Db::table('ml_tbl_goods_class')->select();
        return json(['status'=>1001,'msg'=>'获取分类成功','data'=>$selectgoodsclass]);

    }
    public function getClassList2()
    {
        $selectgoodsclass = Db::table('ml_tbl_goods_class')->select();
        return $selectgoodsclass;
    }

    public function classGoodsList(Request $request)
    {
        if ($request->isPost()){
            $id = $request->param('id');
            if (isset($id) && !empty($id)){
                $goodsList = Db::name('ml_tbl_goods')->where('goods_class',$id)->where('is_online',1)->field('id,head_img,goods_name,goods_summary,goods_stock,ex_time,bonus_price,goods_price,goods_original_price,goods_sell_out')->select();
            }else{
                $goodsList = Db::name('ml_tbl_goods')->where('is_online',1)->field('id,head_img,goods_name,goods_summary,goods_stock,ex_time,bonus_price,goods_price,goods_original_price,goods_sell_out')->select();
            }
            return json(['status'=>1001,'msg'=>'成功','data'=>$goodsList]);
        }

    }


}