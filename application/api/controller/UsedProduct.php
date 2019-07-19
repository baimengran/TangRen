<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/10
 * Time: 15:25
 */

namespace app\api\controller;

use app\admin\model\UsedProductModel;
use think\Controller;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;
use app\api\exception\BannerMissException;

class UsedProduct extends Controller
{

    /**
     * 二手商品列表
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
            $usedProduct = new UsedProductModel();
            if ($search) {
                $usedProduct->where('body', 'like', '%' . $search . '%');
            } else {
                $usedProduct = $usedProduct->where('region_id', 'eq', $region);
            }

            $usedProduct = $usedProduct->order('create_time', 'desc')->paginate(20);

            $data['total'] = $usedProduct->total();
            $data['per_page'] = $usedProduct->listRows();
            $data['current_page'] = $usedProduct->currentPage();
            $data['last_page'] = $usedProduct->lastPage();
            $data['data'] = [];
            foreach ($usedProduct as $k => $val) {
                //获取对应用户
                $member = Db::name('member')->where('id', 'eq', $val['user_id'])->select();
                //获取对应图片
                $usedImage = Db::name('used_image')->where('used_id', 'in', $val['id'])->select();
                //获取对应区域
                $region = Db::name('region_list')->where('region_id', 'eq', $val['region_id'])->select();

                $val['region'] = $region[0];
                $val['user'] = $member[0];
                $val['used_image'] = $usedImage;
                $data['data'][] = $val;
            }
            return jsone('查询成功', 200, $data);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
    }

    /**
     * 二手商品新增
     * @return \think\response\Json
     */
    public function save()
    {

        //获取登录用户ID
        if (!$id = getUserId()) {
            throw new BannerMissException([
                'code' => 401,
                'ertips' => '用户认证失败',
            ]);
        }

        $data = request()->post();
        $data['user_id'] = $id;
        $validate = validate('UsedProduct');
        if (!$validate->check($data)) {
            throw new BannerMissException([
                'code' => 422,
                'ertips' => $validate->getError()
            ]);

        }
        //确定置顶状态，计算置顶结束日期
        if ($day = input('post.sticky_num')) {
            $sticky_create_time = time();
            $sticky_end_time = $sticky_create_time + $day * 24 * 3600;
        } else {
            $sticky_create_time = 0;
            $sticky_end_time = 0;
        }
        try {
            $used_Product = new UsedProductModel();
            $used_Product->body = input('post.body');
            $used_Product->user_id = $data['user_id'];
            $used_Product->region_id = input('post.region_id');
            $used_Product->price = input('price');
            $used_Product->sticky_create_time = $sticky_create_time;
            $used_Product->sticky_end_time = $sticky_end_time;
            $used_Product->sticky_status = $day ? 0 : 1;
            $used_Product->phone = input('post.phone');
            $used_Product->status = 0;
            $used_Product->save();


            //保存图片
            if (array_key_exists('path', $data)) {
                $path = [];
                foreach ($data['path'] as $value) {
                    $value ? $path[]['path'] = $value : null;
                }
                count($path) ? $used_Product->usedImage()->saveAll($path) : null;
            }
            $data = UsedProductModel::with('user,usedImage,regionList')->find($used_Product->id);
        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('创建成功', 201, $data);
    }

    /**
     * 二手商品详情
     * @return \think\response\Json
     */
    public function show()
    {
        if (!Request::instance()->isGet()) {
            throw new BannerMissException([
                'code' => 405,
                'ertips' => '请求错误',
            ]);
        }

        if (!$id = request()->get('used_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数'
            ]);
        }

        try {
            $usedProduct = UsedProductModel::with('usedImage,user,regionList')->find($id);
            $usedProduct->browse = $usedProduct['browse'] + 1;
            $usedProduct->save();

        } catch (Exception $e) {
            throw new BannerMissException();
        }
        return jsone('查询成功', 200, $usedProduct);
    }

    /**
     * 二手商品点赞
     * 参数：praise
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

        if (!$used_id = request()->get('used_id')) {
            throw new BannerMissException([
                'code' => 400,
                'ertips' => '缺少必要参数'
            ]);
        }
        $explain = '';

        try {
            $usedProduct = UsedProductModel::get($used_id);

            $praise = Db::name('member_praise')
                ->where('module_id', 'eq', $usedProduct->id)
                ->where('module_type', 'used_product')
                ->where('user_id', 'eq', $id)
                ->find();

            //判断是否有点赞数据
            if ($praise) {
                //判断点赞数据是否软删除
                if ($praise['delete_time']) {
                    //将软删除恢复
                    Db::name('member_praise')->where('id', $praise['id'])->update(['delete_time' => null]);
                    $usedProduct->praise = $usedProduct['praise'] + 1;
                    $explain = '点赞成功';
                } else {
                    //软删除点赞
                    Db::name('member_praise')->where('id', $praise['id'])->update(['delete_time' => time()]);
                    $usedProduct->praise = $usedProduct['praise'] - 1;
                    $explain = '点赞以取消';
                }

            } else {
                $user_id = 1;
                $usedProduct->memberPraise()->save(['user_id' => $id, 'module_id' => $usedProduct->id]);
                $usedProduct->praise = $usedProduct['praise'] + 1;
                $explain = '点赞成功';
            }

            $usedProduct->save();
            return jsone($explain, 200);
        } catch (Exception $e) {
            throw new BannerMissException();
        }

    }
}