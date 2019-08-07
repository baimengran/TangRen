<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/26
 * Time: 15:11
 */

namespace app\admin\model;


use think\Model;

class GoodOrderModel extends Model
{

    protected $name = 'goods_order';
    protected $pk='order_id';

    public function goodFraction()
    {
        return $this->belongsTo('GoodFractionModel','goods_id');
    }

    public function user()
    {
        return $this->belongsTo('MemberModel','id');
    }

    public function address(){
        return $this->belongsTo('AddressPhone','address_id');
    }
}