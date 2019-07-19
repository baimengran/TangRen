<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/11
 * Time: 16:25
 */
namespace app\admin\controller;

use app\admin\model\UsedProductModel;
use app\used\model\UsedProduct;
use think\Db;

class Used extends Base
{

    public function index(){

        $key = input('key');
        $map = [];
        if($key&&$key!==""){
            $map['title'] = ['like',"%" . $key . "%"];
        }

        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('used_product')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $used = new UsedProductModel();
        $lists = $used->getUsedByWhere($map, $Nowpage, $limits);

        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('count', $count);
        $this->assign('val', $key);
        if(input('get.page')){
            return json($lists);
        }
        $this->assign('lists',$lists);
        return $this->fetch();
    }

    /**
     * 审核状态
     * @return \think\response\Json
     */
    public function used_state()
    {
        $id=input('param.id');
        $status = Db::name('used_product')->where(array('id'=>$id))->value('status');//判断当前状态情况

        if($status==1)
        {
            $flag = Db::name('used_product')->where(array('id'=>$id))->setField(['status'=>0]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已通过']);
        }
        else
        {
            $flag = Db::name('used_product')->where(array('id'=>$id))->setField(['status'=>1]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '未通过']);
        }

    }
}