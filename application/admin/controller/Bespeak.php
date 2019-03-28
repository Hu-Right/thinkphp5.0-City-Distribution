<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/12/19
 * Time: 10:22
 */
namespace app\admin\controller;

use think\Db;
use app\common\controller\Backend;
class Bespeak extends Backend
{
    public function index()
    {
        $map = [];
        if ($this -> request -> param('mobile')){
            $map['mobile'] = ['like','%'.$this -> request -> param('mobile').'%'];
        }

        $listRows = 15;
        $bespeak = Db::name('bespeak') -> where($map) -> order('id desc') -> paginate($listRows,false,['query' => $this -> request -> get()]);

        $this -> assign('bespeak',$bespeak);
        $this -> assign('page',$bespeak -> render());
        $this -> assign('count',$bespeak -> total());
        return view();
    }

    public function is_handle($id)
    {
        $res = Db::name('bespeak') -> where(['id' => $id]) -> update(['is_handle' => 1]);
        if ($res){
            $this -> success('修改成功');
        }else{
            return false;
        }
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('bespeak') -> where(['id' => $id]) -> delete();
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
        $res = Db::name('bespeak') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }
}