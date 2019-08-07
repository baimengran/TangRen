<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16
 * Time: 14:21
 */

namespace app\api\controller;


use app\admin\model\CouponModel;
use app\api\exception\BannerMissException;
use think\Exception;
use think\Log;

class Coupon
{
    public function discovery()
    {


        try {
            $data = [];
            $coupons = CouponModel::where('expire', 'eq', '0')->select();

            //优惠卷过期
            foreach ($coupons as $coupon) {
                if ($coupon['activity_end_time'] <= time()) {
                    $coupon->save(['expire' => 1]);
                }
            }
            $coupons = CouponModel::where('status',0)->order('expire asc,create_time desc')->paginate(20);
            foreach ($coupons as $k => $coupon) {
                $data[$k]['id'] = $coupon['id'];
                $data[$k]['title'] = $coupon['title'];
                $data[$k]['money'] = $coupon['money'];
                $data[$k]['description'] = $coupon['description'];
                $data[$k]['shop_name'] = $coupon['shop_name'];
                $data[$k]['address'] = $coupon['address'];
                $data[$k]['status'] = $coupon['status'];
                $data[$k]['activity_create_time'] = date('Y.m.d', $coupon['activity_create_time']);
                $data[$k]['activity_end_time'] = date('Y.m.d', $coupon['activity_end_time']);
                $data[$k]['expire'] = $coupon['expire'];
                $data[$k]['create_time'] = $coupon['create_time'];
                $data[$k]['update_time'] = $coupon['update_time'];
            }

            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }
}