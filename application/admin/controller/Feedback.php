<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/10/31
 * Time: 14:00
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Session;
class Feedback extends Backend
{
    public function index()
    {
        $map = [];
        $admin=session::get('admin');
        if ($this -> request -> param('user_id')){
            $map['user_id'] = ['like','%'.$this -> request -> param('user_id').'%'];
        }
        $listRows = 20;
        if($admin['id']==1){
        $feedback = Db::name('feedback')
            -> alias("a")
            -> join('zs_user b','b.id = a.user_id','LEFT')
            -> field('a.*,b.nickname,b.city')
            -> where($map)
            -> order('id desc')
            -> paginate($listRows,false,['query' => $this -> request -> get()]);
        }else{
        $groupas  =  Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
        $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city    
        $feedback = Db::name('feedback')
            -> alias("a")
            -> join('zs_user b','b.id = a.user_id','LEFT')
            -> field('a.*,b.nickname,b.city')
            -> where($map)
            -> where(array('b.city'=>$cityList['city']))
            -> order('id desc')
            -> paginate($listRows,false,['query' => $this -> request -> get()]);

        }
        $this -> assign('feedback',$feedback);
        $this -> assign('page',$feedback -> render());
        $this -> assign('count',$feedback -> total());
        return view();
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('feedback') -> where(['id' => $id]) -> delete();
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
        $res = Db::name('feedback') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }

    public function show_img()
    {
        $id = input('param.id');
        $json_img = Db::name('feedback') -> where(['id' => $id]) -> value('img');

        $img = json_decode($json_img,true);

        $this -> assign('img',$img);
        return view();
    }



}