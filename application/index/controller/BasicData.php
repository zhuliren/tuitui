<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/9
 * Time: 13:58
 */

namespace app\index\controller;


use app\index\model\UserModel;
use think\Db;

class BasicData
{
    public function getSecret(){
        $user_id = $_REQUEST['userid'];
        $userModel = new UserModel();
        $user_type = $userModel->userIdentity($user_id);
        switch ($user_type) {
            case -1:
                $data = array('status' => 1, 'msg' => '用户不存在', 'data' => '');
                return json($data);
            case 0:
                $data = array('status' => 1, 'msg' => '无权限查询', 'data' => '');
                return json($data);
        }
        $returndata = array('secret'=>'a1904ad7e0ab761657a294bc00352c3d');
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function userAgreement(){
        $user_id = $_REQUEST['userid'];
        //判断用户参与项目
        $userModel = new UserModel();
        $user_type = $userModel->userIdentity($user_id);
        switch ($user_type) {
            case -1:break;
            case 0:break;
            case 1:break;
            case 2:break;
        }
    }

    public function test(){
        return 1;
        $user_id = 3;
        $insertdata = ['pro_id'=>1,'pro_stage_id'=>1,'pro_card_oriprice'=>400,'pro_card_newprice'=>400,'pro_card_lasttrantime'=>date("Y-m-d H:i:s", time()),'pro_card_firstrantime'=>date("Y-m-d H:i:s", time()),'user_id'=>$user_id,'pro_card_pprice'=>400];
        for($i=0;$i<100;$i++){
        Db::table('xm_tbl_pro_card')->insert($insertdata);
        }
    }

}