<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/25
 * Time: 23:18
 */

namespace app\admin\controller;


use app\api\exception\BannerMissException;
use think\Db;
use think\Loader;
use think\Validate;

class Sticky extends Base
{
    public function index()
    {
        try {
            $key = input('key');
            $coupon = Db::name('sticky');
            if ($coupon) {
                $coupon->where('day_num', 'like', '%' . $key . '%');
            }
            $coupon = $coupon->order('')->paginate(20);
            if ($coupon) {
                return view('index', [
                    'val' => $key,
                    'stickies' => $coupon,
                    'empty' => '<tr><td colspan="4" align="center"><span>暂无数据</span></td></tr>'
                ]);
            }
        } catch (\Exception $e) {
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


        $validate = Loader::validate('StickyValidate');
        if (!$validate->check($form)) {
            return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
        }
        $form['create_time'] = time();
        $form['update_time'] = time();
        try {
            $region_id = Db::name('sticky')->insert($form);
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


        $id = input('param.id');
        $data = Db::name('sticky')->where('id', $id)->find();
        if ($data) {
            return view('edit', ['sticky' => $data]);
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
            $form = input('post.');
            $validate = Loader::validate('StickyValidate');
            if (!$validate->check($form)) {
                return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
            }
            try {

                $result = Db::name('sticky')->where('id', $form['id'])->update($form);
                return json(['code' => 1, 'data' => '', 'msg' => '置顶编辑成功']);
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
                $id = Db::name('sticky')->delete($id);
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
            $status = Db::name('sticky')->where(array('id' => $id))->value('status');//判断当前状态情况
            if ($status == 0) {
                $flag = Db::name('sticky')->where(array('id' => $id))->setField(['status' => 1]);
                return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
            } else {
                $flag = Db::name('sticky')->where(array('id' => $id))->setField(['status' => 0]);
                return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'data', 'msg' => '出错啦']);
        }
    }
}