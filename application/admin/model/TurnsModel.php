<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class TurnsModel extends Model
{
    public function select()
    {
        //查询出轮播图信息
        $turns = Db::table('think_turns_list')->select();

        return $turns;
    }
}