<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 13:25
 */

namespace app\api\validate;


use think\Db;
use think\Exception;
use think\Log;
use think\Validate;

class JobSeek extends Validate
{
    protected $rule = [
        'user_id' => 'require|number|userId',
        'region_id' => 'number|regionId',
        'profession_id' => 'require|number|professionId',
        'body' => 'require|min:5|max:550',
        'salary' => 'require',
        'sticky_num' => 'number',
        'phone' => 'require',
    ];

    protected $message = [
        'user_id.require' => '请登录后重试',
        'user_id.number' => '非法用户信息',
        'user_id.userId' => '非法用户信息',
        'region_id.number' => '区域错误',
        'region_id.regionId' => '区域错误',
        'profession_id.require' => '请选择行业',
        'profession_id.number' => '请选择正确行业',
        'profession_id.professionId' => '请选择正确行业',
        'body.require' => '请填写招聘内容',
        'body.min' => '招聘内容不能少于5个字',
        'body.max' => '招聘内容不能大于550个字',
        'salary.require' => '请填写薪资',
        'sticky_num.number' => '置顶状态错误',
        'phone.require'=>'请填写电话'
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
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
        return false;
    }

    public function regionId($value, $rule, $data)
    {
        try {
            $ids = Db::name('region_list')->column('region_id');
            foreach ($ids as $id) {
                if ($value == $id) {
                    return true;
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
        return false;
    }

    public function professionId($value, $rule, $data)
    {
        try {
            $ids = Db::name('profession_cate')->column('id');
            foreach ($ids as $id) {
                if ($value == $id) {
                    return true;
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
        return false;
    }
}