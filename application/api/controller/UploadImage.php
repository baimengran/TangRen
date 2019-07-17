<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/15
 * Time: 16:43
 */

namespace app\api\controller;


class UploadImage
{
    public function upload()
    {
        $module = request()->post('module_name');
        //验证登录状态
        if (!getUserId()) {
            return jsone('请登录后再操作', [], 1, 'error');
        }
        if (isset($_FILES['images'])) {
            // 获取表单上传文件 例如上传了001.jpg
            $files = request()->file('images');
            //图片上传处理
            if (is_array($uploads = uploadImage($files, $module))) {
                foreach ($uploads as $value) {
                    $path = ['path' => $value];
                }
                return jsone('上传成功', $path);
            } else {
                return jsone($uploads, [], 1, 'error');
            }
        } else {
            return jsone('请选择图片后上传', [], 1, 'error');
        }
    }
}