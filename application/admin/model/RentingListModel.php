<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/11
 * Time: 18:17
 */

namespace app\admin\model;


use think\Model;

class RentingListModel extends Model
{
    protected $name = 'renting_list';

    // 开启自动写入时间戳字段
//    protected $autoWriteTimestamp = true;


    /**
     * 根据搜索条件获取用户列表信息
     * @author [田建龙] [864491238@qq.com]
     */
    public function getRentingByWhere($map, $Nowpage, $limits)
    {
        return $this->field('think_renting_list.*,renting_content')->join('think_user_list', 'think_renting_list.user_id = think_user_list.user_id')->where($map)->page($Nowpage, $limits)->order('renting_id desc')->select();
    }
}