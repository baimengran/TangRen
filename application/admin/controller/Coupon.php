<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/25
 * Time: 11:04
 */

namespace app\admin\controller;


use think\Db;
use think\Exception;
use think\exception\HttpException;
use think\Validate;

class Coupon extends Base
{
    public function index()
    {
        try {
            $key = input('key');
            $coupon = Db::name('coupon');
            if ($coupon) {
                $coupon->where('title', 'like', '%' . $key . '%');
            }
            $coupon = $coupon->order('')->paginate(20);
            if ($coupon) {
                return view('index', [
                    'val' => $key,
                    'coupons' => $coupon,
                    'empty' => '<tr><td colspan="4" align="center"><span>暂无数据</span></td></tr>'
                ]);
            }
        } catch (Exception $e) {
            throw new HttpException(500);
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
        $rule = [
            'region_name' => 'require|unique:region_list',
            'region_status' => 'require'
        ];

        $msg = [
            'region_name.require' => '区域名称必须填写',
            'region_name.unique' => '区域名称已经存在',
            'region_status' => '区域状态必须填写',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($form)) {
            return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
        }

        try {
            $region_id = Db::name('region_list')->insert($form);
            if ($region_id) {
                return json(['code' => 1, 'data', 'msg' => '创建成功']);
            } else {
                return json(['code' => 1, 'data', 'msg' => '添加失败，稍候再试吧']);
            }
        } catch (Exception $e) {
            throw new HttpException(500);
        }
    }

    /**
     *  编辑区域
     * damin/region/edit?id=1
     */
    public function edit()
    {

        $region = new RegionListModel();
//        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        $id = input('param.id');
        $data = $region->where('region_id', $id)->find();
        return view('edit', ['region' => $data]);
    }

    /**
     * 更新区域
     * @return \think\response\Json
     */
    public function update()
    {

        if (request()->isAjax()) {
            try {
                $param = input('post.');
                $region = new RegionListModel($param['region_id']);
                $result = $region->update($param);
                return json(['code' => 1, 'data' => '', 'msg' => '区域编辑成功']);
            } catch (Exception $e) {
                return json(['code' => 0, 'data' => '', 'msg' => '出错啦']);
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
        return json(['code' => 0, 'data', 'msg' => '没有这种功能']);
        if (request()->isAjax()) {
            $region_id = input('get.id');
            try {
                $region_id = Db::name('region_list')->delete($region_id);
                if ($region_id) {
                    return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
                } else {
                    return json(['code' => 0, 'data' => '', 'msg' => '删除失败']);
                }
            } catch (Exception $e) {
                return json(['code' => 0, 'data' => '', 'msg' => '出错啦']);
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
            $status = Db::name('coupon')->where(array('id' => $id))->value('status');//判断当前状态情况
            if ($status == 0) {
                $flag = Db::name('coupon')->where(array('id' => $id))->setField(['status' => 1]);
                return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
            } else {
                $flag = Db::name('coupon')->where(array('id' => $id))->setField(['status' => 0]);
                return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
            }
        } catch (Exception $e) {
            throw new HttpException(500);
        }
    }
}