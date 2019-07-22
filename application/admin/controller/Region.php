<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/22
 * Time: 9:50
 */

namespace app\admin\controller;


use app\admin\model\RegionListModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Exception;
use think\exception\PDOException;

class Region extends Base
{
    public function index()
    {
        try {
            $region_name = input('key');
            $region = Db::name('region_list');
            if ($region) {
                $region->where('region_name', 'like', '%' . $region_name . '%');
            }
            $region = $region->order('')->paginate(20);
            return view('index', ['val' => $region_name, 'regions' => $region]);
        } catch (Exception $e) {
            return json(['code' => 0, 'data' => '', 'msg' => $e->getMessage()]);
        }
    }


    public function create()
    {

    }

    public function store()
    {

    }

    /**
     * [edit_article 编辑文章]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function edit()
    {
        $region = new RegionListModel();
//        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        $id = input('param.id');
        $data = $region->where('region_id', $id)->find();
        return view('edit', ['region' => $data]);
    }

    public function update()
    {
//        if (request()->isAjax()) {
        try {
            $param = input('post.');
            $region = new RegionListModel($param['region_id']);
            $result = $region->update($param);
            return json(['code' => 1, 'data' => '', 'msg' => '区域编辑成功']);
        } catch (Exception $e) {
            return json(['code' => 0, 'data' => '', 'msg' => $e->getMessage()]);
        }

    }

    public function destroy()
    {

    }

    /**
     * [article_state 文章状态]
     * @return [type] [descriptio]
     */
    public function status()
    {
        $id = input('param.id');
        $status = Db::name('region_list')->where(array('region_id' => $id))->value('region_status');//判断当前状态情况
        if ($status == 0) {
            $flag = Db::name('region_list')->where(array('region_id' => $id))->setField(['region_status' => 1]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('region_list')->where(array('region_id' => $id))->setField(['region_status' => 0]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }

    }
}