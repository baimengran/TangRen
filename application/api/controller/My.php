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
use app\api\exception\BannerMissException;
use think\Db;
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
        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }
        try {

            $module_ids = Db::name('member_collect')->where('user_id', $id)->column('module_id');

            $community = CommunityModel::with('user,topic,communityFile')
                ->where('id', 'in', $module_ids)
                ->paginate(20);
            return jsone('查询成功', 200, $community);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }

}