<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 22:57
 */

namespace app\admin\model;


use think\Model;

class TaxiCommentModel extends Model
{
    protected $name = 'taxi_user';

    public function getCommentTimeAttr($value)
    {
        return date('m月d日', $value);
    }

    public function user()
    {
        return $this->belongsTo('memberModel', 'id');
    }
}