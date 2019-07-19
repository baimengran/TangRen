<?php

/**
 * 名  称 : userToken()
 * 功  能 : 生成Token标识字符串
 * 变  量 : --------------------------------------
 * 输  入 : --------------------------------------
 * 输  出 : 单一功能函数，只返回token字符串
 */
function userToken()
{
    $str  = 'abcdefghijklmnopqrstuvwxyz';
    $str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str .= '_123456789';

    $newStr = '';
    for( $i = 0; $i < 32; $i++) {
        $newStr .= $str[mt_rand(0,strlen($str)-1)];
    }
    $newStr .= time().mt_rand(100000,999999);

    return md5($newStr);
}

function getCurl($url,&$httpCode= 0) {

    // 创建一个新cURL资源
    $ch = curl_init();

    // 设置URL和相应的选项
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

    // 不做证书效验，部署在Linux环境改为true
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);

    $file_contents = curl_exec($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 返回结果
    return $file_contents;
}


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
        $info = $file->move(ROOT_PATH . 'public' . DS.$dir);
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

/**
 * 名  称 : order_number()
 * 功  能 : 自动生成订单号
 * 变  量 : --------------------------------------
 * 输  入 : --------------------------------------
 * 输  出 : 单一功能函数，只返回订单号字符串
 * 创  建 : 2018/10/22 17:12
 */
function order_number($id)
{
    //获取用户ID
    return time().mt_rand(100000,999999).$id;
}


