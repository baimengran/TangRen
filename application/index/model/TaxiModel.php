<?php
namespace app\index\model;

use think\Db;
use think\Model;

class TaxiModel extends Model
{
    /**
     * index方法调用
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
     * index方法调用
     * 查询品质优选(最多4个)
     */
    public function select()
    {
        //查询品质优选酒店
        $date = Db::table('think_taxi_list')
            ->field('taxi_id,taxi_logo,taxi_name,taxi_all')
            ->where('taxi_status',0)
            ->select();
        if(!$date){
            return $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'暂时没有优选汽车'];
        }
        return $date;
    }

    /**
     * index方法调用()
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
     * index方法调用 taxi()
     * 查询与区分类下的汽车公司
     */
    public function taxi($get)
    {
        //查询区域分类下的汽车公司
        $date = Db::table('think_taxi_list')
            ->where('taxi_class',$get)
            ->paginate(25);

        if(!$date){
            return $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'这个区域下没有汽车'];
        }

        $date = json_decode(json_encode($date,320),true);

        foreach($date['data'] as $k=>$v)
        {
            $date['data'][$k]['taxi_label'] = json_decode(
                $date['data'][$k]['taxi_label']
            );
        }

        return $date;
    }



}