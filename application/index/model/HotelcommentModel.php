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

        switch ($post['comment_sati'])
        {
            case 0:
                $post['comment_sati'] = '很不满意';
                break;
            case 1:
                $post['comment_sati'] = '很不满意';
                break;
            case 2:
                $post['comment_sati'] = '不满意';
                break;
            case 3:
                $post['comment_sati'] = '一般';
                break;
            case 4:
                $post['comment_sati'] = '满意';
                break;
            case 5:
                $post['comment_sati'] = '超满意';
                break;
        }

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