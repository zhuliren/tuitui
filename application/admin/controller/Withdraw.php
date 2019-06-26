<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/5/22
 * Time: 09:09
 */

namespace app\admin\controller;


use app\admin\Controller;
use think\Db;
use think\Request;

class Withdraw extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

    }

    public function getWithdrawList()
    {

        $all = $this->request->param();
//        $sql = "SELECT W.id,W.uid,W.order_no,W.amount,BC.name,BC.card_id,BC.tel,W.ctime,W.desc FROM `ml_tbl_withdraw` AS W LEFT  JOIN `ml_tbl_user_bank_card` AS BC ON W.uid =  BC.uid WHERE W.pay_time = 0 ";
        $sql = "SELECT w.id,w.order_no,w.amount,u.id AS uid,u.user_name,u.user_phone,w.ctime,w.desc,w.code,w.status FROM `ml_tbl_withdraw` AS w JOIN `ml_tbl_user` AS u ON w.uid=u.id ";


        if (isset($all['code']) && !empty($all['code'])){
            $code = strtoupper($all['code']);
            $sql .= " WHERE w.code = '{$code}' ";
        }

        $sql .= " ORDER BY w.id desc ";
        $list = Db::query($sql);
        return json(['status'=>1001,'msg'=>'成功','data'=>$list]);
    }

    public function exportExcel()
    {
        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $data = self::getWithdrawList()->getData()['data'];

        foreach ($data as $k=>$v){
            if ($v['ctime']){
                $data[$k]['ctime'] = date('Y-m-d H:i:s',$v['ctime']);
            }
        }
        $indexKey = array_keys($data[0]);
        $name = date('Ymd',time()).rand(10000,9999);
        $res = toExcel($data,$name,$indexKey);

    }

    public function cutWithdraw()
    {
        $all = $this->request->param();

        if (isset($all['id']) && !empty($all['id'])){

            $res = Db::name('ml_tbl_withdraw')->where('id',$all['id'])->update(['pay_time'=>time(),'status'=>1]);
            if ($res){
                return json(['status'=>1001,'msg'=>'成功','data'=>'']);
            }else{
                return json(['status'=>1001,'msg'=>'成功','data'=>'']);
            }
        }
    }

    public function refuseWithdraw()
    {
        $all = $this->request->param();

        if (isset($all['id']) && !empty($all['id'])){
            $withdraw_info = Db::name('ml_tbl_withdraw')->where('id',$all['id'])->find();
            $balance = Db::name('ml_tbl_wallet')->where('user_id',$withdraw_info['uid'])->find();
            $balance['balance'] += $withdraw_info['amount'];
            Db::startTrans();
            $edit_wallet = Db::name('ml_tbl_wallet')->where('user_id',$withdraw_info['uid'])->update(['balance'=>$balance['balance']]);
            if (!$edit_wallet){
                Db::rollback();
                return json(['status'=>3001,'msg'=>'失败','data'=>'']);
            }
            $res = Db::name('ml_tbl_withdraw')->where('id',$all['id'])->update(['status'=>2]);

            if ($res){
                Db::commit();
                return json(['status'=>1001,'msg'=>'成功','data'=>'']);
            }else{
                Db::rollback();
                return json(['status'=>3001,'msg'=>'失败','data'=>'']);
            }
        }

    }




}