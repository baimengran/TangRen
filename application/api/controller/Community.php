<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/14
 * Time: 18:19
 */

namespace app\api\controller;


use app\admin\model\CommunityModel;
use think\Db;
use think\Exception;
use think\Log;

class Community
{
    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $order = request()->get('order');
        $topic = request()->get('topic');

        try {
            $community = Db::name('community');
            if ($topic) {
                $community = $community->where('topic_id', 'eq', $topic);
            }
            switch ($order) {
                case 1:
                    //热门
                    $community = $community->order('browse', 'desc')->paginate(20);
                    break;
                case 2:
                    //精华
                    $community = $community->where('essence', 'eq', 0)->order('create_time', 'desc')->paginate(20);
                    break;
                default:
                    $community = $community->order('create_time', 'desc')->paginate(20);
            }

            $data['total'] = $community->total();
            $data['per_page'] = $community->listRows();
            $data['current_page'] = $community->currentPage();
            $data['last_page'] = $community->lastPage();
            $data['data'] = [];
            foreach ($community as $k => $val) {
                $member = Db::name('member')->where('id', 'eq', $val['user_id'])->select();
                $usedImage = Db::name('community_file')->where('community_id', 'in', $val['id'])->select();
                $topic = Db::name('topic_cate')->where('id', 'eq', $val['topic_id'])->select();

                $val['region'] = $topic[0];
                $val['user'] = $member[0];
                $val['community_file'] = $usedImage;
                $data['data'][] = $val;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }

        return jsone('查询成功', $data);
    }

    /**
     * 创建二手商品
     * 方法：POST
     * 参数：
     *      user_id     用户ID，
     *      body        二手商品文字内容
     *      region_id   区域ID
     *      phone       电话
     *      price       价格
     *      sticky_num  置顶天数
     *      images      图片
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save()
    {
//TODO:图片处理

        $path = [];
        if (isset($_FILES['images'])) {
            // 获取表单上传文件 例如上传了001.jpg
            $files = request()->file('images');
            //图片上传处理
//           return $uploads = uploadImage($files, 'used');
            if (is_array($uploads = uploadImage($files, 'community'))) {
                foreach ($uploads as $value) {
                    $path[] = ['path' => $value];
                }
            } else {
                return jsone($uploads, [], 1, 'error');
            }
        }
        $data = request()->post();
        $validate = validate('Community');
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
            $community = new CommunityModel();
            $community->user_id = input('post.user_id');
            $community->body = input('post.body');
            $community->topic_id = input('post.topic_id');
            $community->sticky_create_time = $sticky_create_time;
            $community->sticky_end_time = $sticky_end_time;
            $community->sticky_status = $day ? 0 : 1;
            $community->status = 0;
            $community->save();
            //保存图片
            if (count($path)) {
                $community->communityFile()->saveAll($path);

            }
            $data = $community->with('user,communityFile,topic')->select($community->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
        return $data ? jsone('创建成功', $data) : json('创建失败', [], 1, 'error');
    }

    /**
     * 二手商品详情
     * 查询二手商品详细信息，并更新 browse 字段
     * @param integer $id 二手商品ID
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show()
    {
        $id = request()->get('community_id');
//        if($id = request()->get('used_id')) {
        try {
            $community = CommunityModel::with('communityFile,user,topic')->find($id);
            $community->browse = $community['browse'] + 1;
            $community->save();

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], '1', 'error');
        }
        return jsone('查询成功', $community);
//        }
    }

    /**
     * 二手商品点赞
     * 参数：praise
     * @return \think\response\Json
     */
    public function praise()
    {
        $id = request()->get('community_id');
        if ($id) {
            try {
                $community = CommunityModel::get($id);
                $community->praise = $community['praise'] + 1;
                $community->save();
                return jsone('点赞成功', $community->with('user,topic,communityFile')->find($community->id));
            } catch (Exception $e) {
                Log::error($e->getMessage());
                return jsone('服务器错误，请稍候重试', [], 1, 'error');
            }
        } else {
            return jsone('请选择正确动态点赞', [], 1, 'error');
        }
    }
}