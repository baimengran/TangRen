<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 13:24
 */

namespace app\admin\model;


use think\Model;

class JobCommentModel extends Model
{
    protected $name = 'job_comment';
    protected $autoWriteTimestamp = true;


    public function getCreateTimeAttr($value)
    {
        return date('m月d日', $value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date('m月d日', $value);
    }

    public function job()
    {
        return $this->belongsTo('JobSeekModel', 'job_id','id');
    }

    public function commentImage()
    {
        return $this->hasMany('JobCommentImageModel', 'comment_id');
    }

    public function user(){
        return $this->belongsTo('memberModel','user_id');
    }
}