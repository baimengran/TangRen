<?php
namespace app\index\controller;

use app\index\model\HotelcommentModel;
use app\index\model\HotelModel;
use think\Controller;
use think\Db;

class Hotel extends Controller
{
    /**
     * 酒店首页接口
     * 接收：区域名称
     * 返回：酒店首页所有可见信息 注：酒店评论只显示最新5条记录
     */
    public function first(\think\Request $request)
    {
        //接收参数
        $get = $request->get('region_name');

        //实例化模型
        $HotelModel = new HotelModel();
        //查出一个默认地区分类
        $address = $HotelModel->address();

        $get = !empty($get) ? $get : $address['0']['region_name'];

        //获取4个酒店精选
        $elect = $HotelModel->select();

        //获取区域分类
        $region = $HotelModel->region();

        //获取区域分类下的酒店
        $hotel = $HotelModel->hotel($get);

        $date = ['elect'=>$elect,'region'=>$region,'hotel'=>$hotel];

        //将数据返回出去
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'酒店首页信息查询成功','retData'=>$date],320);

    }

    /**
     * 酒店详情接口
     * 接收：酒店ID   hotel_id
     * 返回：酒店详情所有可见信息 注：酒店评论只显示最新5条记录
     */
    public function index(\think\Request $request)
    {
        //接收参数
        $post = $request->post();

        $rule =   [
            'hotel_id' => 'require|number',
        ];
        $message  = [
            'hotel_id.require'      => '酒店ID不能为空',
            'hotel_id.number'       => '酒店ID类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //查酒店信息表，
        $hotel = Db::table('think_hotel_list')
            ->where('hotel_id',$post['hotel_id'])
            ->select();

        if(count($hotel)<=0){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'酒店信息不存在','retData'=>$hotel],320);
        }

        //查酒店详情图片
        $images = Db::table('think_hotel_img')
            ->field('hotel_images')
            ->where('hotel_id',$post['hotel_id'])
            ->where('img_status',0)
            ->select();

        $hotel['0']['images'] = $images;

        //查询酒店服务分数
        $hotel_service = $this->val('hotel_service',$post['hotel_id']);
        //查询酒店环境分数
        $hotel_ambient = $this->val('hotel_ambient',$post['hotel_id']);
        //查询酒店卫生分数
        $hotel_hygiene = $this->val('hotel_hygiene',$post['hotel_id']);

        //计算酒店的服务评分
        $hotel_service = $this->average($hotel_service,'hotel_service','hotel_id');
        $hotel_service_number = array_sum($hotel_service);

        //计算出酒店的环境评分
        $hotel_ambient = $this->average($hotel_ambient,'hotel_ambient','hotel_id');
        $hotel_ambient_number = array_sum($hotel_ambient);

        //计算出酒店的卫生评分
        $hotel_hygiene = $this->average($hotel_hygiene,'hotel_hygiene','hotel_id');
        $hotel_hygiene_number = array_sum($hotel_hygiene);

        //计算出综合评分
        $hotel_all = round(($hotel_service_number + $hotel_ambient_number + $hotel_hygiene_number) / 3);

        //酒店评分信息
        $comment[] = [
            'hotel_service' =>$hotel_service_number,
            'hotel_ambient' =>$hotel_ambient_number,
            'hotel_hygiene' =>$hotel_hygiene_number,
            'hotel_all'     =>$hotel_all,
            ];

        //查询评论信息和用户头像,昵称(只显示5条)
        $user_comment = Db::table('think_hotel_user')->alias('a')
            ->where('hotel_id',$post['hotel_id'])
            ->join('think_member b','a.id=b.id')
            ->field('a.hotel_user_id,b.nickname,b.head_img,a.comment_time,a.comment_content,a.images,a.comment_all')
            ->order('comment_time desc')
            ->limit(5)
            ->select();

        $date[] = ['hotel'=>$hotel,'user_comment'=>$user_comment];

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'酒店信息查询成功','retData'=>$date],320);
    }

    /**
     * 查询酒店分数
     * 接收：酒店打分字段，酒店ID
     * 返回：酒店相对分数
     */
    private function val($val,$id)
    {
        $comment = Db::table('think_hotel_list')
            ->field($val)
            ->where('hotel_id',$id)
            ->select();
        return $comment;
    }
    /**
     * 计算平均值
     * 接收：用户ID 用户评价酒店表所有字段，用户评价酒店图片
     * 返回：评论成功信息或失败信息
     */
    private function average($array,$val,$id)
    {
        return array_column($array, $val,$id);
    }
    /**
     * 酒店评论接口
     * 接收：用户ID 用户评价酒店表所有字段，用户评价酒店图片
     * 返回：评论成功信息或失败信息
     */
    public function comment(\think\Request $request)
    {
        //接收用户提交数据
        $post = $request->post();

        //验证数据
        $rule =   [
            'hotel_id'          => 'require|number',
            'id'                => 'require|number',
            'comment_content'   => 'require|max:550',
            'comment_service'   => 'require|number',
            'comment_ambient'   => 'require|number',
            'comment_hygiene'   => 'require|number',
        ];
        $message  = [
            'hotel_id.require'          => '酒店ID不能为空',
            'hotel_id.number'           => '酒店ID类型错误',
            'id.require'                => '用户ID不能为空',
            'id.number'                 => '用户ID类型错误',
            'comment_content.require'   => '用户评论不能为空',
            'comment_content.max'       => '用户评论不能过长',
            'comment_service.require'   => '服务评分不能为空',
            'comment_service.number'    => '服务评分类型错误',
            'comment_ambient.require'   => '环境评分不能为空',
            'comment_ambient.number'    => '环境评分类型错误',
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
        $hotel_all = round(($post['comment_service'] + $post['comment_ambient'] + $post['comment_hygiene']) / 3);
        //定义综合评分
        $post['comment_all'] = $hotel_all;
        //定义评论时间
        $post['comment_time'] = time();
;
        //判断有无图片,有则上传
        if($files = request()->file('images')){
            if(count($_FILES['images']['name']) >= 10){
                return json_encode($date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'图片不能超过9张'],320);
            }
            $aa = uploadImage(
                $files,
                '/uploads/hotel/'
            );
            //判断图片是否上传成功
            if(!isset($aa['0'])){
                return json_encode($date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'图片没有上传成功'],320);
            }
            $post['images'] = implode(",", $aa);
        }else{
            $post['images'] = '';
        }

        //将数据填入数据库
        $HotelcommentModel = new HotelcommentModel();

        $date =  $HotelcommentModel->com_add($post);

        //更新酒店评分
        $HotelModel = new HotelModel();

        //获取酒店环境评分(平均值)
        $hotel_ambient = $HotelModel->ambient($post['hotel_id']);
        //获取酒店卫生评分(平均值)
        $hotel_hygiene = $HotelModel->hygiene($post['hotel_id']);
        //获取酒店服务评分(平均值)
        $hotel_service = $HotelModel->services($post['hotel_id']);
        //获取酒店综合评分(平均值)
        $hotel_all = $HotelModel->select_comment($post['hotel_id'],'comment_all');

        //修改酒店评分表
        $res = $HotelModel->update_comment($post['hotel_id'],$hotel_hygiene,$hotel_ambient,$hotel_service,$hotel_all);
        //将数据返回出去
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'评论成功','retData'=>$date],320);
    }


}