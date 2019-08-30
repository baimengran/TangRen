<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 18:16
 */

namespace app\api\validate;


use think\Db;
use think\Validate;

class Community extends Validate
{

    protected $rule = [
        'title' => 'require|min:3|max:15',
        'user_id' => 'require|number|userId',
        'body' => 'require|max:550|min:5',
        'sticky_id' => 'require|number',

    ];

    protected $message = [
        'title.require' => '主题必须填写',
        'title.min' => '主题不能小于3个字符',
        'title.max' => '主题不能大于15个字符',
        'user_id.require' => '必须指定用户',
        'user_id.number' => '用户信息错误',
        'user_id.userId' => '用户信息错误',
        'body.require' => '内容必须填写',
        'body.min' => '内容最小不能低于5个字',
        'body.max' => '内容最大不能超过550个字',
        'sticky_id.require' => '置顶填写错误',
        'sticky_id.number' => '置顶填写错误',

    ];

    public function topicId($value, $rule, $data)
    {
        $data = Db::name('topic_cate')->column('id');
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