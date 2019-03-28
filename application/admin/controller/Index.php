<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\Config;
use think\Hook;
use think\Validate;
use think\Db;
use think\Session;
use app\common\library\Sms;

/**
 * 后台首页
 * @internal
 */
class Index extends Backend
{

    protected $noNeedLogin = ['login', 'bespeak', 'yz_code'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 后台首页
     */
    public function index()
    {
        //左侧菜单
        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar([
            'dashboard' => '',
            'addon'     => ['new', 'red', 'badge'],
            'auth/rule' => '',
            'general'   => '',
            'bespeak'   => ['0','blue', 'badge'],
        ], $this->view->site['fixedpage']);
        $action = $this->request->request('action');
        if ($this->request->isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }

        $admin = session::get('admin');
        if ($admin['id'] == 1) {
            $yesterday = date('Y-m-d', time() - 3600 * 24);
            $is_find = Db::name('order_num_statistics')->where(['city' => 0,'order_time' => $yesterday])->find();
            if (empty($is_find)) {
                //$get_YesterdayOrder = new Dashboard();
                $this -> yesterday_order_num();
            }
        }else{
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            $yesterday = date('Y-m-d', time() - 3600 * 24);
            $is_find = Db::name('order_num_statistics')->where(['city' => $cityList['city'],'order_time' => $yesterday])->find();
            if (empty($is_find)) {
                //$get_YesterdayOrder = new Dashboard();
                $this-> yesterday_order_num();
            }
        }

        $this->view->assign('menulist', $menulist);
        $this->view->assign('navlist', $navlist);
        $this->view->assign('fixedmenu', $fixedmenu);
        $this->view->assign('referermenu', $referermenu);
        $this->view->assign('title', __('Home'));
        return $this->view->fetch();
    }

    /**
     * 管理员登录
     */
    public function login()
    {
        $url = $this->request->get('url', 'index/index');
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
            if (Config::get('fastadmin.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }
            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }
        $background = Config::get('fastadmin.login_background');
        $background = stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background;
        $this->view->assign('background', $background);
        $this->view->assign('title', __('Login'));
        Hook::listen("admin_login_init", $this->request);
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        $this->success(__('Logout successful'), 'index/login');
    }


    public function yesterday_order_num()
    {
        $admin = session::get('admin');
        $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
        $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
        $today = strtotime(date('Y-m-d',time()));

        if ($admin['id'] == 1){
            $new_find = Db::name('order_num_statistics') -> where(['city' => 0]) -> order('id desc') -> limit(1) -> find();
            if (empty($new_find)){
                $data = [
                    'order_time' => date('Y-m-d', time() - 3600 * 24),
                    'create_time' => time(),
                ];
                Db::name('order_num_statistics')->insert($data);
            }else {
                if ($today - strtotime($new_find['order_time']) == 24 * 3600) {
                    //昨日开始时间
                    $yesterday_start = strtotime(date('Y-m-d', time() - 3600 * 24));
                    $yesterday_end = strtotime(date('Y-m-d', time()));
                    if ($admin['id'] == 1) {
                        $where['create_time'] = ['between', [$yesterday_start, $yesterday_end]];
                        //昨日总订单数
                        $yesterday_order_num = Db::name('order')
                            ->where($where)
                            ->count();
                        //完成数
                        $where['status'] = ['in', '5,6'];
                        $yesterday_complete_order_num = Db::name('order')
                            ->where($where)
                            ->count();

                        $data = [
                            'order_time' => date('Y-m-d', time() - 3600 * 24),
                            'order_num' => $yesterday_order_num,
                            'complete_order_num' => $yesterday_complete_order_num,
                            'create_time' => time(),
                        ];
                        Db::name('order_num_statistics')->insert($data);
                    } else {
                        $where['city'] = $cityList['city'];
                        $where['create_time'] = ['between', [$yesterday_start, $yesterday_end]];
                        //昨日总订单数
                        $yesterday_order_num = Db::name('order')
                            ->where($where)
                            ->count();
                        //完成数
                        $where['status'] = ['in', '5,6'];
                        $yesterday_complete_order_num = Db::name('order')
                            ->where($where)
                            ->count();

                        $data = [
                            'order_time' => date('Y-m-d', time() - 3600 * 24),
                            'order_num' => $yesterday_order_num,
                            'complete_order_num' => $yesterday_complete_order_num,
                            'create_time' => time(),
                            'city' => $cityList['city'],
                        ];
                        Db::name('order_num_statistics')->insert($data);
                    }
                } else {
                    $frequency = ($today - strtotime($new_find['order_time'])) / (24 * 3600);
                    for ($i = $frequency - 1; $i > 0; $i--) {
                        $yesterday_start = strtotime(date('Y-m-d', time())) - ($i * 24 * 3600);
                        $yesterday_end = strtotime(date('Y-m-d', time())) - (($i - 1) * 24 * 3600);
                        if ($admin['id'] == 1) {
                            $where['create_time'] = ['between', [$yesterday_start, $yesterday_end]];
                            //总订单数
                            $yesterday_order_num = Db::name('order')
                                ->where($where)
                                ->count();
                            //完成数
                            $where['status'] = ['in', '5,6'];
                            $yesterday_complete_order_num = Db::name('order')
                                ->where($where)
                                ->count();

                            $data = [
                                'order_time' => date('Y-m-d', $yesterday_start),
                                'order_num' => $yesterday_order_num,
                                'complete_order_num' => $yesterday_complete_order_num,
                                'create_time' => time(),
                            ];
                            Db::name('order_num_statistics')->insert($data);
                        } else {
                            $where['city'] = $cityList['city'];
                            $where['create_time'] = ['between', [$yesterday_start, $yesterday_end]];
                            //昨日总订单数
                            $yesterday_order_num = Db::name('order')
                                ->where($where)
                                ->count();
                            //完成数
                            $where['status'] = ['in', '5,6'];
                            $yesterday_complete_order_num = Db::name('order')
                                ->where($where)
                                ->count();

                            $data = [
                                'order_time' => date('Y-m-d', $yesterday_start),
                                'order_num' => $yesterday_order_num,
                                'complete_order_num' => $yesterday_complete_order_num,
                                'create_time' => time(),
                                'city' => $cityList['city'],
                            ];
                            Db::name('order_num_statistics')->insert($data);
                        }
                    }
                }
            }
        }else {
            $new_find = Db::name('order_num_statistics') -> where(['city' => $cityList['city']]) -> order('id desc') -> limit(1) -> find();

            if (empty($new_find)){
                $data = [
                    'order_time' => date('Y-m-d', time() - 3600 * 24),
                    'city' => $cityList['city'],
                    'create_time' => time(),
                ];
                Db::name('order_num_statistics')->insert($data);
            }else {
                if ($today - strtotime($new_find['order_time']) == 24 * 3600) {
                    //昨日开始时间
                    $yesterday_start = strtotime(date('Y-m-d', time() - 3600 * 24));
                    $yesterday_end = strtotime(date('Y-m-d', time()));
                    if ($admin['id'] == 1) {
                        $where['create_time'] = ['between', [$yesterday_start, $yesterday_end]];
                        //昨日总订单数
                        $yesterday_order_num = Db::name('order')
                            ->where($where)
                            ->count();
                        //完成数
                        $where['status'] = ['in', '5,6'];
                        $yesterday_complete_order_num = Db::name('order')
                            ->where($where)
                            ->count();

                        $data = [
                            'order_time' => date('Y-m-d', time() - 3600 * 24),
                            'order_num' => $yesterday_order_num,
                            'complete_order_num' => $yesterday_complete_order_num,
                            'create_time' => time(),
                        ];
                        Db::name('order_num_statistics')->insert($data);
                    } else {
                        $where['city'] = $cityList['city'];
                        $where['create_time'] = ['between', [$yesterday_start, $yesterday_end]];
                        //昨日总订单数
                        $yesterday_order_num = Db::name('order')
                            ->where($where)
                            ->count();
                        //完成数
                        $where['status'] = ['in', '5,6'];
                        $yesterday_complete_order_num = Db::name('order')
                            ->where($where)
                            ->count();

                        $data = [
                            'order_time' => date('Y-m-d', time() - 3600 * 24),
                            'order_num' => $yesterday_order_num,
                            'complete_order_num' => $yesterday_complete_order_num,
                            'create_time' => time(),
                            'city' => $cityList['city'],
                        ];
                        Db::name('order_num_statistics')->insert($data);
                    }
                } else {
                    $frequency = ($today - strtotime($new_find['order_time'])) / (24 * 3600);
                    for ($i = $frequency - 1; $i > 0; $i--) {
                        $yesterday_start = strtotime(date('Y-m-d', time())) - ($i * 24 * 3600);
                        $yesterday_end = strtotime(date('Y-m-d', time())) - (($i - 1) * 24 * 3600);
                        if ($admin['id'] == 1) {
                            $where['create_time'] = ['between', [$yesterday_start, $yesterday_end]];
                            //总订单数
                            $yesterday_order_num = Db::name('order')
                                ->where($where)
                                ->count();
                            //完成数
                            $where['status'] = ['in', '5,6'];
                            $yesterday_complete_order_num = Db::name('order')
                                ->where($where)
                                ->count();

                            $data = [
                                'order_time' => date('Y-m-d', $yesterday_start),
                                'order_num' => $yesterday_order_num,
                                'complete_order_num' => $yesterday_complete_order_num,
                                'create_time' => time(),
                            ];
                            Db::name('order_num_statistics')->insert($data);
                        } else {
                            $where['city'] = $cityList['city'];
                            $where['create_time'] = ['between', [$yesterday_start, $yesterday_end]];
                            //昨日总订单数
                            $yesterday_order_num = Db::name('order')
                                ->where($where)
                                ->count();
                            //完成数
                            $where['status'] = ['in', '5,6'];
                            $yesterday_complete_order_num = Db::name('order')
                                ->where($where)
                                ->count();

                            $data = [
                                'order_time' => date('Y-m-d', $yesterday_start),
                                'order_num' => $yesterday_order_num,
                                'complete_order_num' => $yesterday_complete_order_num,
                                'create_time' => time(),
                                'city' => $cityList['city'],
                            ];
                            Db::name('order_num_statistics')->insert($data);
                        }
                    }
                }
            }
        }
    }

    /**
     * 预约接口
     */
    public function bespeak()
    {
        header('Access-Control-Allow-Origin:http://zs013gw.c01.zstestsite.com');
        if (empty($_GET['username'])){
            return json(array('status' => -1,'msg' => '请填写用户名'));
        }
        if (empty($_GET['mobile'])){
            return json(array('status' => -1,'msg' => '请填写电话'));
        }
        if (!preg_match("/1[3458]{1}\d{9}$/", $_GET['mobile'])){
            return json(array('status' => -1,'msg' => '电话格式有误'));
        }
        if (empty($_GET['service_type'])){
            return json(array('status' => -1,'msg' => '请选择服务类型'));
        }
        if (empty($_GET['remarks'])){
            return json(array('status' => -1,'msg' => '请填写备注信息'));
        }
        $yz_code = new Sms();
        $res = $yz_code -> check($_GET['mobile'],$_GET['code'],'despeak',false);
        if ($res === false){
            return json(array('status' => -1,'msg' => '验证码输入有误'));
        }

        $data = [
            'username' => $_GET['username'],
            'mobile' => $_GET['mobile'],
            'service_type' => $_GET['service_type'],
            'remarks' => $_GET['remarks'],
            'create_time' => time(),
        ];

        $res = Db::name('bespeak') -> insert($data);
        if ($res){
            return json(array('status' => 1,'msg' => '预约成功！稍后工作人员会跟您联系'));
        }else{
            return json(array('status' => -1,'msg' => '预约失败~请您核对信息'));
        }
    }

    /**
     * 预约验证码
     */
    public function yz_code()
    {
        header('Access-Control-Allow-Origin:http://zs013gw.c01.zstestsite.com');
        $code = rand(0000,9999);
        //return $code;
        $mobile = $_GET['mobile'];
        if (empty($mobile)){
            return json(array('status' => -1,'msg' => '请填写电话'));
        }
        //$res = sendSms($mobile,$code);
        $ycode = new Sms();
        $res = $ycode->send($mobile,$code,'despeak');
        if (!empty($res)){
            return json(array('status' => 1,'msg' => '已发送，请注意查收'));
        }else{
            return json(array('status' => -1,'msg' => '发送失败，请重试'));
        }
    }



    /**
     * ajax请求预约下标数字
     */
    public function subscript()
    {
        $num = Db::name('bespeak') -> where(['is_handle' => 0]) -> count();
        return $num;
    }




}
