<?php
namespace app\admin\controller;

use app\admin\model\TurnsModel;
use think\Config;
use think\Controller;
use think\Loader;
use think\Db;

class Turns extends Controller
{
    //轮播图列表页
    public function index()
    {
        //获取轮播图信息
        $turns = new TurnsModel();
        $list =  $turns->select();

        //将数据传至页面
        return view('turns/turns',['name'=>'list']);
//        return $this->fetch('turns/turns',$list);
    }

    public function add()
    {
        //获取轮播图信息
        $turns = new TurnsModel();
        $list =  $turns->select();

        //将数据传至页面
        return $this->fetch('turns/turns',$list);
    }
}