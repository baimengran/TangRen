<?php

namespace app\admin\controller;

use app\admin\model\TaxiModel;
use app\admin\model\TurnsModel;
use think\Controller;
use think\Db;

class Taxi extends Base
{
    /**
     * 叫车列表
     */
    public function index()
    {

        $key = input('key');
        $list = Db::table('think_taxi_list')
            ->where('exits_status', 0);
        if($key){
            $list = $list->where('taxi_name','like','%'.$key.'%');
        }
        //执行查询操作

           $list = $list ->order('taxi_status')
            ->paginate(20);

        //统计多少数据
        $count = Db::table('think_taxi_list')
            ->where('exits_status', 0)
            ->select();

        $count = count($list);
        $date = ['list' => $list, 'count' => $count];

        //将数据传至页面
        $this->assign('list', $date);
        return $this->fetch();
    }

    /**
     * 添加叫车
     */
    public function add_taxi(\think\Request $request)
    {
        //获取参数
        $post = $request->post();

        $rule = [
            'taxi_class' => 'require',
            'taxi_name' => 'require',
            'taxi_content' => 'require|max:550',
            'taxi_time_one' => 'require',
            'taxi_time_two' => 'require',
            'taxi_day_one' => 'require',
            'taxi_day_ones' => 'require',
            'taxi_day_two' => 'require',
            'taxi_day_twos' => 'require',
            'taxi_phone'=>'require',
            'taxi_address' => 'require',
            'photo' => 'require',

        ];
        $message = [
            'taxi_class.require' => '地区不能为空',
            'taxi_name.require' => '名称不能为空',
            'taxi_content.require' => '介绍不能为空',
            'taxi_content.max' => '介绍不能过长',
            'taxi_time_one.require' => '每周开始营业时间不能为空',
            'taxi_time_two.require' => '每周结束营业时间不能为空',
            'taxi_day_one.require' => '每天开始营业时间不能为空',
            'taxi_day_ones.require' => '每天开始营业时间不能为空',
            'taxi_day_two.require' => '每天结束营业时间不能为空',
            'taxi_day_twos.require' => '每天结束营业时间不能为空',
            'taxi_phone.require' => '联系电话不能为空',
            'taxi_address.require' => '具体地址不能为空',
            'photo.require' => '请上传图片',
        ];
        if (!empty($post)) {

            //实例化验证器
            $result = $this->validate($post, $rule, $message);

            //判断有无错误
            if (true !== $result) {
                $date = ['code' => 0, 'errMsg' => 'error', 'msg' => $result];
                // 验证失败 输出错误信息
                return json($date);
            }

            //判断标签是否有值
            if (!$post['taxi_label'] && !$post['taxi_label_two']) {
                return $err = json(['code' => '0', 'msg' => '标签不能为空']);
            }

            //处理标签
            $label = [];
            if ($post['taxi_label'] && $post['taxi_label_two']) {
                $label = array($post['taxi_label'], $post['taxi_label_two']);
            } else if ($post['taxi_label']) {
                $label = $post['taxi_label'];
            } else if ($post['taxi_label_two']) {
                $label = $post['taxi_label_two'];
            }
            $post['taxi_label'] = json_encode($label,320);

            //每周营业时间
            $post['taxi_day'] = $post['taxi_time_one'] . '到' . $post['taxi_time_two'];
            //每天营业时间
            $post['taxi_time'] = $post['taxi_day_one'] . ':' . $post['taxi_day_ones'] . '-' . $post['taxi_day_two'] . ':' . $post['taxi_day_twos'];
            //图片logo
            $post['taxi_logo'] = $post['photo'];

            //添加数据到数据库
            $taxiModel = new TaxiModel();
            $res = $taxiModel->add_taxi($post);

            if ($res) {
                $arr = ['code' => 1, 'msg' => '添加成功'];
                return $arr;
            } else {
                return $arr = ['code' => 2, 'msg' => '添加失败'];
            }
        }

        //查询出地区
        $taxiModel = new TaxiModel();
        $taxi_class = $taxiModel->select_class();
        //将查出的数据赋值给一个数组
        $region = [];
        foreach ($taxi_class as $k => $v) {
            foreach ($v as $vv) {
                $region[] = $vv;
            }
        }
        //获取营业时间周，时，分
        $week = $taxiModel->select_week();
        $day = $taxiModel->select_day();
        $minute = $taxiModel->select_minute();

        $date = ['region' => $region, 'week' => $week, 'day' => $day, 'minute' => $minute];

        //将数据传至页面
        $this->assign('list', $date);

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
        foreach ($taxi_class as $k => $v) {
            foreach ($v as $vv) {
                $region[] = $vv;
            }
        }
        $week = $taxiModel->select_week();
        $day = $taxiModel->select_day();
        $minute = $taxiModel->select_minute();
        //正则规则匹配中文英文额下划线
        $reg = '/[\x{4e00}-\x{9fa5}0-9a-zA-Z_]/u';
        //将字符串转数组
        $label = explode(',', $data['taxi_label']);
        $str = [];
        foreach ($label as $k => $v) {
            //去掉[]和“”
            preg_match_all($reg, $label[$k], $str[$k]);
        }

        foreach ($label as $k => $v) {
            //组装数据
            $data['label' . $k] = implode('', $str[$k][0]);
        }
        if (!array_key_exists('label0', $data)) {
            $data['label0'] = null;
        }
        if (!array_key_exists('label1', $data)) {
            $data['label1'] = null;
        }
        $data = ['region' => $region, 'week' => $week, 'day' => $day, 'minute' => $minute, 'taxi' => $data];

        //加载视图
        $this->assign('data', $data);

        // 模板输出
        return $this->fetch('taxi/edit');
    }

