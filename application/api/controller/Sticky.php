<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/20
 * Time: 16:43
 */

namespace app\api\controller;


use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;

class Sticky
{
    public function index()
    {
        try {
            $sticky = Db::name('sticky')->where('status', 0)->select();
            return jsone('查询成功', 200, $sticky);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }
}