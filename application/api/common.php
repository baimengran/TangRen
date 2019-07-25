<?php
//'errcode'=>'0',
////    'errMsg'=>'success',
////    'ertips'=>'查询成功',
////    'retData'=>[


use app\admin\model\MemberModel;
use app\api\exception\BannerMissException;
use think\db\exception\BindParamException;
use think\Log;
use think\Request;

/**
 * 根据token查询指定用户
 * @return bool
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function getUserId()
{
    $token = input('authorization');
    try {
        if ($token) {
            $member = MemberModel::where('token', 'eq', $token)->field('id')->find();
            return $member['id'] ?: false;
        }
        return false;
    } catch (Exception $e) {
        throw new BannerMissException();
    }
}

//'code' => $this->code,
//            'errcode' => $this->errcode,
//            'errmsg' => $this->errmsg,
//            'ertips' => $this->ertips,
//            'request_url' => $request->url()
/*
 * 200 201（新增）204（put update） 400(通用) 401(登录认证) 405(get post 方法使用错误) 422(验证) 500
 */

function jsone($ertips = '查询成功', $code = 200, $retData = [])
{
    $errcode = 0;
    $errMsg = 'success';
    $header = [];
    switch ($code) {
        case 401:
            $errcode = 1;
            $errMsg = 'error';
            $header = ['WWW-Authenticate' => 'Authorization'];
            break;
        case 405:
            $errcode = 1;
            $errMsg = 'error';
            $header = ['Allow' => 'GET'];
            break;
        case 400:
            $errcode = 1;
            $errMsg = "error";
            break;
        case 422:
            $errcode = 1;
            $errMsg = 'error';
            break;
    }

    $data = [
        'errcode' => $errcode,
        'errMsg' => $errMsg,
        'ertips' => $ertips,
        'retData' => $retData
    ];
    return json($data, $code);
}


//图片上传
function uploadImage($file, $dir)
{
    $dir = 'issue';
    $path = [];
//    foreach($files as $file){
    // 移动到框架应用根目录/public/uploads/ 目录下
    $info = $file->validate(['size' => 5242880, 'ext' => 'jpg,png,jpeg'])->move('public' . DS . 'uploads' . DS . $dir);
    if ($info) {
        // 输出 20160725/42a79759f284b767dfcb2a0197904287.jpg  request()->domain() .
        $path[] = '/public' . DS . 'uploads' . DS . $dir . DS . $info->getSaveName();
    } else {
        // 上传失败获取错误信息
        return $file->getError();
    }
//    }
    return $path;
}