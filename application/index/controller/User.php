<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 12:38
 */

namespace app\index\controller;


class user
{
    public function userlogin()
    {
        $user_id = $_REQUEST['user_id'];
        return $user_id;
    }
}