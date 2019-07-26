<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/26
 * Time: 14:19
 */

namespace app\admin\controller;


use app\api\exception\BannerMissException;
use think\Db;
use think\Validate;

class CustomerService
{
    public function index()
    {
        try {
            $key = input('key');
            $customer = Db::name('customer_service');
            $customer = $customer->order('')->paginate(20);
            if ($customer) {
                return view('index', [
                    'val' => $key,
                    'customers' => $customer,
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
        $rule = [
            'phone' => 'require|min:5|max:50|unique:customer_service',
            'status' => 'require|number',
        ];
        $msg = [
            'phone.require' => '电话必须填写',
            'phone.unique' => '电话以存在',
            'phone.min' => '电话不能少于5个字',
            'phone.max' => '电话不能大于50个字',
            'status.require' => '状态必须填写',
            'status.number' => '状态填写错误',
        ];

        $validate = new Validate($rule, $msg);
        if (!$validate->check($form)) {
            return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
        }
        $form['create_time'] = time();
        $form['update_time'] = time();
        try {
            $marquee_id = Db::name('customer_service')->insert($form);
            if ($marquee_id) {
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
        try {
            $data = Db::name('customer_service')->where('id', $id)->find();
            if ($data) {
                return view('edit', ['customer' => $data]);
            }
            return view('error/500');
        } catch (\Exception $e) {
            return view('error/500');
        }
    }

    /**
     * 更新区域
     * @return \think\response\Json
     */
    public function update()
    {

        if (request()->isAjax()) {
            $form = input('post.');
            $rule = [
                'phone' => 'require|min:5|max:50|unique:customer_service',
                'status' => 'require|number',
            ];
            $msg = [
                'phone.require' => '电话必须填写',
                'phone.unique' => '电话以存在',
                'phone.min' => '电话不能少于5个字',
                'phone.max' => '电话不能大于50个字',
                'status.require' => '状态必须填写',
                'status.number' => '状态填写错误',
            ];

            $validate = new Validate($rule, $msg);
            $validate->scene('edit',['phone'=>'require|min:5|max:50']);
            if (!$validate->scene('edit')->check($form)) {
                return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
            }
            $form['update_time'] = time();
            try {

                $result = Db::name('customer_service')->where('id', $form['id'])->update($form);
                return json(['code' => 1, 'data' => '', 'msg' => '客服编辑成功']);
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
                $id = Db::name('customer_service')->delete($id);
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
            $status = Db::name('customer_service')->where(array('id' => $id))->value('status');//判断当前状态情况

            if ($status == 0) {
                $flag = Db::name('customer_service')->where(array('id' => $id))->setField(['status' => 1]);
                return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
            } else {
                $flag = Db::name('customer_service')->where(array('id' => $id))->setField(['status' => 0]);
                return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'data', 'msg' => '出错啦']);
        }
    }
}