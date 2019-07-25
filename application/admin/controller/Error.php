<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/24
 * Time: 17:04
 */

namespace app\admin\controller;


class Error
{
    public function index(){
        return view('error/404');
    }
}