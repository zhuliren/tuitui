<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/23
 * Time: 9:53
 */

namespace app\index\controller;


use think\Db;

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
        $selectdata = Db::table('ml_tbl_business_clerk')->where('business_id', $business_id)->column('id,name,cardid,type');
        if ($selectdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $selectdata);
        } else {
            $data = array('status' => 1, 'msg' => '无数据', 'data' => '');
        }
        return json($data);
    }
}