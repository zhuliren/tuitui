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
Route::rule('renewalFeeOrder', 'index/WechatPay/renewalFeeOrder');
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

Route::rule('mallIndexTwo', 'index/Mall/mallIndexTwo');

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


Route::rule('getTicketGoods', 'index/Ticket123/getSingleGoods');//
Route::rule('tGoodsList', 'index/Ticket123/getMenPiaoGoodslist');//
Route::rule('viewPointInfo', 'index/Ticket123/getViewPointInfo');//
Route::rule('takeGoods', 'index/Ticket123/getGoodsInfo');//


//TODO 新商城首页接口  20190515
Route::rule('mustBuy', 'index/Mall/getMustbuyGoods');
Route::rule('mustBuyTwo', 'index/Mall/getMustbuyGoodsTwo');
Route::rule('getGoodsList', 'index/Mall/getGoodsClassList');
Route::rule('getClassList', 'index/Mall/getClassList');
Route::rule('classGoodsList', 'index/Mall/classGoodsList');
Route::rule('getGoodsFormat', 'index/MallGoods/getGoodsFormat');


Route::rule('getUserPact', 'index/User/getUserPact');   //  获取分销协议
Route::rule('doWithdraw', 'index/WechatPay/doWithdraw');   //  获取分销协议
Route::rule('insertBank', 'index/User/insertBankInfo');   //  新增用户银行卡
Route::rule('createWithdrawOrder', 'index/User/createWithdrawOrder');   //  生成提现订单号
Route::rule('commissionList', 'index/User/commissionList');   //  返佣明细
Route::rule('getMyWithdrawList', 'index/User/getMyWithdrawList');   //  提现明细
Route::rule('getMyBank', 'index/User/getMyBankCard');   //  获取我的银行卡
Route::rule('editMyBankCard', 'index/User/editMyBankCard');   //  修改我的银行卡
Route::rule('getGoodsInfo', 'index/Mall/getGoodsInfo');   //  商品详情
Route::rule('getIndexGoodsList', 'index/Mall/getIndexGoodsList');   //  商品详情


Route::rule('getTodayClerk', 'index/MallUser/getTodayClerk');   //  获取今日分销
Route::rule('getMyClerk', 'index/MallUser/getMyClerk');   //  获取我的历史分销
Route::rule('createCpn', 'index/User/createCpn');
Route::rule('mustNow', 'index/MallUser/mustNow');
Route::rule('sendTemplateMessage', 'index/MallUser/sendTemplateMessage');






Route::rule('goodsOnline', 'admin/Goods/goodsOnline');   //  商品上下架


Route::rule('editUserSales', 'index/MallUser/editUserSales');   //
Route::rule('renewalDisTri', 'index/MallOrder/renewalDisTri');   //
Route::rule('v1/clerkrcode', 'index/MallOrder/UnionPayOpenAPI');   // 银联API
Route::rule('v1/binddevice', 'index/MallOrder/bindDevice');   //
Route::rule('v1/agenbinddevice', 'index/MallOrder/againBindDevice');   //






Route::rule('ToExcel', 'admin/Excel/testExcel');   //

Route::rule('allgoodsList', 'admin/Goods/allgoodsList');    //  所有已上线商品
Route::rule('unlineGoods', 'admin/Goods/getUnlineGoods');   //  所有已下线商品
Route::rule('goodsDetail', 'admin/Goods/getGoodsDetail');   //  商品详情
Route::rule('editGoods', 'admin/Goods/editGoods');   //  修改商品详情
Route::rule('editBanner', 'admin/Goods/editGoodsBanner');   //  修改商品详情
Route::rule('editShareInfo', 'admin/Goods/editShareInfo');   //  修改商品详情
Route::rule('getShareInfo', 'admin/Goods/getGoodsShareInfo');   //  获取商品分享信息
Route::rule('cutOrder', 'admin/Order/cutOrder');   //  完成订单
Route::rule('getWithdrawList', 'admin/Withdraw/getWithdrawList');   //  获取提现列表
Route::rule('exportExcel', 'admin/Withdraw/exportExcel');   //  提现导出
Route::rule('newGoodsAdd', 'admin/Goods/goodsAdd');   //  新- 新增商品
Route::rule('inserttag', 'admin/Goods/inserttag');   //  新- 新增商品标签
Route::rule('goodsTop', 'admin/Goods/goodsTop');   //  商品必买置顶
Route::rule('unGoodsTop', 'admin/Goods/unGoodsTop');   //  商品取消必买
Route::rule('editGoodsFormat', 'admin/Goods/editGoodsFormat');   //  商品规格修改
Route::rule('getOrderList', 'admin/Order/getOrderList');   // 订单类表
Route::rule('goodsFront', 'admin/Goods/goodsFront');   // 商品提位
Route::rule('cutWithdraw', 'admin/Withdraw/cutWithdraw');   // 提现完成
Route::rule('refuseWithdraw', 'admin/Withdraw/refuseWithdraw');   // 提现拒绝



/* ------- test    */
Route::rule('testcpn', 'index/User/testcpn');


