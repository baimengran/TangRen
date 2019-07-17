<?php

namespace app\index\controller;

use \think\Controller;
use think\Db;


class Turns extends Controller
{
    public function index(\think\Request $request)
    {
        //获取传入参数
        $post = $request->post();

        //定义规则
         $rule =   [
            'turns_class' => 'require|number'
         ];
        $message  = [
            'turns_class.require'      => '轮播图类型不能为空',
            'turns_class.number'       => '轮播图类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //查询轮播图
        $date = Db::table('think_turns_list')->where('turns_class',$post['turns_class'])->select();

        //执行函数返回值
        $date = ['errcode'=> 0,'errMsg'=>'success','ertips'=>'查询成功','retData'=>$date];

        return json_encode($date,320);
    }


}