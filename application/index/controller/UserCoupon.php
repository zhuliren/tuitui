<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/5/13
 * Time: 11:05
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Request;

class UserCoupon extends Controller
{
    protected $table;


    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->table = 'xm_tbl_coupon';
    }

    public function getMyCoupon(Request $request)
    {
        if ($request->isPost()){
            $all = $request->param();
            if (isset($all['user_id']) && !empty($all['user_id'])){
                if (isset($all['goods_id']) && !empty($all['goods_id'])){
                    $gid = $all['goods_id'];
                }else{
                    $gid = 0;
                }
                if (isset($all['sizeid']) && !empty($all['sizeid'])){
                    $sizeid = $all['sizeid'];
                }else{
                    $sizeid = 0;
                }
                $goods_info = Db::name('ml_tbl_goods_two')->where('id',$gid)->field('goods_class,business_id')->find();
                $listInfo = Db::name($this->table)->where('user_id',$all['user_id'])->where('use_status',1)->select();
                $business = Db::name('ml_tbl_business')->field('id')->select();
                $bid = [];
                foreach ($business as $key=>$val){
                    $bid[] = $val['id'];
                }
                $goods_class = $goods_info['goods_class'];
                $goods_business = $goods_info['business_id'];
                foreach ($listInfo as $k=>$v){
                    if ($v['use_type'] == 1) {

                        $listInfo[$k]['is_use'] = 1;
                        $listInfo[$k]['is_useInfo'] = '';
                    } else {
                        if (isset($goods_class) && !empty($goods_class)){
                            if ($v['use_type'] == $goods_class) {
                                if (isset($goods_business) && !empty($goods_business)){
                                    if ($v['business_id'] == 0){
                                        $listInfo[$k]['is_use'] = 1;
                                        $listInfo[$k]['is_useInfo'] = '';
                                    }else{
                                        if ($v['business_id'] == $goods_business) {

                                            $listInfo[$k]['is_use'] = 1;
                                            $listInfo[$k]['is_useInfo'] = '';
                                        }else{
                                            $listInfo[$k]['is_use'] = 0;
                                            $listInfo[$k]['is_useInfo'] = '该商品不适用与本店';
                                        }
                                    }
                                }else{
                                    $listInfo[$k]['is_use'] = 1;
                                    $listInfo[$k]['is_useInfo'] = '';
                                }
                            }else{
//                                $v['is_use'] = 0;
                                $listInfo[$k]['is_use'] = 0;
                                $listInfo[$k]['is_useInfo'] = '商品种类不符合优惠券使用规则';
                            }
                        }else{
                            $listInfo[$k]['is_use'] = 1;
                            $listInfo[$k]['is_useInfo'] = '';
                        }
                    }
                }
                return json(['status'=>1001,'msg'=>'成功','data'=>$listInfo]);
            }else{
                return json(['status'=>2002,'msg'=>'参数错误','data'=>'']);
            }
        }else{
            return json(['status'=>2001,'msg'=>'请求方法错误','data'=>'']);
        }


    }


}