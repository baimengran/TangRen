<?php
namespace app\admin\controller;

use app\admin\model\HotelModel;
use think\Config;
use think\Controller;
use think\Db;

class Hotel extends Controller
{
    //查询酒店列表接口
    public function index()
    {
        //执行查询操作
        $list= Db::table('think_hotel_list')
            ->where('exits_status',0)
            ->paginate(10);

        //统计多少数据
        $count= Db::table('think_hotel_list')
            ->where('exits_status',0)
            ->select();

        $count = count($list);
        $date = ['list'=>$list,'count'=>$count];

        //将数据传至页面
        $this->assign('list',$date);
        return $this->fetch();
    }

    //添加酒店接口
    public function add()
    {
        return view('hotel/add',['name'=>'list']);
    }

    //编辑酒店接口
    public function eidt()
    {
        return view('hotel/eidt',['name'=>'list']);
    }

    //删除酒店接口
    public function delete()
    {
        echo'111';die;
    }


}