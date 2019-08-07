<?php
namespace app\index\controller;

use app\index\model\FractionModel;
use app\index\model\UserModel;
use think\Controller;
use think\Db;
use think\Exception;
use think\Log;

class Personal extends Controller
{
    /**
     * 个人中心首页接口
     * 输入：用户ID
     * 返回：用户昵称，头像，在线多少天，签到状态
     */
    public function index(\think\Request $request)
    {
        //接收数据
        $get = $request->get('id');

        $user = new UserModel();
        $date = $user->test_1($get);

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);

    }

    /**
     * 个人中心积分商城接口
     * 输入：用户ID
     * 返回：商品列表
     */
    public function fraction_goods(\think\Request $request)
    {
        //接收数据
        $get = $request->get('id');

        //获取用户积分
        $user = new UserModel();
        $fraction = $user->user_fraction($get);

        //获取商品信息
        $goods = new FractionModel();
        $goods_list = $goods->index();

        $date = ['fraction'=> $fraction,'goods'=>$goods_list];

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);

    }
    /**
     * 个人中心积分兑换商品接口
     * 输入：用户ID 商品ID
     * 返回：购买成功状态
     */
    public function buy(\think\Request $request)
    {
        //接收参数
        $post = $request->post();

        $rule =   [
            'id'              => 'require',
            'goods_id'        => 'require',
            'address_id'=>'require'
        ];
        $message  = [
            'id.require'      => '用户ID不能为空',
            'goods_id.require' => '商品ID不能为空',
            'address_id'=>'地址不能为空'
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //获取用户积分
        $user = new UserModel();
        $fraction = $user->user_fraction($post['id']);
        if(!$fraction){
            return $err = json_encode(['errCode'=>'1','msg'=>'eerror','ertips'=>'没有这个用户'],320);
        }
        //查询商品所用积分
        $FractionModel = new FractionModel();
        $goods_fraction = $FractionModel->select($post['goods_id']);
        //查询地址
        $address = Db::name('address_phone')->where('address_id',$post['address_id'])->value('address_id');

        if(!$address){
            return $err = json_encode(['errCode'=>'1','msg'=>'eerror','ertips'=>'没有这个地址'],320);
        }
        if(!$goods_fraction){
            return $err = json_encode(['errCode'=>'1','msg'=>'eerror','ertips'=>'没有这个商品'],320);
        }

        //判断是否足够购买
        if($fraction['integral'] < $goods_fraction['goods_fraction']){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'积分不足'],320);
        }

        //购买积分商品逻辑，减少用户积分
        $newfraction = $fraction['integral'] - $goods_fraction['goods_fraction'];

        Db::startTrans();
        try{

            //修改用户表的积分
            Db::name('member')->update([
                    'integral'=>$newfraction,
                    'id'      =>$post['id']
                ]);

            //将用户购买的商品加入到订单表中
            $data = ['id' => $post['id'], 'goods_id' => $post['goods_id'],'order_status'=> '0',
                'order_time'=>date('Y年m月d日',time()),'logistics'=>'1','address_id'=>$post['address_id'] ];
            $order_id = Db::table('think_goods_order')->insertGetId($data);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
        if(!$order_id){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'购买失败'],320);
        }

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'购买成功','retData'=>$order_id],320);
    }
    /**
     * 马上兑换支付页接口(返回用户头像，昵称，默认地址，手机号)
     * 输入：用户ID 商品ID
     * 返回：
     */
    public function integral_shop(\think\Request $request)
    {
        $get = $request->get();

        if(!$get['id']){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'用户ID不能为空'],320);
        }

        $address = Db::table('think_address_phone')
            ->where('id',$get['id'])
            ->where('default_address',0)
            ->field(['address_id','city','name','phone'])
            ->find();

        if(!$address){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这个地址'],320);
        }

        $user = Db::table('think_member')
            ->field('id,nickname,sex,head_img,integral')
            ->where('id',$get['id'])
            ->find();
        if(!$user){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这个用户'],320);
        }

        $goods = Db::table('think_goods_fraction')
            ->where('goods_id',$get['goods_id'])
            ->field(['goods_id','goods_name','goods_img','goods_fraction'])
            ->find();

        if(!$goods){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这个商品'],320);
        }

//        $date = $user;
        $date['address']=$address;
        $date['good']=$goods;
