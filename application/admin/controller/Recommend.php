<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/29
 * Time: 9:56
 */

namespace app\admin\controller;


use app\admin\model\HotSubjectModel;
use app\admin\model\RecommendModel;
use app\api\exception\BannerMissException;
use think\Db;
use think\Loader;

class Recommend extends Base
{
    public function index()
    {
        try {
            $key = input('key');
            $coupon = Db::name('recommend');
            if ($coupon) {
                $coupon->where('title', 'like', '%' . $key . '%');
            }
            $coupon = $coupon->order('create_time desc')->paginate(20);
            if ($coupon) {
                return view('index', [
                    'val' => $key,
                    'coupons' => $coupon,
                    'empty' => '<tr><td colspan="10" align="center"><span>暂无数据</span></td></tr>'
                ]);
            }
        } catch (\Exception $e) {
            return view('error/500');
        }
    }


    public function create()
    {
        $admin = Db::name('admin')->where('status', 1)->select();
        $this->assign('admin', $admin);
        return view('add');
    }

    /**
     * 创建区域
     * @return \think\response\Json
     */
    public function store()
    {
        $form = input('post.');

        $validate = Loader::validate('Recommend');
        if (!$validate->check($form)) {
            return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
        }
        unset($form['file']);
        $form['create_time'] = time();
        $form['update_time'] = time();
        try {
            $region_id = Db::name('recommend')->insert($form);
            if ($region_id) {
                return json(['code' => 1, 'data', 'msg' => '创建成功']);
            } else {
                return json(['code' => 1, 'data', 'msg' => '添加失败，稍候再试吧']);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'data', 'msg' => '出错啦']);
        }
    }

    /**
     *  编辑区域
     * damin/region/edit?id=1
     */
    public function edit()
    {
        $admin = Db::name('admin')->where('status', 1)->select();
        $coupon = new RecommendModel();
        $id = input('param.id');
        $data = $coupon->where('id', $id)->find();
        if ($data) {
            return view('edit', ['coupon' => $data, 'admin' => $admin]);
        }
        return view('error/500');
    }

    /**
     * 更新区域
     * @return \think\response\Json
     */
    public function update()
    {

        if (request()->isAjax()) {
            try {
                $form = input('post.');
                $validate = Loader::validate('Recommend');
                if (!$validate->check($form)) {
                    return json(['code' => 0, 'data', 'msg' => $validate->getError()]);
                }
                unset($form['file']);
                $form['update_time'] = time();
                $coupon = new RecommendModel($form['id']);
                $result = $coupon->update($form);
                return json(['code' => 1, 'data' => '', 'msg' => '编辑成功']);
            } catch (\Exception $e) {
                return json(['code' => 0, 'data', 'msg' => '出错啦']);
            }
        }
    }

    /**
     * 删除区域
     * admin/region/destroy?id=1
     * @return \think\response\Json
     */
    public function destroy()
    {

        if (request()->isAjax()) {
            $id = input('get.id');
            Db::startTrans();
            try {
                $collect_id = Db::name('member_collect')->where('module_id',$id)
                    ->where('module_type','recommend_model')->delete();
                $id = Db::name('recommend')->delete($id);
                Db::commit();
                if ($id) {
                    return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
                } else {
                    return json(['code' => 0, 'data' => '', 'msg' => '删除失败']);
                }
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => 0, 'data', 'msg' => '出错啦']);
            }
        }

    }

    /**
     * [article_state 优惠卷状态]
     * @return [type] [descriptio]
     */
    public function status()
    {
        $id = input('param.id');
        Db::startTrans();
        try {
            $status = Db::name('recommend')->where(array('id' => $id))->value('status');//判断当前状态情况
            if ($status == 0) {
                $module = Db::name('member_collect')->where('module_id', $id)
                    ->where('module_type', 'recommend_model')->update(['delete_time' => time()]);
                $flag = Db::name('recommend')->where(array('id' => $id))->setField(['status' => 1]);
                Db::commit();
                return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
            } else {
                $module = Db::name('member_collect')->where('module_id', $id)
                    ->where('module_type', 'recommend_model')->update(['delete_time' => null]);
                $flag = Db::name('recommend')->where(array('id' => $id))->setField(['status' => 0]);
                Db::commit();
                return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 0, 'data', 'msg' => '出错啦']);
        }
    }


    public function detailed_hot($id)
    {
        //执行查询操作
        $list = Db::name('recommend_file')
            ->where('community_id', $id)
            ->paginate(10);

        //将数据传至页面
        $this->assign('list', $list);
        $this->assign('id', $id);
        return $this->fetch('recommend/detailed');
    }

    //添加详情图片
    public function add_detailed($id)
    {
        //加载视图
        $this->assign('data', $id);
        // 模板输出
        return $this->fetch('recommend/add_detailed');
    }

    //接收详情图片
    public function store_detailed(\think\Request $request)
    {
        $post = $request->post();

        $rule = [
            'community_id' => 'require',
            'path' => 'require'
        ];
        $message = [
            'community_id.require' => '专题ID不能为空',
            'path.require' => '请上传图片'
        ];

        //实例化验证器
        $result = $this->validate($post, $rule, $message);

        //判断有无错误
        if (true !== $result) {
            $date = ['code' => 0, 'msg' => $result];
            // 验证失败 输出错误信息
            return json($date);
        }

        $file = Db::name('recommend_file')->where('community_id', $post['community_id'])->count('id');
        if ($file >= 9) {
            return json(['code' => 0, 'msg' => '当前已达图片上线不能上传']);
        }

        $date = ['community_id' => $post['community_id'], 'path' => $post['path'], 'create_time' => time(), 'update_time' => time()];
        //执行添加操作
        $res = Db::name('recommend_file')->insert($date);

        if ($res) {
            return $arr = ['code' => 1, 'msg' => '添加成功', 'id' => $post['community_id']];
        } else {
            return $arr = ['code' => 2, 'msg' => '添加失败', 'id' => $post['community_id']];
        }
    }

    //修改详情图片
    public function edit_detailed($id, $community_id)
    {
        if (!$id) {
            return $arr = ['code' => '2', 'msg' => '修改失败'];
        }

        //查询出这条数据(图片)
        $data = Db::name('recommend_file')->where('id', $id)->find();

        //加载视图
        $this->assign('data', $data);
        $this->assign('id', $community_id);
        // 模板输出
        return $this->fetch('recommend/edit_detailed');
    }

    //接收修改
    public function update_detailed(\think\Request $request)
    {
        $post = $request->post();

        $res = Db::name('recommend_file')
            ->update([
                'id' => $post['id'],
                'path' => $post['path']
            ]);

        if ($res) {
            return $arr = ['code' => '1', 'msg' => '修改成功', 'dining_id' => $post['community_id']];
        } else {
            return $arr = ['code' => '2', 'msg' => '修改失败', 'dining_id' => $post['community_id']];
        }
    }

    //删除详情图片
    public function del_detailed($id)
    {
        //判断有无这个数据
        $res = Db::name('recommend_file')->where('id', $id)->find();

        if (!$res) {
            return $arr = ['code' => '2', 'msg' => '删除失败'];
        }
        //执行删除
        $res = Db::name('recommend_file')->delete($id);

        if ($res) {
            return $arr = ['code' => '1', 'msg' => '删除成功'];
        } else {
            return $arr = ['code' => '2', 'msg' => '删除失败'];
        }


    }
}