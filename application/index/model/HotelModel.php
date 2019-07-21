<?php
namespace app\index\model;

use think\Db;
use think\Model;

class HotelModel extends Model
{
    /**
     * first方法调用()
     * 查询1个默认地区
     */
    public function address()
    {
        //查询区域分类
        $date = Db::table('think_region_list')
            ->field('region_name')
            ->where('region_status','0')
            ->limit(1)
            ->select();

        if(!$date){
            return $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'没有查到区域'];
        }

        return $date = ['errcode'=> 0,'errMsg'=>'success','retData'=>$date['0']];
    }

    /**
     * first方法调用()
     * 查询4个酒店精选
     */
    public function select()
    {
        //查询品质优选酒店
        $date = Db::table('think_hotel_list')
            ->field('hotel_id,hotel_logo,hotel_name,hotel_all')
            ->where('hotel_status',0)
            ->select();
        if(!$date){
            return $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'暂时没有优选酒店'];
        }
        return $date;
    }
    /**
     * first方法调用()
     * 查询与区分类
     */
    public function region()
    {
        //查询区域分类
        $date = Db::table('think_region_list')
            ->field('region_name')
            ->where('region_status','0')
            ->select();

        if(!$date){
            return $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'没有查到区域'];
        }
        return $date;
    }

    /**
     * first方法调用()
     * 查询与区分类下的酒店
     */
    public function hotel($get)
    {
        //查询区域分类下的酒店
        $date = Db::table('think_hotel_list')
            ->where('hotel_class',$get)
            ->paginate(25);

        if(!$date){
            return $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'这个区域下没有酒店'];
        }

        $date = json_decode(json_encode($date,320),true);

        foreach($date['data'] as $k=>$v)
        {
            $date['data'][$k]['hotel_label'] = json_decode(
                $date['data'][$k]['hotel_label']
            );
        }

        return $date;
    }

    /**
     * services()
     * 获取酒店服务平均分信息
     */
    public function services($hotel_id)
    {
        //查询出酒店评论表所有的酒店服务评分平均值
        $res = Db::table('think_hotel_user')
            ->where('hotel_id',$hotel_id)
            ->sum('comment_service');
        $count = Db::table('think_hotel_user')
            ->where('hotel_id',$hotel_id)
            ->count();
        $date = round($res / $count);

        return $date;
    }
    /**
     * ambient()
     * 获取酒店环境平均分信息
     */
    public function ambient($hotel_id)
    {
        //查询出酒店评论表所有的酒店服务评分平均值
        $res = Db::table('think_hotel_user')
            ->where('hotel_id',$hotel_id)
            ->sum('comment_ambient');
        $count = Db::table('think_hotel_user')
            ->where('hotel_id',$hotel_id)
            ->count();
        $date = round($res / $count);

        return $date;
    }

    /**
     * hygiene()
     * 获取酒店卫生评分信息
     */
    public function hygiene($hotel_id)
    {
        //查询出酒店评论表所有的酒店卫生评分平均值
        $res = Db::table('think_hotel_user')
            ->where('hotel_id',$hotel_id)
            ->sum('comment_hygiene');
        $count = Db::table('think_hotel_user')
            ->where('hotel_id',$hotel_id)
            ->count();
        $date = round($res / $count);

        return $date;
    }
    /**
     * select_comment()
     * 获取酒店综合平均分信息
     */
    public function select_comment($hotel_id)
    {
        //查询出酒店评论表所有的酒店卫生评分平均值
        $res = Db::table('think_hotel_user')
            ->where('hotel_id',$hotel_id)
            ->sum('comment_all');
        $count = Db::table('think_hotel_user')
            ->where('hotel_id',$hotel_id)
            ->count();
        $date = round($res / $count);

        return $date;
    }


    /**
     * update_comment()
     * 更新酒店评分信息
     */
    public function update_comment($hitel_id,$hotel_hygiene,$hotel_ambient,$hotel_service)
    {
        //计算出酒店综合评分
        $zong = $hotel_hygiene + $hotel_ambient + $hotel_service;
        $hotel_all = $zong / 3;

        //查询出酒店评论表所有的酒店评分
        $res = Db::name('hotel_list')
            ->update([
                'hotel_hygiene' =>$hotel_hygiene,
                'hotel_ambient' =>$hotel_ambient,
                'hotel_service' =>$hotel_service,
                'hotel_all'     =>$hotel_all,
                'hotel_id'      =>$hitel_id
            ]);

        return $res;
    }

}