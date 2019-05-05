<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/30
 * Time: 13:22
 */

namespace app\index\model;


use think\Db;
use think\response\Redirect;

class MallUserWallet
{
    public function walletOperation($type, $amount, $user_id, $remarks)
    {
        //查询用户当前余额
        $walletdata = Db::table('ml_tbl_wallet')->where('user_id', $user_id)->find();
        if (!$walletdata) {
            $wallet_id = $walletdata['id'];
        } else {
            $wallet_id = $this->creatWallet($user_id);
        }
        $balance = $walletdata['balance'];
        $time = date("Y-m-d h:i:s", time());
        if ($type == 0) {
            $newbalance = $amount + $balance;
            //插入明细秒
            $intodata = array('wallet_id' => $wallet_id, 'time' => $time, 'amount' => $amount, 'nowbalance' => $newbalance, 'type' => $type, 'remarks' => $remarks);
        } elseif ($type == 1) {
            $newbalance = $balance - $amount;
            //插入明细秒
            $intodata = array('wallet_id' => $wallet_id, 'time' => $time, 'amount' => $amount, 'nowbalance' => $newbalance, 'type' => $type, 'remarks' => $remarks);
        }
        Db::table('ml_tbl_wallet')->where('user_id', $user_id)->update(['balance' => $newbalance]);
        Db::table('ml_tbl_wallet_details')->insert($intodata);
        return true;
    }

    public function creatWallet($user_id)
    {
        $time = date("Y-m-d h:i:s", time());
        $intodata = array('user_id' => $user_id, 'creat_time' => $time);
        $walletid = Db::table('ml_tbl_wallet')->insertGetId($intodata);
        return $walletid;
    }
}