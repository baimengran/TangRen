<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class TaxiModel extends Model
{
    public function index()
    {
        $date = Db::table('think_taxi_list')
            ->paginate(10);

        return $date;
    }
    //查询地区分类方法
    public function select_class()
    {
        $date = Db::table('think_region_list')
            ->field('region_name')
            ->where('region_status',0)
            ->select();

        return $date;
    }


    //添加数据方法
    public function add_taxi($post)
    {
        $data = [
            'taxi_logo'     => $post['taxi_logo'],
            'taxi_class'    => $post['taxi_class'],
            'taxi_name'     => $post['taxi_name'],
            'taxi_content'  => $post['taxi_content'],
            'taxi_day'      => $post['taxi_day'],
            'taxi_time'     => $post['taxi_time'],
            'taxi_phone'    => $post['taxi_phone'],
            'taxi_address'  => $post['taxi_address'],
            'taxi_label'    => $post['taxi_label'],
            ];
        $res = Db::table('think_taxi_list')->insert($data);
        return $res;
    }
}