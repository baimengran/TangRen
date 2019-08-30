<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 22:48
 */

namespace app\admin\controller;


use app\admin\model\DiningCommentModel;

class DiningComment extends Base
{
    public function index()
    {
        try {
            $key = input('key');
            $community = new DiningCommentModel();
            if ($key) {
                $community->where('comment_content', 'like', '%' . $key . '%');
            }
            $community = $community->where('delete_time', 0)->order('comment_time desc')->paginate(20);
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

    public function destroy($id)
    {
        try {
            $community = (new DiningCommentModel())->where('dining_user_id', $id)->find();
            $community->save(['delete_time' => time()]);
            if ($community) {
                return json(['code' => 1, 'msg' => '删除成功']);
            }
            return json(['code' => 0, 'msg' => '删除失败']);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '删除失败']);
        }
    }
}