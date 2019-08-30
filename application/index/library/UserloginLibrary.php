<?php
namespace app\index\library;

class UserloginLibrary
{
    public function userInfo($code,$appID,$appSecret)
    {
        $wx_config = config('mysql_config.');
        $appid = config('mysql_config.wx_AppID');
        $secret = config('mysql_config.wx_AppSecret');


        // 处理url字符串，发送指定数据
//        $loginUrl  = 'https://api.weixin.qq.com/sns/jscode2session?appid=wxcc02f121e350a650&secret=bf621d03c47a53461927082843e09cfb';
        $loginUrl  = 'https://api.weixin.qq.com/sns/jscode2session?appid=wxb3c7bc50b284e85b&secret=4c4cf83dc496b2e5a86eaa756e130be5';
//        $loginUrl .= '?appid='.$appID;
//        $loginUrl .= '&secret='.$appSecret;
        $loginUrl .= '&js_code='.$code;
        $loginUrl .= '&grant_type=authorization_code';

        // 获取请求接口返回的用户Openid数据
        $result = getCurl($loginUrl);

        $wxResult = json_decode($result,true);

        // 验证返回数据格式是否正确
        if(empty($wxResult['openid'])){
            return $err = ['errCode'=>'1','msg'=>'error','ertips'=>'没有openid'];
        }

        $loginFile = array_key_exists('errCode',$wxResult);

        if($loginFile){
            return $err = ['errCode'=>'1','msg'=>'error'];
        }

        return $date = ['errCode'=>'0','msg'=>'success','retData'=>$wxResult];
    }


}
