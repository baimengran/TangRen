<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/28
 * Time: 12:56
 */

namespace app\admin\model;


use think\Model;

class ExposureCommentModel extends Model
{
    protected $name='exposure_comment';
    protected $autoWriteTimestamp = true;

    public function getCreateTimeAttr($value)
    {
        return date('m月d日', $value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date('m月d日', $value);
    }

    public function user()
    {
        return $this->belongsTo('memberModel', 'user_id');
    }

    public function community()
    {
        return $this->belongsTo('ExposureModel', 'exposure_id');
    }

    public function commentImage()
    {
        return $this->hasMany('ExposureCommentImageModel', 'comment_id');
    }
}