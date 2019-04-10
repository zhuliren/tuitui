<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/9
 * Time: 13:58
 */

namespace app\index\controller;


use app\index\model\UserModel;

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
}