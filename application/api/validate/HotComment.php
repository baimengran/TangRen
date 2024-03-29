<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 1:15
 */

namespace app\api\validate;


use think\Db;
use think\Validate;

class HotComment extends Validate
{
    public $rule = [
        'community_id' => 'require|number|communityId',
        'user_id' => 'require|number|userId',
        'body' => 'require|min:5|max:150',
    ];


    public $message = [
        'community_id.require' => '请选择正确专题评论',
        'community_id.number' => '请选择正确专题评论',
        'community_id.communityId' => '请选择正确专题评论',
        'user_id.require' => '请登录后评论',
        'user_id.number' => '用户信息出错，请登录后重试',
        'user_id.userId' => '用户信息出错，请登录后重试',
        'body.require' => '请输入评论内容',
        'body.min' => '评论内容不能少于5个字',
        'body.max' => '评论内容不能大于150个字',
    ];

    public function communityId($value, $rule, $data)
    {
        try {
            $community_ids = Db::name('hot_subject')->column('id');
            foreach ($community_ids as $community_id) {
                if ($value == $community_id) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
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