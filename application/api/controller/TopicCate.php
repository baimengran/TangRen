<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/20
 * Time: 16:28
 */

namespace app\api\controller;


use app\api\exception\BannerMissException;
use think\Db;

class TopicCate
{
        public function index(){
            try {
                $topic = Db::name('topic_cate')->where('status', 0)->select();
                return jsone('查询成功', 200, $topic);
            }catch(Exception $e){
                throw new BannerMissException();
            }
        }
}