Route::rule('getEventInfo', 'index/Event/getEventInfo');   // 活动首页
Route::rule('joinActivity', 'index/Event/joinActivity');   // 参加活动
Route::rule('eventGoodsInfo', 'index/Event/goodsInfo');   // 商品信息
Route::rule('goodsShareUrl', 'index/Event/goodsShareUrl');   // 加入团长队伍
Route::rule('buyGoods', 'index/Event/buyGoods');   // 下单页信息
Route::rule('createEventOrder', 'index/Event/createEventOrder');   // 创建订单
Route::rule('eventNotify', 'index/WechatPay/eventNotify');   // 订单回调
Route::rule('eventPay', 'index/WechatPay/eventPay');   // 支付
Route::rule('eventOrderList', 'index/Event/eventOrderList');   // 订单列表
Route::rule('eventOrderDetail', 'index/Event/eventOrderDetail');   // 订单详情
Route::rule('eventShareInfo', 'index/Event/shareInfo');   // 分享信息
Route::rule('lookTeamOrder', 'index/Event/lookTeamOrder');   // 查看团队订单
Route::rule('editOrderStatus', 'index/Event/editOrderStatus');   // 修改订单状态
Route::rule('editJoinActivityType', 'index/Event/editJoinActivityType');   // 修改订单状态
Route::rule('eventShareRcode', 'index/Event/eventShareRcode');   // 修改订单状态


Route::rule('editFormId', 'index/User/editFormId');  // 修改form_id
Route::rule('exportExcel', 'admin/Withdraw/exportExcel');   //  获取提现列表


//推王争霸活动相关接口
Route::rule('creatGame', 'index/MallGame/creatGame');   //  创建活动
Route::rule('gameList', 'index/MallGame/gameList');   //  历史活动列表
Route::rule('gameDetails', 'index/MallGame/gameDetails');   //  活动详情
Route::rule('creatGameGoods', 'index/MallGame/creatGameGoods');   //  增加活动商品
Route::rule('updGameGoods', 'index/MallGame/updGameGoods');   //  修改商品详情
Route::rule('gameGoodsList', 'index/MallGame/gameGoodsList');   //  活动商品列表
Route::rule('gameGoodsInfo', 'index/MallGame/gameGoodsInfo');   //  活动商品详情
Route::rule('gameLeaderInfo', 'index/MallGame/gameLeaderInfo');   //  团长信息查询
Route::rule('creatGameOrder', 'index/MallGame/creatGameOrder');   //  创建订单
Route::rule('gameOrderPay', 'index/MallGame/gameOrderPay');   //  订单支付
Route::rule('gameOrderPayConfirm', 'index/MallGame/gameOrderPayConfirm');   //  订单支付完成确认
Route::rule('myGameOrderList', 'index/MallGame/myGameOrderList');   //  我的订单列表
Route::rule('leaderGameOrderList', 'index/MallGame/leaderGameOrderList');   //  团长的订单列表
Route::rule('gameOrderDetails', 'index/MallGame/gameOrderDetails');   //  订单详情
Route::rule('gameOrderConfirm', 'index/MallGame/gameOrderConfirm');   //  团长确认订单
Route::rule('allGameOrderList', 'index/MallGame/allGameOrderList');   //  全部订单列表
Route::rule('gameUnifiedOrder', 'index/MallGame/gameUnifiedOrder');   //  微信预下单接口
Route::rule('gamePayNotify', 'index/MallGame/gamePayNotify');   //  活动异步回调通知
Route::rule('joinTeam', 'index/MallGame/joinTeam');   //  加入团队
Route::rule('editTeamInfo', 'index/MallGame/editTeamInfo');   //  处理消息
Route::rule('joinGame', 'index/MallGame/joinGame');   //  加入活动
Route::rule('myTeamInfo', 'index/MallGame/myTeamInfo');   //  我的信息
Route::rule('gameChatInfo', 'index/MallGame/gameChatInfo');   //  我的信息
Route::rule('getmemberstatus', 'index/MallGame/getmemberstatus');   //  查看用户会员状态
Route::rule('getmyteamdata', 'index/MallGame/getmyteamdata');   //  查看用户会员状态
Route::rule('allteamInfo', 'index/MallGame/allteamInfo');   //  所有战队信息
Route::rule('gamegoodsdown', 'index/MallGame/gamegoodsdown');   //  商品下架
Route::rule('gamedown', 'index/MallGame/gamedown');   //  活动下架
Route::rule('editGameInfo', 'index/MallGame/editGameInfo');   //  修改活动信息
Route::rule('gamegoodsrcode', 'index/MallGame/gamegoodsrcode');   //  活动分享二维码

//  新增的
Route::rule('addGameClass', 'index/MallGame/addGameClass');   //  新增分类
Route::rule('updateGameClass', 'index/MallGame/updateGameClass');   //  修改分类
Route::rule('getgameclass', 'index/MallGame/getgameclass');   //  修改分类
Route::rule('getrankdata', 'index/MallGame/getrankdata');   //  修改分类
Route::rule('getclassInfo', 'index/MallGame/getclassInfo');   //  修改分类
Route::rule('getUserAddress', 'index/MallGame/getUserAddress');   //  获取用户地址
Route::rule('addmyaddress', 'index/MallGame/addmyaddress');   //  增加地址
Route::rule('leaderstatus', 'index/MallGame/leaderstatus');   //  判断是否是团长




Route::rule('createWxQrcode', 'index/MallGame/createWxQrcode');   //  判断是否是团长
Route::rule('createMiniWechat', 'index/MallGame/createMiniWechat');   //  判断是否是团长





