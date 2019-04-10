<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
//
//return [
//    '__pattern__' => [
//        'name' => '\w+',
//    ],
//    '[hello]' => [
//        ':id' => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
//        ':name' => ['index/hello', ['method' => 'post']],
//    ],
//
//];
use think\Route;
//Route::rule('路由表达式','路由地址','请求类型','路由参数（数组）','变量规则（数组）');
//用户模块
Route::rule('userRegister','index/User/userRegister');
Route::rule('userInfoSet','index/User/userInfoSet');
Route::rule('userUpCodeSet','index/User/userUpCodeSet');
Route::rule('userPwdSet','index/User/userPwdSet');
Route::rule('userPwdChange','index/User/userPwdChange');
Route::rule('userInfoSet','index/User/userInfoSet');
Route::rule('myCode','index/User/myCode');
Route::rule('userRegisterWithCode','index/User/userRegisterWithCode');
Route::rule('selUserId','index/User/selUserId');
Route::rule('userBankInfo','index/User/userBankInfo');
Route::rule('userBankSet','index/User/userBankSet');
Route::rule('userBankChange','index/User/userBankChange');
//项目模块
Route::rule('proList','index/Pro/proList');
Route::rule('proDetails','index/Pro/proDetails');
//代理权模块
Route::rule('proCardDetails','index/ProCard/proCardDetails');
Route::rule('myCardList','index/ProCard/myCardList');
Route::rule('myProCardDetails','index/ProCard/myProCardDetails');
Route::rule('cardGiveTo','index/ProCard/cardGiveTo');
//订单模块
Route::rule('generateOrder','index/Order/generateOrder');
//系统基础数据模块
Route::rule('getSecret','index/BasicData/getSecret');