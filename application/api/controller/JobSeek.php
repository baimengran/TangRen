<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 13:25
 */

namespace app\api\controller;

use app\admin\model\JobSeekModel;
use think\Db;
use think\Exception;
use think\Log;

class JobSeek
{

    /**
     * 招聘列表
     * @return \think\response\Json
     */
    public function index()
    {
        //区域ID
        $region = request()->get('region_id');
        //行业ID
        $profession = request()->get('profession_id');
        //搜索
        $search = request()->get('search');
        try {
            $job = Db::name('job_seek');

            if ($search) {
                $job->where('body', 'like', '%' . $search . '%');
            }
            if ($region) {
                $job = $job->where('region_id', 'eq', $region);
            }
            if ($profession) {
                $job = $job->where('profession_id', 'eq', $profession);
            }
            $job = $job->order('create_time', 'desc')->paginate(20);

            $data['total'] = $job->total();
            $data['per_page'] = $job->listRows();
            $data['current_page'] = $job->currentPage();
            $data['last_page'] = $job->lastPage();
            $data['data'] = [];
            foreach ($job as $k => $val) {
                //查询对应招聘信息用户
                $member = Db::name('member')->where('id', 'eq', $val['user_id'])->select();
                //查询对应招聘信息行业
                $profession = Db::name('profession_cate')->where('id', 'eq', $val['profession_id'])->select();
                //查询对应招聘信息区域
                $region = Db::name('region_list')->where('region_id', 'eq', $val['region_id'])->select();

                $val['region'] = $region[0];
                $val['user'] = $member[0];
                $val['profession'] = $profession[0];
                $data['data'][] = $val;
            }
            return jsone('查询成功', $data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }

    }

    /**
     * 发布求职招聘
     * @return \think\response\Json
     */
    public function save()
    {
        $data = request()->post();
        //获取用户ID
        $id = getUserId();
        $data['user_id'] = $id;
        $validate = validate('JobSeek');
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
            $job = JobSeekModel::create([
                'user_id' => $data['user_id'],
                'region_id' => array_key_exists('region_id', $data) ? $data['region_id'] ? $data['region_id'] : 1 : 1,
                'profession_id' => $data['profession_id'],
                'body' => $data['body'],
                'salary' => $data['salary'],
                'sticky_create_time' => $sticky_create_time,
                'sticky_end_time' => $sticky_end_time,
                'sticky_status' => $day ? 0 : 1,
                'phone' => $data['phone'],
            ]);

            $data = JobSeekModel::with('user,Profession,region')->select($job->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
        return $data ? jsone('创建成功', $data) : json('创建失败', [], 1, 'error');
    }

    /**
     * 求职招聘详情
     * @return \think\response\Json
     */
    public function show()
    {

        $id = request()->get('job_id');
        try {
            $job = JobSeekModel::with('user,region,profession')->find($id);
            $job->browse = $job['browse'] + 1;
            $job->save();

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], '1', 'error');
        }
        return jsone('查询成功', $job);
    }

    /**
     * 点赞
     * @return \think\response\Json
     */
    public function praise()
    {
        //获取登录用户ID
        $id = getUserId();
        $explain = '';
        if ($id) {
            try {
                $job = JobSeekModel::get(request()->get('job_id'));

                $praise = Db::name('member_praise')
                    ->where('module_id', 'eq', $job->id)
                    ->where('module_type', 'job_seek')
                    ->where('user_id', 'eq', $id)
                    ->find();

                //判断是否有点赞数据
                if ($praise) {
                    //判断点赞数据是否软删除
                    if ($praise['delete_time']) {
                        //将软删除恢复
                        Db::name('member_praise')->where('id', $praise['id'])->update(['delete_time' => null]);
                        $job->praise = $job['praise'] + 1;
                        $explain = '点赞成功';
                    } else {
                        //软删除点赞
                        Db::name('member_praise')->where('id', $praise['id'])->update(['delete_time' => time()]);
                        $job->praise = $job['praise'] - 1;
                        $explain = '点赞以取消';
                    }
                } else {
                    $job->memberPraise()->save(['user_id' => $id, 'module_id' => $job->id]);
                    $job->praise = $job['praise'] + 1;
                    $explain = '点赞成功';
                }

                $job->save();
                return jsone($explain, $job->with('user,region,profession')->find($job->id));
            } catch (Exception $e) {
                Log::error($e->getMessage());
                return jsone('服务器错误，请稍候重试', [], 1, 'error');
            }
        } else {
            return jsone('请登录后重试', [], 1, 'error');
        }
    }
}