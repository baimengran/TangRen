<?php
namespace app\admin\controller;

use app\admin\model\TurnsModel;
use think\Controller;
use think\Db;
use think\Request;

class Turns extends Base
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
            'title' => 'require|number',
            'photo'=>'require'
        ];
        $message  = [
            'title.require' => '轮播图类型不能为空',
            'title.number'  => '轮播图类型错误',
            'photo.require'=>'请上传图片'
        ];

        if(!empty($post)){
            if($post['title'] > 7){
                return $err = json(['code'=>0,'msg'=>'类型不能大于7','retData'=>$post['title']]);
            }
            //实例化验证器
            $result=$this->validate($post,$rule,$message);
            //判断有无错误
            if(true !== $result){
                $date = ['code'=> 0,'errMsg'=>'error','msg'=>$result];
                // 验证失败 输出错误信息
                return json($date);
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

        $turns = new TurnsModel();
        $data = $turns->region();

        $this->assign('data',$data);

        return $this->fetch('turns/add_turns',$data);
    }

    //修改轮播图
    public function edit_turns($id)
    {
        $turns = new TurnsModel();
        $data = $turns->find($id);
        $this->assign('data',$data);
        return $this->fetch();
    }
    // 修改提交页面
    public function update_turns(\think\Request $request)
    {

        $post = $request->post();

        $data['turns_img'] = $post['photo'];
        $data['turns_class'] = $post['title'];

        $res = Db::name('turns_list')->where('turns_id',$post['id'])->update($data);

        if($res){
            $arr = ['code'=>1,'msg'=>'修改成功'];
            return $arr;
        }else{
            $arr = ['code'=>2,'msg'=>'修改失败'];
            return $arr;
        }
    }
    // 删除轮播图
    public function del($id)
    {
        $data['turns_status'] = date('Ymd H:i:s',time());
        $res = Db::name('turns_list')->where('turns_id',$id)->update($data);
        if($res){
            $arr = ['code'=>1,'msg'=>'删除成功'];
            return $arr;
        }else{
            $arr = ['code'=>2,'msg'=>'删除失败'];
            return $arr;
        }
    }

    //接收添加轮播图方法
    public function store_turns(\think\Request $request)
    {
        //将数据传至页面
        return $this->fetch('turns/add_turns');
    }

}