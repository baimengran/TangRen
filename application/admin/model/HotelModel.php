<?php

namespace app\admin\model;
use think\Model;
use think\Db;

class HotelModel extends Model
{
    public function select_hotel()
    {
        $date = Db::table('think_hotel_list')
            ->paginate(10);

        return $date;
    }

}