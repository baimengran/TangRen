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
}