<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/20
 * Time: 20:06
 */

namespace app\admin\model;


use think\Model;

class MarqueeModel extends Model
{
    protected $name = 'marquee';
    protected $autoWriteTimestamp = true;


    public function getCreateTimeAttr($value)
    {
        return date('m月d日', $value);
    }
}