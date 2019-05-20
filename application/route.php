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
Route::rule('test2', 'index/Index/test2');
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
Route::rule('orderGoodsClerk', 'index/MallOrder/orderGoodsClerk');
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
//商城分享模块
Route::rule('goodssharercode', 'index/MallShareCode/goodssharercode');




Route::rule('myOrderNum', 'index/MallUser/myOrderNum');
Route::rule('myDistri', 'index/MallUser/myDistribution');
Route::rule('regBefore', 'index/MallUser/registerBefore');
Route::rule('deCryptData', 'index/MallUser/deCryptData');
Route::rule('myWallet', 'index/MallUser/myWallet');
Route::rule('goodsRcode', 'index/MallUser/goodsRcode');
Route::rule('subUser', 'index/MallUser/subUserList');
Route::rule('getSessionkey', 'index/MallUser/getSessionkey');
Route::rule('cancelOrder', 'index/MallUser/cancelOrder');
Route::rule('shareGoods', 'index/MallGoods/shareGoods');
Route::rule('getUndoneOrder', 'index/MallBusiness/getUndoneOrder');
Route::rule('updateOrder', 'index/MallBusiness/updateOrderType');
Route::rule('xmWechatPay', 'index/WechatPay/xmWechatPay');
Route::rule('goodsAdd', 'index/MallGoods/goodsAdd');
Route::rule('goodsImgUpload', 'index/MallGoods/shareImgUpload');
Route::rule('namePreg', 'index/MallUser/namePreg');

Route::rule('getMyCoupon', 'index/UserCoupon/getMyCoupon');//获取我的优惠券


Route::rule('getTicketGoods', 'index/Ticket123/getSingleGoods');//获取我的优惠券
Route::rule('tGoodsList', 'index/Ticket123/getMenPiaoGoodslist');//获取我的优惠券


//TODO 新商城首页接口  20190515
Route::rule('mustBuy', 'index/Mall/getMustbuyGoods');
Route::rule('getGoodsList', 'index/Mall/getGoodsClassList');
Route::rule('getClassList', 'index/Mall/getClassList');
Route::rule('classGoodsList', 'index/Mall/classGoodsList');
Route::rule('getRichText', 'index/MallGoods/getRichText');









Route::rule('allgoodsList', 'admin/Goods/allgoodsList');    //  所有已上线商品
Route::rule('unlineGoods', 'admin/Goods/getUnlineGoods');   //  所有已下线商品
Route::rule('goodsDetail', 'admin/Goods/getGoodsDetail');   //  商品详情
Route::rule('editGoods', 'admin/Goods/editGoods');   //  修改商品详情
Route::rule('editBanner', 'admin/Goods/editGoodsBanner');   //  修改商品详情
Route::rule('editShareInfo', 'admin/Goods/editShareInfo');   //  修改商品详情
Route::rule('getShareInfo', 'admin/Goods/getGoodsShareInfo');   //  获取商品分享信息






