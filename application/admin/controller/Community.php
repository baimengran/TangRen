<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/26
 * Time: 12:54
 */

namespace app\admin\controller;


use app\admin\model\CommunityModel;
use think\Db;

class Community
{
    public function index()
    {
        try {
            $key = input('key');
            $community = new CommunityModel();
            if ($key) {
                $community->where('body', 'like', '%' . $key . '%');
            }
            $community = $community->order('recommend_status asc, sticky_status asc')->paginate(20);
            if ($community) {
                return view('index', [
                    'val' => $key,
                    'communities' => $community,
                    'empty' => '<tr><td colspan="7" align="center"><span>暂无数据</span></td></tr>'
                ]);
            }
        } catch (\Exception $e) {
            return view('error/500');
        }
    }

    /**
     * [article_state 优惠卷状态]
     * @return [type] [descriptio]
     */
    public function status()
    {
        $id = input('param.id');
        $cate = input('param.cate');

//        try {
            $status = Db::name('community')->where(array('id' => $id))->field('recommend_status,essence')->find();//判断当前状态情况
            if ($cate == 'cate') {

                if ($status['essence'] == 0) {
                    $flag = Db::name('community')->where(array('id' => $id))->setField(['essence' => 1]);
                    return json(['code' => 11, 'data' => $flag['data'], 'msg' => '以取消加精']);
                } else {
                    $flag = Db::name('community')->where(array('id' => $id))->setField(['essence' => 0]);
                    return json(['code' => 10, 'data' => $flag['data'], 'msg' => '以加精']);
                }
            }
            if ($status['recommend_status'] == 0) {
                $flag = Db::name('community')->where(array('id' => $id))->setField(['recommend_status' => 1]);
                return json(['code' => 1, 'data' => $flag['data'], 'msg' => '未推荐']);
            } else {
                $flag = Db::name('community')->where(array('id' => $id))->setField(['recommend_status' => 0]);
                return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已推荐']);
            }
//        } catch (\Exception $e) {
//            return json(['code' => 0, 'data', 'msg' => '出错啦']);
//        }
    }
}