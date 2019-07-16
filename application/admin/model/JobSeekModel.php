<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 13:24
 */

namespace app\admin\model;


use think\Model;

class JobSeekModel extends Model
{
    protected $name = 'job_seek';
    protected $autoWriteTimestamp = true;

    public function user(){
        return $this->belongsTo('MemberModel','user_id');
    }

    public function region(){
        return $this->belongsTo('RegionListModel','region_id','region_id');
    }

    public function profession(){
        return $this->belongsTo('ProfessionCateModel','profession_id');
    }

    public function memberPraise()
    {
        return $this->morphMany('MemberPraiseModel', 'module');
    }
}