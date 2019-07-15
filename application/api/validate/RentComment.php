<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 19:17
 */

namespace app\api\validate;


use think\Db;
use think\Exception;
use think\Log;
use think\Validate;

class RentComment extends Validate
{

    protected $rule = [
        'user_id' => 'require|number|userId',
        'rent_id' => 'require|number|rentId',
        'body' => 'require|min:5|max:150',
    ];

    protected $message = [
        'user_id.require' => '请登录后评论',
        'user_id.number' => '用户信息非法',
        'user_id.userId' => '用户信息非法',
        'rent_id.require' => '请选择正确房屋信息评论',
        'rent_id.number' => '请选择正确房屋信息评论',
        'rent_id.rentId' => '请选择正确房屋信息评论',
        'body.require' => '评论内容必须填写',
        'body.min' => '评论内容不能少于5个字',
        'body.max' => '评论内容不能大于150个字'
    ];

    public function userId($value, $rule, $data)
    {
        try {
            $ids = Db::name('member')->column('id');
            foreach ($ids as $id) {
                if ($value == $id) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }

    }

    public function rentId($value, $rule, $data)
    {
        try {
            $ids = Db::name('rent_house')->column('id');
            foreach ($ids as $id) {
                if ($value == $id) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }

    }

}