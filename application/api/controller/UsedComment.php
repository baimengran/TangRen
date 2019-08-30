<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 9:59
 */

namespace app\api\controller;


use app\admin\model\UsedCommentModel;
use app\admin\model\UsedProductModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\Log;
use think\Request;
use app\admin\model\MemberModel;

class UsedComment
{

    /**
     * 二手商品评论
     * @return \think\response\Json
     */
    public function index()
    {

        if (!$id = input('used_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数',
            ]);
        }
        try {
            $comments = UsedCommentModel::where('used_id', 'eq', $id)->where('delete_time',0)->order('create_time', 'desc')->paginate(20);
            $data['total'] = $comments->total();
            $data['per_page'] = $comments->listRows();
            $data['current_page'] = $comments->currentPage();
            $data['last_page'] = $comments->lastPage();
            $data['data'] = [];
            foreach ($comments as $comment) {
                $user = Db::name('member')->where('id', 'eq', $comment['user_id'])->find();
                $commentImage = Db::name('used_comment_image')->where('comment_id', 'eq', $comment['id'])->select();
                $used = UsedProductModel::where('id', 'eq', $comment['used_id'])->find();

                $comment['user'] = $user;
                $comment['comment_image'] = $commentImage;
                $comment['used'] = $used;
                $data['data'][] = $comment;
            }

            return jsone('查询成功', 201, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }

    /**
     * 二手商品评论新增
     * @return \think\response\Json
     */
    public function save()
    {
        $data = input();
        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败'
            ]);
        }
        $data['user_id'] = $id;
        $validate = validate('UsedComment');
        if (!$validate->check($data)) {
            throw new BannerMissException([
                'code' => 422,
                'ertips' => $validate->getError()
            ]);
        }

        try {
            $usedComment = new UsedCommentModel();
            $usedComment->used_id = $data['used_id'];
            $usedComment->user_id = $data['user_id'];
            $usedComment->body = $data['body'];
            $usedComment->save();

            //保存图片
            if($data['path']){
                $path = explode(',', $data['path']);
                $data = [];
                foreach ($path as $k => $value) {
                    $data[$k]['path'] = $value;
                }


                if (count($data)) {
                    $usedComment->commentImage()->saveAll($data);
                }
            }

            $review = $usedComment->used()->find($usedComment['used_id']);
            Db::name('used_product')->where('id', 'eq', $review->id)->update(['review' => $review->review + 1]);
            $data = $usedComment::with('commentImage,user,used')->find($usedComment->id);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('发布成功', 201, $data);
    }


}