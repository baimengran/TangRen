<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16
 * Time: 9:13
 */

namespace app\api\controller;


use app\admin\model\ProfessionCateModel;
use think\Exception;
use think\Log;

class ProfessionCate
{
    public function index()
    {
        try {
            $profession = ProfessionCateModel::all(['status' => 0]);

            return jsone('查询成功', $profession);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
    }
}