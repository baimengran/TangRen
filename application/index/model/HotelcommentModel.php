<?php
namespace app\index\model;

use think\Db;
use think\Model;

class HotelcommentModel extends Model
{

    public function com_add($post)
    {
        //执行添加操作
        $data = [
            'hotel_id'          => $post['hotel_id'],
            'id'                => $post['id'],
            'comment_content'   => $post['comment_content'],
            'comment_service'   => $post['comment_service'],
            'comment_ambient'   => $post['comment_ambient'],
            'comment_hygiene'   => $post['comment_hygiene'],
            'comment_all'       => $post['comment_all'],
            'images'            => $post['images'],
            'comment_time'      => time(),
        ];

        $res = Db::table('think_hotel_user')->insertGetId($data);
        return $res;
    }
}