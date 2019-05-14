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
                $xm_id = Db::name('ml_xm_binding')->where('ml_user_id',$all['user_id'])->value('xm_user_id');
                $listInfo = Db::name($this->table)->where('user_id',$xm_id)->select();
                return json(['status'=>1001,'msg'=>'成功','data'=>$listInfo]);
            }else{
                return json(['status'=>2002,'msg'=>'参数错误','data'=>'']);
            }
        }else{
            return json(['status'=>2001,'msg'=>'请求方法错误','data'=>'']);
        }


    }


}