<?php
namespace app\index\controller;

use app\index\model\HotelcommentModel;
use app\index\model\HotelModel;
use think\Controller;
use think\Db;
use think\Request;

class Ceshi extends Controller
{
    public function aaa(request $request){
        $a = $request->post();
        dump($a);
    }
}