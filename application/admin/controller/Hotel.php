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
        $list =  $hotel->select();

        return view('hello',['name'=>'list']);
    }
}