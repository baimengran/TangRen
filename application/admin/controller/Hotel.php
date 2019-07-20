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
        $hotel= new HotelModel();
        $list =  $hotel->select_hotel();
        //统计多少条数据
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