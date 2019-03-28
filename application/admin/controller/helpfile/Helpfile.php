<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/1
 * Time: 14:37
 */
namespace app\admin\controller\helpfile;

use think\Db;
use think\Session;
use app\common\controller\Backend;
class Helpfile extends Backend
{
    public function index()
    {
        $admin=session::get('admin');
        $map = [];
        if ($this -> request -> param('title')){
            $map['title'] = ['like','%'.$this -> request -> param('title').'%'];
        }

        $listRows = 20;
        if($admin['id']==1){
        $article = Db::name('help_document')
            -> alias("a")
            -> join('zs_help_document_type b','b.id = a.type','LEFT')
            ->join('zs_area r','r.id=a.city','LEFT')
            ->field('a.*,r.areaname,b.type_name')
            -> where($map)
            -> order('id desc')
            -> paginate($listRows,false,['query' => $this -> request -> get()]);
        }else{
        $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
        $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city    
        $article = Db::name('help_document')
            -> alias("a")
            -> join('zs_help_document_type b','b.id = a.type','LEFT')
            -> join('zs_area r','r.id=a.city','LEFT')
            -> field('a.*,r.areaname,b.type_name')
            -> where($map)
            -> where(array('a.city'=>$cityList['city']))
            -> order('id desc')
            -> paginate($listRows,false,['query' => $this -> request -> get()]);    
        }
        $this -> assign('article',$article);
        $this -> assign('page',$article -> render());
        $this -> assign('count',$article -> total());
        return view();
    }

    //添加文章
    public function add_article()
    {   $citylist = Db::name('area')->where(array('level' => 2))->select();//city   
        if (\think\Request::instance()->isPost()){
            $_POST['create_time'] = time();
            $res = Db::name('help_document') -> insert($_POST);
            if ($res){
                $this -> success('添加成功');
            }else{
                return false;
            }
        }else {
            $filetype = Db::name('help_document_type') -> where(['status' => 1]) -> select();
            $this->assign('citylist',$citylist);
            $this -> assign('filetype',$filetype);
            return view();
        }
    }

    //详情展示
    public function show_article()
    {  
        $id = input('param.id');
        $article = Db::name('help_document') -> where(['id' => $id]) -> find();

        $this -> assign('article',$article);
        return view();
    }

    //编辑文章
    public function edit_article($id)
    {   $citylist = Db::name('area')->where(array('level' => 2))->select();//city  
        if (\think\Request::instance()->isPost()){
            $_POST['create_time'] = time();
            $res = Db::name('help_document') -> where(['id' => $id]) -> update($_POST);
            if ($res){
                $this -> success('修改成功');
            }else{
                return false;
            }
        }else {
            $article = Db::name('help_document') -> where(['id' => $id]) -> find();
            $filetype = Db::name('help_document_type') -> where(['status' => 1]) -> select();

            $this -> assign('article',$article);
            $this->assign('citylist',$citylist);
            $this -> assign('filetype',$filetype);
            return view();
        }
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('help_document') -> where(['id' => $id]) -> delete();
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
        $res = Db::name('help_document') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }





}
