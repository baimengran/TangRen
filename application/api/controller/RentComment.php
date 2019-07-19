<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 18:55
 */

namespace app\api\controller;


use app\admin\model\RentCommentModel;
use app\admin\model\RentHouseModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class RentComment
{

    /**
     * 房屋出租评论列表
     * @return \think\response\Json
     */
    public function index()
    {
        if (!Request::instance()->isGet()) {
            throw new BannerMissException([
                'code' => 405,
                'ertips' => '请求错误',
            ]);
        }

        if (!$id = request()->get('rent_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数',
            ]);
        }
        try {
            $comments = RentCommentModel::where('rent_id', 'eq', $id)->order('create_time', 'desc')->paginate(20);
            $data['total'] = $comments->total();
            $data['per_page'] = $comments->listRows();
            $data['current_page'] = $comments->currentPage();
            $data['last_page'] = $comments->lastPage();
            $data['data'] = [];
            foreach ($comments as $comment) {
                $user = Db::name('member')->where('id', 'eq', $comment['user_id'])->select();
                $commentImage = Db::name('used_comment_image')->where('comment_id', 'eq', $comment['id'])->select();
                $rent = RentHouseModel::where('id', 'eq', $comment['rent_id'])->select();

                $comment['user'] = $user[0];
                $comment['comment_image'] = $commentImage;
                $comment['used'] = $rent[0];
                $data['data'][] = $comment;
            }

            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }

    /**
     * 房屋评论新增
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
        $data = request()->post();
        $data['user_id'] = $id;

        $validate = validate('RentComment');
        if (!$validate->check($data)) {
            throw new BannerMissException([
                'code' => 422,
                'ertips' => $validate->getError(),
            ]);
        }

        try {
            $rentComment = new RentCommentModel();
            $rentComment->rent_id = $data['rent_id'];
            $rentComment->user_id = $data['user_id'];
            $rentComment->body = $data['body'];
            $rentComment->save();

            //保存图片
            if (array_key_exists('path', $data)) {
                $path = [];
                foreach ($data['path'] as $value) {
                    $value ? $path[]['path'] = $value : null;
                }
                count($path) ? $rentComment->commentImage()->saveAll($path) : null;
            }

            $review = $rentComment->rent()->find($rentComment['rent_id']);

            Db::name('rent_house')->where('id', 'eq', $review->id)->update(['review' => $review->review + 1]);
            $data = $rentComment::with('commentImage,user,rent')->find($rentComment->id);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('创建成功', 201, $data);
    }

}