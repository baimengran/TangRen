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

        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }

        try {
            $data = [];
            $coupon = CouponModel::all();
//            foreach($coupons as $coupon){
//                $data[]=$coupon;
//                if($coupon['activity_end_time']>=$coupon['activity_create_time']){
//                    $data[]['expire']=''
//                }
//            }

            return jsone('查询成功', 200, $coupon);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }
}