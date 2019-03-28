<?php
namespace app\admin\controller\management;

use app\common\controller\Backend;
use think\Db;
use think\File;
use think\Request;
use think\Session;

class Management extends Backend
{

    public function index()
    {
        $admin = Session::get('admin'); //获取登录session数组信息
        // $hide=Db::name('runmen')->where('is_extension',['>',0],['<>',1],'or')->find();
        $map = [];
        $areaname = $this -> request -> param('areaname');
        if ($areaname){
            $map['areaname'] = ['like','%'.$areaname.'%'];
        }
        $this -> assign('areaname',$areaname);
        $keyword = $this->request->param('keyword');
        if ($keyword) {
            $map['truename'] = ['like', '%' . $keyword . '%'];
        }
        $this->assign('keyword', $keyword);

        if ($admin['id'] == 1) {
            $list = db('runmen')
                ->alias("a")
                ->join('zs_area e', 'e.id=a.city', 'LEFT')
                ->join('zs_runmen_level c', 'c.id=a.level', 'LEFT')
                ->field('a.*,e.areaname ,c.name')
                ->where($map)->order('id dec')->paginate(20);

        } else {
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            // // $cityList = [];
            //     foreach($runmenlist as $kk=>$vv){
            //      $runmencity = $vv['city'];
            // }
            //     foreach($groups as $k=>$v){
            //      $groupsList []= $v['city'];
            // }
            //$groupsLists = implode(',', $groupsList);
            //$runmencitys =implode(',',$runmencity);
            //select * from treenodes where FIND_IN_SET(id,'1,2,3,4,5');
            // $map['city']="FIND_IN_SET($runmencity,'11100')";
            //$map['_string']="FIND_IN_SET(".$group_id.",group_id_list)";查询某个分组的所有人，而每个人有多个分组id，通过逗号组合成的group_id_list作为字段存储的
            //$map['city'] = ['IN', $cityList];
            $map['city'] = $cityList['city'];
            $list = db('runmen')
                ->alias("a")
                ->join('zs_area e', 'e.id=a.city', 'LEFT')
                ->join('zs_runmen_level c', 'c.id=a.level', 'LEFT')
                ->field('a.*, e.areaname ,c.name')
                ->where($map)->order('id dec')->paginate(20);
        }
        $page = $list->render();
        $this->assign('count', $list->total());
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->view->fetch();
    }

    //照片详情
    public function show_paper()
    {
        $id = input('param.id'); //请求当前变量值
        $list = Db::name('runmen')->where(array('id' => $id))->find();
        $this->assign('list', $list);
        return $this->view->fetch();
    }

    //推广状态--0/1
    public function is_exten()
    {

        $where = array(
            'is_extension' => array('neq', '0'),
        );

        $show = Db::name('runmen')->where($where)->setField('is_extension', 0); //开启

        if ($show) {
            $res['code'] = 0;
            $res['msg'] = '开启推广成功!';
            // return $this->success('a','',$res);
            exit(json_encode($res));
        }
        $where['is_extension'] = 0;
        $show = Db::name('runmen')->where($where)->setField('is_extension', 1); //关闭
        if ($show) {
            $res['code'] = 1;
            $res['msg'] = '关闭推广成功';
            exit(json_encode($res));
        } else {
            $res['code'] = 2;
            $res['msg'] = '操作失败';
            exit(json_encode($res));
        }

    }

    //增加---方法
    public function add()
    {
        $list = Db::name('area')->where(array('level' => 2))->select();
        $levels = Db::name('runmen_level')->select();
        $add = array();
        if ($_POST) {
            $add['mobile'] = input('post.mobile', " ", "trim");
            $add['password'] = md5(input('post.password'));
            $add['city'] = input('post.city');
            $add['truename'] = input('post.truename', "", "trim");
            $add['gender'] = input('post.gender');
            $add['id_number'] = input('post.id_number');
            $add['adress'] = input('post.adress');
            $add['urgent_phone'] = input('post.urgent_phone');
            $add['career'] = input('post.career');
            $add['money'] = input('post.money');
            $add['score'] = input('post.score');
            $add['pmobile'] = input('post.pmobile');
            $time = time();
            $add['jointime'] = $time;
            $add['status'] = input('post.status');
            $add['level'] = input('post.level');
            $datfile = $_FILES;

            foreach ($datfile as $k => $v) {
                if ($v['name'] != "") {
                    $add[$k] = $this->upload($k);
                    // var_dump($add[$k]);
                    // die();
                }
            }

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

    //获取县级分类
    public function xianji()
    {
        $parent_id['parentid'] = input('post.pro_id', 'addslashes');
        var_dump($parent_id);

        $region = Db::name('area')->where($parent_id)->select();
        $opt = '<option>--请选择市区--</option>';
        foreach ($region as $key => $val) {
            $opt .= "<option value='{$val['id']}'>{$val['shortname']}</option>";
        }
        //  echo json_encode($opt);
        echo $opt;
    }

    //上传文件--方法
    public function upload($image)
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($image);

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->validate(['size' => 1567800000, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . '/uploads');
            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 jpg'
                $img = "/" . 'uploads/' . $info->getSaveName();

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
            $datfile = $_FILES;
            foreach ($datfile as $k => $v) {
                if ($v['name'] != "") {
                    $data[$k] = $this->upload($k);
                }
            }
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
