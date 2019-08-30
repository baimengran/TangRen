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

        $search = input('search');
        try {
            $data = [];
            $coupons = CouponModel::where('expire', 'eq', '0')->select();

            //优惠卷过期
            foreach ($coupons as $coupon) {
                if ($coupon['activity_end_time'] <= time()) {
                    $coupon->save(['expire' => 1]);
                }
            }
            $coupons = CouponModel::where('status',0);
            if($search){
               $coupons = $coupons->where('title','like','%'.$search.'%');
            }
            $coupons = $coupons->order('expire asc,create_time desc')->paginate(20);

            $data['total'] = $coupons->total();
            $data['per_page'] = $coupons->listRows();
            $data['current_page'] = $coupons->currentPage();
            $data['last_page'] = $coupons->lastPage();
            $data['data'] = [];
            foreach ($coupons as $k => $coupon) {
                $data['data'][$k]['id'] = $coupon['id'];
                $data['data'][$k]['title'] = $coupon['title'];
                $data['data'][$k]['money'] = $coupon['money'];
                $data['data'][$k]['description'] = $coupon['description'];
                $data['data'][$k]['shop_name'] = $coupon['shop_name'];
                $data['data'][$k]['address'] = $coupon['address'];
                $data['data'][$k]['status'] = $coupon['status'];
                $data['data'][$k]['activity_create_time'] = date('Y.m.d', $coupon['activity_create_time']);
                $data['data'][$k]['activity_end_time'] = date('Y.m.d', $coupon['activity_end_time']);
                $data['data'][$k]['expire'] = $coupon['expire'];
                $data['data'][$k]['create_time'] = $coupon['create_time'];
                $data['data'][$k]['update_time'] = $coupon['update_time'];
            }

            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }
}