    /**
     * 修改逻辑
     */
    public function update_taxi(\think\Request $request)
    {
        //接收参数
        $post = $request->post();
        $rule = [
            'taxi_class' => 'require',
            'taxi_name' => 'require',
            'taxi_content' => 'require|max:550',
            'taxi_time_one' => 'require',
            'taxi_time_two' => 'require',
            'taxi_day_one' => 'require',
            'taxi_day_ones' => 'require',
            'taxi_day_two' => 'require',
            'taxi_day_twos' => 'require',
            'taxi_phone'=>'require',
            'taxi_address' => 'require',
            'photo'=>'require'
        ];
        $message = [
            'taxi_class.require' => '地区不能为空',
            'taxi_name.require' => '名称不能为空',
            'taxi_content.require' => '介绍不能为空',
            'taxi_content.max' => '介绍不能过长',
            'taxi_time_one.require' => '每周开始营业时间不能为空',
            'taxi_time_two.require' => '每周结束营业时间不能为空',
            'taxi_day_one.require' => '每天开始营业时间不能为空',
            'taxi_day_ones.require' => '每天开始营业时间不能为空',
            'taxi_day_two.require' => '每天结束营业时间不能为空',
            'taxi_day_twos.require' => '每天结束营业时间不能为空',
            'taxi_phone.require' => '联系电话不能为空',
            'taxi_address.require' => '具体地址不能为空',
            'photo.require'=>'请上传图片'
        ];

        //实例化验证器
        $result = $this->validate($post, $rule, $message);

        //判断有无错误
        if (true !== $result) {
            $date = ['code' => 0, 'errMsg' => 'error', 'msg' => $result];
            // 验证失败 输出错误信息
            return json($date);
        }

        //判断标签是否有值
        if (!$post['taxi_label'] && !$post['taxi_label_two']) {
            return json(['code' => '0', 'msg' => '标签不能为空']);
        }

        //处理标签
        $label = [];
        if ($post['taxi_label'] && $post['taxi_label_two']) {
            $label = array($post['taxi_label'], $post['taxi_label_two']);
        } else if ($post['taxi_label']) {
            $label = $post['taxi_label'];
        } else if ($post['taxi_label_two']) {
            $label = $post['taxi_label_two'];
        }

        $post['taxi_label'] = json_encode($label, 320);

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
        $data['taxi_day'] = $post['taxi_time_one'] . '到' . $post['taxi_time_two'];
        $data['taxi_time'] = $post['taxi_day_one'] . ':' . $post['taxi_day_ones'] . '-' . $post['taxi_day_two'] . ':' . $post['taxi_day_two'];
        $data['taxi_phone'] = $post['taxi_phone'];
        $data['taxi_address'] = $post['taxi_address'];
        $data['taxi_label'] = $post['taxi_label'];
        $res = Db::table('think_taxi_list')->where(['taxi_id' => $post['id']])->update($data);
        if ($res) {
            return $arr = ['code' => 1, 'msg' => '修改成功'];
        } else {
            return $arr = ['code' => 2, 'msg' => '修改失败'];
        }

    }

    /**
     * 删除叫车
     */
    public function del_taxi($id)
    {
        //查询有无这条数据
        $data = Db::table('think_taxi_list')->where('taxi_id', $id)->find();

        if (!$data) {
            return $arr = ['code' => 2, 'msg' => '没有这条数据'];
        }

        $data['exits_status'] = time();
        $res = Db::table('think_taxi_list')
            ->where(['taxi_id' => $id])
            ->update($data);

        if ($res) {
            return $arr = ['code' => 1, 'msg' => '删除成功'];
        } else {
            return $arr = ['code' => 2, 'msg' => '删除失败'];
        }
    }

