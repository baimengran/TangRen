<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/22
 * Time: 9:50
 */

namespace app\admin\controller;


use think\Db;

class Region
{
    public function index(){
        $region = Db::name('region_list')->select();
        return view('index',['region'=>$region]);
    }

    public function create(){

    }

    public function store(){

    }


    public function edit(){

    }

    public function update(){

    }

    public function destroy(){

    }
}