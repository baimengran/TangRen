<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/25
 * Time: 10:30
 */

namespace app\admin\controller;


use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\exception\HttpException;
use think\Validate;

class Profession extends Base
{
    public function index()
    {
        try {
            $key = input('key');
            $profession = Db::name('profession_cate');
            if ($key) {
                $profession = $profession->where('name', 'like', '%' . $key . '%');
            }
            $profession = $profession->paginate(20);
            return view('profession/index', [
                'professions' => $profession,
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
            'name' => 'require|unique:profession_cate',
            'status' => 'require'
        ];

        $msg = [
            'name.require' => '行业名称必须填写',
            'name.unique' => '行业名称已经存在',
            'status' => '行业状态必须填写',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($form)) {
            return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
        }
        $form['create_time'] = time();
        $form['update_time'] = time();
        try {
            $id = Db::name('profession_cate')->insert($form);
            if ($id) {
                return json(['code' => 1, 'data', 'msg' => '创建成功']);
            } else {
                return json(['code' => 1, 'data', 'msg' => '添加失败，稍候再试吧']);
            }
        } catch (Exception $e) {
            throw new BannerMissException(['code' => 0,]);
        }
    }

    /**
     * [article_state 话题状态]
     * @return [type] [descriptio]
     */
    public function status()
    {
        $id = input('param.id');

        $status = Db::name('profession_cate')->where(array('id' => $id))->value('status');//判断当前状态情况
        if ($status == 0) {
            $flag = Db::name('profession_cate')->where(array('id' => $id))->setField(['status' => 1]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('profession_cate')->where(array('id' => $id))->setField(['status' => 0]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    }
}