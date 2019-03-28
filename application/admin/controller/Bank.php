<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2019/3/11
 * Time: 15:49
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
class Bank extends Backend
{
    public function index()
    {
        $bank = Db::name('bank') -> order('id') -> paginate(15);

        $this -> assign('bank',$bank);
        $this -> assign('page',$bank -> render());
        $this -> assign('count',$bank -> total());
        return view();
    }

    public function add_bank()
    {
        if (\think\Request::instance()->isPost()){
            $id = input('param.id');
            $data = [
                'bank_name' => $_POST['bank_name'],
                'add_time' => time(),
            ];
            $res = Db::name('bank') -> insert($data);
            if ($res){
                $this -> success('添加成功');
            }else{
                $this -> error('添加失败');
            }
        }else{
            return view();
        }
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('bank') -> where(['id' => $id]) -> delete();
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
        $res = Db::name('bank') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }

}