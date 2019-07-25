<?php
namespace app\admin\controller;

use app\admin\model\HotelModel;
use think\Config;
use think\Controller;
use think\Db;

class Hotel extends Controller
{
    //查询酒店列表接口
    public function index()
    {
        //执行查询操作
        $list= Db::table('think_hotel_list')
            ->where('exits_status',0)
            ->paginate(10);

        //统计多少数据
        $count= Db::table('think_hotel_list')
            ->where('exits_status',0)
            ->select();

        $count = count($list);
        $date = ['list'=>$list,'count'=>$count];

        //将数据传至页面
        $this->assign('list',$date);
        return $this->fetch();
    }

    //添加酒店接口
    public function add(\think\Request $request)
    {
        //获取参数
        $post = $request->post();

        $rule =   [
            'hotel_class'       => 'require',
            'hotel_name'        => 'require',
            'hotel_content'     => 'require|max:550',
            'taxi_time_one'     => 'require',
            'taxi_time_two'     => 'require',
            'taxi_day_one'      => 'require',
            'taxi_day_ones'     => 'require',
            'taxi_day_two'      => 'require',
            'taxi_day_twos'     => 'require',
            'hotel_address'     => 'require',
        ];
        $message  = [
            'hotel_class.require'       => '地区不能为空',
            'hotel_name.require'        => '名称不能为空',
            'hotel_content.require'     => '介绍不能为空',
            'hotel_content.max'         => '介绍不能过长',
            'taxi_time_one.require'     => '每周开始营业时间不能为空',
            'taxi_time_two.require'     => '每周结束营业时间不能为空',
            'taxi_day_one.require'      => '每天开始营业时间不能为空',
            'taxi_day_ones.require'     => '每天开始营业时间不能为空',
            'taxi_day_two.require'      => '每天结束营业时间不能为空',
            'taxi_day_twos.require'     => '每天结束营业时间不能为空',
            'hotel_phone.require'       => '联系电话不能为空',
            'hotel_address.require'     => '具体地址不能为空',
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
            $post['hotel_label'] =json_encode($label,320);

            //每周营业时间
            $post['hotel_day'] = $post['taxi_time_one'].'到'.$post['taxi_time_two'];
            //每天营业时间
            $post['hotel_time'] = $post['taxi_day_one'].':'.$post['taxi_day_ones'].'-'.$post['taxi_day_two'].':'.$post['taxi_day_twos'];
            //图片logo
            $post['hotel_logo'] = $post['photo'];

            //添加数据到数据库
            $hotelModel = new HotelModel();
            $res = $hotelModel->add_taxi($post);

            if($res){
                $arr = ['code'=>1,'msg'=>'添加成功'];
                return $arr;
            }else{
                $arr = ['code'=>2,'msg'=>'添加失败'];
                return $arr;
            }
        }

        //查询出地区
        $hotelModel = new HotelModel();
        $taxi_class = $hotelModel->select_class();
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
        $week = $hotelModel->select_week();
        $day= $hotelModel->select_day();
        $minute= $hotelModel->select_minute();

        $date = ['region'=>$region,'week'=>$week,'day'=>$day,'minute'=>$minute];

        //将数据传至页面
        $this->assign('list',$date);

        return $this->fetch();
    }

    //编辑酒店接口
    public function edit($id)
    {

        //查询出数据
        $hotelModel = new HotelModel();
        $data = $hotelModel->find_taxi($id);

        $taxi_class = $hotelModel->select_class();
        //将查出的数据赋值给一个数组
        $region = [];
        foreach ($taxi_class as $k => $v) {
            foreach ($v as $vv) {
                $region[] = $vv;
            }
        }

        $week = $hotelModel->select_week();
        $day = $hotelModel->select_day();
        $minute = $hotelModel->select_minute();

        $data = ['region' => $region, 'week' => $week, 'day' => $day, 'minute' => $minute, 'taxi' => $data];

        //加载视图
        $this->assign('data', $data);
        // 模板输出
        return $this->fetch('hotel/edit');
    }

