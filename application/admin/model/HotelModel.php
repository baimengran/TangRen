<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class HotelModel extends Model
{
    public function index()
    {
        $date = Db::table('think_hotel_list')
            ->select();

        return $date;
    }

}