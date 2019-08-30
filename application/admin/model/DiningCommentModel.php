<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 22:53
 */

namespace app\admin\model;


use think\Model;

class DiningCommentModel extends Model
{
    protected $name='dining_user';

    public function getCommentTimeAttr($value)
    {
        return date('m月d日', $value);
    }


    public function user()
    {
        return $this->belongsTo('MemberModel', 'id');
    }
}