<?php
namespace app\index\model;

use think\Db;
use think\Model;

class TaxicommentModel extends Model
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
            'taxi_id'           => $post['taxi_id'],
            'id'                => $post['id'],
            'comment_content'   => $post['comment_content'],
            'comment_service'   => $post['comment_service'],
            'comment_speed'     => $post['comment_speed'],
            'comment_quality'   => $post['comment_quality'],
            'comment_all'       => $post['comment_all'],
            'comment_images'    => $post['path'],
            'comment_sati'      => $post['comment_sati'],
            'comment_time'      => date('Y年m月d日',time()),
        ];

        $res = Db::table('think_taxi_user')->insertGetId($data);
        return $res;
    }

    /**
     * update_comment()
     * 更新酒店评分信息
     */
    public function update_comment($taxi_id,$taxi_speed,$taxi_quality,$taxi_service,$taxi_all)
    {
        //查询出酒店评论表所有的酒店评分
        $res = Db::name('taxi_list')
            ->update([
                'taxi_speed'   =>$taxi_speed,
                'taxi_quality' =>$taxi_quality,
                'taxi_service' =>$taxi_service,
                'taxi_all'     =>$taxi_all,
                'taxi_id'      =>$taxi_id
            ]);

        return $res;
    }


    /**
     * services()
     * 获取酒店服务平均分信息
     */
    public function services($taxi_id)
    {
        //查询出酒店评论表所有的酒店服务评分平均值
        $res = Db::table('think_taxi_user')
            ->where('taxi_id',$taxi_id)
            ->sum('comment_service');
        $count = Db::table('think_taxi_user')
            ->where('taxi_id',$taxi_id)
            ->count();
        $date = round($res / $count);

        return $date;
    }
    /**
     * ambient()
     * 获取酒店环境平均分信息
     */
    public function ambient($taxi_id)
    {
        //查询出酒店评论表所有的酒店服务评分平均值
        $res = Db::table('think_taxi_user')
            ->where('taxi_id',$taxi_id)
            ->sum('comment_speed');
        $count = Db::table('think_taxi_user')
            ->where('taxi_id',$taxi_id)
            ->count();
        $date = round($res / $count);

        return $date;
    }

    /**
     * hygiene()
     * 获取酒店卫生评分信息
     */
    public function hygiene($taxi_id)
    {
        //查询出酒店评论表所有的酒店卫生评分平均值
        $res = Db::table('think_taxi_user')
            ->where('taxi_id',$taxi_id)
            ->sum('comment_quality');
        $count = Db::table('think_taxi_user')
            ->where('taxi_id',$taxi_id)
            ->count();
        $date = round($res / $count);

        return $date;
    }
    /**
     * select_comment()
     * 获取酒店综合平均分信息
     */
    public function select_comment($taxi_id)
    {
        //查询出酒店评论表所有的酒店卫生评分平均值
        $res = Db::table('think_taxi_user')
            ->where('taxi_id',$taxi_id)
            ->sum('comment_all');
        $count = Db::table('think_taxi_user')
            ->where('taxi_id',$taxi_id)
            ->count();
        $date = round($res / $count);

        return $date;
    }



}