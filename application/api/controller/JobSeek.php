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

    public function index()
    {
        $region = request()->get('region_id');
        $profession = request()->get('profession_id');
        try {
            $job = Db::name('job_seek');
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
                $member = Db::name('member')->where('id', 'eq', $val['user_id'])->select();
                $profession = Db::name('profession_cate')->where('id', 'in', $val['profession_id'])->select();
                $region = Db::name('region_list')->where('region_id', 'eq', $val['region_id'])->select();

                $val['region'] = $region[0];
                $val['user'] = $member[0];
                $val['profession'] = $profession[0];
                $data['data'][] = $val;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
//return view();
        return jsone('查询成功', $data);
    }

    public function save()
    {
//        $path = [];
//        if (isset($_FILES['images'])) {
//            // 获取表单上传文件 例如上传了001.jpg
//            $files = request()->file('images');
//            //图片上传处理
////           return $uploads = uploadImage($files, 'used');
//            if (is_array($uploads = uploadImage($files, 'used'))) {
//                foreach ($uploads as $value) {
//                    $path[] = ['path' => $value];
//                }
//            } else {
//                return jsone($uploads, [], 1, 'error');
//            }
//        }
        $data = request()->post();
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
                'region_id' => $data['region_id'] ? $data['region_id'] : null,
                'profession_id' => $data['profession_id'],
                'body' => $data['body'],
                'salary' => $data['salary'],
                'sticky_create_time' => $sticky_create_time,
                'sticky_end_time' => $sticky_end_time,
                'sticky_status' => $day ? 0 : 1,
                'phone' => $data['phone'],
            ]);

            //保存图片
//            if (count($path)) {
//                $used_Product->usedImage()->saveAll($path);
//
//            }
            $data = JobSeekModel::with('user,Profession,region')->select($job->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
        return $data ? jsone('创建成功', $data) : json('创建失败', [], 1, 'error');
    }

    public function show()
    {
        $id = request()->get('job_id');
//        if($id = request()->get('used_id')) {
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

    public function praise()
    {
        $id = request()->get('job_id');
        if ($id) {
            try {
                $job = JobSeekModel::get($id);
                $job->praise = $job['praise'] + 1;
                $job->save();
                return jsone('点赞成功', $job->with('user,region,profession')->find($job->id));
            } catch (Exception $e) {
                Log::error($e->getMessage());
                return jsone('服务器错误，请稍候重试', [], 1, 'error');
            }
        } else {
            return jsone('请选择正确招聘信息点赞', [], 1, 'error');
        }
    }
}