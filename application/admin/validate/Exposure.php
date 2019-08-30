<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 10:09
 */

namespace app\admin\validate;


use think\Validate;

class Exposure extends Validate
{
    protected $rule = [
        'title' => 'require|min:3|max:15',
        'body' => 'require|max:550|min:5',
        'user_id'=>'require'
    ];

    protected $message = [
        'title.require' => '标题必须填写',
        'title.min' => '标题不能小于3个字符',
        'title.max' => '标题不能大于15个字符',
        'body.require' => '内容必须填写',
        'body.min' => '内容最小不能低于5个字',
        'body.max' => '内容最大不能超过550个字',
        'user_id.require'=>'帐户不能为空'
    ];
}