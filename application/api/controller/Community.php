<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 18:19
 */

namespace app\api\controller;


use app\admin\model\CommunityModel;
use app\admin\model\MemberCollectModel;
use app\admin\model\MemberModel;
use app\admin\model\MemberPraiseModel;
use app\admin\model\UserTaskModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class Community
{
    /**
     * 动态列表
     * @return \think\response\Json
     */
    public function index()
    {
        if (!$search = input('search')) {
            if (!$order = input('order')) {
                throw new BannerMissException([
                    'code' => 400,
                    'ertips' => '缺少必要参数',
                ]);
            }
        }

        $topic = input('topic_id');
//        if($topic = request()->get('topic_id')){
//            throw new BannerMissException([
//                'code'=>400,
//                'ertips'=>'缺少必要参数'
//            ]);
//        }
        try {
            $community = new CommunityModel();
            if ($search) {
                $community->where('body', 'like', '%' . $search . '%');
            } else {
                //$community = $community->where('topic_id', 'eq', $topic);
                switch ($order) {
                    case 1:
                        //热门
                        $community = $community->order('browse', 'desc');
                        break;
                    case 2:
                        //精华
                        $community = $community->where('essence', 'eq', 0)->order('create_time', 'desc');
                        break;
                    default:
                        $community = $community->order('create_time', 'desc');
                }
            }
            $community = $community->paginate(20);
            $data['total'] = $community->total();
            $data['per_page'] = $community->listRows();
            $data['current_page'] = $community->currentPage();
            $data['last_page'] = $community->lastPage();
            $data['data'] = [];
            foreach ($community as $k => $val) {
                $member = Db::name('member')->where('id', 'eq', $val['user_id'])->select();
                $usedImage = Db::name('community_file')->where('community_id', 'in', $val['id'])->select();
                $topic = Db::name('topic_cate')->where('id', 'eq', $val['topic_id'])->select();

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

                $val['region'] = $topic[0];
                $val['user'] = $member[0];
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
    public function save()
    {

        $data = input();

        //获取用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }
        $data['user_id'] = $id;
        $validate = validate('Community');
        if (!$validate->check($data)) {
            throw new BannerMissException([
                'code' => 422,
                'ertips' => $validate->getError(),
            ]);
        }
        $sticky = Db::name('sticky')->where('id', $data['sticky_id'])->find();
        //确定置顶状态，计算置顶结束日期
        if ($sticky) {
            //查询积分
            $integral = MemberModel::where('id', $id)->find();
            if ($integral['integral'] - $sticky['integral'] >= 0) {
                $integral->update([
                    'integral' => $integral['integral'] - $sticky['integral']
                ], ['id' => $id]);

            } else {
                throw new BannerMissException([
                    'code' => 422,
                    'ertips' => '积分不足'
                ]);
            }
            if ($day = $sticky['day_num']) {
                $sticky_create_time = time();
                $sticky_end_time = $sticky_create_time + $day * 24 * 3600;
            }
        } else {

            $sticky_create_time = 0;
            $sticky_end_time = 0;
        }
        try {
            $community = new CommunityModel();
            $community->user_id = $data['user_id'];
            $community->body = input('post.body');
            $community->topic_id = input('post.topic_id');
            $community->sticky_create_time = $sticky_create_time;
            $community->sticky_end_time = $sticky_end_time;
            $community->sticky_status = $data['sticky_id'] ? 0 : 1;
            $community->status = 0;
            $community->save();

            //保存图片
            $path = explode(',', $data['path']);
            $data = [];
            foreach ($path as $k => $value) {
                $data[$k]['path'] = $value;
            }

            if (count($data)) {
                $community->communityFile()->saveAll($data);
            }

//
            $data = $community->with('user,communityFile,topic')->select($community->id);
            //添加积分
            $this->addIntegral($id, 'publish');
        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('创建成功', 201, $data);
    }

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
            $community = CommunityModel::with('communityFile,user,topic')->find($id);
            //获取点赞数据
            $praise = Db::name('member_praise')->where('user_id', 'eq', getUserId())
                ->where('module_id', 'eq', $community['id'])
                ->where('module_type', 'eq', 'community')
                ->find();

            //获取收藏数据
            $collect = Db::name('member_collect')->where('user_id', 'eq', getUserId())
                ->where('module_id', 'eq', $community->id)
                ->where('module_type', 'eq', 'community')
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
            $community = CommunityModel::get($community_id);

            $praise = Db::name('member_praise')
                ->where('module_id', 'eq', $community->id)
                ->where('module_type', 'community')
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
                    $community->praise = $community['praise'] - 1;
                    $explain = '点赞以取消';
                }

            } else {
                $community->memberPraise()->save(['user_id' => $id, 'module_id' => $community->id]);
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

        if (!$community_id = input('community_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数'
            ]);
        }
        $explain = '';

        $community = new CommunityModel();
//        $db = $community->db(false);
//        $db->startTrans();
        try {
            $community = $community->get($community_id);

            $collect = Db::name('member_collect')
                ->where('module_id', 'eq', $community->id)
                ->where('module_type', 'community')
                ->where('user_id', 'eq', $id)
                ->find();
            //判断是否有点赞数据
            if ($collect) {
                //判断点赞数据是否软删除
                if ($collect['delete_time']) {
                    //将软删除恢复
                    Db::name('member_collect')->where('id', $collect['id'])->update(['delete_time' => null]);
                    $community->collect = $community['collect'] + 1;
                    $explain = '收藏成功';
                    //加积分
                    $this->addIntegral($id, 'collect');
                } else {
                    //软删除收藏
                    Db::name('member_collect')->where('id', $collect['id'])->update(['delete_time' => time()]);
                    $community->collect = $community['collect'] - 1;
                    $explain = '以取消收藏';
                }

            } else {
                $community->membercollect()->save(['user_id' => $id, 'module_id' => $community->id]);
                $community->collect = $community['collect'] + 1;
                $explain = '收藏成功';
                //加积分
                $this->addIntegral($id, 'collect');
            }

            $community->save();

            return jsone($explain, 200);
        } catch (Exception $e) {
            $db->rollback();
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
            } else {
                //与当天日期比较，大于收藏日期，进行加积分操作
                $today = date('Ymd', time());

                if ($today - $task["$field"] || !$task["$field"]) {
                    Db::name('user_task')->where('task_id', $task['task_id'])
                        ->update(["$field" => $today, "$field" . "_type" => 0]);
                    //并加积分
                    $integral = MemberModel::where('id', 'eq', $id)->find();
                    $integral->update(['integral' => $integral['integral'] + 5], ['id' => $integral['id']]);
                }
            }
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }

}