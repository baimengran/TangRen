<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31
 * Time: 16:45
 */

namespace app\admin\controller;


use app\api\exception\BannerMissException;
use think\Db;
use think\Loader;
use think\Validate;

class Integral extends Base
{
    public function index()
    {
        try {
            $key = input('key');
            $coupon = Db::name('integral_list');

            $coupon = $coupon->order('')->paginate(20);
            if ($coupon) {
                return view('index', [
                    'val' => $key,
                    'integrals' => $coupon,
                    'empty' => '<tr><td colspan="5" align="center"><span>暂无数据</span></td></tr>'
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
            'integral_number'=>'require|number|exists',
            'rmb_number'=>'require|number'
        ];
        $msg = [
            'integral_number.require'=>'积分数量必须填写',
            'integral_number.number'=>'积分格式错误',
            'integral_number.exists'=>'当前积分数量已存在',
            'rmb_number.require'=>'金额必须填写',
            'rmb_number.number'=>'金额填写错误',
        ];

        $validate = new Validate($rule,$msg);
        $validate->extend('exists',function($value){
            $integral_number =Db::name('integral_list')->where('integral_number',$value)->find();
            if($integral_number){
                return false;
            }
            return true;
        });
        if (!$validate->check($form)) {
            return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
        }
        $form['create_time'] = time();
        $form['update_time'] = time();
        try {
            $region_id = Db::name('integral_list')->insert($form);
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
        $data = Db::name('integral_list')->where('integral_id', $id)->find();
        if ($data) {

            return view('integral/edit', ['sticky' => $data]);
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
            $rule = [
                'integral_number'=>'require|number|exists',
                'rmb_number'=>'require|number'
            ];
            $msg = [
                'integral_number.require'=>'积分数量必须填写',
                'integral_number.number'=>'积分格式错误',
                'integral_number.exists'=>'当前积分数量已存在',
                'rmb_number.require'=>'金额必须填写',
                'rmb_number.number'=>'金额填写错误',
            ];
            $id = $form['integral_id'];
            $validate = new Validate($rule,$msg);
            $validate->extend('exists',function($value) use ($id){
                $integral_number =Db::name('integral_list')
                    ->where('integral_number',$value)
                    ->where('integral_id','<>',$id)
                    ->find();
                if($integral_number){
                    return false;
                }
                return true;
            });

            if (!$validate->check($form)) {
                return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
            }

            try {
                $result = Db::name('integral_list')->where('integral_id', $form['integral_id'])->update($form);
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
                $id = Db::name('integral_list')->delete($id);
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
            $status = Db::name('integral_list')->where(array('integral_id' => $id))->value('integral_status');//判断当前状态情况
            if ($status == 0) {
                $flag = Db::name('integral_list')->where(array('integral_id' => $id))->setField(['integral_status' => 1]);
                return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
            } else {
                $flag = Db::name('integral_list')->where(array('integral_id' => $id))->setField(['integral_status' => 0]);
                return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'data', 'msg' => '出错啦']);
        }
    }
}