<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Db;
use think\Session;

/**
 * 会员充值账单
 *
 * @icon fa fa-user
 */
class Paycheck extends Backend
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

    public function index()
    {
        //查询-显示--分页
        //$hide=Db::name('user')->where('is_extension',['>',0],['<>',1],'or')->find();
        $admin=session::get('admin');
        $keyword = input('post.keyword', '', 'trim'); //过滤开头结尾空格
        $map['nickname'] = array('like', "%$keyword%"); //模糊查询
        $map['order_type']=array('like','%0%');
        if($admin['id']==1){
            $list =db('bill')
            ->alias("b")
            ->join('zs_user u', 'u.id=b.user_id', 'LEFT')
            ->join('zs_area a','a.id=b.city','LEFT')
            ->field('b.*,u.nickname,u.username,a.areaname')
            ->where($map)
            ->order('id asc')->paginate(20);                    
        }else{
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            //$map['city']=$cityList['city'];
            $list =db('bill')
            ->alias("b")
            ->join('zs_user u', 'u.id=b.user_id', 'LEFT')
            ->join('zs_area a','a.id=b.city','LEFT')
            ->field('b.*,u.nickname,u.username,a.areaname')           
            ->where($map)
            ->where(array('u.city'=>$cityList['city']))->
            order('id asc')->paginate(20);    
        }
         $this->assign('count', $list->total());
         $page = $list->render();          
         $this->view->assign('list', $list);
         $this->assign('keyword', $keyword);  
         $this->view->assign('page', $page);
        return $this->view->fetch();
    }
}
