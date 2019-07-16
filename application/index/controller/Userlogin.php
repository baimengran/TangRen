<?php
namespace app\index\controller;

use app\index\library\UserloginLibrary;
use app\index\model\UserlistModel;
use app\index\model\UserloginModel;
use think\Controller;
use think\Db;

class Userlogin extends Controller
{
    /**
     * 名  称 : login()_用户登录
     * 功  能 : 用户登录
     * 变  量 : --------------------------------------
     * 创  建 : 2019/07/10 23:42
     */
    public function login($code)
    {
        //获取appID
        $appID = config('mysql_config.wx_AppID');
        //获取密钥
        $appSecret = config('mysql_config.wx_AppSecret');
        //获取openid
        $data = (new UserloginLibrary())->userInfo($code,$appID,$appSecret);

        //判断library层返回数据
        if(!$data['msg'] == 'success'){
            return $err = ['errCode'=>'1','msg'=>'error','ertips'=>'数据有误'];
        }

        $openid = $data['retData']['openid'];

        //处理用户openid数据
        $openid = $this->getToken($openid);

        $date = ['errcode'=> 0,'errMsg'=>'success','ertips'=>'登录成功','retData'=>$openid];
        //返回用户token openid
        return json_encode($date,320);
    }

    /**
     * 名  称 : getToken()
     * 功  能 : 保存用户openid，返回token用户身份标识
     * 变  量 : --------------------------------------
     * 输  入 : (array) $wxResult  => '用户openid/session_key信息';
     * 输  出 : [ 'msg' => 'success','data' => [ 'token'=>$token ] ]
     * 输  出 : [ 'msg' => 'error',  'data' => false ] ]
     * 创  建 : 2019/07/10 16:29
     */
    public function getToken($openid)
    {

        //判断是否有openid
        if (!$openid) {
            return $err = ['errCode'=>'1','msg'=>'error','ertips'=>'没有openid'];
        }

        //执行Doa层逻辑,看数据库中是否有用户的openid
        $userInfo = Db::table('think_user_login')
            ->where('openid',$openid)
            ->find();

        //如果没有查到数据
        if (empty($userInfo)) {
            $time = time();
            //添加数据到登录表
            $data = ['user_token' => userToken(), 'openid' => $openid,'login_time'=> $time ];
            $res = Db::table('think_user_login')->insert($data);

            //验证数据格式,看是否添加成功
            if (empty($res)) {
                return $err = ['errCode'=>'1','msg'=>'error','ertips'=>'openid添加失败'];
            }

            //查看数据库中是否有用户的openid
            $userInfo = Db::table('think_user_login')->where('openid',$openid)->find();
        }

//        //获取用户openid标识
        if (empty($userInfo['openid'])) {
            return $err = ['errCode'=>'1','msg'=>'error','ertips'=>'没有openid'];
        }

        //看数据库中是否有用户的openid
        $userInfo = Db::table('think_user_login')
            ->where('openid',$openid)
            ->field('user_token,openid,login_time')
            ->find();

        return $userInfo ;
    }

    /**
     * 名  称 : create() 保存用户信息
     * 功  能 : 保存用户信息，返回用户信息
     * 变  量 : --------------------------------------
     * 输  入 : (array) token  => '用户token值';
     * 输  出 : [ 'msg' => 'success','data' => [ 'token'=>$token ] ]
     * 输  出 : [ 'msg' => 'error',  'data' => false ] ]
     * 创  建 : 2019/07/10 22:10
     */
    public function create(\think\Request $request)
    {
        $post = $request->post();

        //在用户授权登录成功以后,前端需要再次传入之前的openid值
//        $post['openid'] = 'ozBCf4u7oQJ2aipXSVN0zdr7Mu64';
//        $post['token'] = '13a83cabf8767b256ea53bfe7bc13aaa';
        if(!isset($post['token'])){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有用户token值'],320);
        }
        //根据token值查询这个用户是否录入过信息
        $userInfo = Db::table('think_member')
                ->where('token',$post['token'])
                ->find();

        //如果录入过直接查询出来返回
        if($userInfo){
            $date = Db::table('think_user_login')->alias('a')
                ->join('think_member b','a.user_token=b.token')
                ->field('b.id,b.nickname,b.sex,b.country,b.city,b.province,b.head_img,a.user_token,a.openid,a.login_time')
                ->select();

            return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);
        }
        //否则录入用户信息
        $dat = [
                'nickname'  => $post['nickName'],
                'sex'       => $post['gender'],
                'city'      => $post['city'],
                'head_img'  => $post['avatarUrl'],
                'country'   => $post['country'],
                'province'  => $post['province'],
                'token'     => $post['token'],
                'create_time'=> time(),
            ];
        $res = Db::table('think_member')->insert($dat);

        //判断是否录入信息成功
        if($res){
            return $err = ['errCode'=>'1','msg'=>'error','ertips'=>'添加用户信息失败'];
        }

        $date = Db::table('think_user_login')->alias('a')
                ->join('think_member b','a.user_token=b.token')
                ->field('b.id,b.nickname,b.sex,b.country,b.city,b.province,b.head_img,a.user_token,a.openid,a.login_time')
                ->select();

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'信息添加成功','retData'=>$date],320);

    }






}

