<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/11
 * Time: 16:35
 */

namespace app\admin\model;


use think\Model;

class UsedProductModel extends Model
{
    protected $name = 'used_product';
// 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function usedImage()
    {
        return $this->hasMany('UsedImageModel', 'used_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('MemberModel', 'user_id');
    }

    public function regionList()
    {
        return $this->belongsTo('RegionListModel', 'region_id', 'region_id');
    }

    public function memberPraise()
    {
        return $this->morphMany('MemberPraiseModel', 'module');
    }

    /**
     * 根据搜索条件获取用户列表信息
     * @author [田建龙] [864491238@qq.com]
     */
    public function getUsedByWhere($map, $Nowpage, $limits)
    {
        return $this->field('think_used_product.*,title')->join('think_user_list', 'think_used_product.user_id =think_user_list.user_id')->where($map)->page($Nowpage, $limits)->order('id desc')->select();
    }
}