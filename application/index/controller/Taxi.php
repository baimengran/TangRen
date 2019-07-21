<?php
namespace app\index\controller;

use app\index\model\TaxicommentModel;
use app\index\model\TaxiModel;
use think\Controller;
use think\Db;

class Taxi extends Controller
{
    /**
     * 叫车首页接口
     * 接收：区域名称
     * 返回：叫车首页所有可见信息(品质优选，区域分类，) 注：叫车评论只显示最新5条记录
     */
    public function index(\think\Request $request)
    {
        //接收参数
        $get = $request->get('region_name');
        $search = $request->get('taxi_content');
        //实例化模型
        $TaxiModel = new TaxiModel();

        if($search){
            $res = Db::table('think_taxi_list')
                ->where('taxi_content', 'like', '%' . $search . '%')
                ->select();

            return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$res],320);
        }
        //查出一个默认地区分类
        $address = $TaxiModel->address();

        if($address['errMsg'] == 'error'){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'没有查到区域'];
            return json_encode($date,320);
        }

        $get = !empty($get) ? $get : $address['retData']['region_name'];

        //最多获取4个品质精选
        $elect = $TaxiModel->select();

        //获取区域分类下的汽车公司
        $taxi = $TaxiModel->taxi($get);

        $date = ['elect'=>$elect,'taxi'=>$taxi];

        //将数据返回出去
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'叫车首页信息查询成功','retData'=>$date],320);

    }

    /**
     * 叫车详情接口
     * 接收：酒店ID   taxi_id
     * 返回：汽车公司详情所有可见信息 注：酒店评论只显示最新5条记录
     */
    public function details(\think\Request $request)
    {

        //接收参数
        $post = $request->post();

        $rule =   [
            'taxi_id'               => 'require|number',
        ];
        $message  = [
            'taxi_id.require'       => '汽车公司ID不能为空',
            'taxi_id.number'        => '汽车公司ID类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //查汽车公司信息表
        $taxi = Db::table('think_taxi_list')
            ->where('taxi_id',$post['taxi_id'])
            ->select();

        //查询餐厅标签字段
        $taxi_label = Db::table('think_taxi_list')
            ->field('taxi_label')
            ->where('taxi_id',$post['taxi_id'])
            ->select();
        //处理标签数据加入详情数据中
        $date = json_decode(json_encode($taxi_label,320),true);
        $taxi['0']['taxi_label'] = json_decode($date['0']['taxi_label'],true);

        if(count($taxi)<=0){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'汽车公司信息不存在','retData'=>$taxi],320);
        }

        //查酒店详情图片
        $images = Db::table('think_taxi_img')
            ->field('taxi_images')
            ->where('taxi_id',$post['taxi_id'])
            ->where('img_status',0)
            ->select();

        $taxi['0']['images'] = $images;

        //查询汽车各项分数
        $taxi_service = $this->val('taxi_service',$post['taxi_id']);
        $taxi_quality = $this->val('taxi_quality',$post['taxi_id']);
        $taxi_speed= $this->val('taxi_speed',$post['taxi_id']);

        //计算汽车的评分
        $taxi_service = $this->average($taxi_service,'taxi_service','taxi_id');
        $taxi_service_number = array_sum($taxi_service);


        $taxi_quality = $this->average($taxi_quality,'taxi_quality','taxi_id');
        $taxi_quality_number = array_sum($taxi_quality);


        $taxi_speed = $this->average($taxi_speed,'taxi_speed','taxi_id');
        $taxi_speed_number = array_sum($taxi_speed);

        //计算出综合评分
        $taxi_all = round(($taxi_service_number + $taxi_quality_number + $taxi_speed_number) / 3);

        //汽车公司评分信息
        $comment[] = [
            'taxi_speed'    =>$taxi_speed_number,
            'taxi_quality'  =>$taxi_quality_number,
            'taxi_service'  =>$taxi_service_number,
            'taxi_all'      =>$taxi_all,
        ];

        //查询评论信息和用户头像,昵称
        $user_comment = Db::table('think_taxi_user')->alias('a')
            ->where('taxi_id',$post['taxi_id'])
            ->join('think_member b','a.id=b.id')
            ->field('a.taxi_user_id,b.nickname,b.head_img,a.comment_time,a.comment_content,a.comment_images,a.comment_all')
            ->order('a.comment_time desc')
            ->paginate(10);

        $date = ['taxi'=>$taxi,'user_comment'=>$user_comment];

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'汽车公司信息查询成功','retData'=>$date],320);
    }

    /**
     * 查询汽车公司分数
     * 接收：酒店打分字段，酒店ID
     * 返回：酒店相对分数
     */
    private function val($val,$id)
    {
        $comment = Db::table('think_taxi_list')
            ->field($val)
            ->where('taxi_id',$id)
            ->select();
        return $comment;
    }

    /**
     * 计算平均值
     * 接收：用户ID 用户评价汽车公司表所有字段，用户评价汽车公司图片
     * 返回：评论成功信息或失败信息
     */
    private function average($array,$val,$id)
    {
        return array_column($array, $val,$id);
    }

    /**
     * 叫车评论接口
     * 接收：用户ID 用户评价叫车表所有字段，用户评价叫车图片
     * 返回：评论成功信息或失败信息
     */
    public function comment(\think\Request $request)
    {
        //接收用户提交数据
        $post = $request->post();

        //验证数据
        $rule =   [
            'taxi_id'                   => 'require|number',
            'id'                        => 'require|number',
            'comment_content'           => 'require|max:550',
            'comment_service'           => 'require|number',
            'comment_speed'             => 'require|number',
            'comment_quality'           => 'require|number',
        ];
        $message  = [
            'taxi_id.require'           => '汽车公司ID不能为空',
            'taxi_id.number'            => '汽车公司ID类型错误',
            'id.require'                => '用户ID不能为空',
            'id.number'                 => '用户ID类型错误',
            'comment_content.require'   => '用户评论不能为空',
            'comment_content.max'       => '用户评论不能过长',
            'comment_service.require'   => '服务评分不能为空',
            'comment_service.number'    => '服务评分类型错误',
            'comment_quality.require'   => '汽车质量评分不能为空',
            'comment_quality.number'    => '汽车质量评分类型错误',
            'comment_speed.require'     => '汽车速度评分不能为空',
            'comment_speed.number'      => '汽车速度评分类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //计算出综合评分
        $taxi_all = round(($post['comment_service'] + $post['comment_quality'] + $post['comment_speed']) / 3);
        //定义综合评分
        $post['comment_all'] = $taxi_all;

        //获取图片参数
        if(!isset($post['path'])){
            $post['path'] = '';
        }else{
//            $post['path'] = implode(",", $post['path']);
            $post['path'] = $post['path'];
        }

        //判断有无图片,有则上传
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
//        }else{
//            $post['comment_images'] = '';
//        }

        //将数据填入数据库
        $TaxicommentModel= new TaxicommentModel();
        $date =  $TaxicommentModel->com_add($post);

        //获取(平均值)
        $taxi_speed = $TaxicommentModel->ambient($post['taxi_id']);

        //获取评分(平均值)
        $taxi_quality= $TaxicommentModel->hygiene($post['taxi_id']);

        //获取评分(平均值)
        $taxi_service = $TaxicommentModel->services($post['taxi_id']);

        //获取综合评分(平均值)
        $taxi_all = $TaxicommentModel->select_comment($post['taxi_id']);

        //修改汽车公司列表的评分
        $res = $TaxicommentModel->update_comment($post['taxi_id'],$taxi_speed,$taxi_quality,$taxi_service,$taxi_all);

        //将数据返回出去
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'评论成功','retData'=>$date],320);
    }

}