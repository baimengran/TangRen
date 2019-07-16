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
use think\Db;
use think\Exception;
use think\Log;

class JobComment
{

    /**
     * 招聘评论列表
     * @return \think\response\Json
     */
    public function index()
    {
        if ($id = request()->get('job_id')) {
            try {
                $comments = Db::name('job_comment')->where('job_id', 'eq', $id)->order('create_time', 'desc')->paginate(20);
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
                    $job = Db::name('job_seek')->where('id', 'eq', $comment['job_id'])->select();

                    $comment['user'] = $user[0];
                    $comment['image'] = $commentImage;
                    $comment['job'] = $job[0];
                    $data['data'][] = $comment;
                }

                return jsone('查询成功', $data);
            } catch (Exception $e) {
                Log::error($e->getMessage());
                return jsone('服务器错误，请稍候重试', [], 1, 'error');
            }
        } else {
            return jsone('评论加载失败', [], 1, 'error');
        }
    }

    /**
     * 招聘评论新增
     * @return \think\response\Json
     */
    public function save()
    {

        $data = request()->post();
        //获取登录用户ID
        $id = getUserId();
        $data['user_id'] = $id;
        $validate = validate('JobComment');
        if (!$validate->check($data)) {
            return jsone($validate->getError(), [], 1, 'error');
        }

        try {
            $jobComment = JobCommentModel::create([
                'user_id' => $data['user_id'],
                'job_id' => $data['job_id'],
                'body' => $data['body']
            ]);

            //保存图片
            if (array_key_exists('path', $data)) {
                $path = [];
                foreach ($data['path'] as $value) {
                    $value ? $path[]['path'] = $value : null;
                }
                count($path) ? $jobComment->commentImage()->saveAll($path) : null;
            }

            $review = $jobComment->job()->find($jobComment['job_id']);

            Db::name('job_seek')->where('id', 'eq', $review->id)->update(['review' => $review->review + 1]);

            $data = $jobComment::with('commentImage,user,job')->find($jobComment->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
        return jsone('创建成功', $data);
    }
}