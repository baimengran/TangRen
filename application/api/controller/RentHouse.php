<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13
 * Time: 18:55
 */

namespace app\api\controller;


use app\admin\model\JobSeekModel;
use app\admin\model\MemberModel;
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
        if (!$search = input('search')) {
            //搜索不存在时设定区域参数
            if (!$region = input('region_id')) {
                $region = 1;
            }
        }

        try {
            //查询有置顶的动态
            $rentSticky = new RentHouseModel();
            $stickies = $rentSticky->where('sticky_status', 0)->select();
            //检查置顶是否过期
            $updates = [];
            foreach ($stickies as $sticky) {
                if (time() > $sticky['sticky_end_time']) {

                    $val['id'] = $sticky['id'];
                    $val['sticky_status'] = 1;
                    $updates[] = $val;
                }
            }
            //批量更新过期置顶数据
            $rentSticky->saveAll($updates);

            $rentHouse = new RentHouseModel();

            //搜索
            if ($search) {
                $rent = Db::name('rent_house')->where('body', 'like', '%' . $search . '%')->buildSql();
            } else {
                $rent = Db::name('rent_house')->where('region_id', $region)->buildSql();
            }
            $rentSticky = Db::name('rent_house')->where('sticky_status', 0)->union($rent)->buildSql();
            $rentHouse = $rentHouse->table($rentSticky . 'a')->order('sticky_status asc ,create_time desc')->paginate(20);


            $data['total'] = $rentHouse->total();
            $data['per_page'] = $rentHouse->listRows();
            $data['current_page'] = $rentHouse->currentPage();
            $data['last_page'] = $rentHouse->lastPage();
            $data['data'] = [];

            foreach ($rentHouse as $rent) {
                $user = Db::name('member')->where('id', 'eq', $rent['user_id'])->find();
                $rentImage = Db::name('rent_image')->where('rent_id', 'eq', $rent['id'])->select();
                $region = Db::name('region_list')->where('region_id', 'eq', $rent['region_id'])->find();

                //获取点赞数据
                $praise = Db::name('member_praise')->where('user_id', 'eq', getUserId())
                    ->where('module_type', 'eq', 'rent_house')
                    ->where('module_id', 'eq', $rent['id'])->find();
//                return $praise;
                //获取收藏数据
                $collect = Db::name('member_collect')->where('user_id', 'eq', getUserId())
                    ->where('module_type', 'eq', 'rent_house_model')
                    ->where('module_id', 'eq', $rent['id'])->find();
//                $data[]=$community;
                if (!$praise) {
                    //如果是空，证明没点攒
                    $praise = 1;
                } else {
                    //如果存在，证明以软删除点赞
                    if ($praise['delete_time']) {
                        $praise = 1;
                    } else {
                        $praise = 0;
                    }
                }
                if (!$collect) {
                    //如果是空，证明没点攒
                    $collect = 1;
                } else {
                    //如果存在，证明以软删除点赞
                    if ($collect['delete_time']) {
                        $collect = 1;
                    } else {
                        $collect = 0;
                    }
                }
                $rent['user_praise'] = $praise;
                $rent['user_collect'] = $collect;

                $rent['user'] = $user;
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

        $data = input();
        $data['user_id'] = $id;
        $validate = validate('RentHouse');
        if (!$validate->check($data)) {
            throw new BannerMissException([
                'code' => 422,
                'ertips' => $validate->getError(),
            ]);
        }

        $sticky = Db::name('sticky')->where('id', $data['sticky_id'])->find();
//        return $sticky;
        //确定置顶状态，计算置顶结束日期
        if ($sticky) {
            //查询积分
            $integral = MemberModel::where('id', $id)->find();
            if ($integral['integral'] - $sticky['integral'] >= 0) {
                $integral->update([
                    'integral' => $integral['integral'] - $sticky['integral']
                ], ['id' => $id]);

            } else {
                throw new BannerMissException([
                    'code' => 422,
                    'ertips' => '积分不足'
                ]);
            }
            if ($day = $sticky['day_num']) {
                $sticky_create_time = time();
                $sticky_end_time = $sticky_create_time + $day * 24 * 3600;
            }
        } else {

            $sticky_create_time = 0;
            $sticky_end_time = 0;
        }

//        try {
        $rent = RentHouseModel::create([
            'user_id' => $data['user_id'],
            'region_id' => $data['region_id'],
            'body' => $data['body'],
            'price' => $data['price'],
            'sticky_status' => $data['sticky_id'] ? 0 : 1,
            'sticky_create_time' => $sticky_create_time,
            'sticky_end_time' => $sticky_end_time,
            'phone' => $data['phone'],
        ]);
        //保存图片
        $path = explode(',', $data['path']);
        $data = [];
        foreach ($path as $k => $value) {
            $data[$k]['path'] = $value;
        }
        if (count($data)) {
            $rent->rentImage()->saveAll($data);
        }
//            if (array_key_exists('path', $data)) {
//                $path = [];
//                foreach ($data['path'] as $value) {
//                    $value ? $path[]['path'] = $value : null;
//                }
//                count($path) ? $rent->rentImage()->saveAll($path) : null;
//            }

        $data = $rent->with('user,regionList,rentImage')->find($rent->id);
        return jsone('创建成功', 201, $data);
//        } catch (Exception $e) {
//            throw new BannerMissException();
//        }
    }

    /**
     * 房屋出租详情
     * @return \think\response\Json
     */
    public function show()
    {
        if (!$id = input('rent_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数',
            ]);
        }

        try {
            $rent = RentHouseModel::with('user,regionList,rentImage')->find($id);

            //获取点赞数据
            $praise = Db::name('member_praise')->where('user_id', 'eq', getUserId())
                ->where('module_id', 'eq', $rent['id'])
                ->where('module_type', 'eq', 'rent_house')
                ->find();

            //获取收藏数据
            $collect = Db::name('member_collect')->where('user_id', 'eq', getUserId())
                ->where('module_id', 'eq', $rent->id)
                ->where('module_type', 'eq', 'rent_house_model')
                ->find();
//            return $praise;
            if (!$praise) {
                //如果是空，证明没点攒
                $praise = 1;
            } else {
                //如果存在，证明以软删除点赞
                if ($praise['delete_time']) {
                    $praise = 1;
                } else {
                    $praise = 0;
                }
            }
            if (!$collect) {
                //如果是空，证明没点攒
                $collect = 1;
            } else {
                //如果存在，证明以软删除点赞
                if ($collect['delete_time']) {
                    $collect = 1;
                } else {
                    $collect = 0;
                }
            }

            $data = $rent->toArray();
            $data['user_praise'] = $praise;
            $data['user_collect'] = $collect;
            $rent->browse = $rent['browse'] + 1;
            $rent->save();
            return jsone('查询成功', 200, $data);
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
        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }
        $explain = '';
        if (!$rent_id = input('rent_id')) {
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
                    $rent->praise = $rent['praise'] > 0 ? $rent['praise'] - 1 : 0;
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