<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 13:25
 */

namespace app\api\controller;


use app\admin\model\JobCommentModel;
use app\admin\model\JobSeekModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class JobComment
{

    /**
     * 招聘评论列表
     * @return \think\response\Json
     */
    public function index()
    {

        if (!$id = input('job_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数',
            ]);
        }
        try {
            $comments = JobCommentModel::where('job_id', 'eq', $id)->order('create_time', 'desc')->paginate(20);
            $data['total'] = $comments->total();
            $data['per_page'] = $comments->listRows();
            $data['current_page'] = $comments->currentPage();
            $data['last_page'] = $comments->lastPage();
            $data['data'] = [];
            foreach ($comments as $comment) {
                //获取招聘评论对应用户
                $user = Db::name('member')->where('id', 'eq', $comment['user_id'])->select();
                //获取招聘评论对应图片
                $commentImage = Db::name('job_comment_image')->where('comment_id', 'eq', $comment['id'])->select();
                //获取招聘评论对应招聘信息
                $job = JobSeekModel::where('id', 'eq', $comment['job_id'])->select();

                $comment['user'] = $user[0];
                $comment['image'] = $commentImage;
                $comment['job'] = $job[0];
                $data['data'][] = $comment;
            }

            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }

    /**
     * 招聘评论新增
     * @return \think\response\Json
     */
    public function save()
    {

        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败'
            ]);
        }
        $data = input();
        //获取登录用户ID
        $id = getUserId();
        $data['user_id'] = $id;
        $validate = validate('JobComment');
        if (!$validate->check($data)) {
            throw new BannerMissException([
                'code' => 422,
                'ertips' => $validate->getError(),
            ]);
        }

        try {
            $jobComment = JobCommentModel::create([
                'user_id' => $data['user_id'],
                'job_id' => $data['job_id'],
                'body' => $data['body']
            ]);

            //保存图片
            $path = explode(',', $data['path']);
            $data = [];
            foreach ($path as $k => $value) {
                $data[$k]['path'] = $value;
            }

            if (count($data)) {
                $jobComment->commentImage()->saveAll($data);
            }

            $review = $jobComment->job()->find($jobComment['job_id']);

            Db::name('job_seek')->where('id', 'eq', $review->id)->update(['review' => $review->review + 1]);

            $data = $jobComment::with('commentImage,user,job')->find($jobComment->id);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('创建成功', 201, $data);
    }
}