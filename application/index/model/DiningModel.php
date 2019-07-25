<?php
namespace app\index\model;

use think\Db;
use think\Model;

class DiningModel extends Model
{

    protected $name='dining_list';
    protected $pk = 'dining_id';

    public function memberCollect(){
        return $this->morphMany('MemberCollectModel','module');
    }

    /**
     * index方法调用()
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
     * 查询4个美食推荐
     */
    public function select()
    {
        //查询品质优选酒店
        $date = Db::table('think_dining_list')
            ->field('dining_id,dining_logo,dining_name,dining_all')
            ->where('dining_status',0)
            ->select();
        if(!$date){
            return $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'暂时没有精品推荐'];
        }
        return $date;
//        return $date = ['errcode'=> 0,'errMsg'=>'success','retData'=>$date['0']];
    }

    /**
     * index方法调用
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
     * index方法调用
     * 查询与区分类下的餐厅
     */
    public function dining($get,$user_id)
    {
        //查询区域分类下的酒店
        $date = Db::table('think_dining_list')
            ->where('dining_class',$get)
            ->paginate(25);

        if(!$date){
            return $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>'这个区域下没有酒店'];
        }

        $date = json_decode(json_encode($date,320),true);

        foreach($date['data'] as $k=>$v)
        {
            $date['data'][$k]['dining_label'] = json_decode(
                $date['data'][$k]['dining_label']
            );
            //获取点赞数据
            $praise = Db::name('member_praise')->where('user_id','eq',$user_id)
                ->where('module_id','eq',$date['data'][$k]['dining_id'])
                ->where('module_type','eq','dining_list_model')
                ->find();

            //获取收藏数据
            $collect = Db::name('member_collect')->where('user_id','eq',$user_id)
                ->where('module_id','eq',$date['data'][$k]['dining_id'])
                ->where('module_type','eq','dining_list_model')
                ->find();

            if(!$praise){
                //如果是空，证明没点攒
                $praise=1;
            }else{
                //如果存在，证明以软删除点赞
                if($praise['delete_time']){
                    $praise=1;
                }else{
                    $praise=0;
                }
            }
            if(!$collect){
                //如果是空，证明没点攒
                $collect=1;
            }else{
                //如果存在，证明以软删除点赞
                if($collect['delete_time']){
                    $collect=1;
                }else{
                    $collect=0;
                }
            }


            $date['data'][$k]['user_praise']=$praise;
            $date['data'][$k]['user_collect']=$collect;
        }

        return $date;
    }

    /**
     * search方法调用
     * 查询与区分类下的餐厅
     */
    public function type_dining($id)
    {
        $date = Db::table('think_dining_list')
            ->where('dining_id',$id)
            ->select();
        return $date;
    }

    /**
     * search方法调用
     * 查询与区分类下的餐厅
     */
    public function type_hotel($id)
    {
        $date = Db::table('think_hotel_list')
            ->where('hotel_id',$id)
            ->select();
        return $date;
    }

    /**
     * search方法调用
     * 查询与区分类下的餐厅
     */
    public function type_taxi($id)
    {
        $date = Db::table('think_taxi_list')
            ->where('taxi_id',$id)
            ->select();
        return $date;
    }


}
