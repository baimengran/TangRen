<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 18:55
 */

namespace app\api\controller;


use app\admin\model\RentCommentModel;
use think\Db;
use think\Exception;
use think\Log;

class RentComment
{

    public function index()
    {

        if ($id = request()->get('rent_id')) {

            try {
                $comments = Db::name('rent_comment')->where('rent_id', 'eq', $id)->order('create_time', 'desc')->paginate(20);
                $data['total'] = $comments->total();
                $data['per_page'] = $comments->listRows();
                $data['current_page'] = $comments->currentPage();
                $data['last_page'] = $comments->lastPage();
                $data['data'] = [];
                foreach ($comments as $comment) {
                    $user = Db::name('member')->where('id', 'eq', $comment['user_id'])->select();
                    $commentImage = Db::name('used_comment_image')->where('comment_id', 'eq', $comment['id'])->select();
                    $rent = Db::name('rent_house')->where('id', 'eq', $comment['rent_id'])->select();

                    $comment['user'] = $user[0];
                    $comment['comment_image'] = $commentImage;
                    $comment['used'] = $rent[0];
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
        $path = [];
        if (isset($_FILES['images'])) {
            $uploads = uploadImage(request()->file('images'), 'rentComment');
            if (is_array($uploads)) {
                foreach ($uploads as $value) {
                    $path[] = ['path' => $value];
                }
            } else {
                return jsone($uploads, [], 1, 'error');
            }
        }

        $data = request()->post();
        $validate = validate('RentComment');
        if (!$validate->check($data)) {
            return jsone($validate->getError(), [], 1, 'error');
        }
//        return $path;
        try {
            $rentComment = new RentCommentModel();
            $rentComment->rent_id = $data['rent_id'];
            $rentComment->user_id = $data['user_id'];
            $rentComment->body = $data['body'];
            $rentComment->save();
            $rentComment->commentImage()->saveAll($path);
            $review = $rentComment->rent()->find($rentComment['rent_id']);

            Db::name('rent_house')->where('id', 'eq', $review->id)->update(['review' => $review->review + 1]);
            $data = $rentComment::with('commentImage,user,rent')->find($rentComment->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
        return jsone('创建成功', $data);
    }

}