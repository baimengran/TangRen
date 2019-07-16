<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16
 * Time: 10:08
 */

namespace app\api\controller;


use app\admin\model\CommunityModel;
use app\admin\model\CouponModel;
use app\admin\model\MemberCollectModel;
use think\Exception;
use think\Log;

class My
{
    /**
     * 我的收藏
     * @return \think\response\Json
     */
    public function collect()
    {
        $id = getUserId();
        if (!$id) {
            return jsone('请登录后重试', 1, 'error');
        }
        try {
            $memberCollect = MemberCollectModel::where('user_id', 'eq', $id)->column('id');

            $community = CommunityModel::with('user,topic,communityFile')->select($memberCollect);
            return jsone('查询成功', $community);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }

    }

}