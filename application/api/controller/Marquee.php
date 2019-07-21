<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/20
 * Time: 20:08
 */

namespace app\api\controller;


use app\admin\model\MarqueeModel;
use app\api\exception\BannerMissException;
use think\Exception;

class Marquee
{
    public function index()
    {
        try {
            $marquee = MarqueeModel::where('status', 0)->select();
            return jsone('查询成功', 200, $marquee);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }
}