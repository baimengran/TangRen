<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/12
 * Time: 14:45
 */

namespace app\admin\model;


use think\Model;

class UsedCommentModel extends Model
{
    protected $autoWriteTimestamp = true;
    protected $name = 'used_comment';

    public function getCreateTimeAttr($value)
    {
        return date('m月d日', $value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date('m月d日', $value);
    }

    public function commentImage()
    {
        return $this->hasMany('UsedCommentImageModel', 'comment_id');
    }

    public function user()
    {
        return $this->belongsTo('memberModel', 'user_id');
    }

    public function used()
    {
        return $this->belongsTo('usedProductModel', 'used_id');
    }

}