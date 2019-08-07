<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31
 * Time: 18:28
 */

namespace app\admin\controller;


use app\admin\model\IdeaModel;
use think\Db;

class Idea
{
    public function index()
    {
//        try {
            $key = input('key');
            $ideas = IdeaModel::order('status desc,create_time desc')->paginate(20);

            if ($ideas) {
                return view('index', [
                    'val' => $key,
                    'ideas' => $ideas,
                    'empty' => '<tr><td colspan="5" align="center"><span>暂无数据</span></td></tr>'
                ]);
            }
//        } catch (\Exception $e) {
//            return view('error/500');
//        }
    }

    /**
     * [article_state 优惠卷状态]
     * @return [type] [descriptio]
     */
    public function status()
    {
        $id = input('param.id');
        try {
            $status = Db::name('idea')->where(array('eid' => $id))->value('status');//判断当前状态情况
            if ($status == 0) {
                return;
            } else {
                $flag = Db::name('idea')->where(array('eid' => $id))->setField(['status' => 0]);
                return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已阅']);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'data', 'msg' => '出错啦']);
        }
    }
}