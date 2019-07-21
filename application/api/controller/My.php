<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16
 * Time: 10:08
 */

namespace app\api\controller;


use app\admin\model\CommunityModel;
use app\admin\model\CouponModel;
use app\admin\model\MemberCollectModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\Log;

class My
{
    /**
     * 我的收藏
     * @return \think\response\Json
     */
    public function collect()
    {
        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }
//        return $id;
        try {
            //获取当前用户收藏id
            $module_ids = Db::name('member_collect')->where('user_id', $id)
                ->where('module_type','community')
                ->where('delete_time',null)
                ->column('module_id');

//            //获取以收藏动态
            $community = CommunityModel::with('user,topic,communityFile')
                ->where('id', 'in', $module_ids)
                ->paginate(20);
            $data['total'] = $community->total();
            $data['per_page'] = $community->listRows();
            $data['current_page'] = $community->currentPage();
            $data['last_page'] = $community->lastPage();
            $data['data'] = [];
                foreach($community as $val){
                    //获取点赞数据
                    $praise = Db::name('member_praise')->where('user_id', 'eq', getUserId())
                        ->where('module_id', 'eq', $val['id'])
                        ->where('module_type', 'eq', 'community')
                        ->find();
//                return $praise;
                    //获取收藏数据
                    $collect = Db::name('member_collect')->where('user_id', 'eq', getUserId())
                        ->where('module_id', 'eq', $val['id'])
                        ->where('module_type', 'eq', 'community')
                        ->where('delete_time',null)
                        ->find();

//                $data[]=$community;
                    if (!$praise) {
                        //如果是空，证明没点攒
                        $praise = 1;
                    } else {
                        //如果存在，证明以软删除点赞
                        if ($praise['delete_time']) {
                            $praise = 1;
                        } else {
                            $praise = 0;
                        }
                    }
                    if (!$collect) {
                        //如果是空，证明没点攒
                        $collect = 1;
                    } else {
                        //如果存在，证明以软删除点赞
                        if ($collect['delete_time']) {
                            $collect = 1;
                        } else {
                            $collect = 0;
                        }
                    }

                    $val['user_praise'] = $praise;
                    $val['user_collect'] = $collect;
                    $data['data'][] =$val;
                }


            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }

}