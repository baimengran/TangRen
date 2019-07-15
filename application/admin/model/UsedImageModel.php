<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/10
 * Time: 18:54
 */

namespace app\admin\model;


use think\Model;

class UsedImageModel extends Model
{
    protected $name = 'used_image';
    protected $autoWriteTimestamp = true;

//    public function usedProduct(){
//        return $this->belongsTo('UsedProductModel','used_id');
//    }

}