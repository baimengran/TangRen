<?php
namespace app\index\controller;

use app\admin\model\MemberModel;
use app\index\model\FractionModel;
use app\index\model\UserModel;
use think\Controller;
use think\Db;

class Personal extends Controller
{
    /**
     * 个人中心首页接口
     * 输入：用户ID
     * 返回：用户昵称，头像，在线多少天，签到状态
     */
    public function index(\think\Request $request)
    {
        //接收数据
        $get = $request->get('id');

        $user = new UserModel();
        $date = $user->test_1($get);

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);

    }

    /**
     * 个人中心积分商城接口
     * 输入：用户ID
     * 返回：商品列表
     */
    public function fraction_goods(\think\Request $request)
    {
        //接收数据
        $get = $request->get('id');

        //获取用户积分
        $user = new UserModel();
        $fraction = $user->user_fraction($get);

        //获取商品信息
        $goods = new FractionModel();
        $goods_list = $goods->index();

        $date = ['fraction'=> $fraction,'goods'=>$goods_list];

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);

    }
    /**
     * 个人中心积分兑换商品接口
     * 输入：用户ID 商品ID
     * 返回：购买成功状态
     */
    public function buy(\think\Request $request)
    {
        //接收参数
        $post = $request->post();

        //获取用户积分
        $user = new UserModel();
        $fraction = $user->user_fraction($post['id']);

        //查询商品所用积分
        $goods = new FractionModel();
        $goods_fraction = $goods->select($post['goods_id']);


        print_r($goods_fraction);
        die;
    }

}