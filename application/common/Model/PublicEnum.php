<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/5/6
 * Time: 10:21
 */

namespace app\common\Model;


class PublicEnum
{
    const WX_XM_APPID = 'wx4473d33d20a8d3b3';


    const WX_APPID = 'wx0fda8074ccdb716d';
    const WX_SECRET = 'bf55d7a720d5bc162621e3901b7645be';



    //  发货状态
    const ORDER_UNPAID = 0; //未支付
    const ORDER_UNRECEIVED = 1;//待收货
    const ORDER_UNAPPLIED = 2;//待核销
    const ORDER_COMPLETED = 3;//已完成
    const ORDER_CANCELLED = 4;//已取消
    const ORDER_NO_SHIPPED = 5;//待发货


    const TICKET_ID = 'shihuituan';
    const TICKET_SECRET = 'shihuituan123';

}