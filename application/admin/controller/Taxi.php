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
            'taxi_time_one' => 'require',
            'taxi_time_two' => 'require',
            'taxi_day_one'  => 'require',
            'taxi_day_ones' => 'require',
            'taxi_day_two'  => 'require',
            'taxi_day_twos' => 'require',
            'taxi_address'  => 'require',
            'photo'         => 'require',
        ];
        $message  = [
            'taxi_class.require'      => '地区不能为空',
            'taxi_name.require'       => '名称不能为空',
            'taxi_content.require'    => '介绍不能为空',
            'taxi_content.max'        => '介绍不能过长',
            'taxi_time_one.require'   => '每周开始营业时间不能为空',
            'taxi_time_two.require'   => '每周结束营业时间不能为空',
            'taxi_day_one.require'    => '每天开始营业时间不能为空',
            'taxi_day_ones.require'   => '每天开始营业时间不能为空',
            'taxi_day_two.require'    => '每天结束营业时间不能为空',
            'taxi_day_twos.require'   => '每天结束营业时间不能为空',
            'taxi_phone.require'      => '联系电话不能为空',
            'taxi_address.require'    => '具体地址不能为空',
            'photo.require'           => '上传图片不能为空',
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
            if (!$post['taxi_label'] && !$post['taxi_label_two']) {
                return $err = json_encode(['errCode' => '0', 'msg' => 'success', 'ertips' => '22标签不能为空'], 320);
            }

            //处理标签
            $label = [];
            if($post['taxi_label'] && $post['taxi_label_two']){
                $label = array($post['taxi_label'],$post['taxi_label_two']);
            }else if($post['taxi_label']){
                $label = $post['taxi_label'];
            }else if($post['taxi_label_two']){
                $label = $post['taxi_label_two'];
            }

            $post['taxi_label'] =json_encode($label,320);

            //每周营业时间
            $post['taxi_day'] = $post['taxi_time_one'].'到'.$post['taxi_time_two'];
            //每天营业时间
            $post['taxi_time'] = $post['taxi_day_one'].':'.$post['taxi_day_ones'].'-'.$post['taxi_day_two'].':'.$post['taxi_day_twos'];
            //图片logo
            $post['taxi_logo'] = $post['photo'];

            //添加数据到数据库
            $taxiModel = new TaxiModel();
            $res = $taxiModel->add_taxi($post);


            if($res){
                $arr = ['code'=>1,'msg'=>'添加成功'];
                return  $arr;
            }else{
                return  $arr = ['code'=>2,'msg'=>'添加失败'];
            }
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
        //获取营业时间周，时，分
        $week = $taxiModel->select_week();
        $day= $taxiModel->select_day();
        $minute= $taxiModel->select_minute();

        $date = ['region'=>$region,'week'=>$week,'day'=>$day,'minute'=>$minute];

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

    /**
     * 修改叫车
     */
    public function edit_taxi($id)
    {
        //查询出数据
        $taxiModel = new TaxiModel();
        $data = $taxiModel->find_taxi($id);

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
        $week = $taxiModel->select_week();
        $day= $taxiModel->select_day();
        $minute= $taxiModel->select_minute();


        $data = ['region'=>$region,'week'=>$week,'day'=>$day,'minute'=>$minute,'taxi'=>$data];

        //加载视图
        $this->assign('data',$data);

        // 模板输出
        return $this->fetch('taxi/edit');
    }

    //修改逻辑
    public function update_taxi(\think\Request $request)
    {
        //接收参数
        $post = $request->post();
        $rule =   [
            'taxi_class'    => 'require',
            'taxi_name'     => 'require',
            'taxi_content'  => 'require|max:550',
            'taxi_time_one' => 'require',
            'taxi_time_two' => 'require',
            'taxi_day_one'  => 'require',
            'taxi_day_ones' => 'require',
            'taxi_day_two'  => 'require',
            'taxi_day_twos' => 'require',
            'taxi_address'  => 'require',
        ];
        $message  = [
            'taxi_class.require'      => '地区不能为空',
            'taxi_name.require'       => '名称不能为空',
            'taxi_content.require'    => '介绍不能为空',
            'taxi_content.max'        => '介绍不能过长',
            'taxi_time_one.require'   => '每周开始营业时间不能为空',
            'taxi_time_two.require'   => '每周结束营业时间不能为空',
            'taxi_day_one.require'    => '每天开始营业时间不能为空',
            'taxi_day_ones.require'   => '每天开始营业时间不能为空',
            'taxi_day_two.require'    => '每天结束营业时间不能为空',
            'taxi_day_twos.require'   => '每天结束营业时间不能为空',
            'taxi_phone.require'      => '联系电话不能为空',
            'taxi_address.require'    => '具体地址不能为空',
        ];

        //实例化验证器
        $result = $this->validate($post, $rule, $message);

        //判断有无错误
        if (true !== $result) {
            $date = ['errcode' => 1, 'errMsg' => 'error', 'ertips' => $result];
            // 验证失败 输出错误信息
            return json_encode($date, 320);
        }

        //判断标签是否有值
        if (!$post['taxi_label'] && !$post['taxi_label_two']) {
            return $err = json_encode(['errCode' => '0', 'msg' => 'success', 'ertips' => '标签不能为空'], 320);
        }

        //处理标签
        $label = [];
        if($post['taxi_label'] && $post['taxi_label_two']){
            $label = array($post['taxi_label'],$post['taxi_label_two']);
        }else if($post['taxi_label']){
            $label = $post['taxi_label'];
        }else if($post['taxi_label_two']){
            $label = $post['taxi_label_two'];
        }

        $post['taxi_label'] =json_encode($label,320);

        //每周营业时间
//        $post['taxi_day'] = $post['taxi_time_one'].'到'.$post['taxi_time_two'];
//        //每天营业时间
//        $post['taxi_time'] = $post['taxi_day_one'].':'.$post['taxi_day_ones'].'-'.$post['taxi_day_two'].':'.$post['taxi_day_twos'];
        //图片logo
        $post['taxi_logo'] = $post['photo'];


        //执行修改逻辑
        $data['taxi_logo'] = $post['taxi_logo'];
        $data['taxi_class'] = $post['taxi_class'];
        $data['taxi_name'] = $post['taxi_name'];
        $data['taxi_content'] = $post['taxi_content'];
        $data['taxi_day'] = $post['taxi_time_one'].'到'.$post['taxi_time_two'];
        $data['taxi_time'] = $post['taxi_day_one'].':'.$post['taxi_day_ones'].'-'.$post['taxi_day_two'].':'.$post['taxi_day_two'];
        $data['taxi_phone'] = $post['taxi_phone'];
        $data['taxi_address'] = $post['taxi_address'];
        $data['taxi_label'] = $post['taxi_label'];

        $res = Db::table('think_taxi_list')->where(['taxi_id'=>$post['id']])->update($data);

        if($res){
            return $arr = ['code'=>1,'msg'=>'修改成功'];
        }else{
            return $arr = ['code'=>2,'msg'=>'修改失败'];
        }

    }

    //删除叫车
    public function del_taxi($id)
    {
        //查询有无这条数据
        $data = Db::table('think_taxi_list')->where('taxi_id',$id)->find();

        if(!$data){
            return $arr = ['code'=>2,'msg'=>'没有这条数据'];
        }

        $data['exits_status'] = time();
        $res = Db::table('think_taxi_list')
            ->where(['taxi_id'=>$id])
            ->update($data);

        if($res){
            return $arr = ['code'=>1,'msg'=>'删除成功'];
        }else{
            return $arr = ['code'=>2,'msg'=>'删除失败'];
        }
    }

    //修改状态
    public function status_taxi($id)
    {
        //判断有无这条信息
        $data = Db::name('taxi_list')->where('taxi_id',$id)->find();

        if(!$data){
            return $arr = ['code'=>3,'msg'=>'没有这条数据'];
        }
        //判断当前状态
        if($data['taxi_status'] == 0){
            //查询推荐总数是否大于4 大于4则不能再推荐
            $count = Db::name('taxi_list')->where('taxi_status',0)->count();
            if($count >= 4){
                 $arr = ['code'=>3,'msg'=>'不能在推荐'];
//                return json_encode($arr,320);
                return $arr;
            }
            //修改推荐状态为不推荐
            $res = Db::name('taxi_list')
                ->update([
                    'taxi_status'   =>1,
                    'taxi_id'      =>$id
                ]);

            if($res){
                return $arr = ['code'=>1,'msg'=>'未推荐'];
            }else{
                return $arr = ['code'=>2,'msg'=>'已推荐'];
            }

        }else{
            //修改推荐状态为推荐
            $res = Db::name('taxi_list')
                ->update([
                    'taxi_status'   =>0,
                    'taxi_id'      =>$id
                ]);
            if($res){
                return $arr = ['code'=>2,'msg'=>'已推荐'];
            }else{
                return $arr = ['code'=>1,'msg'=>'未推荐'];
            }
        }

    }

    //管理详情图片
    public function detailed_taxi($id)
    {
        //根据id查询出详情图
        $list = Db::table('think_taxi_img')->where('taxi_id',1)->select();

        print_r($list);die;
        $data = ['list'=>$list,];
        //加载视图
        $this->assign('list',$data);
        return $this->fetch('taxi/detailed');
    }



}