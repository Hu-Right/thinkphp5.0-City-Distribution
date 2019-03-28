<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/2
 * Time: 15:20
 */
namespace app\admin\controller;

use think\Db;
use think\Session;
use app\common\controller\Backend;
class Notice extends Backend
{
    public function index()
    {
        $map = [];
        $admin=session::get('admin');
        if ($this -> request -> param('title')){
            $map['title'] = ['like','%'.$this -> request -> param('title').'%'];
        }
        $listRows = 15;
        if($admin['id']==1){
        $notice = Db::name('notice')
            ->alias("n")
            ->join('zs_area a','a.id=n.city','LEFT')
            ->field('n.*,a.areaname')
            -> where($map)
            -> order('id desc')
            -> paginate($listRows,false,['query' => $this -> request -> get()]);
              $this -> assign('notice',$notice);
              $this -> assign('page',$notice -> render());
              $this -> assign('count',$notice -> total());
        }else{
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            $notice = Db::name('notice')
            ->alias("n")
            ->join('zs_area a','a.id=n.city','LEFT')
            ->field('n.*,a.areaname')
            ->where($map)
            ->where(array('city'=>$cityList['city']))
            -> order('id desc')
            -> paginate($listRows,false,['query' => $this -> request -> get()]);
            $this -> assign('notice',$notice);
            $this -> assign('page',$notice -> render());
            $this -> assign('count',$notice -> total()); 
        }

        return view();
    }

    /**
     * 添加
     */
    public function add_notice()
    {
        $citylist = Db::name('area')->where(array('level' => 2))->select();
        if (\think\Request::instance()->isPost()){
            $_POST['create_time'] = time();
            $res = Db::name('notice') -> insert($_POST);
            if ($res){
                $this -> success('添加成功');
            }else{
                return false;
            }
        }else{
            $this->assign('citylist',$citylist);
            return view();
        }
    
    }

    /**
     * 编辑
     */
    public function edit_notice()
    {      $citylist = Db::name('area')->where(array('level' => 2))->select();
        if (\think\Request::instance()->isPost()){
            $_POST['create_time'] = time();
            $res = Db::name('notice') -> where(['id' => $_POST['id']]) -> update($_POST);
            if ($res){
                $this -> success('修改成功');
            }else{
                return false;
            }
        }else{
            $id = input('param.id');
            $notice = Db::name('notice') -> where(['id' => $id]) -> find();
            $this->assign('citylist',$citylist);
            $this -> assign('notice',$notice);
            return view();
        }
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('notice') -> where(['id' => $id]) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }

    //批量删除
    public function delslect()
    {
        $id = input('param.id/a');
        $where['id'] = ['in',$id];
        $res = Db::name('notice') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }

    /**
     * 展示详情
     */
    public function show_notice()
    {
        $id = input('param.id');
        $notice = Db::name('notice') -> where(['id' => $id]) -> find();

        $this -> assign('notice',$notice);
        return view();
    }


}