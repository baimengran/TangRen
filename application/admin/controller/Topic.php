<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/24
 * Time: 14:45
 */

namespace app\admin\controller;


use app\admin\model\TopicCateModel;
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
                'empty' => '<tr><td colspan="3" align="center"><span>暂无数据</span></td></tr>',
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
                return json(['code' => 0, 'data', 'msg' => '添加失败，稍候再试吧']);
            }
        } catch (Exception $e) {
            throw new BannerMissException(['code'=>0]);
        }
    }
    /**
     *  编辑区域
     * damin/region/edit?id=1
     */
    public function edit()
    {
        try {
            $topic = new TopicCateModel();
//        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            $id = input('param.id');
            $data = $topic->where('id', $id)->find();
            return view('edit', ['topic' => $data]);
        }catch(\Exception $e){
            return view('error/500');
        }
    }

    /**
     * 更新区域
     * @return \think\response\Json
     */
    public function update()
    {


//        return json(['code'=>1,'data'=>input('region_id')]);
        if (request()->isAjax()) {
            try {
                $param = input('post.');
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
                $validate->scene('edit', ['name' => 'require', 'status' => 'require']);
                if (!$validate->check($param)) {
                    return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
                }

                $region = new TopicCateModel($param['id']);
                $result = $region->update($param);
                return json(['code' => 1, 'data' => '', 'msg' => '话题编辑成功']);
            } catch (Exception $e) {
                return json(['code' => 0, 'data' => '', 'msg' => '出错啦']);
            }
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