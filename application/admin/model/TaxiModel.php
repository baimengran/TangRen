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
    //获取一周营业时间
    public function select_week()
    {
        $week =['周一','周二','周三','周四','周五','周六','周日'];

        return $week;
    }
    //获取一天营业时间
    public function select_day()
    {
        $day = [];
        for ($i=0; $i<=23; $i++)
        {
            $day[] =  $i<10?'0'.$i:$i;

        }
        return $day;
    }
    //获取营业时间哪一分钟
    public function select_minute()
    {
        $day = [];
        for ($i=0; $i<=59; $i++)
        {
            $day[] =  $i<10?'0'.$i:$i;
        }
        return $day;
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

    //查询叫车公司
    public function find_taxi($id)
    {

        $date = Db::name('taxi_list')
            ->where('taxi_id',$id)
            ->find();

        return $date;

    }
}