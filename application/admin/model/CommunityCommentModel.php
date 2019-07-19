<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 18:20
 */

namespace app\admin\model;


use think\Model;

class CommunityCommentModel extends Model
{
    protected $name = 'community_comment';
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
        return $this->belongsTo('CommunityModel', 'community_id');
    }

    public function commentImage()
    {
        return $this->hasMany('CommunityCommentImageModel', 'comment_id');
    }
}