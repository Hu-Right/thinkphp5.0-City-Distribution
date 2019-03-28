<?php

namespace app\admin\controller\management;

use app\common\controller\Backend;
use think\Db;
use think\Session;
use think\Paginate;


/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class Toexamine extends Backend
{

    protected $relationSearch = true;

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
    }

    // toexamine
    public function index()
    {
        $admin = session::get('admin');
        $map=[];
        $keyword = $this->request->param('keyword');
        if ($keyword) {
            $map['true_name'] = ['like', '%' . $keyword . '%'];
        } 
        if($admin['id']==1){
            $cashwithdrawal=Db::name('runmen_putforward')
            -> alias("p")
            // -> join('zs_runmen r','r.id=p.rid','LEFT')
            -> join('zs_set_reflect_ptd s','s.id=p.reflect_id','LEFT')
            -> field('p.*,s.true_name,s.bank_name,s.bank_card')
            -> where($map)
            -> order('id dec')
            -> paginate(20);
        }else{
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            $cashwithdrawal=Db::name('runmen_putforward')
            -> alias("p")
            -> join('zs_runmen r','r.id=p.rid','LEFT')
            -> join('zs_set_reflect_ptd s','s.id=p.reflect_id','LEFT')
            -> field('p.*,s.true_name,s.bank_name,s.bank_card,r.city')
            -> where($map)
            -> where(array('r.city'=>$cityList['city']))
            -> order('id dec')
            -> paginate(20);
        }  
        $page = $cashwithdrawal->render();
        $this -> assign('page', $page);
        $this -> assign('count',$cashwithdrawal->total());
        $this -> assign('cashwithdrawal',$cashwithdrawal);
        $this -> assign('keyword', $keyword);    
        return $this->view->fetch();
    }
    //审核--写入系统通知表
    public function saves()
    {
        $id   = input('param.id');
        $rlist = Db::name('runmen_putforward')->where(array('id'=>$id))->find();
        if($_POST)
        {  
          $data['status'] = input('post.status');   
          $list = Db::name('runmen_putforward')->where(array('id'=>$id))->update($data);
          if($list!==false){
            $this->r_notice($rlist); 
            $this->success('操作成功',url('index'));
    
          }else{
            $this->error('操作失败');
          }
        }else{
         $list = Db::name('runmen_putforward')
         -> alias("p")
         -> join('zs_set_reflect_ptd s','s.id=p.reflect_id','LEFT')
         -> field('p.*,s.true_name,s.bank_name,s.bank_card')
         -> where(array('p.id'=>$id))
         -> order('id dec')
         -> find();
        }
      $this -> assign('list',$list);  
      return $this->view->fetch();
    }
    //系统通知记录
    public function r_notice($rlist)
    {
      $data['detail'] = '提现成功';  
      $data['add_time'] = time();
      $data['rid'] = $rlist['rid'];
      $notice = Db::name('runmen_notice')->insert($data);
      if($notice){
       return true;
      }else{
       return false;
      }
    }
}
 
 