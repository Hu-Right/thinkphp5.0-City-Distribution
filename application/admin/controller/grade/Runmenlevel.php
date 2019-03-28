<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/2
 * Time: 9:37
 */
namespace app\admin\controller\grade;

use think\Db;
use app\common\controller\Backend;
use think\Session;
class Runmenlevel extends Backend
{
    public function index()
    {
//        $map = [];
//        if ($this -> request -> param('')){
//            $map[''] = ['like','%'.$this -> request -> param('').'%'];
//        }

        $listRows = 15;
        $runmen_level = Db::name('runmen_level')
//            -> where($map)
            -> order('level')
            -> paginate($listRows,false,['query' => $this -> request -> get()]);

        $this -> assign('runmen_level',$runmen_level);
        $this -> assign('page',$runmen_level -> render());
        $this -> assign('count',$runmen_level -> total());
        return view();
    }

    //添加
    public function add_level()
    {   $admin=session::get('admin');
        if (\think\Request::instance()->isPost()){
            $data = [];
            $data['name']     = $_POST['name'];
            $data['level']    = $_POST['level'];
            $data['upgrading_condit'] = $_POST['upgrading_condit'];
            $data['img']      = $_POST['img'];
            $data['add_time'] = time();
            if($admin['id']==1){
            $res = Db::name('runmen_level') -> insert($data);
            if ($res){
                $this -> success('添加成功');
            }else{
                return false;
            }
        }else{
              $this->error('您没有权限:请联系管理员操作',url('index')); 
        }
        }else {
            return view();
        }
    }

    //编辑
    public function edit_level()
    {    $admin=session::get('admin');
        if (\think\Request::instance()->isPost()){
            $id = $_POST['id'];
            $data = [];
            $data['name']     = $_POST['name'];
            $data['level']    = $_POST['level'];
            $data['upgrading_condit'] = $_POST['upgrading_condit'];
            $data['img']      = $_POST['img'];
            $data['add_time'] = time();
            if($admin['id']==1){
            $res = Db::name('runmen_level') -> where(['id' => $id]) -> update($data);
            if ($res){
                $this -> success('修改成功');
            }else{
                return false;
            }
        }else{
            $this->error('您没有权限:请联系管理员操作',url('index')); 
        }
        }else {
            $id = input('param.id');
            $runmen_level = Db::name('runmen_level') -> where(['id' => $id]) -> find();

            $this -> assign('runmen_level',$runmen_level);
            return view();
        }
    }

    //删除单个
    public function des($id)
    {   $admin=session::get('admin');
        if($admin['id']==1){
        $res = Db::name('runmen_level') -> where(['id' => $id]) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
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
        $res = Db::name('runmen_level') -> where($where) -> delete();
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
        $img = Db::name('runmen_level') -> where(['id' => $id]) -> value('img');
        $this -> assign('img',$img);
        return view();
    }



}