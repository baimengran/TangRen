<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/24
 * Time: 14:45
 */

namespace app\admin\controller;


use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\exception\HttpException;
use think\Validate;

class Topic extends Base
{
    public function index()
    {
        try {
            $key = input('key');
            $topic = Db::name('topic_cate');
            if ($key) {
                $topic = $topic->where('name', 'like', '%' . $key . '%');
            }
            $topic = $topic->paginate(20);
            return view('topic/index', [
                'topics' => $topic,
                'val' => $key,
                'empty' => '<tr><td colspan="4" align="center"><span>暂无数据</span></td></tr>',
            ]);
        } catch (Exception $e) {
            return view('error/500');
        }
    }

    public function create()
    {
        return view('add');
    }

    /**
     * 创建话题
     * @return \think\response\Json
     */
    public function store()
    {
        $form = input('post.');
        $rule = [
            'name' => 'require|unique:topic_cate',
            'status' => 'require'
        ];

        $msg = [
            'name.require' => '话题名称必须填写',
            'name.unique' => '话题名称已经存在',
            'status' => '话题状态必须填写',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($form)) {
            return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
        }
        $form['create_time'] = time();
        $form['update_time'] = time();
        try {
            $id = Db::name('topic_cate')->insert($form);
            if ($id) {
                return json(['code' => 1, 'data', 'msg' => '创建成功']);
            } else {
                return json(['code' => 1, 'data', 'msg' => '添加失败，稍候再试吧']);
            }
        } catch (Exception $e) {
            throw new BannerMissException(['code'=>0]);
        }
    }

    /**
     * [article_state 话题状态]
     * @return [type] [descriptio]
     */
    public function status()
    {
        $id = input('param.id');
        $status = Db::name('topic_cate')->where(array('id' => $id))->value('status');//判断当前状态情况
        if ($status == 0) {
            $flag = Db::name('topic_cate')->where(array('id' => $id))->setField(['status' => 1]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('topic_cate')->where(array('id' => $id))->setField(['status' => 0]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }

    }
}