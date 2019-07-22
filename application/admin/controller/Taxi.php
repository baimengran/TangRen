<?php
namespace app\admin\controller;

use app\admin\model\TaxiModel;
use app\admin\model\TurnsModel;
use think\Controller;
use think\Db;
use think\index\validator;

class Taxi extends Controller
{
    //叫车列表
    public function index()
    {
        //执行查询操作
        $list= Db::table('think_taxi_list')
                ->where('exits_status',0)
                ->paginate(10);

        //统计多少数据
        $count= Db::table('think_taxi_list')
            ->where('exits_status',0)
            ->select();

        $count = count($list);
        $date = ['list'=>$list,'count'=>$count];

        //将数据传至页面
        $this->assign('list',$date);
        return $this->fetch();
    }
    /**
     * 添加叫车
     */
    public function add_taxi(\think\Request $request)
    {
        //获取参数
        $post = $request->post();
        $rule =   [
            'taxi_class'    => 'require',
            'taxi_name'     => 'require',
            'taxi_content'  => 'require|max:550',
            'taxi_day'      => 'require',
            'taxi_time'     => 'require',
            'taxi_phone'    => 'require',
            'taxi_address'  => 'require',
            'taxi_status'   => 'require',
        ];
        $message  = [
            'taxi_class.require'      => '地区不能为空',
            'taxi_name.require'       => '名称不能为空',
            'taxi_content.require'    => '介绍不能为空',
            'taxi_content.max'        => '介绍不能过长',
            'taxi_day.require'        => '每周营业时间不能为空',
            'taxi_time.require'       => '每天营业时间不能为空',
            'taxi_phone.require'      => '联系电话不能为空',
            'taxi_address.require'    => '具体地址不能为空',
        ];
        if(!empty($post)) {
            //实例化验证器
            $result = $this->validate($post, $rule, $message);

            //判断有无错误
            if (true !== $result) {
                $date = ['errcode' => 1, 'errMsg' => 'error', 'ertips' => $result];
                // 验证失败 输出错误信息
                return json_encode($date, 320);
            }

            //判断标签是否有值
            if (!$post['taxi_label'] || !$post['taxi_label_two']) {
                return $err = json_encode(['errCode' => '0', 'msg' => 'success', 'ertips' => '标签不能为空'], 320);
            }

            //添加数据到数据库
            $taxiModel = new TaxiModel();
            $date = $taxiModel->add_taxi($post);

            //执行函数返回值
        }

        //查询出地区
        $taxiModel = new TaxiModel();
        $taxi_class = $taxiModel->select_class();
        //将查出的数据赋值给一个数组
        $region = [];
        foreach ($taxi_class as $k=>$v)
        {
            foreach ($v as $vv)
            {
                $region[] = $vv;
            }
        }
        //获取营业时间
        $taxiModel->

        $date = ['region'=>$region];


        //将数据传至页面
        $this->assign('list',$date);

        return $this->fetch();


//        if($files = request()->file('comment_images')){
//            if(count($_FILES['comment_images']['name']) >= 10){
//                return json_encode($date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'图片不能超过9张'],320);
//            }
//            $aa = uploadImage(
//                $files,
//                '/uploads/taxi/'
//            );
//            //判断图片是否上传成功
//            if(!isset($aa['0'])){
//                return json_encode($date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'图片没有上传成功'],320);
//            }
//            $post['comment_images'] = implode(",", $aa);
//        }



    }

}