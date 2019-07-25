<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 13:25
 */

namespace app\api\controller;

use app\admin\model\JobSeekModel;
use app\admin\model\MemberModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class JobSeek
{

    /**
     * 招聘列表
     * @return \think\response\Json
     */
    public function index()
    {
        if (!$search = input('search')) {
            //搜索不存在时设定区域参数
            if (!$profession = input('profession_id')) {
                throw new BannerMissException([
                    'code' => 400,
                    'ertips' => '缺少必要参数'
                ]);
            }
        }
//        return JobSeekModel::get(1);
        //区域ID
        $region = input('region_id');
        //行业ID
        $profession = input('profession_id');
        //搜索

        try {
            //查询有置顶的动态
            $jobSticky = new JobSeekModel();
            $stickies = $jobSticky->where('sticky_status',0)->select();
            //检查置顶是否过期
            $updates=[];
            foreach($stickies as $sticky){
                if(time()>$sticky['sticky_end_time']){

                    $val['id']=$sticky['id'];
                    $val['sticky_status']=1;
                    $updates[]=$val;
                }
            }
            //批量更新过期置顶数据
            $jobSticky->saveAll($updates);

            $job = new JobSeekModel();

            if ($search) {
                $jobs= Db::name('job_seek')->where('body', 'like', '%' . $search . '%')->buildSql();
            } else {
                $jobs = Db::name('job_seek')->where('profession_id',$profession)->buildSql();
            }
            $jobSticky = Db::name('job_seek')->where('sticky_status',0)->union($jobs)->buildSql();
            $job= $job->table($jobSticky.'a')->order('sticky_status asc ,create_time desc')->paginate(20);




            $data['total'] = $job->total();
            $data['per_page'] = $job->listRows();
            $data['current_page'] = $job->currentPage();
            $data['last_page'] = $job->lastPage();
            $data['data'] = [];
            foreach ($job as $k => $val) {
                //查询对应招聘信息用户
                $member = Db::name('member')->where('id', 'eq', $val['user_id'])->find();
                //查询对应招聘信息行业
                $profession = Db::name('profession_cate')->where('id', 'eq', $val['profession_id'])->find();
                //查询对应招聘信息区域
                $region = Db::name('region_list')->where('region_id', 'eq', $val['region_id'])->find();

                //获取点赞数据
                $praise = Db::name('member_praise')->where('user_id','eq',getUserId())
                    ->where('module_id','eq',$val['id'])
                    ->where('module_type','eq','job_seek')
                    ->find();
//                return $praise;
                //获取收藏数据
                $collect = Db::name('member_collect')->where('user_id','eq',getUserId())
                    ->where('module_id','eq',$val['id'])
                    ->where('module_type','eq','job_seek_model')
                    ->find();
//                $data[]=$community;
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
                $val['user_praise']=$praise;
                $val['user_collect']=$collect;
                $val['user'] = $member;
                $val['profession'] = $profession;
                $data['data'][] = $val;
            }
            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }

    /**
     * 发布求职招聘
     * @return \think\response\Json
     */
    public function save()
    {

        //获取用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }

        $data = input();
        $data['user_id'] = $id;
        $validate = validate('JobSeek');
        if (!$validate->check($data)) {
            throw new BannerMissException([
                'code' => 422,
                'ertips' => $validate->getError(),
            ]);
        }
        $sticky = Db::name('sticky')->where('id', $data['sticky_id'])->find();
//        return $sticky;
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
                    'code'=>422,
                    'ertips'=>'积分不足'
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
            $job = JobSeekModel::create([
                'user_id' => $data['user_id'],
                'region_id' => array_key_exists('region_id', $data) ? $data['region_id'] ? $data['region_id'] : 1 : 1,
                'profession_id' => $data['profession_id'],
                'body' => $data['body'],
                'salary' => $data['salary'],
                'sticky_create_time' => $sticky_create_time,
                'sticky_end_time' => $sticky_end_time,
                'sticky_status' => $data['sticky_id'] ? 0 : 1,
                'phone' => $data['phone'],
            ]);

            $data = JobSeekModel::with('user,Profession,region')->find($job->id);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('创建成功', 201, $data);
    }

    /**
     * 求职招聘详情
     * @return \think\response\Json
     */
    public function show()
    {
        if (!$job_id = input('job_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数'
            ]);
        }

        try {
            $job = JobSeekModel::with('user,region,profession')->find($job_id);

            //获取点赞数据
            $praise = Db::name('member_praise')->where('user_id','eq',getUserId())
                ->where('module_id','eq',$job['id'])
                ->where('module_type','eq','job_seek')
                ->find();

            //获取收藏数据
            $collect = Db::name('member_collect')->where('user_id','eq',getUserId())
                ->where('module_id','eq',$job->id)
                ->where('module_type','eq','job_seek_model')
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

            $data=$job->toArray();
            $data['user_praise']=$praise;
            $data['user_collect']=$collect;
            $job->browse = $job['browse'] + 1;
            $job->save();

        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('查询成功', 200, $data);
    }

    /**
     * 点赞
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

        if (!$job_id = input('job_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数'
            ]);
        }
        $explain = '';

        try {
            $job = JobSeekModel::get($job_id);

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
            return jsone($explain, 200);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }
}