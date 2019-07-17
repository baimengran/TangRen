<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 18:19
 */

namespace app\api\controller;

use think\Db;
use think\Exception;
use think\Log;
use app\admin\model\CommunityCommentModel;

class CommunityComment
{
    public function index()
    {

        if ($id = request()->get('community_id')) {

            try {
                $comments = Db::name('community_comment')->where('community_id', 'eq', $id)->order('create_time', 'desc')->paginate(20);
                $data['total'] = $comments->total();
                $data['per_page'] = $comments->listRows();
                $data['current_page'] = $comments->currentPage();
                $data['last_page'] = $comments->lastPage();
                $data['data'] = [];
                foreach ($comments as $comment) {
                    $user = Db::name('member')->where('id', 'eq', $comment['user_id'])->select();
                    $commentImage = Db::name('community_comment_image')->where('comment_id', 'eq', $comment['id'])->select();
                    $community = Db::name('community')->where('id', 'eq', $comment['community_id'])->select();

                    $comment['user'] = $user[0];
                    $comment['image'] = $commentImage;
                    $comment['community'] = $community[0];
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


    public function save()
    {
        //TODO:图片处理
//        $path = [];
//        if (isset($_FILES['images'])) {
//            $uploads = uploadImage(request()->file('images'), 'communityComment');
//            if (is_array($uploads)) {
//                foreach ($uploads as $value) {
//                    $path[] = ['path' => $value];
//                }
//            } else {
//                return jsone($uploads, [], 1, 'error');
//            }
//        }

        $data = request()->post();
        //获取登录用户ID
        $id = getUserId();
        $data['user_id'] = $id;
        $validate = validate('CommunityComment');
        if (!$validate->check($data)) {
            return jsone($validate->getError(), [], 1, 'error');
        }
//        return $path;
        try {
            $communityComment = CommunityCommentModel::create([
                'user_id' => $data['user_id'],
                'community_id' => $data['community_id'],
                'body' => $data['body']
            ]);

            //保存图片
            if (array_key_exists('path', $data)) {
                $path = [];
                foreach ($data['path'] as $value) {
                    $value ? $path[]['path'] = $value : null;
                }
                count($path) ? $communityComment->commentImage()->saveAll($path) : null;
            }

            $review = $communityComment->community()->find($communityComment['community_id']);

            Db::name('community')->where('id', 'eq', $review->id)->update(['review' => $review->review + 1]);

            $data = $communityComment::with('commentImage,user,community')->find($communityComment->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
        return jsone('创建成功', $data);
    }
}