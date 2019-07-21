<?php
namespace app\index\model;

use think\Db;
use think\Model;

class HotelcommentModel extends Model
{

    public function com_add($post)
    {
        //计算出酒店综合评分
        $comment_all = $post['comment_hygiene'] + $post['comment_ambient'] + $post['comment_service'];
        $post['comment_all'] = $comment_all / 3;

        //执行添加操作
        $data = [
            'hotel_id'          => $post['hotel_id'],
            'id'                => $post['id'],
            'comment_content'   => $post['comment_content'],
            'comment_service'   => $post['comment_service'],
            'comment_ambient'   => $post['comment_ambient'],
            'comment_hygiene'   => $post['comment_hygiene'],
            'images'            => $post['path'],
            'comment_all'       => $post['comment_all'],
            'comment_sati'       => $post['comment_sati'],
            'comment_time'      => date('Y年m月d日',time()),
        ];

        $res = Db::table('think_hotel_user')->insertGetId($data);
        return $res;
    }
}