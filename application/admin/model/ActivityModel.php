<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/28
 * Time: 14:06
 */

namespace app\admin\model;


use think\Model;

class ActivityModel extends Model
{
    protected $name = 'activity';
    protected $autoWriteTimestamp = true;

    public function getExpireAttr($value, $data)
    {
        if ($data['activity_end_time'] <= time()) {
            return 1;
        } else {
            return 0;
        }
    }
}