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


class MemberPraiseModel extends Model
{
    use SoftDelete;
    protected $name = 'member_praise';
    protected $autoWriteTimestamp = true;
    protected static $deleteTime = 'delete_time';
}