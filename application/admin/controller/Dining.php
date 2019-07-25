<?php
namespace app\admin\controller;

use app\admin\model\DiningModel;
use think\Controller;
use think\Db;

class Dining extends Controller
{
    //美食列表
    public function index()
    {
        //执行查询操作
        $list= Db::table('think_dining_list')
            ->where('exits_status',0)
            ->paginate(10);

        //统计多少数据
        $count= Db::table('think_dining_list')
            ->where('exits_status',0)
            ->select();

        $count = count($list);
        $date = ['list'=>$list,'count'=>$count];

        //将数据传至页面
        $this->assign('list',$date);
        return $this->fetch();
    }

    //添加数据操作
    public function add_dining(\think\Request $request)
    {
        //获取参数
        $post = $request->post();

        $rule =   [
            'dining_class'    => 'require',
            'dining_name'     => 'require',
            'dining_content'  => 'require|max:550',
            'taxi_time_one' => 'require',
            'taxi_time_two' => 'require',
            'taxi_day_one'  => 'require',
            'taxi_day_ones' => 'require',
            'taxi_day_two'  => 'require',
            'taxi_day_twos' => 'require',
            'dining_address'  => 'require',
        ];
        $message  = [
            'dining_class.require'      => '地区不能为空',
            'dining_name.require'       => '名称不能为空',
            'dining_content.require'    => '介绍不能为空',
            'dining_content.max'        => '介绍不能过长',
            'taxi_time_one.require'   => '每周开始营业时间不能为空',
            'taxi_time_two.require'   => '每周结束营业时间不能为空',
            'taxi_day_one.require'    => '每天开始营业时间不能为空',
            'taxi_day_ones.require'   => '每天开始营业时间不能为空',
            'taxi_day_two.require'    => '每天结束营业时间不能为空',
            'taxi_day_twos.require'   => '每天结束营业时间不能为空',
            'dining_phone.require'      => '联系电话不能为空',
            'dining_address.require'    => '具体地址不能为空',
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
            $post['dining_label'] =json_encode($label,320);

            //每周营业时间
            $post['dining_day'] = $post['taxi_time_one'].'到'.$post['taxi_time_two'];
            //每天营业时间
            $post['dining_time'] = $post['taxi_day_one'].':'.$post['taxi_day_ones'].'-'.$post['taxi_day_two'].':'.$post['taxi_day_twos'];
            //图片logo
            $post['dining_logo'] = $post['photo'];

            //添加数据到数据库
            $diningModel = new DiningModel();
            $res = $diningModel->add_taxi($post);

            if($res){
                $arr = ['code'=>1,'msg'=>'添加成功'];
                return $arr;
            }else{
                $arr = ['code'=>2,'msg'=>'添加失败'];
                return json_encode($arr,320);
            }
        }

        //查询出地区
        $diningModel = new DiningModel();
        $taxi_class = $diningModel->select_class();
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
        $week = $diningModel->select_week();
        $day= $diningModel->select_day();
        $minute= $diningModel->select_minute();

        $date = ['region'=>$region,'week'=>$week,'day'=>$day,'minute'=>$minute];

        //将数据传至页面
        $this->assign('list',$date);

        return $this->fetch();
    }

    /**
     * 修改美食
     */
    public function edit($id)
    {
        //查询出数据
        $diningModel = new DiningModel();
        $data = $diningModel->find_taxi($id);

        $taxi_class = $diningModel->select_class();
        //将查出的数据赋值给一个数组
        $region = [];
        foreach ($taxi_class as $k=>$v)
        {
            foreach ($v as $vv)
            {
                $region[] = $vv;
            }
        }

        $week = $diningModel->select_week();
        $day= $diningModel->select_day();
        $minute= $diningModel->select_minute();

        $data = ['region'=>$region,'week'=>$week,'day'=>$day,'minute'=>$minute,'taxi'=>$data];

        //加载视图
        $this->assign('data',$data);

        // 模板输出
        return $this->fetch('dining/edit');
    }

