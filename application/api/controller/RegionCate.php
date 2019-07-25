<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16
 * Time: 9:12
 */

namespace app\api\controller;


use app\admin\model\RegionListModel;
use think\Exception;
use think\Log;

class RegionCate
{
    public function index()
    {
        try {
            $region = RegionListModel::all(['region_status' => 0]);
            return jsone('查询成功', 200,$region);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }
}