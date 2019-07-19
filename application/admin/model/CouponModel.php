<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16
 * Time: 14:13
 */

namespace app\admin\model;


use think\Model;

class CouponModel extends Model
{
    protected $name = 'coupon';
    protected $autoWriteTimestamp = true;

    public function getExpireAttr($value, $data)
    {
        if ($data['activity_end_time'] <= time()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getActivityCreateTimeAttr($value)
    {
        return date('yy年m月d天');
    }

    public function getActivityEndTimeAttr($value)
    {
        return date('yy年m月d天');
    }
}