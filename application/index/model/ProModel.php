<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 13:44
 */

namespace app\index\model;


use think\Db;
use think\Model;

class ProModel extends Model
{
    public function proStateSel($pro_id)
    {
        $pro_stage_sel = Db::table('xm_tbl_pro')->where('id', $pro_id)->value('prostate');
        switch ($pro_stage_sel) {
            case 1:
                return 1;
        }
        return 0;
    }
}