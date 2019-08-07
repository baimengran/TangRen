<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31
 * Time: 18:45
 */

namespace app\admin\model;


use think\Model;

class IdeaModel extends Model
{
    protected $name = 'idea';
    protected $autoWriteTimestamp = true;

    public function user()
    {
        return $this->belongsTo('memberModel', 'user_id');
    }

    public function getCreateTimeAttr($value)
    {
        return date('Y年m月d日', $value);
    }
}