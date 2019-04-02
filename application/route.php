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
Route::rule('userRegister','index/User/userRegister');
Route::rule('userInfoSet','index/User/userInfoSet');
Route::rule('userUpCodeSet','index/User/userUpCodeSet');
Route::rule('userPwdSet','index/User/userPwdSet');
Route::rule('userPwdChange','index/User/userPwdChange');
Route::rule('selUserId','index/User/selUserId');

Route::rule('userBankInfo','index/User/userBankInfo');
Route::rule('userBankSet','index/User/userBankSet');
Route::rule('userBankChange','index/User/userBankChange');

Route::rule('proList','index/Pro/proList');
Route::rule('proDetails','index/Pro/proDetails');

Route::rule('proCardDetails','index/ProCard/proCardDetails');
Route::rule('myCardList','index/ProCard/myCardList');
Route::rule('myProCardDetails','index/ProCard/myProCardDetails');
Route::rule('cardGiveTo','index/ProCard/cardGiveTo');

Route::rule('generateOrder','index/Order/generateOrder');