<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/17
 * Time: 16:29
 */

namespace app\api\exception;

use think\Log;
use think\Exception;

class BaseException extends Exception
{
    //http状态码
    public $code = 400;
    //错误信息
    public $errmsg = 'error';
    //后台
    public $msg = '';
    //自定义错误码
    public $errcode = 10000;
    //状态说明
    public $ertips = '参数错误';

    public function __construct($params=[]){
        if(!is_array($params)){
            return;
        }

        if(array_key_exists('code',$params)){
            $this->code = $params['code'];
        }

        if(array_key_exists('errmsg',$params)){
            $this->errmsg=$params['errmsg'];
        }

        if(array_key_exists('errcode',$params)){
            $this->errcode = $params['errcode'];
        }

        if(array_key_exists('ertips',$params)){
            $this->ertips = $params['ertips'];
        }
        if(array_key_exists('msg',$params)){
            $this->ertips = $params['msg'];
        }
    }

}