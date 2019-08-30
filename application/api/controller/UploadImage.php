<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/15
 * Time: 16:43
 */

namespace app\api\controller;

use app\api\exception\BannerMissException;
use app\api\exception\BaseException;
use think\Exception;
use think\Log;

class UploadImage
{
    public function upload()
    {
//        return json($data);
        //验证登录状态
//        if (!getUserId()) {
//            throw new BannerMissException([
//                'code' => 401,
//                'ertips' => '用户认证失败',
//            ]);
//        }
        if (isset($_FILES['images'])) {
            // 获取表单上传文件 例如上传了001.jpg
            $files = request()->file('images');
            //图片上传处理
            if (is_array($uploads = uploadImage($files, ''))) {
                foreach ($uploads as $value) {
                    $path = ['path' => $value,];
                }
                $data = [
                    'errcode' => '0',
                    'errMsg' => 'success',
                    'ertips' => '上传成功',
                    'retData' => $uploads
                ];

                return $data;
            } else {
                $data = [
                    'errcode' => '1',
                    'errMsg' => 'error',
                    'ertips' => $uploads,
                ];
                return $data;
            }
        } else {
            $data = [
                    'errcode' => '1',
                    'errMsg' => 'error',
                    'ertips' => '请选择上传图片',
                ];
                return $data;
        }

    }
}