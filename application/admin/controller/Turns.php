<?php
namespace app\admin\controller;

use app\admin\model\TurnsModel;
use think\Controller;
use think\Db;
use think\Request;

class Turns extends Controller
{
    //轮播图列表页
    public function index()
    {
        //获取轮播图信息
        $turns = new TurnsModel();
        //查询所有数据
        $list =  $turns->paginate(10);
        //统计数据
        $count = $turns->count();

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
            'title' => 'require|number'
        ];
        $message  = [
            'title.require' => '轮播图类型不能为空',
            'title.number'  => '轮播图类型错误',
        ];

        if(!empty($post)){
            if($post['title'] > 6){
                return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'类型不能大于6','retData'=>$post['title']],320);
            }
            //实例化验证器
            $result=$this->validate($post,$rule,$message);
            //判断有无错误
            if(true !== $result){
                $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
                // 验证失败 输出错误信息
                return json_encode($date,320);
            }

            $data = ['turns_class' => $post['title'], 'turns_img' => $post['photo'] ];
            $res = Db::table('think_turns_list')->insert($data);

            if($res){
                $arr = ['code'=>1,'msg'=>'添加成功'];
                return $arr;
            }else{
                $arr = ['code'=>2,'msg'=>'添加失败'];
                return $arr;
            }

        }

        return $this->fetch();

        //将数据传至页面
//        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$list],320);
    }

    //修改轮播图
    public function edit_turns(\think\Request $request)
    {
        $post = $request->post();
        print_r($post);die;
        $turns = new TurnsModel();
        $data = $turns->find($id);

        //获取轮播图信息
        $post = $request->post();

        $rule =   [
            'title' => 'require|number'
        ];
        $message  = [
            'title.require' => '轮播图类型不能为空',
            'title.number'  => '轮播图类型错误',
        ];

        if(!empty($post)){
            if($post['title'] > 6){
                return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'类型不能大于6','retData'=>$post['title']],320);
            }
            //实例化验证器
            $result=$this->validate($post,$rule,$message);
            //判断有无错误
            if(true !== $result){
                $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
                // 验证失败 输出错误信息
                return json_encode($date,320);
            }
            $data = ['turns_class' => $post['title'], 'turns_img' => $post['photo'] ];
            $res = Db::table('think_turns_list')->insert($data);

            if($res){
                $arr = ['code'=>1,'msg'=>'添加成功'];
                return $arr;
            }else{
                $arr = ['code'=>2,'msg'=>'添加失败'];
                return $arr;
            }

        }

        $this->assign('data',$data);
        return $this->fetch();

    }

    //接收添加轮播图方法
    public function store_turns(\think\Request $request)
    {
        //将数据传至页面
        return $this->fetch('turns/add_turns');
    }

}