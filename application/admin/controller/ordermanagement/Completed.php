<?php
namespace app\admin\controller\ordermanagement;

use app\common\controller\Backend;
use think\Db;
use think\File;
use think\Session;

class Completed extends Backend
{
    //完成订单
    public function index()
    {
        $map = [];
        $admin=session::get('admin');// get global session
        if ($_POST) {
            $order_num = $this->request->param('order_num');
            $create_time = $this->request->param('create_time');
            $nickname = $this->request->param('nickname');
            $mobile = $this->request->param('mobile');
            if ($order_num) {
                $map['order_num'] = ['like', '%' . $order_num . '%'];
            }

            if ($create_time) {
                $map['a.create_time'] = ['between',[strtotime($create_time),(strtotime($create_time)+3600*24)]];
            }

            if ($nickname) {
                $map['nickname'] = ['like', '%' . $nickname . '%'];
            }

            if ($mobile) {
                $map['mobile'] = ['like', '%' . $mobile . '%'];
            }

        }
        if($admin['id']==1){
            $list = Db::name('order')
            ->alias("a")
            ->join('zs_user b', 'b.id=a.uid', 'LEFT')
            ->join('zs_service_type c', 'c.id=a.type', 'LEFT')
            ->join('zs_address d', 'd.uid=b.id', 'LEFT')
            ->join('zs_area e', 'e.id=a.city', 'LEFT')
            ->field('a.*,b.nickname,c.pid,c.service_name,d.address,d.mobile,e.areaname')
            ->where($map)->
            where(array('a.status' => 2))->
            order('id dec')->paginate(20);
            foreach($list->items() as $k=>$v)
            {
              $list->items()[$k]['content'] =json_decode($v['content'],true);//json_ecode/转成数组后成对象-第二个参数必须加true才能以数组的形式取值
   
            }
        }else{
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            $list = Db::name('order')
            ->alias("a")
            ->join('zs_user b', 'b.id=a.uid', 'LEFT')
            ->join('zs_service_type c', 'c.id=a.type', 'LEFT')
            ->join('zs_address d', 'd.uid=b.id', 'LEFT')
            ->join('zs_area e', 'e.id=a.city', 'LEFT')
            ->field('a.*,b.nickname,c.pid,c.service_name,d.address,d.mobile,e.areaname')
            ->where($map)
            ->where(array('a.city'=>$cityList['city']))
            ->where(array('a.status' => 2))->
            order('id dec')->paginate(20);
            foreach($list->items() as $k=>$v)
            {
              $list->items()[$k]['content'] =json_decode($v['content'],true);//json_ecode/转成数组后成对象-第二个参数必须加true才能以数组的形式取值
   
            }
        }  
        $page = $list->render();
        $this->assign('count', $list->total());
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->view->fetch();

    }

    //  查看
    public function seek($id)
    {
        $data = Db::name('order')
            ->alias("a")
            ->join('zs_user b', 'b.id=a.uid', 'LEFT')
            ->join('zs_service_type c', 'c.id=a.type', 'LEFT')
            ->join('zs_address d', 'd.uid=b.id', 'LEFT')
            ->join('zs_runmen e', 'e.id=a.rid', 'LEFT')
            ->field('a.*,b.nickname,c.pid,c.service_name,d.address,d.mobile,e.username,e.truename')
            ->where(array('a.id' => $id))
            ->where(array('a.status' => 2))
            ->find();
        // $page=$data->render();
        // $this -> assign('count',$data -> total());
        $this->assign('data', $data);
        return $this->view->fetch();
    }

    //排序
    public function sort()
    {
        if (input("field") && input("sort")) {
            $order = input("field") . " " . input("sort");
            $list = Db::name('order')
            ->alias("a")
            ->join('zs_user b', 'b.id=a.uid', 'LEFT')
            ->join('zs_service_type c', 'c.id=a.type', 'LEFT')
            ->join('zs_address d', 'd.uid=b.id', 'LEFT')
            ->join('zs_area e', 'e.id=a.city', 'LEFT')
            ->field('a.*,b.nickname,c.pid,c.service_name,d.address,d.mobile,e.areaname')
            ->where(array('a.status' => 2))
            ->order($order)->paginate(20);
            $page = $list->render();
            foreach($list->items() as $k=>$v)
            {
              $list->items()[$k]['content'] =json_decode($v['content'],true);//json_ecode/转成数组后成对象-第二个参数必须加true才能以数组的形式取值
   
            }
            $this->assign('count', $list->total());
            $this->assign('list', $list);
            $this->assign('page', $page);
        }
        // return $this->view->fetch();
        return $this->view->fetch('sort');
    }

    //增加---方法
    public function add()
    {
        $list = Db::name('area')->where(array('level' => 2))->select();
        $levels = Db::name('runmen_level')->select();
        $add = array();
        if ($_POST) {
            $mobile = input('post.mobile', " ", "trim");
            $password = md5(input('post.password'));
            $pro = input('post.pro');

            $truename = input('post.truename', "", "trim");
            $gender = input('post.gender');
            $id_number = input('post.id_number');
            $adress = input('post.adress');
            $urgent_phone = input('post.urgent_phone');
            $career = input('post.career');
            $money = input('post.money');
            $score = input('post.score');
            $pmobile = input('post.pmobile');
            $status = input('post.status');
            $level = input('post.level');

            $datfile = $_FILES;

            foreach ($datfile as $k => $v) {
                if ($v['name'] != "") {

                    $add[$k] = $this->upload($k);

                    // var_dump($add[$k]);
                    // die();
                }

            }
 
            $add['mobile'] = $mobile;
            $add['password'] = $password;
            $add['city'] = $pro;
            $add['truename'] = $truename;
            $add['gender'] = $gender;
            $add['id_number'] = $id_number;
            $add['adress'] = $adress;
            $add['urgent_phone'] = $urgent_phone;
            $add['career'] = $career;
            $add['score'] = $score;
            $add['pmobile'] = $pmobile;
            $add['status'] = $status;
            $time = time();
            $add['jointime'] = $time;
            $add['money'] = $money;
            $add['level'] = $level;

            $return = Db::name('runmen')->insert($add);
            if ($return) {
                $this->success('操作成功');
            } else {
                $this->error("操作失败");
            }
        }
        $this->assign('list', $list);
        $this->assign('levels', $levels);
        return $this->view->fetch();
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

//修改--方法
    public function saves($id)
    {
        $lists = Db::name('area')->where(array('level' => 2))->select();
        $levels = Db::name('runmen_level')->select();
        if ($_POST) {
            $data = input('post.');
            $map = Db::name('runmen')->where(array('id' => $id))->update($data);
            if ($map) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $list = Db::name('runmen')->where(array('id' => $id))->find();
            $this->assign('lists', $lists);
            $this->assign('levels', $levels);
            $this->assign('list', $list);
            return $this->view->fetch();
        }

    }
//删除
    public function des($id)
    {
        $return = Db::name('runmen')->where(array('id' => $id))->delete();
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
        $model = Db::name('runmen')->where($map)->delete();
        if ($model) {

            $this->success('删除成功');
        } else {

            return false;

        }

    }

}
