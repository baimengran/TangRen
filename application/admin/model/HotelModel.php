<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class HotelModel extends Model
{
    public function index()
    {
        $date = Db::table('think_hotel_list')
            ->where('exits_status',0)
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
            'hotel_logo'     => $post['hotel_logo'],
            'hotel_class'    => $post['hotel_class'],
            'hotel_name'     => $post['hotel_name'],
            'hotel_content'  => $post['hotel_content'],
            'hotel_day'      => $post['hotel_day'],
            'hotel_time'     => $post['hotel_time'],
            'hotel_phone'    => $post['hotel_phone'],
            'hotel_address'  => $post['hotel_address'],
            'hotel_label'    => $post['hotel_label'],
        ];
        $res = Db::table('think_hotel_list')->insert($data);
        return $res;
    }
    //查询叫车公司
    public function find_taxi($id)
    {
        $date = Db::name('hotel_list')
            ->where('hotel_id',$id)
            ->find();

        return $date;
    }
}