<?php
namespace app\index\model;

use think\Db;
use think\Model;

class FractionModel extends Model
{
    public function index()
    {
        $goods = Db::table('think_goods_fraction')->select();
        return $goods;
    }

    public function select($post)
    {
        $goods = Db::table('think_goods_fraction')
            ->field('goods_fraction')
            ->where('goods_id',$post)
            ->find();
        return $goods;
    }
}