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
use think\Controller;
use think\Db;
use think\Exception;
use think\Log;

class RentHouse extends Controller
{

    public function index()
    {
        try {
            $rentHouse = Db::name('rent_house');
            if ($region = request()->get('region_id')) {
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
            return jsone('查询成功', $data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }

    }

    public function save()
    {
        //TODO::图片处理

        $path = [];
        if (isset($_FILES['images'])) {
            $uploads = uploadImage(request()->file('images'), 'rent');
            if (is_array($uploads)) {
                foreach ($uploads as $upload) {
                    $path[] = ['path' => $upload];
                }
            } else {
                return jsone($uploads, [], 1, 'error');
            }
        }

        $data = request()->post();
        $validate = validate('RentHouse');
        if (!$validate->check($data)) {
            return jsone($validate->getError(), [], 1, 'error');
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

            if (count($path)) {
                $rent->rentImage()->saveAll($path);
            }
            $data = $rent->with('user,regionList,rentImage')->select($rent->id);
            return jsone('创建成功', $data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
    }

    public function show()
    {
        $id = request()->get('rent_id');
//        if ($id = request()->get('rent_id')) {
        try {
            $rent = RentHouseModel::with('user,regionList,rentImage')->find($id);
            $rent->browse = $rent['browse'] + 1;
            $rent->save();
            return jsone('查询成功', $rent);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
//        }
//        return jsone('查询失败', [], 1, 'error');


    }

    public function praise($rent_id)
    {
        try {
            $rent = RentHouseModel::with('user,regionList,rentImage')->find($rent_id);
            $rent->praise = $rent['praise'] + 1;
            $rent->save();
            return jsone('查询成功', $rent);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
    }

}