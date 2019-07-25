<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/25
 * Time: 15:51
 */

namespace app\admin\validate;


use think\Log;
use think\Validate;

class Coupon extends Validate
{
    protected $rule = [
        'title' => 'require',
        'money' => 'require|number',
        'description' => 'require|min:5|max:100',
        'shop_name' => 'require',
        'address' => 'require',
        'status' => 'require',
        'activity_create_time' => 'require|date',
        'activity_end_time' => 'require|create_time',
    ];

    protected $message = [
        'title.require' => '优惠卷标题必须填写',
        'money.require' => '优惠金额必须填写',
        'money.number' => '优惠金额填写错误',
        'description' => '优惠卷说明必须填写',
        'description.min' => '优惠卷说明不能小于5个字',
        'description.max' => '优惠卷说明不能大于100个字',
        'shop_name.require' => '店铺名称必须填写',
        'address.require' => '店铺地址必须填写',
        'status.require' => '状态必须填写',
        'activity_create_time.require' => '开始时间必须填写',
        'activity_create_time.date' => '开始时间格式不正确',
        'activity_end_time.require' => '结束时间必须填写',
        'activity_end_time.create_time' => '结束时间不能早于开始时间',
    ];


    public function create_time($value, $rule, $data)
    {
        $end_time = strtotime($value);
        $create_time = strtotime($data['activity_create_time']);
        if ($end_time - $create_time > 0) {
            return true;
        } else {
            return false;
        }
    }
}