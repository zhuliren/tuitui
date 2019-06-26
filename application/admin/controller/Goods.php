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

    /**
     * @return \think\response\Json
     * @time: 2019/6/11
     * @autor: duheyuan
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 所有上线商品
     */
    public function allgoodsList()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $goods_class = $_REQUEST['class'];
        $start = $page * $limit;
        $search = $this->request->param('search');

        $sql = "SELECT * FROM `ml_tbl_goods_two` WHERE is_online=1  ";
        if ($goods_class == 1) {
            //商品列表
            $goods['total'] = count(Db::query($sql));
            $sql .= " AND goods_name like '%{$search}%' ORDER BY `id` DESC  LIMIT $start,$limit ";
            $goods['goods_list'] = Db::query($sql);

            if ($goods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        } else {
            $sql .= " AND goods_class={$goods_class} ";
            $goods['total'] = count(Db::query($sql));
            $sql .= " AND goods_name like '%{$search}%' ORDER BY `id` DESC  LIMIT $start,$limit ";
            $goods['goods_list'] = Db::query($sql);

            if ($goods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        }
    }

    /**
     * @return \think\response\Json
     * @time: 2019/6/11
     * @autor: duheyuan
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 所有下线商品
     */
    public function getUnlineGoods()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $goods_class = $_REQUEST['class'];
        $start = $page * $limit;
        $search = $this->request->param('search');
        $sql = "SELECT * FROM `ml_tbl_goods_two` WHERE is_online=1  ";
        if ($goods_class == 1) {
            //商品列表
            $goods['total'] = count(Db::query($sql));
            $sql .= " AND goods_name like '%{$search}%' ORDER BY `id` DESC  LIMIT $start,$limit ";
            $goods['goods_list'] = Db::query($sql);
            if ($goods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        } else {
            $sql .= " AND goods_class={$goods_class} ";
            $goods['total'] = count(Db::query($sql));
            $sql .= " AND goods_name like '%{$search}%' ORDER BY `id` DESC  LIMIT $start,$limit ";
            $goods['goods_list'] = Db::query($sql);
            if ($goods) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goods);
                return json($data);
            } else {
                $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
                return json($data);
            }
        }
    }


    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/6/11
     * @autor: duheyuan
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 商品详情-后台
     */
    public function getGoodsDetail(Request $request)
    {
        $id = $request->param('goodsid',0,'int');

        if (isset($id) && !empty($id)){
            $goods['info'] = Db::name('ml_tbl_goods_two')->where('id',$id)->find();
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
            $goods['content'] = Db::name('ml_tbl_goods_content')->where('gid',$id)->column('content');
            $goods['img'] = Db::name('ml_tbl_goods_img')->where('gid',$id)->column('url');
            $goods['tag'] = Db::name('ml_tbl_goods_tag')->where('goods_id',$id)->column('tag');
            return json(['status'=>1001,'msg'=>'成功','data'=>$goods]);
        }else{
            return json(['status'=>2001,'msg'=>'缺少必要参数','data'=>'']);
        }
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/6/11
     * @autor: duheyuan
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * 修改商品基础信息
     */
    public function editGoods(Request $request)
    {
        $all = $request->param();

        if (isset($all['id']) && !empty($all['id'])){
            $res = Db::name('ml_tbl_goods_two')->where('id',$all['id'])->update($all);

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

            $list = Db::name('ml_tbl_goods_two')->whereIn(['id'=>$all['id']])->update(['is_online'=>$all['online']]);
        }else{
            $list = Db::name('ml_tbl_goods_two')->where(['id'=>$all['id']])->update(['is_online'=>$all['online']]);
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

        $res = Db::name('ml_tbl_goods_two')->where(['id'=>$gid,'is_online'=>1])->update(['goods_sort'=>99]);
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

    public function goodsFront(Request $request)
    {
        $gid = $request->param('id');
        if (empty($gid)){
            return json(['status'=>2001,'msg'=>'参数错误','data'=>'']);
        }
        $res = Db::name('ml_tbl_goods')->where(['id'=>$gid,'is_online'=>1])->update(['goods_sort'=>70]);
        if ($res){
            return json(['status'=>1001,'msg'=>'成功','data'=>'']);
        }else{
            return json(['status'=>5001,'msg'=>'商品不存在或已下架','data'=>'']);
        }

    }

    /**
     * @return \think\response\Json
     * @time: 2019/6/12
     * @autor: duheyuan
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * 修改
     */
    public function editGoodsFormat()
    {
        $all = $this->request->param();
        $format = $all['format'];

        foreach ($format as $k=>$v){
            $v = json_decode($v, true);
            if (isset($v['isSet'])){
                unset($v['isSet']);
            }
            if (isset($v['id']) && !empty($v['id'])){
                $res = Db::name('ml_tbl_goods_format')->where('id',$v['id'])->update($v);
            }else{
                $res_ =  Db::name('ml_tbl_goods_format')->insert($v);
            }
        }
        if (isset($res) || isset($res_)){
            return json(['status'=>'1001','msg'=>'成功','data'=>'']);
        }else{
            return json(['status'=>3001,'msg'=>'成功','data'=>'']);
        }
    }


    public function goodsClerk()
    {
        $sql = "SELECT * FROM `ml_tbl_business_clerk` AS c JOIN `ml_tbl_business` AS b  ";

    }

}