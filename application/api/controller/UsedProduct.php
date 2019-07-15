<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/10
 * Time: 15:25
 */

namespace app\api\controller;

use app\admin\model\MemberModel;
use app\admin\model\UsedProductModel;
use think\Controller;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class UsedProduct extends Controller
{
    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $region = Request::instance()->has('region_id', 'get') ? input('get.region_id') : false;

        try {
            $usedProduct = Db::name('usedProduct');
            if ($region) {
                $usedProduct = $usedProduct->where('region_id', 'eq', $region);
            }
            $usedProduct = $usedProduct->order('create_time', 'desc')->paginate(20);

            $data['total'] = $usedProduct->total();
            $data['per_page'] = $usedProduct->listRows();
            $data['current_page'] = $usedProduct->currentPage();
            $data['last_page'] = $usedProduct->lastPage();
            $data['data'] = [];
            foreach ($usedProduct as $k => $val) {
                $member = Db::name('member')->where('id', 'eq', $val['user_id'])->select();
                $usedImage = Db::name('used_image')->where('used_id', 'in', $val['id'])->select();
                $region = Db::name('region_list')->where('region_id', 'eq', $val['region_id'])->select();

                $val['region'] = $region[0];
                $val['user'] = $member[0];
                $val['used_image'] = $usedImage;
                $data['data'][] = $val;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }

        return jsone('查询成功', $data);
    }

    /**
     * 创建二手商品
     * 方法：POST
     * 参数：
     *      user_id     用户ID，
     *      body        二手商品文字内容
     *      region_id   区域ID
     *      phone       电话
     *      price       价格
     *      sticky_num  置顶天数
     *      images      图片
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save()
    {
//TODO:图片处理

        $path = [];
        if (isset($_FILES['images'])) {
            // 获取表单上传文件 例如上传了001.jpg
            $files = request()->file('images');
            //图片上传处理
//           return $uploads = uploadImage($files, 'used');
            if (is_array($uploads = uploadImage($files, 'used'))) {
                foreach ($uploads as $value) {
                    $path[] = ['path' => $value];
                }
            } else {
                return jsone($uploads, [], 1, 'error');
            }
        }
        $data = request()->post();
        $validate = validate('UsedProduct');
        if (!$validate->check($data)) {
            return jsone($validate->getError(), [], 1, 'error');
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
            $used_Product->user_id = input('post.user_id');
            $used_Product->region_id = input('post.region_id');
            $used_Product->price = input('price');
            $used_Product->sticky_create_time = $sticky_create_time;
            $used_Product->sticky_end_time = $sticky_end_time;
            $used_Product->sticky_status = $day ? 0 : 1;
            $used_Product->phone = input('post.phone');
            $used_Product->status = 0;
            $used_Product->save();
            //保存图片
            if (count($path)) {
                $used_Product->usedImage()->saveAll($path);

            }
            $data = UsedProductModel::with('user,usedImage,regionList')->select($used_Product->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return jsone('服务器错误，请稍候重试', [], 1, 'error');
        }
        return $data ? jsone('创建成功', $data) : json('创建失败', [], 1, 'error');
    }

    /**
     * 二手商品详情
     * 查询二手商品详细信息，并更新 browse 字段
     * @param integer $id 二手商品ID
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show()
    {
        $id = request()->get('used_id');
//        if($id = request()->get('used_id')) {
            try {
                $usedProduct = UsedProductModel::with('usedImage,user,regionList')->find($id);
                $usedProduct->browse = $usedProduct['browse'] + 1;
                $usedProduct->save();

            } catch (Exception $e) {
                Log::error($e->getMessage());
                return jsone('服务器错误，请稍候重试', [], '1', 'error');
            }
            return jsone('查询成功', $usedProduct);
//        }
//        return jsone('查询失败', [], 1, 'error');
    }

    /**
     * 二手商品点赞
     * 参数：praise
     * @return \think\response\Json
     */
    public function praise()
    {
        $id = request()->get('used_id');
        if ($id) {
//            try {
//                $usedProduct = UsedProductModel::get($id);
                $praise = UsedProductModel::with(['memberPraise'])->find($id);
//                $praise = $usedProduct->memberPraise()->where('user_id',1)->find();
                return $praise;
                if($praise){

                    $usedProduct->memberPraise()->delete('id');
                }
                //TODO:用户认证
                $user_id = 1;
                $usedProduct->memberPraise()->save(['user_id'=>1,'module_id'=>$usedProduct->id]);


                $usedProduct->praise = $usedProduct['praise'] + 1;
                $usedProduct->save();
                return jsone('点赞成功', $usedProduct->with('user,region_list,usedImage')->find($usedProduct->id));
//            } catch (Exception $e) {
//                Log::error($e->getMessage());
//                return jsone('服务器错误，请稍候重试', [], 1, 'error');
//            }
        } else {
            return jsone('请选择正确商品点赞', [], 1, 'error');
        }
    }
}