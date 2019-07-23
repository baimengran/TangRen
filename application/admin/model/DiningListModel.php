<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/23
 * Time: 11:27
 */

namespace app\admin\model;


use think\Model;

class DiningListModel extends Model
{
    protected $name='dining_list';
    protected $pk='dining_id';

    public function memberCollect(){
        return $this->morphMany('MemberCollectModel','module');
    }
}