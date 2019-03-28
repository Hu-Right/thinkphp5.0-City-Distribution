<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Db;
use think\Session;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
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
            $areaname = $this -> request -> param('areaname');
            if ($areaname){
                $map['areaname'] = ['like','%'.$areaname.'%'];
            }
            $this -> assign('areaname',$areaname);
            $keyword = input('post.keyword', '', 'trim'); //过滤开头结尾空格
            $map['nickname'] = array('like', "%$keyword%"); //模糊查询s
            $this->assign('keyword', $keyword); //输出数据
            if($admin['id']==1){
                $list =db('user')
                ->alias("a")
                ->join('zs_user_level b', 'b.id=a.level', 'LEFT')
                ->join('zs_area r','r.id=a.city','LEFT')
                ->field('a.*,b.name,r.areaname')
                ->where($map)->order('id asc')->paginate(20);                    
            }else{
                $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
                $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
                $map['city']=$cityList['city'];
                $list =db('user')
                ->alias("a")
                ->join('zs_user_level b', 'b.id=a.level', 'LEFT')
                ->join('zs_area r','r.id=a.city','LEFT')
                ->field('a.*,b.name,r.areaname')
                ->where($map)->order('id asc')->paginate(20);
            }
             $this->assign('count', $list->total());
             $page = $list->render();          
             $this->view->assign('list', $list);
             $this->view->assign('page', $page);
         //$this->view->assign('hide',$hide);
         return $this->view->fetch();
    }
//推广状态--0/1
    // public function is_exten()
    // {
       //     $where=array(
       //         'is_extension'=>array('neq','0')
       //     );
       //     $show=Db::name('user')->where($where)->setField('is_extension',0); //开启
       //     if($show)
       //     {
       //         $res['code']  = 0;
       //         $res['msg'] = '开启推广成功!';
       //        // return $this->success('a','',$res);
       //        exit(json_encode($res));
      //     }
      //     $where['is_extension']=0;
      //     $show=Db::name('user')->where($where)->setField('is_extension',1);//关闭
      //     if($show)
      //      {
      //          $res['code']=1;
      //          $res['msg']='关闭推广成功';
      //          exit(json_encode($res));
      //      }
      //   else
      //     {
      //       $res['code']=2;
      //       $res['msg']='操作失败';
      //       exit(json_encode($res));
      //     }
// }

//增加
    public function add()
    {

        return $this->view->fetch();

    }
//修改方法
    public function save($id)
    {
        $levels = Db::name('user_level')->select();
        if (request()->isPost()) {
            $list = array();
            $list = input('post.');
            $datfile = $_FILES;
            foreach ($datfile as $k => $v) {
                if ($v['name'] != "") {

                    $data['avatar'] = $this->upload($k);

                }
            }
            $edit = Db::name('user')->where(array('id' => $id))->update($list);
            if ($edit) {
                $this->success('操作成功');

            } else {
                $this->error('操作失败');
            }
        } else {
            $map['id'] = $id;
            $data = Db::name('user')->where(array('id' => $id))->find();
            $this->view->assign('data', $data);
        }
        $this->assign('levels', $levels);
        return $this->view->fetch();
    }
    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

//删除
    public function des($id)
    {
        $return = Db::name('user')->where(array('id' => $id))->delete();
        if ($return) {
            $this->success('删除成功');
        } else {
            return false;
        }
    }
//全选删除
    public function delslect()
    {
        $bid = input('post.id/a');
// $bids = implode(',',$bid);//将数组转化成字符串
        $map['id'] = array('in', $bid);
        $model = Db::name('user')->where($map)->delete();
        if ($model) {

            $this->success('删除成功');
        } else {

            return false;

        }
    }

//上传文件--方法
    public function upload($image)
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($image);

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->validate(['size' => 1567800000, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 jpg'
                $img = 'uploads/' . $info->getSaveName();

                $imgp = str_replace("\\", "/", $img);

            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }

            return $imgp;
        }
    }

    /**
     * 用户充值
     */
    public function recharge()
    {
        if (\think\Request::instance()->isPost()){
            $admin = session::get('admin');
            $user_id = input('param.id');
            $user = Db::name('user') -> where(['id' => $user_id]) -> find();
            if (!preg_match("/^\d*$/",$_POST['money'])){
                $this -> error('请填写数字');
            }
            if ($_POST['recharge_type'] == 1){
                $data = [
                    'user_id' => $user_id,
                    'money' => '+'.$_POST['money'],
                    'order_type' => 0,
                    'create_time' => time(),
                    'province' => $user['province'],
                    'city' => $user['city'],
                    'county' => $user['county'],
                    'admin_id' => $admin['id'],
                ];
                Db::name('user') -> where(['id' => $user_id]) -> setInc('balance',$_POST['money']);
                $res = Db::name('bill') -> insert($data);
                if ($res){
                    $this -> success('充值成功');
                }
            }elseif ($_POST['recharge_type'] == 2){
                $data = [
                    'user_id' => $user_id,
                    'money' => '-'.$_POST['money'],
                    'order_type' => 0,
                    'create_time' => time(),
                    'province' => $user['province'],
                    'city' => $user['city'],
                    'county' => $user['county'],
                    'admin_id' => $admin['id'],
                ];
                Db::name('user') -> where(['id' => $user_id]) -> setDec('balance',$_POST['money']);
                $res = Db::name('bill') -> insert($data);
                if ($res){
                    $this -> success('充值成功');
                }
            }
        }else{
            $user_id = input('param.id');
            $user = Db::name('user') -> where(['id' => $user_id]) -> find();

            $this -> assign('user',$user);
            return view();
        }
    }
}
