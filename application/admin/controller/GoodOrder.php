<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/26
 * Time: 15:21
 */

namespace app\admin\controller;


use app\admin\model\GoodOrderModel;
use think\Db;

class GoodOrder
{
//    public function index(){
//
//        $order = GoodOrderModel::with('goodFraction,user')->paginate(20);
//
//        return json($order);
//    }
    public function index()
    {
        try {
            $key = input('key');
            $orders = new GoodOrderModel();
            if ($key) {
                $user_ids = Db::name('member')->where('nickname', 'like', '%' . $key . '%')->column('id');
//                dump($user_ids);die;
                $user_ids ? $orders = $orders->where('id', 'in', $user_ids) : false;
            }

            $orders = $orders->order('logistics asc')->paginate(20);
            if ($orders) {
                return view('index', [
                    'val' => $key,
                    'goods' => $orders,
                    'empty' => '<tr><td colspan="8" align="center"><span>暂无数据</span></td></tr>'
                ]);
            }
        } catch (\Exception $e) {
            return view('error/500');
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
            $status = Db::name('goods_order')->where(array('order_id' => $id))->value('logistics');//判断当前状态情况

            if ($status == 0) {
                $flag = Db::name('goods_order')->where(array('order_id' => $id))->setField(['logistics' => 1]);
                return json(['code' => 1, 'data' => $flag['data'], 'msg' => '未发货']);
            } else {
                $flag = Db::name('goods_order')->where(array('order_id' => $id))->setField(['logistics' => 0]);
                return json(['code' => 0, 'data' => $flag['data'], 'msg' => '以发货']);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'data', 'msg' => '出错啦']);
        }
    }
}