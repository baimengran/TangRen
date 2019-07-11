<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/10
 * Time: 16:19
 */

namespace app\used\model;


use think\Model;

class UsedProduct extends Model
{
    public function usedImage()
    {
        return $this->hasMany(UsedImage::class,'used_id');
    }
}