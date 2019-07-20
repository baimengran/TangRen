<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 19:17
 */

namespace app\api\validate;


use think\Db;
use think\Validate;

class RentHouse extends Validate
{

    protected $rule = [
        'user_id' => 'require|number|userId',
        'region_id' => 'require|number|regionId',
        'body' => 'require|min:5|max:550',
        'price' => 'require|number',
        'sticky_id' => 'require|number',
        'phone' => 'require',
    ];

    protected $message = [
        'user_id.require' => '请登录后重试',
        'user_id.number' => '请登录后重试',
        'user_id.userId' => '用户信息非法',
        'region_id.require' => '区域设置错误',
        'region_id.number' => '区域设置错误',
        'region_id.regionId' => '区域设置错误',
        'body.require' => '房屋信息必须填写',
        'body.min' => '房屋信息不能少于5个字',
        'body.max' => '房屋信息不能大于550个字',
        'price.require' => '价格必须填写',
        'price.number' => '价格填写错误',
        'sticky_id.require' => '置顶状态错误',
        'sticky_id.number' => '置顶状态错误',
        'phone.require' => '电话必须填写',
    ];

    public function userId($value, $rule, $data)
    {
        $ids = Db::name('member')->column('id');
        foreach ($ids as $id) {
            if ($value == $id) {
                return true;
            }
        }
        return false;
    }

    public function regionId($value, $rule, $data)
    {
        $ids = Db::name('region_list')->column('region_id');
        foreach ($ids as $id) {
            if ($value == $id) {
                return true;
            }
        }
        return false;
    }

}