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
    protected $request;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->request = $request;
    }

    public function allgoodsList()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $goods_class = $_REQUEST['class'];
        $start = $page * $limit;

        if ($goods_class == 1) {
            //商品列表
            $goods['total'] = Db::name('ml_tbl_goods')->where('is_online',1)->count();
            $goods['goods_list'] = Db::table('ml_tbl_goods')->where('is_online', 1)->limit($start, $limit)->order('id','desc')
                ->field('id,head_img,goods_name,goods_stock,bonus_price,goods_price,goods_original_price,goods_sell_out,goods_class,type,ex_time,is_online')->select();
            if ($goods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        } else {
            $goods['total'] = Db::name('ml_tbl_goods')->where('is_online',1)->where('goods_class', $goods_class)->count();
            $goods['goods_list'] = Db::table('ml_tbl_goods')->where('is_online', 1)->where('goods_class', $goods_class)->limit($start, $limit)->order('id','desc')
                ->field('id,head_img,goods_name,goods_stock,bonus_price,goods_price,goods_original_price,goods_sell_out,goods_class,type,ex_time,is_online')->select();
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
            $goods['goods_list'] = Db::table('ml_tbl_goods')->where('is_online', 0)->where('goods_class', $goods_class)->limit($start, $limit)->order('id','desc')
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

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/5/29
     * @autor: duheyuan
     */
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


    public function goodsAdd()
    {
        $all = $this->request->param();
        if (!isset($all['format'])){
            return json(['status'=>2001,'msg'=>'缺少商品规格','data'=>'']);
        }
        if (!isset($all['banner'])){
            return json(['status'=>2001,'msg'=>'缺少商品banner','data'=>'']);
        }
        $goods_format = $all['format'];
        $banner = $all['banner'];
        unset($all['banner']);
        unset($all['format']);
        Db::startTrans();
        $goods_id = Db::name('ml_tbl_goods_two')->insertGetId($all);
        if (!$goods_id){
            Db::rollback();
            return json(['status'=>5001,'msg'=>'新增商品失败','data'=>'']);
        }
        if ($goods_format){
            foreach ($goods_format as $k=>$v){
                $goods_format[$k] = object_array(json_decode($v));
            }
            foreach ($goods_format as $fk=>$fv){
                unset($goods_format[$fk]['isSet']);
                $goods_format[$fk]['goods_id'] = $goods_id;
            }
            $format_status = Db::name('ml_tbl_goods_format')->insertAll($goods_format);
            if (!$format_status){
                Db::rollback();
                return json(['status'=>5001,'msg'=>'商品规格添加失败','data'=>'']);
            }
        }
        if ($banner){
            $arr = [];
            foreach ($banner as $key=>$val){
                $arr[] = [
                    'goods_id' => $goods_id,
                    'img_url' => $val,
                ];
            }
            $res = Db::name('ml_tbl_goods_banner')->insertAll($arr);
            if (!$res){
                Db::rollback();
                return json(['status'=>5001,'msg'=>'商品轮播图添加失败','data'=>'']);
            }
        }
        Db::commit();
        return json(['status'=>1001,'msg'=>'商品添加成功','data'=>$goods_id]);
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/5/30
     * @autor: duheyuan
     * 商品上下架
     */
    public function goodsOnline(Request $request)
    {

        $all = $request->param();

        if (is_array($all['id'])){

            $list = Db::name('ml_tbl_goods')->whereIn(['id'=>$all['id']])->update(['is_online'=>$all['online']]);
        }else{
            $list = Db::name('ml_tbl_goods')->where(['id'=>$all['id']])->update(['is_online'=>$all['online']]);
        }

        return json(['status'=>1001,'msg'=>'成功','data'=>$list]);
    }

    /**
     * @param Request $request
     * @time: 2019/5/30
     * @autor: duheyuan
     * 增加商品标签
     */
    public function inserttag(Request $request)
    {
        $all = $request->param();
        if (empty($all['goods_id'])){
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }
        $arr = [];
        if (!empty($all['tag'])){
            if (is_array($all['tag'])){
                foreach ($all['tag'] as $v){
                    $arr[] = [
                        'goods_id'=>$all['goods_id'],
                        'tag'=>$v
                    ];
                }
            }else{
                return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
            }
        }else{
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }
        $list = Db::name('ml_tbl_goods_tag')->insertAll($arr);

        return json(['status'=>1001,'msg'=>'成功','data'=>$list]);
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/6/3
     * @autor: duheyuan
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * 商品置顶必买
     */
    public function goodsTop(Request $request)
    {
        $gid = $request->param('id');
        if (empty($gid)){
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }

        $res = Db::name('ml_tbl_goods')->where(['id'=>$gid,'is_online'=>1])->update(['goods_sort'=>99]);
        if ($res){
            return json(['status'=>1001,'msg'=>'成功','data'=>'']);
        }else{
            return json(['status'=>5001,'msg'=>'商品不存在或已下架','data'=>'']);
        }

    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/6/3
     * @autor: duheyuan
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * 商品取消必买
     */
    public function unGoodsTop(Request $request)
    {
        $gid = $request->param('id');
        if (empty($gid)){
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }

        $res = Db::name('ml_tbl_goods')->where(['id'=>$gid,'is_online'=>1])->update(['goods_sort'=>50]);
        if ($res){
            return json(['status'=>1001,'msg'=>'成功','data'=>'']);
        }else{
            return json(['status'=>5001,'msg'=>'商品不存在或已下架','data'=>'']);
        }

    }


}