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
Route::rule('test', 'index/Index/test');
//用户模块
Route::rule('userRegister', 'index/User/userRegister');
Route::rule('userInfoSet', 'index/User/userInfoSet');
Route::rule('userUpCodeSet', 'index/User/userUpCodeSet');
Route::rule('userPwdSet', 'index/User/userPwdSet');
Route::rule('userPwdChange', 'index/User/userPwdChange');
Route::rule('userInfoSet', 'index/User/userInfoSet');
Route::rule('myCode', 'index/User/myCode');
Route::rule('userRegisterWithCode', 'index/User/userRegisterWithCode');
Route::rule('selUserId', 'index/User/selUserId');
Route::rule('selUserInfo', 'index/User/selUserInfo');
Route::rule('userBankInfo', 'index/User/userBankInfo');
Route::rule('userBankSet', 'index/User/userBankSet');
Route::rule('userBankChange', 'index/User/userBankChange');
Route::rule('myChannel', 'index/User/myChannel');
Route::rule('userInfoGet', 'index/User/userInfoGet');
Route::rule('bindingMalluser', 'index/User/bindingMalluser');
//项目模块
Route::rule('proList', 'index/Pro/proList');
Route::rule('proDetails', 'index/Pro/proDetails');
Route::rule('getProPolicy', 'index/Pro/getProPolicy');
//代理权模块
Route::rule('proCardDetails', 'index/ProCard/proCardDetails');
Route::rule('myCardList', 'index/ProCard/myCardList');
Route::rule('myProCardDetails', 'index/ProCard/myProCardDetails');
Route::rule('cardGiveTo', 'index/ProCard/cardGiveTo');
//订单模块
Route::rule('generateOrder', 'index/Order/generateOrder');
//系统基础数据模块
Route::rule('getSecret', 'index/BasicData/getSecret');
//优惠券模块
Route::rule('myCouponList', 'index/Coupon/myCouponList');
Route::rule('couponGiveTo', 'index/Coupon/couponGiveTo');
Route::rule('couponDetails', 'index/Coupon/couponDetails');
//支付模块
Route::rule('unifiedorder', 'index/WechatPay/unifiedorder');
//商城用户模块
Route::rule('userMallRegister', 'index/MallUser/userMallRegister');
Route::rule('userMallShareRegister', 'index/MallUser/userMallShareRegister');
Route::rule('userBindingGet', 'index/MallUser/userBindingGet');
Route::rule('userBindingSet', 'index/MallUser/userBindingSet');
Route::rule('userMoBinding', 'index/MallUser/bindingMalluser');
Route::rule('userCentre', 'index/MallUser/userCentre');
Route::rule('isClerk', 'index/MallUser/isClerk');
//商城模块
Route::rule('mallBanner', 'index/Mall/mallBanner');
Route::rule('mallIndex', 'index/Mall/mallIndex');
Route::rule('goodsDetails', 'index/Mall/goodsDetails');
Route::rule('getGoodsClass', 'index/Mall/getGoodsClass');
//购物车订单模块
Route::rule('operateShopCar', 'index/MallOrder/operateShopCar');
Route::rule('delShapCar', 'index/MallOrder/delShapCar');
Route::rule('shopCarDetails', 'index/MallOrder/shopCarDetails');
Route::rule('orderList', 'index/MallOrder/orderList');
Route::rule('orderDetails', 'index/MallOrder/orderDetails');
Route::rule('orderClerk', 'index/MallOrder/orderClerk');
//订单模块
Route::rule('creatMallOrder', 'index/MallOrder/creatMallOrder');
Route::rule('mallBuyNow', 'index/MallOrder/mallBuyNow');
Route::rule('creatOrderOnce', 'index/MallOrder/creatOrderOnce');
Route::rule('unifiedorder', 'index/WechatPay/unifiedorder');
Route::rule('payNotify', 'index/WechatPay/payNotify');
Route::rule('orderQuery', 'index/WechatPay/orderQuery');
//商户模块
Route::rule('creatBusiness', 'index/MallBusiness/creatBusiness');
Route::rule('getBusinessInfo', 'index/MallBusiness/getBusinessInfo');
Route::rule('verifyBusiness', 'index/MallBusiness/verifyBusiness');
Route::rule('modifyBusinessInfo', 'index/MallBusiness/modifyBusinessInfo');
Route::rule('getBusinessList', 'index/MallBusiness/getBusinessList');
Route::rule('applyForClerk', 'index/MallBusiness/applyForClerk');
Route::rule('verifyClerk', 'index/MallBusiness/verifyClerk');
Route::rule('clerkList', 'index/MallBusiness/clerkList');
