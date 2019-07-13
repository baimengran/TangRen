<?php
/**
 * 名  称 : uploadImage()
 * 功  能 : 处理文件上传函数(可多图片上传)
 * 变  量 : --------------------------------------
 * 输  入 : ( File ) $fileName  => '文件资源';
 * 输  入 : (String) $fileDir   => '文件保存路径'
 * 输  出 : ['success'=>'文件路径']
 * 创  建 : 2018/10/19 14:41
 */
function uploadImage($files,$dir){
    $path = [];
    foreach($files as $file){
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.$dir);
        if($info){
            // 输出 20160725/42a79759f284b767dfcb2a0197904287.jpg
            $path[] =$dir.$info->getSaveName();
        }else{
            // 上传失败获取错误信息
            return $file->getError();
        }
    }
    return $path;
}


