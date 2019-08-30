<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/28
 * Time: 12:55
 */

namespace app\admin\model;


use think\Model;

class ExposureModel extends Model
{

    protected $name = 'exposure';
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
        return $this->belongsTo('AdminModel', 'user_id');
    }

    public function topic()
    {
        return $this->belongsTo('TopicCateModel', 'topic_id');
    }

    public function communityFile()
    {
        return $this->hasMany('ExposureFileModel', 'exposure_id');
    }

    public function memberPraise()
    {
        return $this->morphMany('MemberPraiseModel', 'module');
    }

    public function memberCollect(){
        return $this->morphMany('MemberCollectModel','module');
    }
}