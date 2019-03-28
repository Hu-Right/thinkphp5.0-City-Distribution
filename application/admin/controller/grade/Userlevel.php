<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/2
 * Time: 9:36
 */
namespace app\admin\controller\grade;

use think\Db;
use app\common\controller\Backend;
use think\Session;
class Userlevel extends Backend
{
  public function index()
    {
//        $map = [];
//        if ($this -> request -> param('')){
//            $map[''] = ['like','%'.$this -> request -> param('').'%'];
//        }
        $listRows = 15;
      
        $user_level = Db::name('user_level')
//          -> where($map)
            -> order('level')
            -> paginate($listRows,false,['query' => $this -> request -> get()]);

        $this -> assign('user_level',$user_level);
        $this -> assign('page',$user_level -> render());
        $this -> assign('count',$user_level -> total());
        return view();
    }

    //添加
    public function add_level()
    {     $admin=session::get('admin');
        if (\think\Request::instance()->isPost()){
            $data = [];
            $data['name']        = $_POST['name'];
            $data['level']       = $_POST['level'];
            $data['condition']   = $_POST['condition'];
            $data['remark']      = $_POST['remark'];
            $data['img']         = $_POST['img'];
            $data['create_time'] = time();
            if($admin['id']==1){
            $res = Db::name('user_level') -> insert($data);
            if ($res){
                $this -> success('添加成功');
            }else{
                return false;
            }
        }else{
          $this->error('您没有权限:请联系管理员增加',url('index'));   
        }   
        }else {
            return view();
        }
    }

    //编辑
    public function edit_level($id)
    {   $admin=session::get('admin');
        if (\think\Request::instance()->isPost()){
            $data = [];
            $data['name']        = $_POST['name'];
            $data['level']       = $_POST['level'];
            $data['condition']   = $_POST['condition'];
            $data['remark']      = $_POST['remark'];
            $data['img']         = $_POST['img'];
            $data['create_time'] = time();
            if($admin['id']==1){
            $res = Db::name('user_level') -> where(['id' => $id]) -> update($data);
            if ($res){
                $this -> success('修改成功');
            }else{
                return false;
            }
        }else{
            $this->error('您没有权限:请联系管理员修改',url('index')); 
        }
        }else {
            $user_level = Db::name('user_level') -> where(['id' => $id]) -> find();

            $this -> assign('user_level',$user_level);
            return view();
        }
    }

    //删除单个
    public function des($id)
    {   $admin=session::get('admin');
        if($admin['id']==1){
        $res = Db::name('user_level') -> where(['id' => $id]) -> delete();
        if ($res){
            $this -> success('删除成功');
        }
        else{
            return false;
        }
      }else{

        $this->error('您没有权限:请联系管理员操作',url('index')); 
      }
    }

    //批量删除
    public function delslect()
    {   $admin=session::get('admin');
        $id = input('param.id/a');
        $where['id'] = ['in',$id];
        if($admin['id']==1){
        $res = Db::name('user_level') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
       }else{
        $this->error('您没有权限:请联系管理员操作',url('index')); 
        }
    }

    /**
     * 图片上传
     */
    public function upfile()
    {
        $file = $this->request->file('file');//file是传文件的名称，这是webloader插件固定写入的。因为webloader插件会写入一个隐藏input，不信你们可以通过浏览器检查页面
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info){
            $img[] = 'uploads/'.$info -> getSaveName();
        }else{
            $file -> getError();
        }
        return json($img);
    }

    /**
     * 图片展示
     */
    public function show_img()
    {
        $id = input('param.id');
        $img = Db::name('user_level') -> where(['id' => $id]) -> value('img');
        $this -> assign('img',$img);
        return view();
    }

}