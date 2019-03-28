<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/1
 * Time: 10:24
 */
namespace app\admin\controller\general;

use app\common\controller\Backend;
use think\Db;
use think\Session;
class Smstemplate extends Backend
{
    public function index()
    {
        $map = [];
        $admin = session::get('admin');
        if ($this -> request -> param('sms_code')){
            $map['sms_code'] = ['like','%'.$this -> request -> param('sms_code').'%'];
        }

        $listRows = 20;
        // if($admin['id']==1){
            $template = Db::name('sms_template')
            -> alias("s")
            -> join('zs_area a','a.id=s.city','LEFT')
            -> field('s.*,a.areaname')
            -> where($map)
            -> order('id desc')
            -> paginate($listRows,false,['query'  => $this -> request -> get()]);
        // }else{
        //     $groupas  = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
        //     $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city   
        //     $template = Db::name('sms_template')
        //     -> alias("s")
        //     -> join('zs_area a','a.id=s.city','LEFT')
        //     -> field('s.*,a.areaname')
        //     -> where($map)
        //     -> where(array('city'=>$cityList['city']))
        //     -> order('id desc')
        //     -> paginate($listRows,false,['query'  => $this -> request -> get()]);
        // }
        $this -> assign('template',$template);
        $this -> assign('page',$template -> render());
        $this -> assign('count',$template -> total());
        return view();
    }

    //添加
    public function add_template()
    {   //$citylist = Db::name('area')->where(array('level' => 2))->select();//city 
        if (\think\Request::instance()->isPost()){
            $_POST['create_time'] = time();
            $res = Db::name('sms_template') -> insert($_POST);
            if ($res){
                $this -> success('添加成功');
            }else{
                return false;
            }
        }else {
            //$this -> assign('citylist',$citylist);
            return view();
        }
    }

    //编辑
    public function edit_template($id)
    {   //$citylist = Db::name('area')->where(array('level' => 2))->select();//city
        if (\think\Request::instance()->isPost()){
            $_POST['create_time'] = time();
            $res = Db::name('sms_template') -> update($_POST);
            if ($res){
                $this -> success('修改成功');
            }else{
                return false;
            }
        }else{
            //dump($id);die;
            $template = Db::name('sms_template') -> where(['id' => $id]) -> find();
            //$this -> assign('citylist',$citylist);
            $this -> assign('template',$template);
            return view();
        }
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('sms_template') -> where(['id' => $id]) -> delete();
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
        $res = Db::name('sms_template') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }



}