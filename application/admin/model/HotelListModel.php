<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/23
 * Time: 11:28
 */

namespace app\admin\model;


use think\Model;

class HotelListModel extends Model
{
    protected $name = 'hotel_list';
    protected $pk = 'hotel_id';

    public function memberCollect()
    {
        return $this->morphMany('MemberCollectModel', 'module');
    }

    public function getCreateTimeAttr($value)
    {
        return date('m月d日', $value);
    }
}