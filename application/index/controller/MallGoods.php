<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/24
 * Time: 12:35
 */

namespace app\index\controller;


class MallGoods
{
    public function creatGoods()
    {
        $head_img = $_REQUEST['head_img'];//头像
        $goods_name = $_REQUEST['goods_name'];//商品名称
        $goods_summary = $_REQUEST['goods_summary'];//商品简介
        $goods_format = $_REQUEST['goods_format'];//商品规格
        $goods_region = $_REQUEST['goods_region'];//商品地区
        $goods_stock = $_REQUEST['goods_stock'];//商品库存
        $goods_details = $_REQUEST['goods_details'];//商品详情
        $creat_time = date("Y-m-d h:i:s", time());//创建时间
        $ex_time = $_REQUEST['ex_time'];//到期时间
        $bonus_price = 0;//分销价格（默认为0不参与分销）
        $is_online = 0;//是否上架默认为0不上架 1上架
        $goods_class = $_REQUEST['goods_class'];//商品分类
        $goods_price = $_REQUEST['goods_price'];//商品售格
        $goods_original_price = $_REQUEST['goods_original_price'];//商品原价
        $type = $_REQUEST['type'];//核销类型1、配送 2、扫码核销
        $business_id = $_REQUEST['business_id'];//商户id


    }
}