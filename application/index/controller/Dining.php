<?php
namespace app\index\controller;

use app\index\model\DiningcommentModel;
use app\index\model\DiningModel;
use think\Controller;
use think\Db;
use think\Request;

class Dining extends Controller
{
    /**
     * 小程序首页美食推荐接口
     * 接收：
     * 返回：美食首页所有可见信息
     */
    public function homeelect(\think\Request $request)
    {

        //查询出所有推荐美食
        $date = Db::table('think_dining_list')
            ->field('dining_id,dining_logo,dining_name,dining_all')
            ->where('dining_status',0)
            ->limit(4)
            ->select();

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);
    }
    /**
     * 三个模块获取评论接口
     * 接收：模块类型,主键ID
     * 返回：模块下的评论ID
     */
    public function selectcomm(\think\Request $request)
    {
        $post = $request->post();

        $rule =   [
            'model_type' => 'require|number',
            'model_id' => 'require|number',
        ];
        $message  = [
            'model_type.require'      => '模块类型不能为空',
            'model_type.number'       => '模块类型错误',
            'model_id.require'        => 'ID不能为空',
            'model_id.number'         => 'ID类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //判断是否小于3
        if($post['model_type'] > 2){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'类型不能大于2','retData'=>$post['model_type']],320);
        }

        if($post['model_type'] == 0){
            $date = Db::table('think_dining_user')->alias('a')
                ->join('think_member b','a.id=b.id','LEFT')
                ->field('a.dining_user_id,a.comment_time,a.comment_content,' .
                    'a.comment_images,a.comment_all,b.nickname,b.head_img')
                ->where('a.dining_id',$post['model_id'])
                ->order('a.comment_time desc')
                ->paginate(10);
        }else if($post['model_type'] == 1){
            $date = Db::table('think_taxi_user')->alias('a')
                ->where('taxi_id',$post['model_id'])
                ->join('think_member b','a.id=b.id')
                ->field('a.taxi_user_id,b.nickname,b.head_img,a.comment_time,a.comment_content,a.comment_images,a.comment_all')
                ->order('a.comment_time desc')
                ->paginate(10);
        }else if($post['model_type'] == 2){
            $date = Db::table('think_hotel_user')->alias('a')
                ->where('hotel_id',$post['model_id'])
                ->join('think_member b','a.id=b.id')
                ->field('a.hotel_user_id,b.nickname,b.head_img,a.comment_time,a.comment_content,a.images,a.comment_all')
                ->order('a.comment_time desc')
                ->paginate(10);
        }

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);
    }

    /**
     * 小程序首页三个模块搜索接口()
     * 接收：要搜索的类目ID
     * 返回：美食首页所有可见信息
     */
    public function search(\think\Request $request)
    {
        $get = $request->get();

        $rule =   [
            'type' => 'require|number'
        ];
        $message  = [
            'type.require'      => '搜索分类不能为空',
            'type.number'       => '搜索分类格式错误',
        ];

        //实例化验证器
        $result=$this->validate($get,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //判断是否大于3
        if($get['type'] >= 3){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'分类type最大是3'],320);
        }
        $type = $get['type'];
        $community = new DiningModel();

        switch ($type) {
            case 0:
                //美食
                $community = $community->type_dining($get['id']);
                break;
            case 1:
                //酒店
                $community = $community->type_hotel($get['id']);
                break;
            default:
                $community = $community->type_taxi($get['id']);
        }

        //查询出所有推荐美食
