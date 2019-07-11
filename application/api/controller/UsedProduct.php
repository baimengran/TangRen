<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/10
 * Time: 15:25
 */

namespace app\used\controller;
use app\used\model\UsedProduct;
use think\Request;

class UsedProduct
{
    public function index()
    {
//        $datas = UsedProduct::all();
//        print_r($datas);
//        die;
$data = [
    'errcode'=>'0',
    'errMsg'=>'success',
    'ertips'=>'查询成功',
    'retData'=>[
        '0'=>[
            'id'=>1,
            'user_id'=>1,
            'region'=>'海淀',
            'title'=>'二手商品',
            'price'=>153.20,
            'sticky_time'=>1562748225,
            'sticky_status'=>1,
            'phone'=>1111111111,
            'status'=>1,
            'create_time'=>1562748225,
            'update_time'=>1562748225
        ],
        '1'=>[
            'id'=>1,
            'user_id'=>1,
            'region'=>'海淀',
            'title'=>'二手商品',
            'price'=>153.20,
            'sticky_time'=>1562748225,
            'sticky_status'=>1,
            'phone'=>1111111111,
            'status'=>1,
            'create_time'=>1562748225,
            'update_time'=>1562748225
        ],
    ]
];

        return json_encode($data);
    }

    public function save()
    {
        $used_Product = new UsedProduct();
        $used_Product->title = input('post.title');
        $used_Product->user_id = input('post.user_id');
        $used_Product->region = input('post.region');
        $used_Product->price = input('price');
        $used_Product->sticky_time = time();
        $used_Product->sticky_status = input('post.sticky_status');
        $used_Product->phone = input('post.phone');
        $used_Product->status = input('post.status');

        $used_Product->save();
        $used_Product->id;
        // 获取表单上传文件 例如上传了001.jpg
        $files = request()->file('images');
       $uploads =  uploadImage($files,'used');
        if(is_array($uploads)){

        }
       return json_encode(['data'=>$uploads],320);

        //return $used_Product->id;
    }
}