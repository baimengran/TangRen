<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/12
 * Time: 10:57
 */

namespace app\api\validate;

use think\Db;
use think\Log;
use think\Validate;

class UsedProduct extends Validate
{
    protected $rule = [
        'region_id' => 'require|number|regionId',
        'user_id' => 'require|number|userId',
        'body' => 'require|max:550|min:5',
        'price' => 'require|number',
        'sticky_id' => 'require|number',
        'phone' => 'require',
        'images'=>'image',

    ];

    protected $message = [
        'region_id.require' => '区域必须填写',
        'region_id.number' => '区域填写错误',
        'region_id.regionId' => '区域填写错误',
        'user_id.require' => '必须指定用户',
        'user_id.number' => '用户信息错误',
        'user_id.userId' => '用户信息错误',
        'body.require' => '商品信息必须填写',
        'body.min' => '商品信息最小不能低于5个字',
        'body.max' => '商品信息最大不能超过550个字',
        'price.require' => '价格必须填写',
        'price.number' => '价格填写错误',
        'sticky_id.require' => '置顶填写错误',
        'sticky_id.number' => '置顶填写错误',
        'phone.require' => '电话必须填写',
        'images.image' => '请上传图片',

    ];

    public function regionId($value, $rule, $data)
    {
        $data = Db::name('region_list')->column('region_id');
        //Log::error($value);
        foreach ($data as $val) {
            if ($value == $val) {
                return true;
            }
        }
        return false;
    }

    /**
     * 自定义规则
     * 判断用户表是否存在输入id
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     */
    public function userId($value, $rule, $data)
    {
        $data = Db::name('member')->column('id');
        foreach ($data as $val) {
            if ($value == $val) {
                return true;
            }
        }
        return false;
    }

}