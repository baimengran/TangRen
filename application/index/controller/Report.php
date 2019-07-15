<?php
namespace app\index\controller;

use app\index\model\ReportModel;
use think\Controller;
use think\Db;

class Report extends Controller
{
    /**
     * 查询所有区域
     * 输入：
     * 输出：所有开启状态的区域
     */
    public function index(\think\Request $request)
    {
        //查询所有区域分类
        $reportModel = new ReportModel();

        $date = $reportModel->report_list();

        //执行函数返回值
        return $err = json_encode(['errCode'=>'0','msg'=>'success','ertips'=>'查询成功','retData'=>$date],320);

    }

    /**
     * 查询所有区域
     * 输入：
     * 输出：所有开启状态的区域
     */
//    public function message(\think\Request $request)
//    {
//        //获取文件有信息
//        $file = request()->file('file');
//        if ($file) {
//            $info = $file->move('uploads/weixin/');
//            if ($info) {
//                $file = $info->getSaveName();
//                $res = ['errCode'=>0,'errMsg'=>'success','ertips'=>'图片上传成功','retData'=>$file];
//                return json($res);
//            }
//        }
//
//    }

//    public function images(\think\Request $request)
//    {
//        $post = $request->post();
//        print_r($post);die;
//    }
}