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
            $selectgoods = Db::table('ml_tbl_goods_two')->where('is_online', 1)->limit($start, $limit)->order('goods_sort','desc')
                ->field('id,head_img,goods_name,goods_format,goods_region,price_interval,bonus_interval,goods_original_price,goods_sell_out')->select();
            if ($selectgoods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $selectgoods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        } else {
            $selectgoods = Db::table('ml_tbl_goods_two')->where('is_online', 1)->where('goods_class', $goods_class)->limit($start, $limit)->order('goods_sort','desc')
                ->field('id,head_img,goods_name,goods_format,goods_region,price_interval,bonus_interval,,goods_original_price,goods_sell_out')->select();
            if ($selectgoods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $selectgoods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        }
    }

    public function mallIndexTwo()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $goods_class = $_REQUEST['class'];
        $start = $page * $limit;
        if ($goods_class == 1) {
            //商品列表
            $selectgoods = Db::table('ml_tbl_goods_two')->where('is_online', 1)->limit($start, $limit)->order('goods_sort','desc')
                ->field('id,head_img,goods_name,goods_format,goods_region,price_interval,bonus_interval,goods_original_price,goods_sell_out')->select();
            if ($selectgoods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $selectgoods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        } else {
            $selectgoods = Db::table('ml_tbl_goods_two')->where('is_online', 1)->where('goods_class', $goods_class)->limit($start, $limit)->order('goods_sort','desc')
                ->field('id,head_img,goods_name,goods_format,goods_region,price_interval,bonus_interval,,goods_original_price,goods_sell_out')->select();
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
        if ($selectgoods['business_id']){
            $businessarr = Db::name('ml_tbl_business')->where(['id'=>$selectgoods['business_id']])->whereOr(['pid'=>$selectgoods['business_id']])->field('id,business_name,business_hours,phone,business_address,latitude,longitude')->select();
        }else{
            $businessarr = [];
        }
        $tag_info = Db::name('ml_tbl_goods_tag')->where('goods_id',$goods_id)->column('tag');
        if ($selectgoods) {
            if ($selectgoods['is_online'] == 0) {
                $data = array('status' => 1, 'msg' => '商品已下架', 'data' => '');
                return json($data);
            }
            $returndata = array('goods_details' => $selectgoods, 'goods_banner' => $selectgoodsbanner, 'business'=>$businessarr,'tag'=>$tag_info);
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
            $res = Db::name('ml_tbl_goods_two')->where('is_online',1)
                ->where('goods_sort',99)
//                ->field('id,goods_name,goods_stock,ex_time,bonus_price,goods_price,goods_original_price,goods_sell_out,head_img,goods_summary')
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

    public function getMustbuyGoodsTwo(Request $request)
    {
        if ($request->isPost()){
            $res = Db::name('ml_tbl_goods_two')->where('is_online',1)
                ->where('goods_sort',99)
//                ->field('id,goods_name,goods_stock,ex_time,bonus_price,goods_price,goods_original_price,goods_sell_out,head_img,goods_summary')
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
                    'goodslist'=> Db::name('ml_tbl_goods')->where('goods_class',$v['id'])->where('is_online',1)->order('goods_sort','desc')->field('id as gid,goods_name,head_img,goods_stock,goods_price,goods_original_price,bonus_price,goods_sell_out,ex_time,business_id')->limit(4)->select(),
                    ];
                }
            }
            return json(['status'=>1001,'msg'=>'成功','data'=>$returnarr]);
        }else{
            return json(['status'=>2002,'msg'=>'方法错误','data'=>'']);
        }
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/5/30
     * @autor: duheyuan
     * 新首页分类商品接口
     */
    public function getIndexGoodsList(Request $request)
    {
        if ($request->isPost()){
            $goodsClass = $this->getClassList2();
            $arr = [];

            foreach ($goodsClass as $k=>$v){
                if ($v['id'] != 1){
                    $arr[] = [
                        'cid'=>$v['id'],
                        'name'=>$v['class_name'],
                        'as_name'=>$v['as_name'],
                        'goodslist'=> Db::name('ml_tbl_goods_two')
                                        ->where(['goods_class'=>$v['id'],'is_online'=>1])
                                        ->order('goods_sort','desc')
                                        ->field('id as gid,head_img,goods_name,bonus_interval,price_interval,ex_time,goods_original_price')
                                        ->limit(4)
                                        ->select(),
                    ];
                }
            }
            return  json(['status'=>1001,'msg'=>'成功','data'=>$arr]);
        }else{
            return json(['status'=>5001,'msg'=>'方法错误','data'=>'']);
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

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/5/31
     * @autor: duheyuan
     * @throws ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\exception\DbException
     * 获取分类下的商品
     */
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

    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\exception\DbException
     * @time: 2019/5/30
     * @autor: duheyuan
     * 新商品详情
     */
    public function getGoodsInfo(Request $request)
    {
        if ($request->isPost()){
            $id = $request->param('goodsid');
            if (isset($id) && !empty($id)){
                $goods['info'] = Db::name('ml_tbl_goods_two')->where('id',$id)->where('is_online',1)->find();
                if (empty($goods['info'])){
                    return json(['status'=>5001,'msg'=>'商品不存在或商品已下架','data'=>'']);
                }
                if ($goods['info']['business_id']){
                    $goods['business'] = Db::name('ml_tbl_business')->where(['id'=>$goods['info']['business_id']])->whereOr(['pid'=>$goods['info']['business_id']])->field('id,business_name,business_hours,phone,business_address,latitude,longitude')->select();
                }else{
                    $goods['business'] = [];
                }
                $goods['format'] = Db::name('ml_tbl_goods_format'  )->where('goods_id',$id)->select();
                $goods['banner'] = Db::name('ml_tbl_goods_banner')->where('goods_id',$id)->column('img_url');
                $goods['tag'] = Db::name('ml_tbl_goods_tag')->where('goods_id',$id)->column('tag');
                return json(['status'=>1001,'msg'=>'成功','data'=>$goods]);
            }else{
                return json(['status'=>2001,'msg'=>'缺少必要参数','data'=>'']);
            }
        }else{
            return json(['status'=>5001,'msg'=>'请求方法错误','data'=>'']);
        }
    }



}