<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Session;

class Coupon extends Backend
{

    public function index()
    {
        $admin=session::get('admin');
        if($admin['id']==1){
            $list = Db::name('coupon')
            ->alias("c")
            ->join('zs_area a','a.id=c.city','LEFT')
            ->field('c.*,a.areaname')
            ->order('id dec')->paginate(20);
            
        }else{
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            $list = Db::name('coupon')
            ->alias("c")
            ->join('zs_area a','a.id=c.city','LEFT')
            ->field('c.*,a.areaname')
            ->where(array('city'=>$cityList['city']))
            ->order('id dec')->paginate(20);
            
        }
        $page = $list->render();
        $this->assign('count', $list->total());
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->view->fetch();

    }
 

///save or add
    public function sadd($id)
    {
        $rs = Db::name('area')->where(array('level' => 2))->select();
        if ($_POST) {
            $data = input('post.');
            if ($id == 0) {
                $return = Db::name('coupon')->insert($data);
            } else {
                $return = Db::name('coupon')->where()->update($data);
            }
            if ($return !== false) {
                $this->success('操作成功', url('index'));
            } else {
                $this->error('操作失败');
            }
        } else {
            $list = Db::name('coupon')->where(array('id' => $id))->find();
            $this->assign('list', $list);
            $this->assign('rs', $rs);
            return $this->view->fetch();
        }
    }

 
    //删除
    public function des($id)
    {
        $return = Db::name('coupon')->where(array('id' => $id))->delete();
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
        $model = Db::name('coupon')->where($map)->delete();
        if ($model) {

            $this->success('删除成功');
        } else {

            return false;

        }

    }

}
