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

    public function add_turns($post)
    {
        $data = ['turns_img' => $post['turns_img'], 'turns_url' => '','turns_class'=> $post['turns_class'] ];
        $res = Db::table('think_turns_list')->insert($data);

        return $res;
    }
}