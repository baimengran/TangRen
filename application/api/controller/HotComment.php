<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 0:40
 */

namespace app\api\controller;


use app\admin\model\CommunityModel;
use app\admin\model\HotCommentModel;
use app\admin\model\HotSubjectModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\Log;
use app\admin\model\CommunityCommentModel;
use think\Request;

class HotComment
{
    public function index()
    {

        if (!$id = input('community_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数',
            ]);
        }

        try {
            $comments = HotCommentModel::where('community_id', 'eq', $id)->where('delete_time',0)->order('create_time', 'desc')->paginate(20);
            $data['total'] = $comments->total();
            $data['per_page'] = $comments->listRows();
            $data['current_page'] = $comments->currentPage();
            $data['last_page'] = $comments->lastPage();
            $data['data'] = [];
            foreach ($comments as $comment) {
                $user = Db::name('member')->where('id', 'eq', $comment['user_id'])->find();
                $commentImage = Db::name('hot_comment_image')->where('comment_id', 'eq', $comment['id'])->select();
                $community = HotSubjectModel::where('id', 'eq', $comment['community_id'])->find();

                $comment['user'] = $user;
                $comment['image'] = $commentImage;
                $comment['community'] = $community;
                $data['data'][] = $comment;
            }

            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }


    public function save()
    {

        $data = input();
        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }
        $data['user_id'] = $id;
        $validate = validate('HotComment');
        if (!$validate->check($data)) {
            throw new BannerMissException([
                'code' => 422,
                'ertips' => $validate->getError(),
            ]);
        }
//        return $path;
        try {
            $communityComment = HotCommentModel::create([
                'user_id' => $data['user_id'],
                'community_id' => $data['community_id'],
                'body' => $data['body']
            ]);

            //保存图片
            if($data['path']){
                $path = explode(',', $data['path']);
                $data = [];
                foreach ($path as $k => $value) {
                    $data[$k]['path'] = $value;
                }

                if (count($data)) {
                    $communityComment->commentImage()->saveAll($data);
                }
            }

            $review = $communityComment->community()->find($communityComment['community_id']);
            Db::name('hot_subject')->where('id', 'eq', $review->id)->update(['review' => $review->review + 1]);
            $data = $communityComment::with('commentImage,user,community')->find($communityComment->id);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('创建成功', 201, $data);
    }
}