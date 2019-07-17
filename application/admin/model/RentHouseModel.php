<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 18:53
 */

namespace app\admin\model;


use think\Model;

class RentHouseModel extends Model
{
    protected $name = 'rent_house';
    protected $autoWriteTimestamp = true;

    public function rentImage()
    {
        return $this->hasMany('RentImageModel', 'rent_id','id');
    }

    public function user()
    {
        return $this->belongsTo('MemberModel', 'user_id');
    }

    public function regionList()
    {
        return $this->belongsTo('RegionListModel', 'region_id', 'region_id');
    }

    public function memberPraise()
    {
        return $this->morphMany('MemberPraiseModel', 'module');
    }
}