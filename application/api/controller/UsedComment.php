<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 9:59
 */

namespace app\api\controller;


use app\admin\model\UsedCommentModel;
use think\Db;
use think\Exception;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\Log;

class UsedComment
{

    /**
     * 二手商品评论
     * @return \think\response\Json
     */
    public function index()
    {

        if ($id = request()->get('used_id')) {

            try {
                $comments = Db::name('used_comment')->where('used_id', 'eq', $id)->order('create_time', 'desc')->paginate(2);
                $data['total'] = $comments->total();
                $data['per_page'] = $comments->listRows();
                $data['current_page'] = $comments->currentPage();
                $data['last_page'] = $comments->lastPage();
                $data['data'] = [];
                foreach ($comments as $comment) {
                    $user = Db::name('member')->where('id', 'eq', $comment['user_id'])->select();
                    $commentImage = Db::name('used_comment_image')->where('comment_id', 'eq', $comment['id'])->select();
                    $used = Db::name('used_product')->where('id', 'eq', $comment['used_id'])->select();

                    $comment['user'] = $user[0];
                    $comment['comment_image'] = $commentImage;
                    $comment['used'] = $used[0];
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
     * 二手商品评论新增
     * @return \think\response\Json
     */
    public function save()
    {

        $data = request()->post();
        //获取登录用户ID
        $id = getUserId();
        $data['user_id']=$id;
        $validate = validate('UsedComment');
        if (!$validate->check($data)) {
            return jsone($validate->getError(), [], 1, 'error');
        }

        try {
            $usedComment = new UsedCommentModel();
            $usedComment->used_id = $data['used_id'];
            $usedComment->user_id = $data['user_id'];
            $usedComment->body = $data['body'];
            $usedComment->save();

            //保存图片
            if (array_key_exists('path',$data)) {
                $path=[];
                foreach($data['path'] as $value){
                    $value? $path[]['path']=$value:null;
                }
                count($path)?$usedComment->commentImage()->saveAll($path):null;
            }

            $review = $usedComment->used()->find($usedComment['used_id']);
            Db::name('used_product')->where('id', 'eq', $review->id)->update(['review' => $review->review + 1]);
            $data = $usedComment::with('commentImage,user,used')->find($usedComment->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
        return jsone('创建成功', $data);
    }


}