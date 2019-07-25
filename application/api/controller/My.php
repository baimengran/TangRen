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

        //实例化收藏模型
        $modules = new MemberCollectModel();
        //查询当前用户收藏的内容
        $modules = $modules::where('user_id', $id)->order('create_time', 'desc')->paginate(20);

        $data['total'] = $modules->total();
        $data['per_page'] = $modules->listRows();
        $data['current_page'] = $modules->currentPage();
        $data['last_page'] = $modules->lastPage();
        foreach ($modules as $module) {

            //转数组
            $moduleValues = $module->module->toArray();

            if ($module['module_type'] == 'dining_list_model') {
                $keys = ['id', 'logo', 'class', 'name', 'content', 'time','day', 'phone',
                    'address', 'all', 'service', 'hygiene', 'taste', 'label', 'status',
                    'home','collect', 'create_time', 'update_time','exits_status'];
                //重新组合数组key和value
                $moduleValues = array_combine($keys, $moduleValues);
                $moduleValues['cate'] = 'dining';
                $moduleValues['hidden'] = 0;
                $moduleValues['image']['path']=$moduleValues['logo'];
            }
            if ($module['module_type'] == 'hotel_list_model') {
                $keys = ['id', 'logo', 'class', 'name', 'content', 'time','day', 'phone',
                    'address', 'all', 'status', 'hygiene', 'ambient', 'service', 'label',
                    'collect', 'create_time', 'update_time','exits_status'];
                $moduleValues = array_combine($keys, $moduleValues);
                $moduleValues['cate'] = 'hotel';
                $moduleValues['hidden'] = 0;
                $moduleValues['image']['path']=$moduleValues['logo'];
            }
            if ($module['module_type'] == 'taxi_list_model') {
                $keys = ['id', 'logo', 'class', 'name', 'content', 'time','day', 'phone',
                    'address', 'speed', 'quality', 'service', 'all', 'label', 'status',
                    'collect', 'create_time', 'update_time','exits_status'];
                $moduleValues = array_combine($keys, $moduleValues);
                $moduleValues['cate'] = 'taxi';
                $moduleValues['hidden'] = 0;
                $moduleValues['image']['path']=$moduleValues['logo'];
            }

            if (array_key_exists('region_id', $moduleValues)) {
                $region_id = $moduleValues['region_id'];
                //获取区域
                $region = Db::name('region_list')->where('region_id', $region_id)->find();

                $re['id'] = $region['region_id'];
                $re['name'] = $region['region_name'];
                $re['status'] = $region['region_status'];
                $re['delete_time'] = $region['delete_time'];
                $re['type'] = 'region';
                $moduleValues['cate'] = $re;
            }
            if (array_key_exists('topic_id', $moduleValues)) {
                $topic_id = $moduleValues['topic_id'];
                //获取话题
                $topic = Db::name('topic_cate')->where('id', $topic_id)->find();
                $topic['type'] = 'topic';
                $moduleValues['cate'] = $topic;
            }
            if (array_key_exists('profession_id', $moduleValues)) {
                $profession_id = $moduleValues['profession_id'];
                //获取行业
                $profession = Db::name('profession_cate')->where('id', $profession_id)->find();
                $profession['type'] = 'profession';
                $moduleValues['cate'] = $profession;
            }
            if (array_key_exists('user_id', $moduleValues)) {

                //获取用户
                $user_id = $moduleValues['user_id'];
                $user = Db::name('member')->where('id', $user_id)->find();
                $moduleValues['user'] = $user;
            }

            //截取数据表名
            $tableName = substr($module['module_type'], 0, strpos($module['module_type'], '_'));
            $field = $tableName . '_id';
//                $lists[] = $tableName;
            if ($module['module_type'] == 'community_model') {
                $tableName = $tableName . '_file';
            } else {
                $tableName = $tableName . '_image';
            }
            //获取图片
//            return $moduleValues;
            if (array_key_exists('id', $moduleValues) && !array_key_exists('hidden', $moduleValues)) {
                $current_id = $moduleValues['id'];
                $images = [];
                if ($module['module_type'] != 'job_seek_model') {
                    $images = Db::name($tableName)->where($field, $current_id)->select();
                }
                $moduleValues['image'] = $images;
            }
            $moduleValues['user_collect']=0;
            $data['data'][] = $moduleValues;
        }

        return $data;
//        return $id;
        try {
            //获取当前用户收藏id
            $modules = Db::name('member_collect')->where('user_id', $id)
//                ->where('module_type','community')
                ->where('delete_time', null)
                ->select();

//            //获取用户以收藏数据
            $community = CommunityModel::with('user,topic,communityFile')
                ->where('id', 'in', $module_ids)
                ->paginate(20);
            $data['total'] = $community->total();
            $data['per_page'] = $community->listRows();
            $data['current_page'] = $community->currentPage();
            $data['last_page'] = $community->lastPage();
            $data['data'] = [];
            foreach ($community as $val) {
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
                    ->where('delete_time', null)
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
                $data['data'][] = $val;
            }


            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }

}