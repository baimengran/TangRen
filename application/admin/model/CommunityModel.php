<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 18:20
 */

namespace app\admin\model;


use think\Model;

class CommunityModel extends Model
{
    protected $name = 'community';
    protected $autoWriteTimestamp = true;

    public function user()
    {
        return $this->belongsTo('MemberModel', 'user_id');
    }

    public function topic()
    {
        return $this->belongsTo('TopicCateModel', 'topic_id');
    }

    public function communityFile()
    {
        return $this->hasMany('CommunityFileModel', 'community_id');
    }
}