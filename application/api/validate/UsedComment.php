<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 9:43
 */

namespace app\api\validate;


use think\Db;
use think\Validate;

class UsedComment extends Validate
{

    public $rule = [
        'used_id' => 'require|number|usedId',
        'user_id' => 'require|number|userId',
        'body' => 'require|min:5|max:150',
    ];


    public $message = [
        'used_id.require' => '请选择正确商品评论',
        'used_id.number' => '请选择正确商品评论',
        'used_id.usedId' => '请选择正确商品评论',
        'user_id.require' => '请登录后评论',
        'user_id.number' => '用户信息出错，请登录后重试',
        'user_id.userId' => '用户信息出错，请登录后重试',
        'body.require' => '请输入评论内容',
        'body.min' => '评论内容不能少于5个字',
        'body.max' => '评论内容不能大于150个字',
    ];

    public function usedId($value, $rule, $data)
    {
        $used_ids = Db::name('used_product')->column('id');
        foreach ($used_ids as $used_id) {
            if ($value == $used_id) {
                return true;
            }
        }
        return false;
    }


    public function userId($value, $rule, $data)
    {
        $user_ids = Db::name('member')->column('id');
        foreach ($user_ids as $user_id) {
            if ($value == $user_id) {
                return true;
            }
        }
        return false;
    }
}