    //修改逻辑
    public function update_dining(\think\Request $request)
    {
        //接收参数
        $post = $request->post();

        $rule =   [
            'dining_class'    => 'require',
            'dining_name'     => 'require',
            'dining_content'  => 'require|max:550',
            'taxi_time_one' => 'require',
            'taxi_time_two' => 'require',
            'taxi_day_one'  => 'require',
            'taxi_day_ones' => 'require',
            'taxi_day_two'  => 'require',
            'taxi_day_twos' => 'require',
            'dining_address'  => 'require',
            'photo'           => 'require',
        ];
        $message  = [
            'dining_class.require'      => '地区不能为空',
            'dining_name.require'       => '名称不能为空',
            'dining_content.require'    => '介绍不能为空',
            'dining_content.max'        => '介绍不能过长',
            'taxi_time_one.require'   => '每周开始营业时间不能为空',
            'taxi_time_two.require'   => '每周结束营业时间不能为空',
            'taxi_day_one.require'    => '每天开始营业时间不能为空',
            'taxi_day_ones.require'   => '每天开始营业时间不能为空',
            'taxi_day_two.require'    => '每天结束营业时间不能为空',
            'taxi_day_twos.require'   => '每天结束营业时间不能为空',
            'dining_phone.require'      => '联系电话不能为空',
            'dining_address.require'    => '具体地址不能为空',
            'photo.require'             => '上传图片不能为空',
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
        if (!$post['taxi_label'] && !$post['taxi_label_two'] && !$post['taxi_label_three']) {
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

        $post['dining_label'] =json_encode($label,320);

        //每周营业时间
//        $post['taxi_day'] = $post['taxi_time_one'].'到'.$post['taxi_time_two'];
//        //每天营业时间
//        $post['taxi_time'] = $post['taxi_day_one'].':'.$post['taxi_day_ones'].'-'.$post['taxi_day_two'].':'.$post['taxi_day_twos'];
        //图片logo
        $post['dining_logo'] = $post['photo'];

        //执行修改逻辑
        $data['dining_logo'] = $post['dining_logo'];
        $data['dining_class'] = $post['dining_class'];
        $data['dining_name'] = $post['dining_name'];
        $data['dining_content'] = $post['dining_content'];
        $data['dining_day'] = $post['taxi_time_one'].'到'.$post['taxi_time_two'];
        $data['dining_time'] = $post['taxi_day_one'].':'.$post['taxi_day_ones'].'-'.$post['taxi_day_two'].':'.$post['taxi_day_two'];
        $data['dining_phone'] = $post['dining_phone'];
        $data['dining_address'] = $post['dining_address'];
        $data['dining_label'] = $post['dining_label'];

        $res = Db::table('think_dining_list')->where(['dining_id'=>$post['id']])->update($data);

        if($res){
            return $arr = ['code'=>1,'msg'=>'修改成功'];
        }else{
            return $arr = ['code'=>2,'msg'=>'修改失败'];
        }

    }

    /**
     * 删除美食
     */
    public function del_dining($id)
    {
        //查询有无这条数据
        $data = Db::table('think_dining_list')->where('dining_id',$id)->find();

        if(!$data){
            return $arr = ['code'=>2,'msg'=>'没有这条数据'];
        }

        $data['exits_status'] = time();
        $res = Db::table('think_dining_list')
            ->where(['dining_id'=>$id])
            ->update($data);

        if($res){
            return $arr = ['code'=>1,'msg'=>'删除成功'];
        }else{
            return $arr = ['code'=>2,'msg'=>'删除失败'];
        }
    }

    /**
     * 修改推荐状态
     */
    public function status_dining($id)
    {
        //判断有无这条信息
        $data = Db::name('dining_list')->where('dining_id',$id)->find();

        if(!$data){
            return $arr = ['code'=>3,'msg'=>'没有这条数据'];
        }
        //判断当前状态
        if($data['dining_status'] == 0){
            //查询推荐总数是否大于4 大于4则不能再推荐
            $count = Db::name('dining_list')->where('dining_status',0)->count();
//            if($count >= 4){
//                $arr = ['code'=>3,'msg'=>'不能在推荐'];
//
//                return $arr;
//            }
            //修改推荐状态为不推荐
            $res = Db::name('dining_list')
                ->update([
                    'dining_status'   =>1,
                    'dining_id'      =>$id
                ]);

            if($res){
                return $arr = ['code'=>1,'msg'=>'未推荐'];
            }else{
                return $arr = ['code'=>2,'msg'=>'已推荐'];
            }

        }else{
            //修改推荐状态为推荐
            $res = Db::name('dining_list')
                ->update([
                    'dining_status'   =>0,
                    'dining_id'      =>$id
                ]);
            if($res){
                return $arr = ['code'=>2,'msg'=>'已推荐'];
            }else{
                return $arr = ['code'=>1,'msg'=>'未推荐'];
            }
        }

    }

    /**
     * 修改首页推荐状态
     */
    public function status_home($id)
    {
        //判断有无这条信息
        $data = Db::name('dining_list')->where('dining_id',$id)->find();

        if(!$data){
            return $arr = ['code'=>3,'msg'=>'没有这条数据'];
        }
        //判断当前状态
        if($data['dining_home'] == 0){
            //查询推荐总数是否大于4 大于4则不能再推荐
            $count = Db::name('dining_list')->where('dining_status',0)->count();
//            if($count >= 4){
//                $arr = ['code'=>3,'msg'=>'不能在推荐'];
//
//                return $arr;
//            }
            //修改推荐状态为不推荐
            $res = Db::name('dining_list')
                ->update([
                    'dining_home'   =>1,
                    'dining_id'      =>$id
                ]);

            if($res){
                return $arr = ['code'=>1,'msg'=>'未推荐'];
            }else{
                return $arr = ['code'=>2,'msg'=>'已推荐'];
            }

        }else{
            //修改推荐状态为推荐
            $res = Db::name('dining_list')
                ->update([
                    'dining_home'   =>0,
                    'dining_id'      =>$id
                ]);
            if($res){
                return $arr = ['code'=>2,'msg'=>'已推荐'];
            }else{
                return $arr = ['code'=>1,'msg'=>'未推荐'];
            }
        }

    }


}