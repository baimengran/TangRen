<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 18:19
 */

namespace app\api\controller;


use app\admin\model\CommunityModel;
use think\Db;
use think\Exception;
use think\Log;

class Community
{
    /**
     * 动态列表
     * @return \think\response\Json
     */
    public function index()
    {
        $order = request()->get('order');
        $topic = request()->get('topic_id');

        try {
            $community = Db::name('community');
            $search = request()->get('search');
            if ($search) {
                $community->where('body', 'like', '%' . $search . '%');
            }
            if ($topic) {
                $community = $community->where('topic_id', 'eq', $topic);
            }
            switch ($order) {
                case 1:
                    //热门
                    $community = $community->order('browse', 'desc')->paginate(20);
                    break;
                case 2:
                    //精华
                    $community = $community->where('essence', 'eq', 0)->order('create_time', 'desc')->paginate(20);
                    break;
                default:
                    $community = $community->order('create_time', 'desc')->paginate(20);
            }

            $data['total'] = $community->total();
            $data['per_page'] = $community->listRows();
            $data['current_page'] = $community->currentPage();
            $data['last_page'] = $community->lastPage();
            $data['data'] = [];
            foreach ($community as $k => $val) {
                $member = Db::name('member')->where('id', 'eq', $val['user_id'])->select();
                $usedImage = Db::name('community_file')->where('community_id', 'in', $val['id'])->select();
                $topic = Db::name('topic_cate')->where('id', 'eq', $val['topic_id'])->select();

                $val['region'] = $topic[0];
                $val['user'] = $member[0];
                $val['community_file'] = $usedImage;
                $data['data'][] = $val;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }

        return jsone('查询成功', $data);
    }

    /**
     * 动态新增
     * @return \think\response\Json
     */
    public function save()
    {

        $data = request()->post();

        //获取用户ID
        $id = getUserId();
        $data['user_id'] = $id;
        $validate = validate('Community');
        if (!$validate->check($data)) {
            return jsone($validate->getError(), [], 1, 'error');
        }
        //确定置顶状态，计算置顶结束日期
        if ($day = input('post.sticky_num')) {
            $sticky_create_time = time();
            $sticky_end_time = $sticky_create_time + $day * 24 * 3600;
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
            $community->sticky_status = $day ? 0 : 1;
            $community->status = 0;
            $community->save();
            //保存图片
            if (array_key_exists('path', $data)) {
                $path = [];
                foreach ($data['path'] as $value) {
                    $value ? $path[]['path'] = $value : null;
                }
                count($path) ? $community->communityFile()->saveAll($path) : null;
            }
            $data = $community->with('user,communityFile,topic')->select($community->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
        return $data ? jsone('创建成功', $data) : json('创建失败', [], 1, 'error');
    }

    /**
     * 动态详情
     * @return \think\response\Json
     */
    public function show()
    {
        $id = request()->get('community_id');
        try {
            $community = CommunityModel::with('communityFile,user,topic')->find($id);
            $community->browse = $community['browse'] + 1;
            $community->save();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], '1', 'error');
        }
        return jsone('查询成功', $community);
    }

    /**
     * 动态点赞
     * @return \think\response\Json
     */
    public function praise()
    {
        //获取登录用户ID
        $id = getUserId();
        $explain = '';
        if ($id) {
            try {
                $community = CommunityModel::get(request()->get('community_id'));

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
                return jsone($explain, $community->with('user,communityFile,topic')->find($community->id));
            } catch (Exception $e) {
                Log::error($e->getMessage());
                return jsone('服务器错误，请稍候重试', [], 1, 'error');
            }
        } else {
            return jsone('请登录后重试', [], 1, 'error');
        }
    }

    /**
     * 动态收藏
     * @return \think\response\Json
     */
    public function collect()
    {
        $id = getUserId();
        $explain = '';
        if ($id) {
            try {
                $community = CommunityModel::get(request()->get('community_id'));

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
                    } else {
                        //软删除点赞
                        Db::name('member_collect')->where('id', $collect['id'])->update(['delete_time' => time()]);
                        $community->collect = $community['collect'] - 1;
                        $explain = '以取消收藏';
                    }

                } else {

                    $user_id = 1;
                    $community->membercollect()->save(['user_id' => $id, 'module_id' => $community->id]);
                    $community->collect = $community['collect'] + 1;
                    $explain = '收藏成功';
                }

                $community->save();
                return jsone($explain, $community->with('user,communityFile,topic')->find($community->id));
            } catch (Exception $e) {
                Log::error($e->getMessage());
                return jsone('服务器错误，请稍候重试', [], 1, 'error');
            }
        } else {
            return jsone('请登录后重试', [], 1, 'error');
        }
    }

}