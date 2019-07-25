<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/24
 * Time: 14:45
 */

namespace app\admin\controller;


use think\Exception;

class Topic
{
    public function index(){
        try{

        }catch(Exception $e){
            return json(['code'=>1,'data'=>'','msg'=>'出错啦']);
        }
    }

    public function edit(){

    }

    public function update(){

    }

    public function destroy(){

    }
}