//        $date = Db::table('think_dining_list')
//            ->field('dining_id,dining_logo,dining_name,dining_all')
//            ->where('dining_status',0)
//            ->limit(4)
//            ->select();

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$community],320);
    }

    /**
     * 美食首页接口
     * 接收：区域名称
     * 返回：美食首页所有可见信息 注：美食评论只显示最新5条记录
     */
    public function index(\think\Request $request)
    {
        //接收参数
        $get = $request->get('region_name');
        $search = $request->get('dining_content');

        //实例化模型
        $DiningModel = new DiningModel();
        if($search){
            $res = Db::table('think_dining_list')
                ->where('dining_content', 'like', '%' . $search . '%')
                ->select();

            $date = ['dining'=>$res];

            return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);
        }

        //查出一个默认地区分类
        $address = $DiningModel->address();

        if($address['errMsg'] == 'error'){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'没有查到区域'];
            return json_encode($date,320);
        }

        $get = !empty($get) ? $get : $address['retData']['region_name'];

        //获取4个酒店精选
        $elect = $DiningModel->select();

        //获取区域分类下的酒店
        $dining = $DiningModel->dining($get);

        $date = ['elect'=>$elect,'dining'=>$dining];

        //将数据返回出去
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'美食首页信息查询成功','retData'=>$date],320);

    }

    /**
     * 美食详情接口
     * 接收：美食ID   dining_id
     * 返回：美食详情所有可见信息 注：美食评论只显示最新5条记录
     */
    public function details(\think\Request $request)
    {
        //接收参数
        $post = $request->post();

        $rule =   [
            'dining_id'              => 'require|number',
        ];
        $message  = [
            'dining_id.require'      => '餐厅ID不能为空',
            'dining_id.number'       => '餐厅ID类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //查餐厅信息表
        $dining = Db::table('think_dining_list')
            ->where('dining_id',$post['dining_id'])
            ->select();

        //查询餐厅标签字段
        $dining_label = Db::table('think_dining_list')
            ->field('dining_label')
            ->where('dining_id',$post['dining_id'])
            ->select();

        //处理标签数据加入详情数据中
        $date = json_decode(json_encode($dining_label,320),true);
        $dining['0']['dining_label'] = json_decode($date['0']['dining_label'],true);

        if(count($dining)<=0){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'餐厅信息不存在','retData'=>$dining],320);
        }

        //查餐厅详情图片
        $images = Db::table('think_dining_images')
            ->field('dining_images')
            ->where('dining_id',$post['dining_id'])
            ->where('dining_status',0)
            ->select();
        $dining['0']['images'] = $images;

        //查询评论信息和用户头像,昵称
        $user_comment = Db::table('think_dining_user')->alias('a')
            ->join('think_member b','a.id=b.id','LEFT')
            ->field('a.dining_user_id,a.comment_time,a.comment_content,' .
                'a.comment_images,a.comment_all,b.nickname,b.head_img')
            ->where('a.dining_id',$post['dining_id'])
            ->order('a.comment_time desc')
            ->paginate(10);

        $date = ['dining'=>$dining,'user_comment'=>$user_comment];

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'餐厅信息查询成功','retData'=>$date],320);
    }

    /**
     * 美食评论接口
     * 接收：用户ID 用户评价餐厅表所有字段，用户评价餐厅图片
     * 返回：评论成功信息或失败信息
     */
    public function comment(\think\Request $request)
    {
        //接收用户提交数据
        $post = $request->post();

        //验证数据
        $rule =   [
            'dining_id'                 => 'require|number',
            'id'                        => 'require|number',
            'comment_content'           => 'require|max:550',
            'comment_service'           => 'require|number',
            'comment_hygiene'           => 'require|number',
            'comment_taste'             => 'require|number',
        ];
        $message  = [
            'dining_id.require'         => '餐厅ID不能为空',
            'dining_id.number'          => '餐厅ID类型错误',
            'id.require'                => '用户ID不能为空',
            'id.number'                 => '用户ID类型错误',
            'comment_content.require'   => '用户评论不能为空',
            'comment_content.max'       => '用户评论不能过长',
            'comment_service.require'   => '服务评分不能为空',
            'comment_service.number'    => '服务评分类型错误',
            'comment_taste.require'     => '味道评分不能为空',
            'comment_taste.number'      => '味道评分类型错误',
            'comment_hygiene.require'   => '卫生评分不能为空',
            'comment_hygiene.number'    => '卫生评分类型错误',
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
        $taxi_all = round(($post['comment_service'] + $post['comment_hygiene'] + $post['comment_taste']) / 3);
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
//                '/uploads/dining/'
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
        $DiningcommentModel= new DiningcommentModel();
        $date =  $DiningcommentModel->com_add($post);

        //获取(平均值)
        $dining_taste = $DiningcommentModel->ambient($post['dining_id']);

        //获取评分(平均值)
        $dining_hygiene= $DiningcommentModel->hygiene($post['dining_id']);

        //获取评分(平均值)
        $dining_service = $DiningcommentModel->services($post['dining_id']);

        //获取综合评分(平均值)
        $dining_all = $DiningcommentModel->select_comment($post['dining_id']);
//        die;
        //修改汽车公司列表的评分
        $res = $DiningcommentModel->update_comment($post['dining_id'],$dining_taste,$dining_hygiene,$dining_service,$dining_all);

        //将数据返回出去
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'评论成功','retData'=>$date],320);
    }

}