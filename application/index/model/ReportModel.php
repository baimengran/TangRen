<?php
namespace app\index\model;

use think\Db;
use think\Model;

class ReportModel extends Model
{

    public function report_list()
    {
        $date = Db::table('think_region_list')
            ->field('region_name')
            ->where('region_status',0)
            ->select();

        return $date;
    }


}