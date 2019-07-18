<?php
namespace app\index\model;

use think\Db;
use think\Model;

class FractionModel extends Model
{
    /**
     * 查询商品接口
     * 输入：
     * 返回：5条商品信息
     */
    public function index()
    {
        $goods = Db::table('think_goods_fraction')
            ->paginate(10);
        return $goods;
    }

    /**
     * 查询积分接口
     * 输入：用户ID 商品ID
     * 返回：
     */
    public function select($post)
    {
        $goods = Db::table('think_goods_fraction')
            ->field('goods_fraction')
            ->where('goods_id',$post)
            ->find();
        return $goods;
    }

    /**
     * 生成订单信息接口
     * 输入：用户ID 商品ID
     * 返回：购买成功状态
     */
    public function create_goods($uid,$gid)
    {
        $data = ['id' => $uid, 'goods_id' => $gid,'order_status'=> '0','order_time'=>time() ];
        $res = Db::table('date_goods_order')->insert($data);
        return $res;
    }

    /**
     * 查询地址接口
     * 输入：用户ID
     * 返回：是否有地址
     */
    public function select_address($id)
    {
        $address = Db::table('think_address_phone')
            ->where('id',$id)
            ->find();
        return $address;
    }
    /**
     * 修改地址接口
     * 输入：用户ID
     * 返回：是否有地址
     */
    public function update_address($id,$post)
    {
        $address = Db::name('address_phone')
            ->update([
                'city'          =>$post['city'],
                'area'          =>$post['area'],
                'address'       =>$post['address'],
                'mobile_phone'  =>$post['mobile_phone'],
                'id'            =>$post['id'],
                'address_id'    =>$id
            ]);
//        if(!$address){
//            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'设置失败'],320);
//        }
        return $address;
    }

    /**
     * 添加地址接口
     * 输入：用户ID
     * 返回：是否有地址
     */
    public function create_address($post)
    {
        $data = [
                'city'          =>$post['city'],
                'area'          =>$post['area'],
                'address'       =>$post['address'],
                'mobile_phone'  =>$post['mobile_phone'],
                'id'            =>$post['id']
        ];

        $address = Db::table('think_address_phone')->insert($data);

        return $address;
    }

    /**
     * 个人用户
     * 输入：用户ID
     * 返回：是否有地址
     */
    public function user_find($post)
    {
        $user = Db::table('think_member')
            ->where('id',$post['id'])
            ->select();

        return $user;
    }

    /**
     * 获取签到状态表
     * 输入：用户ID
     * 返回：
     */
    public function sign($post)
    {
        $date_sign = date('Ymd',$post['sign']);
        $user = Db::table('think_user_task')
            ->field('sign_type')
            ->where('id',$post['id'])
            ->where('sign',$date_sign)
            ->find();

        return $user;
    }

    /**
     * 修改用户表签到状态
     * 输入：
     * 返回：签到成功状态
     */
    public function update_user($post)
    {
        $user_sign = Db::name('member')
            ->update([
                'sign_status'=>'0',
                'id'         =>$post['id']
            ]);

        return $user_sign;
    }

    /**
     * 修改积分任务表签到状态
     * 输入：用户ID 当前时间戳
     * 返回：是否有地址
     */
    public function update_task($post,$sign_time)
    {

        $id = Db::table('think_user_task')
            ->field('task_id,sign')
            ->where('id',$post['id'])
            ->find();
        $sign_date = date('Ymd',$sign_time);
        //如果用户签到过
        if($id){

            $id_list = Db::table('think_user_task')
                ->field('task_id')
                ->where('id',$post['id'])
                ->find();

            $res = Db::table('think_user_task')
                ->update([
                    'sign'      =>$sign_date,
                    'sign_type' =>'0',
                    'task_id'   =>$id_list['task_id']
                ]);

            $res_id = $this->update_in($post['id'],3);

            return $res_id;
        }else{
            $data = ['sign' =>$sign_date ,'sign_type' =>'0', 'id' => $post['id']];
            $res = Db::table('think_user_task')->insert($data);
            $res_id = $this->update_in($post['id'],3);
            return $res_id;
        }


    }

    private function update_in($id,$num)
    {
        $in_res = Db::table('think_member')
            ->field('id,integral')
            ->where('id',$id)
            ->find();

        //计算要修改的积分
        $integral = $in_res['integral'] + $num;

        $res = Db::name('member')
            ->update([
                'integral'=>$integral,
                'id'      =>$id
            ]);

        return $res;
    }

    /**
     * 修改用户表分享任务状态
     * 输入：
     * 返回：签到成功状态
     */
    public function update_share($post,$share_time)
    {
        //修改分享任务状态为0
        $id = Db::table('think_user_task')
            ->where('id',$post['id'])
            ->find();

        $share_date = date('Ymd',$share_time);

        if($id){
            $date = Db::table('think_user_task')
                ->where('id',$post['id'])
                ->find();

            $date = Db::table('think_user_task')
                ->update([
                    'share'      =>$share_date,
                    'share_type' =>'0',
                    'task_id'    =>$date['task_id']
                ]);

            $res_in = $this->update_in($post['id'],5);
            return $res_in;
        }else{
            print_r($post['id'] );die;
            $data = ['share' => $share_date, 'share_type' => 0,'id'=>$post['id']  ];
            $res = Db::table('think_user_task')->insert($data);

            $res_in = $this->update_in($post['id'],5);
            return $res_in;

        }
    }

