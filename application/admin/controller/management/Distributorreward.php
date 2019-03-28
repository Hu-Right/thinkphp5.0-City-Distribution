<?php

namespace app\admin\controller\management;

use app\common\controller\Backend;
use think\Db;
use think\Session;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class Distributorreward extends Backend
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
    {   $admin = session::get('admin');
        if ($_POST) {
            if($admin['id']==1){     
            $data = input('post.');
            $result = Db::name('distributor_reward')->where(array('id' => 1))->update($data);
            if ($result !== false) {
                $this->success('操作成功', url('index'));
            }
        }else{
            $this->error('您无权操作:请联系管理员',url('index'));
        }
    }
        else {
            $list = Db::name('distributor_reward')->where(array('id' => 1))->find();
            $this->view->assign('list', $list);
            return $this->view->fetch();
        }
    }
}
