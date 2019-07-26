<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/25
 * Time: 23:56
 */

namespace app\admin\validate;


use think\Validate;

class StickyValidate extends Validate
{
    protected $rule = [
        'day_num' => 'require|number|regex:/^[1-9]\d*$/|unique:sticky',
        'integral' => 'require|number|regex:/^[1-9]\d*$/',
        'status' => 'require',
    ];
    protected $message = [
        'day_num.require' => '置顶天数必须填写',
        'day_num.number' => '置顶天数只能是正整数',
        'day_num.regex' => '置顶天数必须是正整数',
        'day_num.unique' => '以有相同置顶天数',
        'integral.require' => '积分必须填写',
        'integral.number' => '积分只能是正整数',
        'integral.regex' => '积分只能是正整数',
    ];
}