<?php
namespace app\index\controller;

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