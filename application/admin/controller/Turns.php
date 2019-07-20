<?php
namespace app\admin\controller;

use app\admin\model\TurnsModel;
use think\Controller;
use think\Db;

class Turns extends Controller
{
    //轮播图列表页
    public function index()
    {
        //获取轮播图信息
        $turns = new TurnsModel();
        //查询所有数据
        $list =  $turns->select();
        //统计数据
        $count = count($list);

        $date = ['list'=>$list,'count'=>$count];

        //将数据传至页面
        $this->assign('list',$date);
        return $this->fetch();
    }
    //添加轮播图
    public function add_turns(\think\Request $request)
    {
        //获取轮播图信息
        $post = $request->post();
        $rule =   [
            'turns_class' => 'require|number'
        ];
        $message  = [
            'turns_class.require'      => '轮播图类型不能为空',
            'turns_class.number'       => '轮播图类型错误',
        ];
        //判断类型是否大于6
        print_r($post);die;
        if($post['turns_class'] > 6){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'类型不能大于6','retData'=>$post['turns_class']],320);
        }
        //实例化验证器
        $result=$this->validate($post,$rule,$message);
        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        if($files = request()->file('turns_img')){
            if(count($_FILES['turns_img']['name']) >= 2){
                return json_encode($date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'每次只能上传一张'],320);
            }

            $aa = uploadImage(
                $files,
                '/uploads/turns/'
            );
            //判断图片是否上传成功
            if(!isset($aa['0'])){
                return json_encode($date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'图片没有上传成功'],320);
            }
            $post['turns_img'] = implode(",", $aa);
        }else{
            $post['turns_img'] = '';
        }

        //获取轮播图信息
        $turns = new TurnsModel();
        $list  = $turns->select($post);
//        $this->assign('list',$list);
//        return $this->fetch();

        //将数据传至页面
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$list],320);
    }

    //接收添加轮播图方法
    public function store_turns(\think\Request $request)
    {


        //将数据传至页面
        return $this->fetch('turns/add_turns');
    }

}