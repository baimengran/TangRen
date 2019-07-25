<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16
 * Time: 9:13
 */

namespace app\api\controller;


use app\admin\model\ProfessionCateModel;
use app\api\exception\BannerMissException;
use think\Exception;
use think\Log;

class ProfessionCate
{
    public function index()
    {
        try {
            $profession = ProfessionCateModel::all(['status' => 0]);
            return jsone('查询成功',200,$profession);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }
}