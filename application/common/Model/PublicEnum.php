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
    const ORDER_NO_SHIPPED = 6;//待发货


    const TICKET_ID = 'shihuituan';
    const TICKET_SECRET = 'shihuituan123';

    const TICKET_TRUE_ID = '10518da3';
    const TICKET_TRUE_SECRET = 'fd1a7fec15a5';


    const ALL = 1;
    const TRAVEL = 4;
    const CHILDREN = 5;
    const HOUSEKEEPING = 7;
    const FRUIT = 8;
    const FOOD = 9;
    const OTHER = 10;


    const ORDER_PAY = 'FTWQioPcN2CR5McOXgXsKqR-A0JRdV_ZZwLH-AZv_8o';
    const ORDER_NOPAY = 'xkYXbN8lYHVNZMKSKKl9Y0AmjlYhes9X2izDNHNS_TM';

    const TEAM_APPLY = '9GuLAo3sFbr07driVIbCM8fUFNMpAFkEPzQHPWaCK60';
    const TEAM_SUCCESS = 'KEUf_AXLX-93_ryivUVhTGyX7sMc07BMTju2bjH3Ju8';
    const TEAM_FAIL = 'm0d24YjHYsDBMWfTDbe0jXQCcvKF3q1NsMs24gl47R0';

    /* ----  ----  ---- 不一定使用,记住类型 ----  ---- ----  ----- */
    const Success = 1001;       //  成功
    const PARAM_ERROR = 2001;   // 传递的参数错误
    const DB_ERROR = 3001;      //  数据库增入错误
    const SELECT_ERROR = 4001;  //  数据库查询错误
    const WARNING_ERROR = 5001; // 非法操作类错误
}