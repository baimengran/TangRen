<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/26
 * Time: 13:31
 */

namespace app\api\controller;


use app\api\exception\BannerMissException;
use think\Db;

class CustomerService
{
    public function index()
    {

        try {
            $phone = Db::name('customer_service')->where('status', 0)->find();
            if ($phone) {
                return jsone('查询成功', 200, $phone);
            } else {
                return jsone('查询成功', 200, []);
            }
        } catch (\Exception $e) {
            throw new BannerMissException(['code' => 500, 'ertips' => '服务器错误']);
        }
    }
}