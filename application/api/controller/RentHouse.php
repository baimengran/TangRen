<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 18:55
 */

namespace app\api\controller;


use app\admin\model\RentHouseModel;
use app\admin\model\RentImageModel;
use app\api\exception\BannerMissException;
use think\Controller;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class RentHouse extends Controller
{

    /**
     * 房屋出租列表
     * @return \think\response\Json
     */
    public function index()
    {
        if (!Request::instance()->isGet()) {
            throw new BannerMissException([
                'code' => 405,
                'ertips' => '请求错误',
            ]);
        }


        if (!$search = request()->get('search')) {
            //搜索不存在时设定区域参数
            if (!$region = request()->get('region_id')) {
                throw new BannerMissException([
                    'code' => 400,
                    'ertips' => '缺少必要参数'
                ]);
            }
        }

        try {
            $rentHouse = new RentHouseModel();
            //搜索
            if ($search) {
                $rentHouse->where('body', 'like', '%' . $search . '%');
            } else {
                $rentHouse = $rentHouse->where('region_id', 'eq', $region);
            }
            $rentHouse = $rentHouse->order('create_time')->paginate(20);

            $data['total'] = $rentHouse->total();
            $data['per_page'] = $rentHouse->listRows();
            $data['current_page'] = $rentHouse->currentPage();
            $data['last_page'] = $rentHouse->lastPage();
            $data['data'] = [];

            foreach ($rentHouse as $rent) {
                $user = Db::name('member')->where('id', 'eq', $rent['user_id'])->select();
                $rentImage = Db::name('rent_image')->where('rent_id', 'eq', $rent['id'])->select();
                $region = Db::name('region_list')->where('region_id', 'eq', $rent['region_id'])->select();

                $rent['user'] = $user[0];
                $rent['region'] = $region;
                $rent['rent_image'] = $rentImage;
                $data['data'][] = $rent;
            }
            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }

    /**
     * 房屋出租新增
     * @return \think\response\Json
     */
    public function save()
    {

        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证错误',
            ]);
        }

        $data = request()->post();
        $data['user_id'] = $id;
        $validate = validate('RentHouse');
        if (!$validate->check($data)) {
            throw new BannerMissException([
                'code' => 422,
                'ertips' => $validate->getError(),
            ]);
        }

        if ($day = $data['sticky_num']) {
            $sticky_create_time = time();
            $sticky_end_time = $sticky_create_time + $day * 24 * 3600;
        } else {
            $sticky_create_time = 0;
            $sticky_end_time = 0;
        }
        try {
            $rent = RentHouseModel::create([
                'user_id' => $data['user_id'],
                'region_id' => $data['region_id'],
                'body' => $data['body'],
                'price' => $data['price'],
                'sticky_status' => $day ? 0 : 1,
                'sticky_create_time' => $sticky_create_time,
                'sticky_end_time' => $sticky_end_time,
                'phone' => $data['phone'],
            ]);
            if (array_key_exists('path', $data)) {
                $path = [];
                foreach ($data['path'] as $value) {
                    $value ? $path[]['path'] = $value : null;
                }
                count($path) ? $rent->rentImage()->saveAll($path) : null;
            }

            $data = $rent->with('user,regionList,rentImage')->find($rent->id);
            return jsone('创建成功', 201, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }

    /**
     * 房屋出租详情
     * @return \think\response\Json
     */
    public function show()
    {
        if (!Request::instance()->isGet()) {
            throw new BannerMissException([
                'code' => 405,
                'ertips' => '请求错误'
            ]);
        }

        if (!$id = request()->get('rent_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数',
            ]);
        }

        try {
            $rent = RentHouseModel::with('user,regionList,rentImage')->find($id);
            $rent->browse = $rent['browse'] + 1;
            $rent->save();
            return jsone('查询成功', 200, $rent);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }

    /**
     * 房屋出租点赞
     * @return \think\response\Json
     */
    public function praise()
    {

        if (!Request::instance()->isGet()) {
            throw new BannerMissException([
                'code' => 405,
                'ertips' => '请求错误'
            ]);
        }
        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }
        $explain = '';
        if (!$rent_id = request()->get('rent_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数',
            ]);
        }
        try {
            $rent = RentHouseModel::get($rent_id);

            $praise = Db::name('member_praise')
                ->where('module_id', 'eq', $rent->id)
                ->where('module_type', 'rent_house')
                ->where('user_id', 'eq', $id)
                ->find();
//                return $praise;
            //判断是否有点赞数据
            if ($praise) {
                //判断点赞数据是否软删除
                if ($praise['delete_time']) {
                    //将软删除恢复
                    Db::name('member_praise')->where('id', $praise['id'])->update(['delete_time' => null]);
                    $rent->praise = $rent['praise'] + 1;
                    $explain = '点赞成功';
                } else {
                    //软删除点赞
                    Db::name('member_praise')->where('id', $praise['id'])->update(['delete_time' => time()]);
                    $rent->praise = $rent['praise'] - 1;
                    $explain = '点赞以取消';
                }

            } else {

                $rent->memberPraise()->save(['user_id' => $id, 'module_id' => $rent->id]);
                $rent->praise = $rent['praise'] + 1;
                $explain = '点赞成功';
            }

            $rent->save();
            return jsone($explain, 200);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }

}