    /**
     * 查询任务表表--签到任务状态
     * 输入：
     * 返回：任务状态
     */
    public function select_sign($post)
    {
        //查询积分任务表,获取用户签到状态和签到主键
        $date = Db::table('think_user_task')
            ->field('task_id,sign_type')
            ->where('id',$post['id'])
            ->find();

        //修改签到状态
        if($date['sign_type'] == 1){
            $task_res = Db::name('user_task')
                ->update([
                    'sign_type'=>'2',
                    'task_id'  =>$date['task_id']
                ]);

            //查询出用户原有的积分
            $user = Db::table('think_member')
                ->where('id',$post['id'])
                ->find();


            //修改将用户积分+3
            $integral = $user['integral'] + 3;

            $task_res = Db::name('member')
                ->update([
                    'integral'=>$integral,
                    'id' =>$post['id']
                ]);
            return $task_res;
        }
        return 'error';

    }

    /**
     * 查询任务表表--收藏任务状态
     * 输入:
     * 返回:任务状态
     */
    public function select_collect($post)
    {
        //查询积分任务表,获取用户签到状态和签到主键
        $date = Db::table('think_user_task')
            ->field('task_id,collect_type')
            ->where('id',$post['id'])
            ->find();

        //修改签到状态
        if($date['collect_type'] == 1){
            $task_res = Db::name('user_task')
                ->update([
                    'collect_type'=>'2',
                    'task_id'  =>$date['task_id']
                ]);

            //查询出用户原有的积分
            $user = Db::table('think_member')
                ->where('id',$post['id'])
                ->find();

            //修改将用户积分+5
            $integral = $user['integral'] + 5;

            $task_res = Db::name('member')
                ->update([
                    'integral'=>$integral,
                    'id' =>$post['id']
                ]);
            return $task_res;
        }
            return 'error';
    }

    /**
     * 查询任务表表--发表任务状态
     * 输入:
     * 返回:任务状态
     */
    public function select_publish($post)
    {
        //查询积分任务表,获取用户签到状态和签到主键
        $date = Db::table('think_user_task')
            ->field('task_id,publish_type')
            ->where('id',$post['id'])
            ->find();

        //修改签到状态
        if($date['publish_type'] == 1){
            $task_res = Db::name('user_task')
                ->update([
                    'publish_type'=>'2',
                    'task_id'  =>$date['task_id']
                ]);

            //查询出用户原有的积分
            $user = Db::table('think_member')
                ->where('id',$post['id'])
                ->find();

            //修改将用户积分+5
            $integral = $user['integral'] + 5;

            $task_res = Db::name('member')
                ->update([
                    'integral'=>$integral,
                    'id' =>$post['id']
                ]);
            return $task_res;
        }
        return 'error';
    }

    /**
     * 查询任务表表--分享任务状态
     * 输入:
     * 返回:任务状态
     */
    public function select_share($post)
    {
        //查询积分任务表,获取用户签到状态和签到主键
        $date = Db::table('think_user_task')
            ->field('task_id,share_type')
            ->where('id',$post['id'])
            ->find();

        //修改签到状态
        if($date['share_type'] == 1){
            $task_res = Db::name('user_task')
                ->update([
                    'share_type'=>'2',
                    'task_id'  =>$date['task_id']
                ]);

            //查询出用户原有的积分
            $user = Db::table('think_member')
                ->where('id',$post['id'])
                ->find();

            //修改将用户积分+5
            $integral = $user['integral'] + 5;

            $task_res = Db::name('member')
                ->update([
                    'integral'=>$integral,
                    'id' =>$post['id']
                ]);
            return $task_res;
        }
        return 0;
    }


    /**
     * 查询任务表表--分享任务状态
     * 输入:
     * 返回:任务状态
     */
    public function select_task($id,$today)
    {
        //查询用户今天签到任务完成情况
        $sign = Db::table('think_user_task')
            ->field('sign_type')
            ->where('id',$id)
            ->where('sign',$today)
            ->find();

        //如果查不到则赋值为未完成
        if(!$sign){
            $sign['sign_type'] = '2';
        }

        //查询用户今天收藏任务完成情况
        $collect = Db::table('think_user_task')
            ->field('collect_type')
            ->where('id',$id)
            ->where('collect',$today)
            ->find();
        //如果查不到则赋值为未完成
        if(!$collect){
            $collect['collect'] = '2';
        }

        //查询用户今天发表任务完成情况
        $publish = Db::table('think_user_task')
            ->field('publish_type')
            ->where('id',$id)
            ->where('publish',$today)
            ->find();
        //如果查不到则赋值为未完成
        if(!$publish){
            $publish['publish'] = '2';
        }

        //查询用户今天分享任务完成情况
        $share = Db::table('think_user_task')
            ->field('share_type')
            ->where('id',$id)
            ->where('share',$today)
            ->find();
        //如果查不到则赋值为未完成
        if(!$share){
            $share['share'] = '2';
        }
        //将所有数据压缩进一个数组
        $task = array_merge($sign,$collect,$publish,$share);

        return $task;
    }




}