    /**
     * 修改状态
     */
    public function status_taxi($id)
    {

        //判断有无这条信息
        $data = Db::name('taxi_list')
            ->where('exits_status',0)
            ->where('taxi_id', $id)->find();

        if (!$data) {
            return $arr = ['code' => 3, 'msg' => '没有这条数据'];
        }
        //判断当前状态
        if ($data['taxi_status'] == 1) {
            //查询推荐总数是否大于4 大于4则不能再推荐
            $count = Db::name('taxi_list')
                ->where('exits_status',0)
                ->where('taxi_status', 0)->count();
            if ($count >= 4) {
                $arr = ['code' => 3, 'msg' => '不能再推荐'];
//                return json_encode($arr,320);
                return $arr;
            }
            //修改推荐状态为不推荐
            $res = Db::name('taxi_list')
                ->update([
                    'taxi_status' => 0,
                    'taxi_id' => $id
                ]);

            if ($res) {
                return $arr = ['code' => 2, 'msg' => '已推荐'];
            } else {
                return $arr = ['code' => 1, 'msg' => '未推荐'];
            }

        } else {
            //修改推荐状态为推荐
            $res = Db::name('taxi_list')
                ->update([
                    'taxi_status' => 1,
                    'taxi_id' => $id
                ]);
            if ($res) {
                return $arr = ['code' => 1, 'msg' => '未推荐'];
            } else {
                return $arr = ['code' => 2, 'msg' => '已推荐'];
            }
        }

    }

    /**
     * 叫车详情列表
     */
    public function detailed_hotel($id)
    {

        //执行查询操作
        $list = Db::table('think_taxi_img')
            ->where('taxi_id', $id)
            ->where('img_status', 0)
            ->paginate(10);

        //统计多少数据
        $count = Db::table('think_taxi_img')
            ->where('img_status', 0)
            ->select();

        $count = count($list);
        $date = ['list' => $list, 'count' => $count];
        //将数据传至页面
        $this->assign('list', $date);
        $this->assign('id', $id);
        return $this->fetch('taxi/detailed');
    }

    /**
     * 添加叫车详情
     */
    public function add_detailed($id)
    {
        //加载视图
        $this->assign('data', $id);
        // 模板输出
        return $this->fetch('taxi/add_detailed');
    }

    /**
     * 接收叫车详情
     */
    public function store(\think\Request $request)
    {
        $post = $request->post();

        $rule = [
            'taxi_id' => 'require',
            'photo'=>'require'
        ];
        $message = [
            'taxi_id.require' => '叫车ID不能为空',
            'photo'=>'请上传图片'
        ];

        //实例化验证器
        $result = $this->validate($post, $rule, $message);

        //判断有无错误
        if (true !== $result) {
            $date = ['code' => 0, 'msg' =>  $result];
            // 验证失败 输出错误信息
            return json($date);
        }
        $date = ['taxi_id' => $post['taxi_id'], 'taxi_images' => $post['photo'], 'img_status' => 0];
        //执行添加操作
        $res = Db::table('think_taxi_img')->insert($date);

        if ($res) {
            return $arr = ['code' => 1, 'msg' => '添加成功', 'id' => $post['taxi_id']];
        } else {
            return $arr = ['code' => 2, 'msg' => '添加失败', 'id' => $post['taxi_id']];
        }

        //加载视图
        $this->assign('data');
        // 模板输出
        return $this->fetch('taxi/add_detailed');
    }

    /**
     * 修改叫车详情
     */
    public function edit_detailed($id,$taxi_id)
    {
        if (!$id) {
            return $arr = ['code' => '2', 'msg' => '修改失败'];
        }
        //查询出这条数据(图片)
        $data = Db::table('think_taxi_img')->where('taxi_img_id', $id)->find();

        //加载视图
        $this->assign('data',$data);
        $this->assign('id',$taxi_id);
        // 模板输出
        return $this->fetch('taxi/edit_detailed');
    }

    /**
     * 修改叫车详情逻辑
     */
    public function update_detailed(\think\Request $request)
    {
        $post = $request->post();

        $res = Db::name('taxi_img')
            ->update([
                'taxi_images' => $post['photo'],
                'taxi_img_id' => $post['taxi_img_id']
            ]);

        if ($res) {
            return $arr = ['code' => '1', 'msg' => '修改成功', 'id' => $post['taxi_img_id'],'taxi_id'=>$post['id']];
        } else {
            return $arr = ['code' => '2', 'msg' => '修改失败', 'id' => $post['taxi_img_id'],'taxi_id'=>$post['id']];
        }
    }

    /**
     * 删除叫车详情
     */
    public function del_detailed($id)
    {
        //判断有无这个数据
        $res = Db::table('think_taxi_img')->where('taxi_img_id', $id)->find();

        if (!$res) {
            return $arr = ['code' => '2', 'msg' => '删除失败'];
        }

        //执行删除
        $res = Db::name('taxi_img')
            ->update([
                'img_status' => 1,
                'taxi_img_id' => $id
            ]);

        if ($res) {
            return json(['code' => '1', 'msg' => '删除成功']);
        } else {
            return json(['code' => '2', 'msg' => '删除失败']);
        }


    }

}