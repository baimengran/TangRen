<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/15
 * Time: 1:22
 */

namespace app\admin\model;


use think\Model;
use traits\model\SoftDelete;

class MemberCollectModel extends Model
{
    use SoftDelete;

    protected $name = 'member_collect';
    protected $autoWriteTimestamp = true;
    protected static $delete_time = 'delete_time';

    public function community()
    {
        return $this->morphTo('module');
    }

    public function usedProduct(){
        return $this->morphTo('module');
    }
}