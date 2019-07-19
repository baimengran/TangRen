<?php
namespace app\index\model;

use think\Db;
use think\Model;

class PayModel extends Model
{
    public function integral_list($id)
    {
        $date = Db::table('think_integral_list')
            ->where('integral_id',$id)
            ->find();

        return $date;
    }
}