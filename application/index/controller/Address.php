<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Sms;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Session;
use think\Db;
use think\Validate;

/**
 * 地址中心
 */
class Address extends Frontend
{

    protected $layout = 'default';
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $auth = $this->auth;

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

        $ucenter = get_addon_info('ucenter');
        if ($ucenter && $ucenter['state']) {
            include ADDON_PATH . 'ucenter' . DS . 'uc.php';
        }
    }

    /**
     * 地址中心
     */
    public function index(){
        $user = $this->auth->getUser();
        $homeInfo = Db::name('address')->where(['uid'=>$user['id'],'is_home'=>1])->find();
        $companyInfo = Db::name('address')->where(['uid'=>$user['id'],'is_company'=>1])->find();
        $addressInfo = Db::name('address')->where(['uid'=>$user['id'],'is_home'=>0,'is_company'=>0])->order('id desc')->select();

        $this->view->assign('homeInfo', $homeInfo);
        $this->view->assign('companyInfo', $companyInfo);
        $this->view->assign('addressInfo', $addressInfo);
        $this->view->assign('title', __('地址中心'));
        return $this->view->fetch();
    }

    //新增地址
    public function newaddress(){
        $user = $this->auth->getUser();
        $type = $this->request->get('type');
        $id = $this->request->get('id');
        $addressInfo = Db::name('address')->where(['uid'=>$user['id'],'is_home'=>0,'is_company'=>0])->order('id desc')->select();
        $this->view->assign('type', $type);
        $this->view->assign('id', $id);
        $this->view->assign('addressInfo', $addressInfo);
        $this->view->assign('title', __('选择地址'));
        return $this->view->fetch();
    }

    //删除地址
    public function deleteaddress(){
        $ids = $this->request->post("ids/a");

        foreach($ids as $id){
            if(strpos($id,'null') === false){
                $res = Db::name('address')->where(['id'=>$id])->delete();
                if(!$res){
                    return $this->error($res);
                }
            }
        }
        return $this->success($res);
    }

    //搜索地址--头
    public function searchhead(){
        $this->view->assign('title', __('搜索地址'));
        return $this->view->fetch();
    }
    //搜索地址--主体
    public function searchbody(){
        $this->view->assign('title', __('搜索地址'));
        return $this->view->fetch();
    }

    //AJAX保存
    public function save(){

        $user = $this->auth->getUser();
        $type = $this->request->post('type');
        $id = $this->request->post('id');
        $address_lon = $this->request->post('address_lon');
        $address_lat = $this->request->post('address_lat');
        $address_head = $this->request->post('address_head');
        $address = $this->request->post('address');
        $linkman = $this->request->post('linkman');
        $mobile = $this->request->post('mobile');
        if($mobile == ''){
            $mobile = $user['mobile'];
        }
        if($type == ''){
            $type = 1;
        }


        //修改/新增家庭
        if($type==1){
            $id = Db::name('address')->where(['uid'=>$user['id'],'is_home'=>1])->value('id');
            if($id){
                $data = [
                    'id'           =>  $id,
                    'linkman'      =>  $linkman,
                    'mobile'       =>  $mobile,
                    'address'      =>  $address,
                    'address_head' =>  $address_head,
                    'address_lon'  =>  $address_lon,
                    'address_lat'  =>  $address_lat,
                ];
                $res = Db::name('address')->update($data);
            }else{
                $data = [
                    'uid'          =>  $user['id'],
                    'linkman'      =>  $linkman,
                    'mobile'       =>  $mobile,
                    'address'      =>  $address,
                    'address_head' =>  $address_head,
                    'address_lon'  =>  $address_lon,
                    'address_lat'  =>  $address_lat,
                    'is_home'      =>  1,
                ];
                $res = Db::name('address')->insert($data);
            }
        }
        //修改/新增公司
        if($type==2){
            $id = Db::name('address')->where(['uid'=>$user['id'],'is_company'=>1])->value('id');
            if($id){
                $data = [
                    'id'           =>  $id,
                    'linkman'      =>  $linkman,
                    'mobile'       =>  $mobile,
                    'address'      =>  $address,
                    'address_head' =>  $address_head,
                    'address_lon'  =>  $address_lon,
                    'address_lat'  =>  $address_lat,
                ];
                $res = Db::name('address')->update($data);
            }else{
                $data = [
                    'uid'          =>  $user['id'],
                    'linkman'      =>  $linkman,
                    'mobile'       =>  $mobile,
                    'address'      =>  $address,
                    'address_head' =>  $address_head,
                    'address_lon'  =>  $address_lon,
                    'address_lat'  =>  $address_lat,
                    'is_company'      =>  1,
                ];
                $res = Db::name('address')->insert($data);
            }
        }
        //修改/新增常用
        if($type==3){
            if($id != ''){
                $data = [
                    'id'           =>  $id,
                    'linkman'      =>  $linkman,
                    'mobile'       =>  $mobile,
                    'address'      =>  $address,
                    'address_head' =>  $address_head,
                    'address_lon'  =>  $address_lon,
                    'address_lat'  =>  $address_lat,
                ];
                $res = Db::name('address')->update($data);
            }else{
                $data = [
                    'uid'          =>  $user['id'],
                    'linkman'      =>  $linkman,
                    'mobile'       =>  $mobile,
                    'address'      =>  $address,
                    'address_head' =>  $address_head,
                    'address_lon'  =>  $address_lon,
                    'address_lat'  =>  $address_lat,
                ];
                $res = Db::name('address')->insert($data);
            }
        }
        if($res){
            return $this->success('','/index/user/index');
        }else{
            return $this->error('','/index/user/index');
        }
    }


    //下单时地址选择
    public function selectaddress(){
        $user = $this->auth->getUser();
        $type = $this->request->get('type');
        $addressInfo = Db::name('address')->where(['uid'=>$user['id'],'is_home'=>0,'is_company'=>0])->order('id desc')->select();
        $this->view->assign('type', $type);
        $this->view->assign('addressInfo', $addressInfo);
        $this->view->assign('title', __('选择地址'));
        return $this->view->fetch();
    }

    //首页搜索地址--头
    public function homesearchhead(){
        $this->view->assign('title', __('搜索地址'));
        return $this->view->fetch();
    }
    //首页搜索地址--主体
    public function homesearchbody(){
        $this->view->assign('title', __('搜索地址'));
        return $this->view->fetch();
    }


}
