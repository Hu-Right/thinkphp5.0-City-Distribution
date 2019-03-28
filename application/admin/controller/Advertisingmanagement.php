<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\File;
use think\Session;

class Advertisingmanagement extends Backend
{

    public function index()
    {
        $admin=session::get('admin');
        if($admin['id']==1)
        {
            $list=Db::name('advertisement')
            ->alias("a")
            ->join('zs_area r','r.id=a.city','LEFT')
            ->field('a.*,r.areaname')
            ->order('id dec')->paginate(20);
        }else
        {
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            $list=Db::name('advertisement')
            ->alias("a")
            ->join('zs_area r','r.id=a.city','LEFT')
            ->field('a.*,r.areaname')
            ->where(array('city'=>$cityList['city']))
            ->order('id dec')->paginate(20);
        } 
        $page = $list->render();
        $this->assign('count', $list->total());
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->view->fetch();
    }
  
//上传文件--方法
    public function upload($image)
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($image);

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->validate(['size' => 1567800000, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 jpg'
                $img = "/" . 'uploads/' . $info->getSaveName();

                $imgp = str_replace("\\", "/", $img);

            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }

            return $imgp;
        }
    }

//修改--方法
    public function sadd($id)
    {
        $citylist = Db::name('area')->where(array('level' => 2))->select();//city 
        if ($_POST) {
            $data = input('post.');
            $datfile = $_FILES;
            foreach ($datfile as $k => $v) {
                if ($v['name'] != "") {
                    $data['img'] = $this->upload($k);
                }
            }

            if ($id == 0) {
                $map = Db::name('advertisement')->insert($data);

            } else {

                $map = Db::name('advertisement')->where(array('id' => $id))->update($data);
            }
            if ($map !== false) {
                $this->success('操作成功', url('index'));
            } else {
                $this->error('操作失败');
            }
        } else {
            $list = Db::name('advertisement')->where(array('id' => $id))->find();
            $this->assign('list', $list);
            $this->assign('citylist',$citylist);
            return $this->view->fetch();
        }

    }
//删除
    public function des($id)
    {
        $return = Db::name('advertisement')->where(array('id' => $id))->delete();
        if ($return) {
            $this->success('删除成功');
        } else {
            return false;
        }
    }
//全选删除
    public function delslect()
    {
        $bid = input('post.id/a');
// $bids = implode(',',$bid);//将数组转化成字符串
        $map['id'] = array('in', $bid);
        $model = Db::name('advertisement')->where($map)->delete();
        if ($model) {

            $this->success('删除成功');
        } else {

            return false;

        }

    }

}
