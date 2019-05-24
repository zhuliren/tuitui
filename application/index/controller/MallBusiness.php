<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/23
 * Time: 9:53
 */

namespace app\index\controller;


use app\common\Model\PublicEnum;
use think\Db;
use think\Request;

class MallBusiness
{
    public function creatBusiness()
    {
        $business_name = $_REQUEST['business_name'];//商户名称
        $business_head = $_REQUEST['business_head'];//商户头像地址
        $business_introduce = $_REQUEST['business_introduce'];//商户经度
        $longitude = $_REQUEST['longitude'];//商户经度
        $latitude = $_REQUEST['latitude'];//商户纬度
        $business_address = $_REQUEST['business_address'];//商户地址文字描述
        $phone = $_REQUEST['phone'];//商户联系电话
        //插入数据库表 待审核
        $data = array(
            'business_name' => $business_name,
            'business_head' => $business_head,
            'business_introduce' => $business_introduce,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'business_address' => $business_address,
            'phone' => $phone,
            'type' => 0,);
        $business_id = Db::table('ml_tbl_business')->insertGetId($data);
        if ($business_id) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('business_id' => $business_id));
        } else {
            $data = array('status' => 1, 'msg' => '创建失败', 'data' => '');
        }
        return json($data);
    }

    public function getBusinessInfo()
    {
        $business_id = $_REQUEST['businessid'];
        //查询数据
        $selectbusinessinfo = Db::table('ml_tbl_business')->where('id', $business_id)->find();
        if ($selectbusinessinfo) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $selectbusinessinfo);
        } else {
            $data = array('status' => 1, 'msg' => '查询失败，请检查商户id', 'data' => '');
        }
        return json($data);
    }

    public function verifyBusiness()
    {
        $business_id = $_REQUEST['businessid'];
        $operation = $_REQUEST['operation'];//对商户的操作 1、上架 2、下架 3、注销
        //修改商户数据库表
        Db::table('ml_tbl_business')->where('id', $business_id)->update(['type' => $operation]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function modifyBusinessInfo()
    {
        $business_id = $_REQUEST['businessid'];
        $type = $_REQUEST['type'];//需要修改的商户字段名：business_head头像 business_introduce介绍 phone联系电话
        $value = $_REQUEST['value'];
        Db::table('ml_tbl_business')->where('id', $business_id)->update([$type => $value]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function getBusinessList()
    {
        $type = $_REQUEST['type'];//商户状态 0、待审核 1、上架中 2、下架 4、已注销
        //查询商户列表
        $selectdata = Db::table('ml_tbl_business')->where('type', $type)->select();
        if ($selectdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $selectdata);
        } else {
            $data = array('status' => 1, 'msg' => '当前无数据', 'data' => '');
        }
        return json($data);
    }

    public function applyForClerk()
    {
        $user_id = $_REQUEST['userid'];
        $business_id = $_REQUEST['businessid'];
        $name = $_REQUEST['name'];
        $cardid = $_REQUEST['cardid'];
        //查询用户是否申请过店员
        $isapplyforclerk = Db::table('ml_tbl_business_clerk')->where('user_id', $user_id)->find();
        if ($isapplyforclerk) {
            $data = array('status' => 1, 'msg' => '已申请过店员', 'data' => array('clerkid' => $isapplyforclerk['id']));
        } else {
            //插入数据
            $data = array('user_id' => $user_id, 'business_id' => $business_id, 'type' => 0, 'name' => $name, 'cardid' => $cardid);
            $id = Db::table('ml_tbl_business_clerk')->insertGetId($data);
            if ($id) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => array('clerkid' => $id));
            } else {
                $data = array('status' => 1, 'msg' => '失败', 'data' => '');
            }
        }
        return json($data);
    }

    public function verifyClerk()
    {
        $cler_id = $_REQUEST['clerkid'];
        $operation = $_REQUEST['operation'];//对店员操作 1、已通过 2、已注销
        //修改商户数据库表
        Db::table('ml_tbl_business_clerk')->where('id', $cler_id)->update(['type' => $operation]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function clerkList()
    {
        $business_id = $_REQUEST['businessid'];
        //查询用户，关联用户表查询
        $selectdata = Db::table('ml_tbl_business_clerk')->where('business_id', $business_id)->where('type','<>','2')->column('id,name,cardid,type');
        if ($selectdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $selectdata);
        } else {
            $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
        }
        return json($data);
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * @time: 2019/5/9
     * @autor: duheyuan
     * 获取待发货订单
     */
    public function getUndoneOrder(Request $request)
    {
        if ($request->isPost()){
            $all = $request->param();
            if (isset($all['b_id']) && !empty($all['b_id'])){
                $business_id = $all['b_id'];
                $list = Db::query("SELECT od.id as oid,g.head_img,g.goods_name,g.goods_price,od.goods_num,o.user_name,o.creat_time,od.express_no, o.pay_time,o.pay_price
                                      FROM ml_tbl_goods as g  join ml_tbl_order as o join ml_tbl_order_details as od
                                      WHERE g.id = od.goods_id and od.order_zid = o.id and g.business_id = (?) and o.order_type = (?)",
                                        [$business_id,PublicEnum::ORDER_NO_SHIPPED]);
                /*
                $sql = "SELECT od.id as oid,g.head_img,g.goods_name,g.goods_price,od.goods_num,o.user_name,o.creat_time,od.express_no, o.pay_time,o.pay_price,
                           FROM `ml_tbl_goods` as g   JOIN `ml_tbl_order_details` as od on g.id = od.goods_id  JOIN `ml_tbl_order` as o
                            and o.id = od.order_zid and g.business_id = {$business_id} AND o.order_type = 6";
                dump($list);die;
                */
                return json(['status'=>1,'msg'=>'成功','data'=>$list]);

            }else{
                return json(['status'=>0,'msg'=>'参数错误,请携带正确参数','data'=>'']);
            }
        }else{
            return json(['status'=>0,'msg'=>'方法错误','data'=>'']);
        }
    }

    public function updateOrderType(Request $request)
    {
            $all = $request->param();
            if (!isset($all['oid']) || empty($all['oid'])){
                return json(['status'=>0,'msg'=>'参数错误,请携带正确参数1','data'=>'']);
            }
            if (!isset($all['exp_no']) || empty($all['exp_no'])){
                return json(['status'=>0,'msg'=>'参数错误,请携带正确参数2','data'=>'']);
            }
            $res = Db::query("UPDATE  ml_tbl_order_details as od join ml_tbl_order as o SET  o.order_type = (?),od.express_no=(?)  WHERE od.id =(?) and od.order_zid = o.id ",[PublicEnum::ORDER_UNRECEIVED,$all['exp_no'],$all['oid']]);
            if ($res > 0){
                return json(['status'=>1,'msg'=>'修改成功','data'=>$res]);
            }else{
                return json(['status'=>0,'msg'=>'修改成功','data'=>'']);

            }
    }

}