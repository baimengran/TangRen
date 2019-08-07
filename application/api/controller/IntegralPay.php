<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31
 * Time: 12:37
 */

namespace app\api\controller;


use app\admin\model\IntegralListModel;
use app\admin\model\OrderModel;
use think\Config;
use think\Db;
use think\Request;
use think\Validate;

class IntegralPay
{
    public function index(Request $request)
    {
        if (!$token = $request->post('authorization')) {
            return jsone('请登录后重试', 401);
        }
        if (!$integral_id = $request->post('integral_id')) {
            return jsone('积分错误', 400);
        }

        $integral = new IntegralListModel();

        if (!$integral = $integral->where('integral_id', $integral_id)->find()) {
            return jsone('积分错误', 400);
        }
        if (!$user_id = getUserId()) {
            return jsone('请登录后重试', 401);
        }
        //生成订单
        $order = new OrderModel();
        $order->user_id = $user_id;
        $order->integral_id = $integral_id;
        $order->total_amount = $integral['rmb_number'];
        $order->remark = '支付'.$integral['rmb_number'].'人民币购买'.$integral['integral_number'].'积分';
        $order->save();
        if (!$order) {
            return jsone('生成订单出错', 400);
        }

//        print_r(Config::get());
//        return $a =  config('wx_AppID');

        //准备支付
        $pay = new Pay();
        return $pay->miniProgramPay($token,$order['remark'],$order['id'],0.01);


    }
}