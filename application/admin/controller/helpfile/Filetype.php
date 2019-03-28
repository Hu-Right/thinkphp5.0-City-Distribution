<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/6
 * Time: 10:41
 */
namespace app\admin\controller\helpfile;
use think\Session;
use think\Db;
use app\common\controller\Backend;
class Filetype extends Backend
{
    public function index()
    { 
        $admin=session::get('admin');
        $listRows = 20;
        if($admin['id']==1){
        $filetype = Db::name('help_document_type') 
        ->alias("h")
        ->join('zs_area a','a.id=h.city','LEFT')
        ->field('h.*,a.areaname')
        -> order('id') -> paginate($listRows);
        }else{
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            $filetype = Db::name('help_document_type') 
            ->alias("h")
            ->join('zs_area a','a.id=h.city','LEFT')
            ->field('h.*,a.areaname')
            ->where(array('city'=>$cityList['city']))
            -> order('id') -> paginate($listRows);
        }
        $this -> assign('filetype',$filetype);
        $this -> assign('page',$filetype -> render());
        $this -> assign('count',$filetype -> total());
        return view();
    }

    //添加
    public function add_type()
    {    $citylist = Db::name('area')->where(array('level' => 2))->select();//city   
        if (\think\Request::instance()->isPost()){
            $_POST['create_time'] = time();
            $_POST['update_time'] = time();
            $res = Db::name('help_document_type') -> insert($_POST);
            if ($res){
                $this -> success('添加成功');
            }else{
                return false;
            }
        }else {
            $this->assign('citylist',$citylist);
            return view();
        }
         
    }

    //编辑
    public function edit_type($id)
    {    $citylist = Db::name('area')->where(array('level' => 2))->select();//city   
        if (\think\Request::instance()->isPost()){
            //$_POST['create_time'] = time();
            $_POST['update_time'] = time();
            $res = Db::name('help_document_type') -> where(['id' => $id]) -> update($_POST);
            if ($res){
                $this -> success('修改成功');
            }else{
                return false;
            }
        }else {
            $filetype = Db::name('help_document_type') -> where(['id' => $id]) -> find();
            $this -> assign('filetype',$filetype);
            $this -> assign('citylist',$citylist);  
            return view();
        }
       
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('help_document_type') -> where(['id' => $id]) -> delete();
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
        $res = Db::name('help_document_type') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }



}