<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16
 * Time: 14:21
 */

namespace app\api\controller;


use app\admin\model\CouponModel;
use think\Exception;
use think\Log;

class Coupon
{
    public function discovery()
    {
        try {
            $coupon = CouponModel::all();
            return jsone('查询成功', $coupon);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
    }
}