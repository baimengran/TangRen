<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class TurnsModel extends Model
{
    public function region()
    {
        //查询所有地区分类
        $res = Db::table('think_turns_list')->select();
        return $res;
    }
    public function count()
    {
        //查询出轮播图信息量
        $turns = Db::table('think_turns_list')->where('turns_status',0)->count();

        return $turns;
    }
    public function find($id)
    {
        $where['turns_status'] = 0;
        $where['turns_id'] = $id;
        //查询出轮播图信息
        $turns = Db::table('think_turns_list')->where($where)->find();

        return $turns;
    }

    public function paginate($page)
    {
        //查询出轮播图信息
        $turns = Db::table('think_turns_list')->where('turns_status',0)->paginate($page);

        return $turns;
    }

    public function add_turns($post)
    {
        $data = ['turns_img' => $post['turns_img'], 'turns_url' => '','turns_class'=> $post['turns_class'] ];
        $res = Db::table('think_turns_list')->insert($data);

        return $res;
    }
}