    //修改逻辑
    public function update_hotel(\think\Request $request)
    {
        //接收参数
        $post = $request->post();

        $rule =   [
            'hotel_class'       => 'require',
            'hotel_name'        => 'require',
            'hotel_content'     => 'require|max:550',
            'taxi_time_one'     => 'require',
            'taxi_time_two'     => 'require',
            'taxi_day_one'      => 'require',
            'taxi_day_ones'     => 'require',
            'taxi_day_two'      => 'require',
            'taxi_day_twos'     => 'require',
            'hotel_address'     => 'require',
        ];
        $message  = [
            'hotel_class.require'       => '地区不能为空',
            'hotel_name.require'        => '名称不能为空',
            'hotel_content.require'     => '介绍不能为空',
            'hotel_content.max'         => '介绍不能过长',
            'taxi_time_one.require'     => '每周开始营业时间不能为空',
            'taxi_time_two.require'     => '每周结束营业时间不能为空',
            'taxi_day_one.require'      => '每天开始营业时间不能为空',
            'taxi_day_ones.require'     => '每天开始营业时间不能为空',
            'taxi_day_two.require'      => '每天结束营业时间不能为空',
            'taxi_day_twos.require'     => '每天结束营业时间不能为空',
            'hotel_phone.require'       => '联系电话不能为空',
            'hotel_address.require'     => '具体地址不能为空',
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

        $post['hotel_label'] =json_encode($label,320);

        //每周营业时间
//        $post['taxi_day'] = $post['taxi_time_one'].'到'.$post['taxi_time_two'];
//        //每天营业时间
//        $post['taxi_time'] = $post['taxi_day_one'].':'.$post['taxi_day_ones'].'-'.$post['taxi_day_two'].':'.$post['taxi_day_twos'];
        //图片logo
        $post['hotel_logo'] = $post['photo'];

        //执行修改逻辑
        $data['hotel_logo'] = $post['hotel_logo'];
        $data['hotel_class'] = $post['hotel_class'];
        $data['hotel_name'] = $post['hotel_name'];
        $data['hotel_content'] = $post['hotel_content'];
        $data['hotel_day'] = $post['taxi_time_one'].'到'.$post['taxi_time_two'];
        $data['hotel_time'] = $post['taxi_day_one'].':'.$post['taxi_day_ones'].'-'.$post['taxi_day_two'].':'.$post['taxi_day_two'];
        $data['hotel_phone'] = $post['hotel_phone'];
        $data['hotel_address'] = $post['hotel_address'];
        $data['hotel_label'] = $post['hotel_label'];

        $res = Db::table('think_hotel_list')->where(['hotel_id'=>$post['id']])->update($data);

        if($res){
            return $arr = ['code'=>1,'msg'=>'修改成功'];
        }else{
            return $arr = ['code'=>2,'msg'=>'修改失败'];
        }

    }

    /**
     * 删除酒店
     */
    public function del_hotel($id)
    {
        //查询有无这条数据
        $data = Db::table('think_hotel_list')->where('hotel_id',$id)->find();

        if(!$data){
            return $arr = ['code'=>2,'msg'=>'没有这条数据'];
        }

        $data['exits_status'] = time();
        $res = Db::table('think_hotel_list')
            ->where(['hotel_id'=>$id])
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
    public function status_hotel($id)
    {
        //判断有无这条信息
        $data = Db::name('hotel_list')->where('hotel_id',$id)->find();

        if(!$data){
            return $arr = ['code'=>3,'msg'=>'没有这条数据'];
        }
        //判断当前状态
        if($data['hotel_status'] == 0){
            //查询推荐总数是否大于4 大于4则不能再推荐
            $count = Db::name('hotel_list')->where('hotel_status',0)->count();
//            if($count >= 4){
//                $arr = ['code'=>3,'msg'=>'不能在推荐'];
//
//                return $arr;
//            }
            //修改推荐状态为不推荐
            $res = Db::name('hotel_list')
                ->update([
                    'hotel_status'   =>1,
                    'hotel_id'      =>$id
                ]);

            if($res){
                return $arr = ['code'=>1,'msg'=>'未推荐'];
            }else{
                return $arr = ['code'=>2,'msg'=>'已推荐'];
            }

        }else{
            //修改推荐状态为推荐
            $res = Db::name('hotel_list')
                ->update([
                    'hotel_status'   =>0,
                    'hotel_id'      =>$id
                ]);
            if($res){
                return $arr = ['code'=>2,'msg'=>'已推荐'];
            }else{
                return $arr = ['code'=>1,'msg'=>'未推荐'];
            }
        }

    }

    //酒店详情列表
    public function detailed_hotel($id)
    {
        //执行查询操作
        $list= Db::table('think_hotel_img')
            ->where('img_status',0)
            ->paginate(10);

        //统计多少数据
        $count= Db::table('think_hotel_img')
            ->where('img_status',0)
            ->select();

        $count = count($list);
        $date = ['list'=>$list,'count'=>$count];
        //将数据传至页面
        $this->assign('list',$date);
        return $this->fetch('hotel/detailed');
    }

    //添加详情图片
    public function add_detailed($id)
    {

        //加载视图
        $this->assign('data', $id);
        // 模板输出
        return $this->fetch('hotel/add_detailed');
    }

    //接收详情图片
    public function store(\think\Request $request)
    {
        $post = $request->post();

        $rule =   [
            'hotel_id'  => 'require',
        ];
        $message  = [
            'hotel_id.require'      => '酒店ID不能为空',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }
        $date = ['hotel_id'=>$post['hotel_id'],'hotel_images'=>$post['photo'],'img_status'=>0];
        //执行添加操作
        $res = Db::table('think_hotel_img')->insert($date);

        if($res){
            return $arr = ['code'=>1,'msg'=>'添加成功'];
        }else{
            return $arr = ['code'=>2,'msg'=>'添加失败'];
        }

        //加载视图
        $this->assign('data');
        // 模板输出
        return $this->fetch('hotel/add_detailed');
    }

    //修改详情图片
    public function edit_detailed($id)
    {
        if(!$id){
            return $arr = ['code'=>'2','msg'=>'修改失败'];
        }

        //查询出这条数据(图片)
        $data = Db::table('think_hotel_img')->where('hotel_img_id',$id)->find();

        //加载视图
        $this->assign('data', $data);
        // 模板输出
        return $this->fetch('hotel/edit_detailed');
    }

    //接收修改
    public function update_detailed(\think\Request $request)
    {
        $post = $request->post();
        dump($post);die;
    }





}