//        return json($date);
        if(!$date){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'查询失败'],320);
        }

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);
    }

    /**
     * 个人中心查看订单接口
     * 输入：用户ID 商品ID
     * 返回：购买成功状态
     */
    public function order(\think\Request $request)
    {
        //接收参数
        $post = $request->get();

        $rule =   [
            'id'              => 'require',
        ];
        $message  = [
            'id.require'      => '用户ID不能为空',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //查询商品订单接口
        $FractionModel = new FractionModel();
        $date = $FractionModel->order($post);

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'订单查询成功','retData'=>$date],320);
    }

    /**
     * 修改默认地址接口
     * 输入：用户ID
     * 返回：用户昵称，头像，在线多少天，签到状态
     */
    public function update_defa(\think\Request $request)
    {
        //获取地址ID
        $post = $request->post();

        if(!$post['id']){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'用户ID不能为空'],320);
        }
        $user = Db::table('think_member')
            ->where('id',$post['id'])
            ->find();

        if(!$user){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这个用户'],320);
        }

        if(!$post['address_id']){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'修改地址不能为空'],320);
        }
        if(!isset($post['default_address']) || $post['default_address'] != 0){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'修改默认状态必须是0'],320);
        }else{
            //查看地址是否存在
            $date = Db::table('think_address_phone')->find($post['address_id']);

            if(!$date){
                return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这个地址'],320);
            }
            //将用户原来的默认地址改为普通状态
            Db::table('think_address_phone')->where('id',$post['id'])
                ->where(['default_address'=>0 ])->update(['default_address'=>1]);

            //执行修改操作
            $date = Db::name('address_phone')
                ->update([
                    'default_address' =>'0',
                    'address_id'      =>$post['address_id']
                ]);

            if(!$date){
                return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'修改失败','retData'=>$date],320);
            }

            return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'修改成功','retData'=>$date],320);
        }
    }

    /**
     * 修改绑定手机接口
     * 输入：用户ID
     * 返回：
     */
    public function update_account(\think\Request $request)
    {
        //获取地址ID
        $post = $request->post();

        $rule =   [
            'id' => 'require|number',
            'account' => 'require',
        ];
        $message  = [
            'id.require'      => '用户ID不能为空',
            'id.number'       => '用户ID类型错误',
            'account.require' => '用户手机号不能为空',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //查询之前有无绑定
        $id = Db::table('think_member')
            ->where('id',$post['id'])
            ->find();
        if(!$id){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这个用户'],320);
        }

            //执行添加添加操作
            $date = Db::name('member')
                ->update([
                    'account' =>$post['account'],
                    'id'      =>$post['id']
                ]);

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'手机绑定成功','retData'=>$date],320);

    }

    /**
     * 个人中心地址查看接口
     * 输入：用户ID 商品ID
     * 返回：
     */
    public function address_select(\think\Request $request)
    {
        //获取参数
        $post = $request->get();

        $rule =   [
            'id'=> 'require',
        ];
        $message  = [
            'id.require' => '用户ID不能为空',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //查看有无这个用户
        $date = Db::table('think_member')->find($post['id']);

        if(!$date){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这个地址'],320);
        }
        //删除这个用户的地址记录
        $FractionModel = new FractionModel();
        $res = Db::table('think_address_phone')
            ->where('id',$post['id'])
            ->select();

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$res],320);

    }

    /**
     * 个人中心地址删除接口
     * 输入：用户ID 商品ID
     * 返回：
     */
    public function address_del(\think\Request $request)
    {
        //获取参数
        $post = $request->post();

        $rule =   [
            'address_id'=> 'require',

        ];
        $message  = [
            'address_id.require' => '地址ID不能为空',

        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }
        //查看有无这个地址
        $date = Db::table('think_address_phone')->find($post['address_id']);

        if(!$date){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这个地址'],320);
        }
        //删除这个用户的地址记录
        $FractionModel = new FractionModel();
        $res = $FractionModel->del($post);

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'删除成功','retData'=>$res],320);

    }

    /**
     * 个人中心地址修改接口
     * 输入：用户ID 商品ID
     * 返回：
     */
    public function address_edit(\think\Request $request)
    {
        //获取参数
        $post = $request->post();

        $rule =   [
            'address_id'=> 'require',
            'id'        => 'require',
            'name'      => 'require',
            'phone'     => 'require',
            'city'      => 'require',
        ];
        $message  = [
            'id.require'         => '用户ID不能为空',
            'city.require'       => '地址不能为空',
            'name.require'       => '姓名不能为空',
            'phone.require'      => '电话不能为空',
            'address_id.address_id'=> '地址ID不能为空',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            // 验证失败 输出错误信息
            return json_encode(['errcode'=> 1,'errMsg'=>'error','ertips'=>$result],320);
        }
        //查看有无这个地址
        $data = Db::table('think_address_phone')->find($post['address_id']);
        if(!$data){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这条信息'],320);
        }

        if (isset($post['default_address']) && $post['default_address'] == 0){
            Db::table('think_address_phone')->where(['id'=>$post['id']])->update(['default_address'=>1]);
        }

        Db::table('think_address_phone')->where(['address_id'=>$post['address_id']])->update($post);
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'设置成功','retData'=>1],320);

    }

    /**
     * 个人中心地址管理接口
     * 输入：用户ID
     * 返回：
     */
    public function address(\think\Request $request)
    {

        //获取参数
        $post = $request->post();

        $rule =   [
            'id'        => 'require',
            'name'      => 'require',
            'phone'     => 'require',
            'city'      => 'require',
        ];
        $message  = [
            'id.require'         => '用户ID不能为空',
            'name.require'       => '姓名不能为空',
            'phone.require'      => '电话不能为空',
            'city.require'       => '地址不能为空',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }
        //添加用户的地址记录
        $FractionModel = new FractionModel();
        if(isset($post['default_address']) && $post['default_address'] == 0){
            $create_address = $FractionModel->create_address($post);
        }else{

            $data = [
                'city'          =>$post['city'],
                'name'          =>$post['name'],
                'phone'         =>$post['phone'],
                'default_address' =>1,
                'id'            =>$post['id']
            ];

            $create_address = Db::table('think_address_phone')->insert($data);
        }

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'设置成功','retData'=>$create_address],320);
    }

    /**
     * 个人中心积分兑换显示接口
     * 输入：
     * 返回：积分兑换信息
     */
    public function integral(\think\Request $request)
    {
        $get =$request->get();
        $rule =   [
            'id' => 'require|number'
        ];
        $message  = [
            'id.require'      => '用户ID不能为空',
            'id.number'       => '用户ID类型错误',
        ];

        //实例化验证器
        $result=$this->validate($get,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }
        //判断有无这个用户
        $id = Db::table('think_member')
            ->field('id')
            ->where('id',$get['id'])
            ->select();
        if(!$id){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有这个用户'],320);
        }

        $integral = Db::table('think_member')
            ->field('integral')
            ->where('id',$get['id'])
            ->select();

        $date = Db::table('think_integral_list')
            ->field('integral_number,rmb_number')
            ->where('integral_status',0)
            ->select();
        $date = ['integral'=>$integral,'date'=>$date];

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);
    }

    /**
     * 用户签到接口
     * 输入：用户ID 签到时间戳
     * 返回：签到成功 or 失败
     */
    public function sign(\think\Request $request)
    {
        //接收参数
        $post = $request->post();

        $rule =   [
            'id'    => 'require|number',
        ];
        $message  = [
            'id.require'      => '用户ID不能为空',
            'id.number'       => '用户ID类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        $post['sign'] = time();

        //查询有无这个用户
        $FractionModel = new FractionModel();
        $user = $FractionModel->user_find($post);

        if(!$user){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有此用户','retData'=>$user],320);
        }

        //判断用户今天是否签到过
        $sign = $FractionModel->sign($post);
        if( $sign['sign_type'] == '0' ){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'当天签过到了','retData'=>$sign],320);
        }

        //修改任务表,执行签到逻辑
        $task_sign = $FractionModel->update_task($post,$post['sign']);

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'签到成功','retData'=>$task_sign],320);
    }


    /**
     * 个人中心积分享任务接口
     * 输入：用户ID 分享时间戳
     * 返回：
     */
    public function share(\think\Request $request)
    {
        //接收参数
        $post = $request->post();

        $rule =   [
            'id' => 'require|number',
        ];
        $message  = [
            'id.require'      => '用户ID不能为空',
            'id.number'       => '用户ID类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }
        $post['share'] = time();

        //判断有无这个用户
        $FractionModel = new FractionModel();
        $user = $FractionModel->user_find($post);

        if(!$user){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有此用户','retData'=>$user],320);
        }
       $share_date =  date('Ymd',$post['share']);
        //查询分享任务是否已经完成
        $share = Db::table('think_user_task')
            ->field('share_type')
            ->where('id',$post['id'])
            ->where('share',$share_date)
            ->find();

        if($share['share_type']  == '1' || $share['share_type'] == '0' ){
            return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'分享成功','retData'=>$share],320);
        }

        //修改任务表,执行签到逻辑
        $task_share = $FractionModel->update_share($post,$post['share']);

        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'分享成功+5点积分','retData'=>$task_share],320);

    }

    /**
     * 个人中心积分任务接口
     * 输入：用户ID 领取积分标识
     * 返回：积分兑换信息
     */
    public function integral_task(\think\Request $request)
    {
        //获取参数
        $post = $request->post();

        $rule =   [
            'id' => 'require|number',
            'type' => 'require|number'
        ];
        $message  = [
            'id.require'      => '用户ID不能为空',
            'id.number'       => '用户ID类型错误',
            'type.require'    => '奖励类型错误不能为空',
            'type.number'     => '奖励类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }
        if($post['type'] < 1 || $post['type'] >= 5){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'领取类型type只能是1-4'],320);
        }

        //判断有无这个用户
        $FractionModel = new FractionModel();
        $user = $FractionModel->user_find($post);
        if(!$user){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'没有此用户','retData'=>$user],320);
        }

        //查询出用户的任务完成情况
        $date = Db::table('think_user_task')
            ->where('id',$post['id'])
            ->find();

        //判断传参要领取哪种奖励
        //如果是领取签到奖励
        if($post['type'] == 1){
            $signtype = $FractionModel->select_sign($post);
            if($signtype == 'error'){
                return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'暂时不能领取奖励','retData'=>$signtype],320);
            }
            return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'签到奖励领取成功','retData'=>$signtype],320);
        }
        //如果是收藏
        if($post['type'] == 2){
            $collecttype = $FractionModel->select_collect($post);
            if($collecttype == 'error'){
                return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'暂时不能领取奖励','retData'=>$collecttype],320);
            }
            return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'收藏奖励领取成功','retData'=>$collecttype],320);
        }
        //如果是发表
        if($post['type'] == 3){
            $publishtype = $FractionModel->select_publish($post);
            if($publishtype == 'error'){
                return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'暂时不能领取奖励','retData'=>$publishtype],320);
            }
            return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'发表奖励领取成功','retData'=>$publishtype],320);
        }
        //如果是分享
        if($post['type'] == 4){
            $sharetype = $FractionModel->select_share($post);
            if($sharetype == 'error'){
                return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'暂时不能领取奖励','retData'=>$sharetype],320);
            }
            return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'分享奖励领取成功','retData'=>$sharetype],320);
        }



    }

    /**
     * 查看个人任务完成接口
     * 输入：用户ID
     * 返回：任务完成情况
     */
    public function select_task(\think\Request $request)
    {
        //获取参数
        $post = request()->post();

        $rule =   [
            'id' => 'require|number'
        ];
        $message  = [
            'id.require'      => '用户ID不能为空',
            'id.number'       => '用户ID类型错误',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //获取当前时间戳
        $today = date('Ymd',time());

        //执行查询操作
        $FractionModel = new FractionModel();
        //执行查询操作
        $task = $FractionModel->select_task($post['id'],$today);
//        print_r($task);die;
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$task],320);
    }

    /**
     * 查看个人任务完成接口
     * 输入：用户ID
     * 返回：任务完成情况
     */
    public function about(\think\Request $request)
    {
        //查询关于我们表
        $date = Db::table('think_about')->find();

        $res = parse_url($date['img']);
        $img = substr($res['path'], 1);
        $date['img'] = $img;
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);
    }


    public function idea(\think\Request $request)
    {
        $post = $request->post();

        $rule =   [
            'id'        => 'require|number',
            'content'   => 'require|max:200'
        ];
        $message  = [
            'id.require'      => '用户ID不能为空',
            'id.number'       => '用户ID类型错误',
            'content.require'  => '提交内容不能为空',
            'content.max'  => '提交内容不能过长',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 0,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //将信息添加到意见表中
        $date=['user_id'=>$post['id'],'content'=>$post['content'],'create_time'=>time(),'update_time'=>time()];
        $res = Db::table('think_idea')->insert($date);

        if(!$res){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'提交失败'],320);
        }
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'提交成功'],320);
    }

}