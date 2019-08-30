<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 9:24
 */

namespace app\api\controller;

use app\admin\model\CommunityModel;
use app\admin\model\ExposureModel;
use app\admin\model\HotSubjectModel;
use app\admin\model\JobSeekModel;
use app\admin\model\MemberCollectModel;
use app\admin\model\MemberModel;
use app\admin\model\MemberPraiseModel;
use app\admin\model\RecommendModel;
use app\admin\model\RentHouseModel;
use app\admin\model\UsedProductModel;
use app\admin\model\UserTaskModel;
use app\api\exception\BannerMissException;
use app\index\model\DiningModel;
use app\index\model\HotelModel;
use app\index\model\TaxiModel;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class Recommend
{
    /**
     * 动态列表
     * @return \think\response\Json
     */
    public function index()
    {
        if (!$search = input('search')) {
            if (!$order = input('order')) {
                $order = 3;
            }
        }
        //判断是否有首页推荐状态参数
        if ($recommend = input('recommend')) {
            $recommend_status = 0;
        } else {
            $recommend_status = 1;
        }

        $user_id = getUserId();
        if(!$user_id){
            return jsone('请登录后重试',422);
        }


        try {

        $community = new RecommendModel();
        if ($search) {
            $community = $community->where('title','like','%'.$search.'%')
                ->where('status',0)
                ->order('create_time desc')->paginate(10);
        } else {
            $community = $community->where('status',0)
                ->order('create_time desc')->paginate(10);
        }

        $data['total'] = $community->total();
        $data['per_page'] = $community->listRows();
        $data['current_page'] = $community->currentPage();
        $data['last_page'] = $community->lastPage();
        $data['data'] = [];

        foreach ($community as $k => $val) {
            $member = Db::name('admin')->field('id,reception,portrait')->where('id', 'eq', $val['user_id'])->find();
            $usedImage = Db::name('recommend_file')->where('community_id', 'in', $val['id'])->select();
//                $topic = Db::name('topic_cate')->where('id', 'eq', $val['topic_id'])->select();

            //获取点赞数据
            $praise = Db::name('member_praise')->where('user_id', 'eq', $user_id)
                ->where('module_id', 'eq', $val['id'])
                ->where('module_type', 'eq', 'recommend')
                ->find();

            //获取收藏数据
            $collect = Db::name('member_collect')->where('user_id', 'eq', $user_id)
                ->where('module_id', 'eq', $val['id'])
                ->where('module_type', 'eq', 'recommend_model')
                ->find();

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

//                $val['region'] = $topic[0];
            $val['user'] = $member;
            $val['community_file'] = $usedImage;
            $data['data'][] = $val;
        }
        } catch (Exception $e) {
            throw new BannerMissException();
        }

        return jsone('查询成功', 200, $data);
    }

    /**
     * 动态新增
     * @return \think\response\Json
     */
//    public function save()
//    {
//
//        $data = input();
//
//        //获取用户ID
//        if (!$id = getUserId()) {
//            throw new BannerMissException([
//                'code' => 401,
//                'ertips' => '用户认证失败',
//            ]);
//        }
//        $data['user_id'] = $id;
//        $validate = validate('HotSubject');
//        if (!$validate->check($data)) {
//            throw new BannerMissException([
//                'code' => 422,
//                'ertips' => $validate->getError(),
//            ]);
//        }
//        $sticky = Db::name('sticky')->where('id', $data['sticky_id'])->find();
//        //确定置顶状态，计算置顶结束日期
//        if ($sticky) {
//            //查询积分
//            $integral = MemberModel::where('id', $id)->find();
//            if ($integral['integral'] - $sticky['integral'] >= 0) {
//                $integral->update([
//                    'integral' => $integral['integral'] - $sticky['integral']
//                ], ['id' => $id]);
//
//            } else {
//                throw new BannerMissException([
//                    'code' => 422,
//                    'ertips' => '积分不足'
//                ]);
//            }
//            if ($day = $sticky['day_num']) {
//                $sticky_create_time = time();
//                $sticky_end_time = $sticky_create_time + $day * 24 * 3600;
//            }
//        } else {
//
//            $sticky_create_time = 0;
//            $sticky_end_time = 0;
//        }
//        try {
//            $community = new CommunityModel();
//            $community->user_id = $data['user_id'];
//            $community->body = input('post.body');
//            $community->title = input('post.title');
//            $community->sticky_create_time = $sticky_create_time;
//            $community->sticky_end_time = $sticky_end_time;
//            $community->sticky_status = $data['sticky_id'] ? 0 : 1;
//            $community->status = 0;
//            $community->save();
//
//            //保存图片
//            if($data['path']){
//                $path = explode(',', $data['path']);
//                $paths = [];
//                foreach ($path as $k => $value) {
//                    $paths[$k]['path'] = $value;
//                }
//                if (count($paths)) {
//                    $community->communityFile()->saveAll($paths);
//                }
//            }
//            $data = $community->with('user,communityFile,topic')->select($community->id);
//            //添加积分
//            if($this->addIntegral($id, 'publish')){
//                $explain = '发布成功，积分+5';
//            }else{
//                $explain = '发布成功';
//            }
//        } catch (\Exception $e) {
//            throw new BannerMissException();
//        }
//        return jsone($explain, 201, $data);
//    }

    /**
     * 动态详情
     * @return \think\response\Json
     */
    public function show()
    {
        if (!$id = input('community_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数',
            ]);
        }
        try {
            $community = RecommendModel::with(['communityFile','user'=>function($query){
                $query->field('id,reception,portrait');
            }])->find($id);

            if(!$community){
                return jsone('数据未找到', 400);
            }
            //获取点赞数据
            $praise = Db::name('member_praise')->where('user_id', 'eq', getUserId())
                ->where('module_id', 'eq', $community['id'])
                ->where('module_type', 'eq', 'recommend')
                ->find();

            //获取收藏数据
            $collect = Db::name('member_collect')->where('user_id', 'eq', getUserId())
                ->where('module_id', 'eq', $community->id)
                ->where('module_type', 'eq', 'recommend_model')
                ->find();

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

            $data = $community->toArray();
            $data['user_praise'] = $praise;
            $data['user_collect'] = $collect;
            //增加浏览量
            $community->browse = $community['browse'] + 1;
            $community->save();
        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('查询成功', 200, $data);
    }

    /**
     * 动态点赞
     * @return \think\response\Json
     */
    public function praise()
    {


        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }

        if (!$community_id = input('community_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数'
            ]);
        }
        $explain = '';
        try {
            $community = RecommendModel::get($community_id);
            if (!$community) {
                throw new BannerMissException([
                    'code' => 400,
                    'ertips' => '没有这条数据'
                ]);
            }
            $praise = Db::name('member_praise')
                ->where('module_id', 'eq', $community->id)
                ->where('module_type', 'recommend')
                ->where('user_id', 'eq', $id)
                ->find();
            //判断是否有点赞数据
            if ($praise) {
                //判断点赞数据是否软删除
                if ($praise['delete_time']) {
                    //将软删除恢复
                    Db::name('member_praise')->where('id', $praise['id'])->update(['delete_time' => null]);
                    $community->praise = $community['praise'] + 1;
                    $explain = '点赞成功';
                } else {
                    //软删除点赞
                    Db::name('member_praise')->where('id', $praise['id'])->update(['delete_time' => time()]);
                    $community->praise = $community['praise']>0? $community['praise']- 1:0;
                    $explain = '点赞以取消';
                }

            } else {
                $praise_like = MemberPraiseModel::create([
                    'user_id'=>$id,
                    'module_id'=>$community->id,
                    'module_type'=>'recommend',
                ]);
//                $community->memberPraise()->save(['user_id' => $id, 'module_id' => $community->id]);
                $community->praise = $community['praise'] + 1;
                $explain = '点赞成功';
            }

            $community->save();
            return jsone($explain, 200);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }

    /**
     * 动态收藏
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

        if (!$module_id = input('module_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数'
            ]);
        }
        if (!$module_type = input('module_type')) {
            $module_type = 4;
        }
        $explain = '';

        switch ($module_type) {
            case 1:
                //二手商品
                $module_class = new UsedProductModel();
                $module_type = 'used_product_model';
                $pk = 'id';
                break;
            case 2:
                //房屋出租
                $module_class = new RentHouseModel();
                $module_type = 'rent_house_model';
                $pk = 'id';
                break;
            case 3:
                //求职招聘
                $module_class = new JobSeekModel();
                $module_type = 'job_seek_model';
                $pk = 'id';
                break;
            case 4:
                //社区动态
                $module_class = new CommunityModel();
                $module_type = 'community_model';
                $pk = 'id';
                break;
            case 5:
                //美食
                $module_class = new DiningModel();
                $module_type = 'dining_list_model';
                $pk = 'dining_id';
                break;
            case 6:
                //酒店
                $module_class = new HotelModel();
                $module_type = 'hotel_list_model';
                $pk = 'hotel_id';
                break;
            case 7:
                //打车
                $module_class = new TaxiModel();
                $module_type = 'taxi_list_model';
                $pk = 'taxi_id';
                break;
            case 8:
                //曝光
                $module_class = new ExposureModel();
                $module_type = 'exposure_model';
                $pk = 'id';
                break;
            case 9:
                //热门
                $module_class = new HotSubjectModel();
                $module_type = 'hot_subject_model';
                $pk = 'id';
                break;
            case 10:
                //热门
                $module_class = new RecommendModel();
                $module_type = 'recommend_model';
                $pk = 'id';
                break;
            default:
                throw new BannerMissException([
                    'code' => 404,
                    'ertips' => '请求错误',
                ]);
        }
        $module = $module_class->get($module_id);
        if (!$module) {
            throw new BannerMissException([
                'code' => 404,
                'ertips' => '没有这条数据',
            ]);
        }
        Db::startTrans();
        try {
            $collect = Db::name('member_collect')
                ->where('module_id', 'eq', $module[$pk])
                ->where('module_type', $module_type)
                ->where('user_id', 'eq', $id)
                ->find();

            //判断是否有收藏数据
            if ($collect) {
                //判断点赞数据是否软删除
                if ($collect['delete_time']) {
                    //将软删除恢复
                    Db::name('member_collect')->where('id', $collect['id'])->update(['delete_time' => null]);
                    $module->collect = $module['collect'] + 1;

                    //加积分
                    if($this->addIntegral($id, 'collect')){
                        $explain = '收藏成功，积分+5';
                    }else{
                        $explain = '收藏成功';
                    }
                } else {
                    //软删除收藏
                    Db::name('member_collect')->where('id', $collect['id'])->update(['delete_time' => time()]);
                    $module->collect = $module['collect']>0?$module['collect'] - 1:0;
                    $explain = '以取消收藏';
                }

            } else {
//                return $id.'/'.$module_id.'/'.$module_type;
//                $module->memberCollect()->save(['user_id'=>$id]);
                $memberCollect = Db::name('member_collect')->insert([
                    'user_id' => $id,
                    'module_id' => $module[$pk],
                    'module_type' => $module_type,
                    'create_time' => time(),
                    'update_time' => time()
                ]);
//                return view();
                $module->collect = $module['collect'] + 1;

                //加积分
                if($this->addIntegral($id, 'collect')){
                    $explain = '收藏成功，积分+5';
                }else{
                    $explain = '收藏成功';
                }
            }

            $module->save();
            Db::commit();
            return jsone($explain, 200);
        } catch (Exception $e) {
            Db::rollback();
            throw new BannerMissException();
        }
    }

    /**
     * 修改收藏状态和加积分
     * @param int $id 用户ID
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     *
     */
    public function addIntegral($id, $field)
    {
        //判断 用户id 添加时间 状态0
        //增加状态
        //查询当前用户是否有签到状态
        try {
            $task = Db::name('user_task')->where('id', 'eq', $id)->find();
            if (!$task) {
                $data = [
                    'id' => $id,
                    "$field" => date('Ymd'),
                    "$field" . "_type" => 0,
                ];
                //没有当前用户签到数据，新增一条
                Db::name('user_task')->insert($data);

                //并加积分
                $integral = MemberModel::where('id', 'eq', $id)->find();
                $integral->update(['integral' => $integral['integral'] + 5], ['id' => $integral['id']]);
                return 1;
            } else {
                //与当天日期比较，大于收藏日期，进行加积分操作
                $today = date('Ymd', time());

                if ($today - $task["$field"] || !$task["$field"]) {
                    Db::name('user_task')->where('task_id', $task['task_id'])
                        ->update(["$field" => $today, "$field" . "_type" => 0]);
                    //并加积分
                    $integral = MemberModel::where('id', 'eq', $id)->find();
                    $integral->update(['integral' => $integral['integral'] + 5], ['id' => $integral['id']]);
                    return 1;
                }
                return 0;
            }
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }

}