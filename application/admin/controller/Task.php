<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/6
 * Time: 14:09
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
class Task extends Backend
{
    public function index()
    {
        $listRows = 20;
        $task = Db::name('task') -> order('id') -> paginate($listRows);

        $this -> assign('task',$task);
        $this -> assign('page',$task -> render());
        $this -> assign('count',$task -> total());
        return view();
    }

    public function add_task()
    {
        if (\think\Request::instance()->isPost()){
            $_POST['add_time'] = time();
            $res = Db::name('task') -> insert($_POST);
            if ($res){
                $this -> success('添加成功');
            }else{
                return false;
            }
        }else{
            return view();
        }
    }

    public function edit_task()
    {
        if (\think\Request::instance()->isPost()){
            $id = input('param.id');
            $_POST['add_time'] = time();
            $res = Db::name('task') -> where(['id' => $id]) -> update($_POST);
            if ($res){
                $this -> success('修改成功');
            }else{
                return false;
            }
        }else{
            $id = input('param.id');
            $task = Db::name('task') -> where(['id' => $id]) -> find();

            $this -> assign('task',$task);
            return view();
        }
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('task') -> where(['id' => $id]) -> delete();
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
        $res = Db::name('task') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }


}