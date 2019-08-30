<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 1:17
 */

namespace app\admin\model;


use think\Model;

class HotCommentModel extends Model
{
    protected $name='hot_comment';
    protected $autoWriteTimestamp=true;

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
        return $this->belongsTo('HotSubjectModel', 'community_id');
    }

    public function commentImage()
    {
        return $this->hasMany('HotCommentImageModel', 'comment_id');
    }
}