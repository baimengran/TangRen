<?php
namespace app\index\model;

use think\Db;
use think\Model;

class DiningcommentModel extends Model
{
    /**
     * 添加评论方法
     * 接收：用户ID 用户评价叫车表所有字段，用户评价叫车图片
     * 返回：评论成功信息或失败信息
     */
    public function com_add($post)
    {
        switch ($post['comment_sati'])
        {
            case 0:
                $post['comment_sati'] = '很不满意';
            break;
            case 1:
                $post['comment_sati'] = '不满意';
                break;
            case 2:
                $post['comment_sati'] = '一般';
                break;
            case 3:
                $post['comment_sati'] = '满意';
                break;
            case 4:
                $post['comment_sati'] = '超满意';
                break;
        }
        //执行添加操作
        $data = [
            'dining_id'         => $post['dining_id'],
            'id'                => $post['id'],
            'comment_content'   => $post['comment_content'],
            'comment_service'   => $post['comment_service'],
            'comment_hygiene'   => $post['comment_hygiene'],
            'comment_taste'     => $post['comment_taste'],
            'comment_all'       => $post['comment_all'],
            'comment_sati'      => $post['comment_sati'],
            'comment_images'    => $post['path'],
            'comment_time'      => date('Y年m月d日',time()),
        ];

        $res = Db::table('think_dining_user')->insertGetId($data);
        return $res;
    }

    /**
     * services()
     * 获取服务平均分信息
     */
    public function services($dining_id)
    {
        //查询出酒店评论表所有的酒店服务评分平均值
        $res = Db::table('think_dining_user')
            ->where('dining_id',$dining_id)
            ->sum('comment_service');
        $count = Db::table('think_dining_user')
            ->where('dining_id',$dining_id)
            ->count();
        $date = round($res / $count);

        return $date;
    }

    /**
     * ambient()
     *获取味道平均分信息
     */
    public function ambient($id)
    {
        //查询出酒店评论表所有的酒店服务评分平均值
        $res = Db::table('think_dining_user')
            ->where('dining_id',$id)
            ->sum('comment_taste');
        $count = Db::table('think_dining_user')
            ->where('dining_id',$id)
            ->count();
        $date = round($res / $count);

        return $date;
    }

    /**
     * hygiene()
     * 获取酒店卫生评分信息
     */
    public function hygiene($id)
    {
        //查询出酒店评论表所有的酒店卫生评分平均值
        $res = Db::table('think_dining_user')
            ->where('dining_id',$id)
            ->sum('comment_hygiene');
        $count = Db::table('think_dining_user')
            ->where('dining_id',$id)
            ->count();
        $date = round($res / $count);

        return $date;
    }
    /**
     * select_comment()
     * 获取餐厅综合平均分信息
     */
    public function select_comment($id)
    {
        //查询出酒店评论表所有的酒店卫生评分平均值
        $res = Db::table('think_dining_user')
            ->where('dining_id',$id)
            ->sum('comment_all');
        $count = Db::table('think_dining_user')
            ->where('dining_id',$id)
            ->count();
        $date = round($res / $count);

        return $date;
    }

    /**
     * update_comment()
     * 更新美食评分信息
     */
    public function update_comment($dining_id,$dining_taste,$dining_hygiene,$dining_service,$dining_all)
    {
        //查询出酒店评论表所有的酒店评分
        $res = Db::name('dining_list')
            ->update([
                'dining_taste'   =>$dining_taste,
                'dining_hygiene' =>$dining_hygiene,
                'dining_service' =>$dining_service,
                'dining_all'     =>$dining_all,
                'dining_id'      =>$dining_id
            ]);

        return $res;
    }



}