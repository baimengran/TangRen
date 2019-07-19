<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 18:54
 */

namespace app\admin\model;


use think\Model;

class RentCommentModel extends Model
{
    protected $name = 'rent_comment';
    protected $autoWriteTimestamp = true;


    public function getCreateTimeAttr($value)
    {
        return date('m月d日', $value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date('m月d日', $value);
    }

    public function rent(){
        return $this->belongsTo('RentHouseModel','rent_id');
    }

    public function user(){
        return $this->belongsTo('MemberModel','user_id');
    }

    public function commentImage(){
        return $this->hasMany('RentCommentImageModel','comment_id');
}
}