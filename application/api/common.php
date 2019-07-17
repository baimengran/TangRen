<?php
//'errcode'=>'0',
////    'errMsg'=>'success',
////    'ertips'=>'查询成功',
////    'retData'=>[


use app\admin\model\MemberModel;
use think\Request;

function getUserId(){
    $token = Request::instance()->header('authorization');;
    $member = MemberModel::where('token','eq',$token)->field('id')->find();
    return $member['id'];
}


function jsone($ertips='查询成功',$retData,$errcode=0,$errMsg='success'){
//    return json($retData);die;
    $data = [
        'errcode'=>$errcode,
        'errMsg'=>$errMsg,
        'ertips'=>$ertips,
        'retData'=>$retData
    ];
//    return json_encode($data,true);
    return json($data);
//    return response();
}


//图片上传
function uploadImage($file,$dir){
$path = [];
//    foreach($files as $file){
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size'=>5242880,'ext'=>'jpg,png,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.$dir);
        if($info){
            // 输出 20160725/42a79759f284b767dfcb2a0197904287.jpg
            $path[] =request()->domain().DS.'uploads'.DS.$dir.DS.$info->getSaveName();
        }else{
            // 上传失败获取错误信息
            return $file->getError();
        }
//    }
    return $path;
}