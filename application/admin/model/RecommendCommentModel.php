<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 9:18
 */

namespace app\admin\model;


use think\Model;

class RecommendCommentModel extends Model
{
    protected $name = 'recommend_comment';
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
        return $this->belongsTo('RecommendModel', 'community_id');
    }

    public function commentImage()
    {
        return $this->hasMany('RecommendCommentImageModel', 'comment_id');
    }
}