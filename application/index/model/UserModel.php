<?php
namespace app\index\model;

use think\Db;
use think\Model;

class UserModel extends Model
{
    public function test_1($get)
    {
        $user = Db::table('think_member')
            ->field('nickname,head_img,sign_status,create_time')
            ->where('id',$get)
            ->find();

        //获取之前的时间
        $create_time = date('Ymd',$user['create_time']);

        //获取现在的时间
        $newtime = date('Ymd',time());

        //计算登录了多少天
        $all['alltime'] = $newtime - $create_time;

        //将登录多少天的时间合并到数组中
        $user = array_merge($user,$all);
        return $user;
    }

    public function user_fraction($get)
    {
        $integral = Db::table('think_member')
            ->field('integral')
            ->where('id',$get)
            ->find();

        return $integral;
    }
}
