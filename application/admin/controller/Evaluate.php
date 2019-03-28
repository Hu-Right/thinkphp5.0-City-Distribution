<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/10/30
 * Time: 11:10
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Session;

class Evaluate extends Backend
{
    public function index()
    {
        $map = [];
        $admin=session::get('admin');
        if ($this -> request -> param('order_num')){
            $map['order_num'] = ['like','%'.$this -> request -> param('order_num').'%'];
        }

        $listRows = 20;
        if($admin['id']==1){
            $evaluate = Db::name('evaluate')
            -> alias("a")
            -> join('zs_order b','b.id = a.order_id','LEFT')
            -> join('zs_user c','c.id = a.user_id','LEFT')
            -> join('zs_runmen d','d.id = a.rid','LEFT')
            -> field('a.*,b.order_num,c.nickname,d.truename')
            -> where($map)
            -> order('id desc')
            -> paginate($listRows,false, ['query' => $this->request->get()]);
        }else{
            $groupas  = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            $evaluate = Db::name('evaluate')
            -> alias("a")
            -> join('zs_order b','b.id = a.order_id','LEFT')
            -> join('zs_user c','c.id = a.user_id','LEFT')
            -> join('zs_runmen d','d.id = a.rid','LEFT')
            -> field('a.*,b.order_num,c.nickname,d.truename')
            -> where($map)
            -> where(array('c.city'=>$cityList['city']))
            -> where(array('d.city'=>$cityList['city']))
            -> order('id desc')
            -> paginate($listRows,false, ['query' => $this->request->get()]); 
        }
        $this -> assign('evaluate',$evaluate);
        $this -> assign('page',$evaluate -> render());
        $this -> assign('count',$evaluate -> total());
        return view();
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('evaluate') -> where(['id' => $id]) -> delete();
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
        //var_dump($id);die;
        $where['id'] = ['in',$id];
        $res = Db::name('evaluate') -> where($where) -> delete();
        if ($res){
            $this -> success('删除成功');
        }else{
            return false;
        }
    }


}