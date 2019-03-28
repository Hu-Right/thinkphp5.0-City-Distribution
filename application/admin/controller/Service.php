<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/5
 * Time: 9:54
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Session;

class Service extends Backend
{
    public function index()
    {$admin = session::get('admin');
        $map = [];
        if ($this->request->param('service_name')) {
            $map['service_name'] = ['like', '%' . $this->request->param('service_name') . '%'];
        }

        $listRows = 20;
        if ($admin['id'] == 1) {
            $service = Db::name('service_type')
                ->alias("a")
                ->join('zs_area c', 'c.id=a.city', 'LEFT')
                ->field('a.*,c.areaname')
                ->where($map)
                ->where(array('pid' => 0))
                ->order('sort,create_time')
                ->paginate($listRows, false, ['query' => $this->request->get()]);

            //   foreach($service->items() as $k=>$v)
            //      {
            //       $children  = Db::name('service_type')
            //       -> alias("a")
            //       -> join('zs_area c','c.id=a.city','LEFT')
            //       -> field('a.*,c.areaname')
            //       -> where($map)
            //       ->where(array('pid'=>$v['id']))
            //       -> order('pid,create_time')
            //       -> paginate($listRows,false,['query' => $this -> request -> get()]);
            //       if($children){
            //         $service->items()[$k]['children']=$children;
            //         // dump($children);
            //     }else{
            //         $service->items()[$k]['children']=0;
            //     }
            // }
            //  public function  getNavCates(){
            //     //获取导航列表及子列表
            //     $cateres=db('cate')->where('pid',0)->select();
            //     foreach ($cateres as $k=> $v){
            //         $children=db('cate')->where('pid',$v['id'])->select();
            //         if($children){
            //             $cateres[$k]['children']=$children;
            //            // dump($children);die;
            //         }else{
            //             $cateres[$k]['children']=0;
            //         }
            //     }
            //   //  dump($cateres);die;
            //     $this->assign('cateres',$cateres);
            // }
        } else {
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            $service = Db::name('service_type')
                ->alias("a")
                ->join('zs_area c', 'c.id=a.city', 'LEFT')
                ->field('a.*,c.areaname')
                ->where($map)
                ->where(array('a.city' => $cityList['city']))
                ->order('pid,create_time')
                ->paginate($listRows, false, ['query' => $this->request->get()]);

        }

        $this->assign('page', $service->render());
        $this->assign('count', $service->total());
        $this->assign('service', $service);

        return view();
    }

    public function d_service()
    {
        // $service = $this->select();
        $service = Db::name('service_type')->select();
        return $this->listservice($service);

    }
    public function listservice($service, $pid = 0, $level = 0)
    {
        static $res = array();
        foreach ($service as $k => $v) {
            if ($v['pid'] == $pid) {
                $v['level'] = $level;
                $ret[] = $v;
                $this->listservice($service, $v['pid'], $level + 1);
            }
        }
        return $res;
    }
    /**
     * 添加
     */
    public function add_service()
    {$citylist = Db::name('area')->where(array('level' => 2))->select();
        if (\think\Request::instance()->isPost()) {
            $data = [];
            $data['service_name'] = $_POST['service_name'];
            $data['starting_price'] = $_POST['starting_price'];
            $data['pid'] = $_POST['pid'];
            $data['status'] = $_POST['status'];
            $data['city'] = $_POST['city'];
            $data['update_time'] = time();
            $data['create_time'] = time();
            $res = Db::name('service_type')->insert($data);
            if ($res) {
                $this->success();
            } else {
                return false;
            }
        } else {
            $top_service = Db::name('service_type')->where(['pid' => 0]) -> order('sort')->select();
            $this->assign('top_service', $top_service);
            $this->assign('citylist', $citylist);
            return view();
        }
    }

    /**
     * 编辑
     */
    public function edit_service()
    {$citylist = Db::name('area')->where(array('level' => 2))->select();
        if (\think\Request::instance()->isPost()) {
            $id = input('param.id');
            $data = [];
            $data['service_name'] = $_POST['service_name'];
            $data['starting_price'] = $_POST['starting_price'];
            $data['pid'] = $_POST['pid'];
            $data['status'] = $_POST['status'];
            $data['city'] = $_POST['city'];
            $data['update_time'] = time();
            $res = Db::name('service_type')->where(['id' => $id])->update($data);
            if ($res) {
                $this->success('修改成功');
            } else {
                return false;
            }
        } else {
            $id = input('param.id');
            $service = Db::name('service_type')->where(['id' => $id])->find();
            $top_service = Db::name('service_type')->where(['pid' => 0]) -> order('sort')->select();

            $this->assign('service', $service);
            $this->assign('citylist', $citylist);
            $this->assign('top_service', $top_service);
            return view();
        }
    }

    //删除单个
    public function des($id)
    {
        $res = Db::name('service_type')->where(['id' => $id])->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            return false;
        }
    }

    //批量删除
    public function delslect()
    {
        $id = input('param.id/a');
        $where['id'] = ['in', $id];
        $res = Db::name('service_type')->where($where)->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            return false;
        }
    }

////

    /**
     * 类别列表
     */
    public function category()
    {

        $sotitle = input('sotitle');

        $sotype = input('sotype');
        $limit = input('limit');
        $limit = $limit ? $limit : 10;
        $this->assign('sotype', $sotype);
        $this->assign('sotitle', $sotitle);
        $this->assign('limit', $limit);

        if ($sotitle) {
            //模糊查询
            if ($sotype == "title" || $sotype == "") {
                $where = [
                    ['title', 'like', "%" . $sotitle . "%"],
                ];
            } else {
                $where[$sotype] = $sotitle;
            }

        } else {
            $where['level'] = 1;
        }

        $rs = Db::name('product_category')->where($where)->order(['sort' => 'desc'])->paginate($limit, false, ['query' => request()->param()]);

        //$rs = Db::name('product_category')->where('level',1)->order('sort desc')->select();
        $data = "";

        foreach ($rs as $k => $v) {
            //2级
            $res2 = Db::name('product_category')->where("pid=" . $v['id'])->select();
            $data2 = "";
            foreach ($res2 as $k2 => $v2) {
                $data2[$k2]['id'] = $v2['id'];
                $data2[$k2]['title'] = $v2['title'];
                $data2[$k2]['sort'] = $v2['sort'];
                $data2[$k2]['level'] = $v2['level'];
                $data2[$k2]['time'] = $v2['time'];
                //$data2[$k2]['title']=$v2['title'];
                $data2[$k2]['child'] = $v2['title'];
            }

            $data[$k]['id'] = $v['id'];
            $data[$k]['title'] = $v['title'];
            $data[$k]['sort'] = $v['sort'];
            $data[$k]['level'] = $v['level'];
            $data[$k]['time'] = $v['time'];
            $data[$k]['child'] = $data2;

        }
        $this->assign('name', $data);

        $page = $rs->render();
        $this->assign('page', $page);
        $this->assign('rs', $rs);
        return $this->fetch('category');
    }

////

}
