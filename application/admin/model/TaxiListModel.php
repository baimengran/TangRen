<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/23
 * Time: 11:29
 */

namespace app\admin\model;


use think\Model;

class TaxiListModel extends Model
{
    protected $name = 'taxi_list';
    protected $pk = 'taxi_id';

    public function memberCollect()
    {
        return $this->morphMany('MemberCollectModel', 'module');
    }

    public function getCreateTimeAttr($value)
    {
        return date('m月d日', $value);
    }
}