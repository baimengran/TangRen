<?php
namespace app\admin\controller;

use app\admin\model\TurnsModel;
use think\Controller;
use think\Db;

class Taxi extends Controller
{
    public function index()
    {
        //执行查询操作
        $list= Db::table('think_taxi_list')
                ->where('exits_status',0)
                ->paginate(10);

        //
        $list= Db::table('think_taxi_list')
            ->where('exits_status',0)
            ->paginate(10);

        $count = count($list);
        $date = ['list'=>$list,'count'=>$count];

        //将数据传至页面
        $this->assign('list',$date);
        return $this->fetch();
    }
}