<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/17
 * Time: 17:14
 */

namespace app\api\exception;

use app\api\exception\BaseException;
use think\Exception;
use think\Request;

class BannerMissException extends BaseException
{

    //http状态码
    public $code = 500;
    //状态
    public $errmsg = 'error';
    //自定义状态码
    public $errcode = 1;
    //状态说明
    public $ertips = '服务器内部错误';
    public $msg = '出错啦';


}