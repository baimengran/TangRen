<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/28
 * Time: 18:08
 */

namespace app\admin\controller;

use app\admin\model\ActivityModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\Loader;
use think\Validate;

class Activity extends Base
{
    public function index()
    {
        try {
            $key = input('key');
            $coupon = Db::name('activity');
            if ($coupon) {
                $coupon->where('title', 'like', '%' . $key . '%');
            }
            $coupon = $coupon->order('')->paginate(20);
            if ($coupon) {
                return view('index', [
                    'val' => $key,
                    'coupons' => $coupon,
                    'empty' => '<tr><td colspan="10" align="center"><span>暂无数据</span></td></tr>'
                ]);
            }
        } catch (Exception $e) {
            return view('error/500');
        }
    }


    public function create()
    {
        return view('add');
    }

    /**
     * 创建区域
     * @return \think\response\Json
     */
    public function store()
    {
        $form = input('post.');


        $validate = Loader::validate('ActivityValidate');
        if (!$validate->check($form)) {
            return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
        }
        $create = strtotime($form['activity_create_time']);
        $end = strtotime($form['activity_end_time']);
        $form['activity_create_time'] = $create;
        $form['activity_end_time'] = $end;
        $form['create_time'] = time();
        $form['update_time'] = time();
        try {
            $region_id = Db::name('activity')->insert($form);
            if ($region_id) {
                return json(['code' => 1, 'data', 'msg' => '创建成功']);
            } else {
                return json(['code' => 1, 'data', 'msg' => '添加失败，稍候再试吧']);
            }
        } catch (\Exception $e) {
            throw new BannerMissException(['code' => 0]);
        }
    }

    /**
     *  编辑区域
     * damin/region/edit?id=1
     */
    public function edit()
    {

        $coupon = new ActivityModel();
        $id = input('param.id');
        $data = $coupon->where('id', $id)->order('create_time desc')->find();
        if ($data) {
            $data['activity_create_time'] = date('Y-m-d H:i:s', $data['activity_create_time']);
            $data['activity_end_time'] = date('Y-m-d H:i:s', $data['activity_end_time']);
            return view('edit', ['coupon' => $data]);
        }
        return view('error/500');
    }

    /**
     * 更新区域
     * @return \think\response\Json
     */
    public function update()
    {

        if (request()->isAjax()) {
            try {
                $form = input('post.');
                $validate = Loader::validate('ActivityValidate');
                if (!$validate->check($form)) {
                    return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
                }
                $create = strtotime($form['activity_create_time']);
                $end = strtotime($form['activity_end_time']);
                $form['activity_create_time'] = $create;
                $form['activity_end_time'] = $end;
                $form['update_time'] = time();
                $coupon = new ActivityModel($form['id']);
                $result = $coupon->update($form);
                return json(['code' => 1, 'data' => '', 'msg' => '优惠卷编辑成功']);
            } catch (\Exception $e) {
                throw new BannerMissException(['code' => 0]);
            }
        }
    }

    /**
     * 删除区域
     * admin/region/destroy?id=1
     * @return \think\response\Json
     */
    public function destroy()
    {

        if (request()->isAjax()) {
            $id = input('get.id');
            try {
                $id = Db::name('activity')->delete($id);
                if ($id) {
                    return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
                } else {
                    return json(['code' => 0, 'data' => '', 'msg' => '删除失败']);
                }
            } catch (\Exception $e) {
                return json(['code' => 0, 'data', 'msg' => '出错啦']);
            }
        }

    }

    /**
     * [article_state 优惠卷状态]
     * @return [type] [descriptio]
     */
    public function status()
    {
        $id = input('param.id');
        try {
            $status = Db::name('activity')->where(array('id' => $id))->value('status');//判断当前状态情况
            if ($status == 0) {
                $flag = Db::name('activity')->where(array('id' => $id))->setField(['status' => 1]);
                return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
            } else {
                $flag = Db::name('activity')->where(array('id' => $id))->setField(['status' => 0]);
                return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
            }
        } catch (Exception $e) {
            return json(['code' => 0, 'data', 'msg' => '出错啦']);
        }
    }
}