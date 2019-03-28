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
 * 会员中心
 */
class User extends Frontend
{

    protected $layout = 'default';
    protected $noNeedLogin = ['oneLogin', 'index', 'login', 'register', 'third', 'mobilereg', 'mobilelog', 'resetpwd', 'runmen', 'otherLogin', 'chenkBinding'];
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

        //监听注册登录注销的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    /**
     * 空的请求
     * @param $name
     * @return mixed
     */
    public function _empty($name)
    {
        Hook::listen("user_request_empty", $name);
        return $this->view->fetch('user/' . $name);
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $isLogin = $this->auth->isLogin();
        //会员登录信息
        if($isLogin == false){
            $userInfo = '';
        }else{
            $user =$this->auth->getUser();
            $uid = $user['id'];

            $days = intval((time() - $user['createtime']) / (24 * 60 * 60));
            $this->user_level($days);

            /*echo '<pre>';
            print_r ($this->user_level($days));
            exit;*/

            $userInfo = Db::name('user')
                ->alias('u')
                ->join('__USER_LEVEL__ l ','l.level=u.level','LEFT')
                ->where(['u.id'=>$uid])
                ->field('u.*,l.name')
                ->find();

            //支付顺序
            $paymentsort = explode(",",$userInfo['paymentsort']);
            $this->view->assign('paymentsort', $paymentsort);
        }
        //菜单信息
        $serviceInfo = Db::name('ServiceType')->where(['pid'=>0,'status'=>1])->order('sort asc')->select();

        foreach($serviceInfo as $k => $v){
            $sonInfo = Db::name('ServiceType')->where(['pid'=>$v['id'],'status'=>1])->select();
            if($sonInfo){
                $serviceInfo[$k]['son'] = $sonInfo;
            }

        }

        /*//动态订单--假
        $arrOrder = [];
        for ($x=0; $x<=100; $x++) {
            $arrOrder[$this->getName()] = $this->getorder();
        }*/
        //动态订单--真
        $time = strtotime(date('Y').'-'.date('m').'-'.date('d'));//获取当日0时时间戳
        $data = Db::name('order')->where(['create_time'=>['>',$time],'status'=>['in',[5,6]]])->select();
        $arrOrder = [];

        foreach($data as $v){
            $v['content'] = json_decode($v['content'],true);
            switch($v['type'])
            {
                case 1:
                    $arrOrder[$v['content']['name']] = '帮我买';
                    break;
                case 2:
                    $arrOrder[$v['content']['name']] = '帮我送';
                    break;
                case 3:
                    $arrOrder[$v['content']['name']] = '帮我办';
                    break;
                case 4:
                    $arrOrder[$v['content']['name']] = '代排队';
                    break;
                case 5:
                    $arrOrder[$v['content']['name']] = '帮我取';
                    break;
                default:
                    $arrOrder[$v['content']['name']] = '帮我买';
            }
        }

        $this->view->assign('arrOrder', $arrOrder);
        /*echo '<pre>';
        print_r ($arrOrder);
        exit;*/

        $this->view->assign('serviceInfo', $serviceInfo);
        $this->view->assign('userInfo', $userInfo);//用户信息
        $this->view->assign('isLogin', $isLogin);//是否登录
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    /**
     * 手机注册
     */
    public function mobilereg(){
        $url = $this->request->request('url');
        if ($this->auth->id)
            $this->success(__('You\'ve logged in, do not login again'), $url);
        if ($this->request->isPost()) {

            //return $_POST;

            $username = $this->request->post('mobile');
            $password = $this->request->post('password');
            $confirmpw = $this->request->post('confirmpw');
            $mobile = $this->request->post('mobile', '');
            $referee = $this->request->post('referee');
            $captcha = $this->request->post('captcha');
            //验证规则
            $rule = [
                'username'   => 'require|length:3,30',
                'password'   => 'require|length:6,30|regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,30}$/',
                'confirmpw'  => 'require|confirm:password',
                'mobile'     => 'require|regex:/^1\d{10}$/',
                'referee'    => 'regex:/^1\d{10}$/',
                'captcha'    => 'require',
            ];
            //验证信息
            $msg = [
                'username.require'  => 'Username can not be empty',
                'username.length'   => 'Username must be 3 to 30 characters',
                'password.require'  => 'Password can not be empty',
                'password.length'   => 'Password must be 6 to 30 characters',
                'password.regex'    => '密码为6~30为字母加数字的组合',
                'confirmpw.require' => 'Password can not be empty',
                'confirmpw.confirm' => '两次密码不一致',
                'captcha.require'   => 'Captcha can not be empty',
                'mobile.require'    => '手机号不能为空',
                'mobile.regex'      => 'Mobile is incorrect',
                'referee.regex'     => '推荐人手机号格式不对',
            ];
            //验证数组
            $data = [
                'username'      => $username,
                'password'      => $password,
                'confirmpw'     => $confirmpw,
                'mobile'        => $mobile,
                'referee'       => $referee,
                'captcha'       => $captcha,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果

            if (!$result) {
                $this->error(__($validate->getError()), null);
            }

            //校验验证码
            $sms_check = Sms::check($mobile, $captcha, 'register', false);
            if(!$sms_check){
                $this->error(__('验证码不正确'));
            }
            if($referee != ''){
                $referee = Db::name('user')->where(['mobile'=>$referee])->value('id');
            }
            $extend = [
                'QQ_openid'  =>  '',
                'WX_openid'  =>  '',
                'WX_unionid' =>  '',
                'nickname'   =>  '',
                'avatar'     =>  '',
                'referee'    =>  $referee
            ];

            //注册账号
            if ($this->auth->register($username, $password, $email='', $mobile, $extend)) {
                if($referee != ''){
                    $yaoqing = Db::name('config')->where(['name'=>'yaoqing'])->value('value');
                    Db::name('user')->where(['id'=>$referee])->setInc('balance',$yaoqing);
                    $bill = [
                        'user_id'       =>  $referee,
                        'order_num'     =>  '',
                        'money'         =>  $yaoqing,
                        'order_type'    =>  0,
                        'create_time'   =>  time(),
                        'province'      =>  '',
                        'city'          =>  '',
                        'county'        =>  '',
                        'admin_id'      =>  '',
                    ];
                    Db::name('bill')->insert($bill);
                }
                $this->success(__('注册成功'), $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('注册'));
        return $this->view->fetch();
    }

    /**
     * 会员登录
     */
    public function login()
    {
        $url = $this->request->request('url');
        if ($this->auth->id)
            $this->success(__('You\'ve logged in, do not login again'), $url);
        if ($this->request->isPost()) {

            $mobile = $this->request->post('mobile');
            $password = $this->request->post('password');
            $keeplogin = (int)$this->request->post('keeplogin');
            $rule = [
                'mobile'    => 'require|regex:/^1\d{10}$/',
                'password'  => 'require|length:6,30',
            ];

            $msg = [
                'mobile.require'   => '手机号不能为空',
                'mobile.regex'     => '手机号格式不对',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
            ];
            $data = [
                'mobile'   => $mobile,
                'password'  => $password,
            ];

            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null);
                return FALSE;
            }

            if ($this->auth->login($mobile, $password)) {
                $this->success(__('登陆成功'), $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }

    /**
     * 验证码登录
     */
    public function mobilelog(){
        $url = $this->request->request('url');
        if ($this->auth->id)
            $this->success(__('You\'ve logged in, do not login again'), $url);
        if ($this->request->isPost()) {
            $mobile = $this->request->post('mobile');
            $captcha = $this->request->post('captcha');
            $keeplogin = (int)$this->request->post('keeplogin');
            //验证规则
            $rule = [
                'mobile'   => 'require|regex:/^1\d{10}$/',
                'captcha'  => 'require',
            ];
            //提示信息
            $msg = [
                'mobile.require'  => '手机号不能为空',
                'mobile.regex'    => 'Mobile is incorrect',
                'captcha.require' => '验证码不能为空',
            ];
            //验证数据
            $data = [
                'mobile'   => $mobile,
                'captcha'  => $captcha,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null);
                return FALSE;
            }

            //校验验证码
            $sms_check = Sms::check($mobile, $captcha, 'login', false);
            if(!$sms_check){
                $this->error(__('验证码不正确'));
            }

            $user_id = Db::name('user')->where(['mobile'=>$mobile])->value('id');
            if ($user_id) {
                //手机号已被注册--登录账号
                $result = $this->auth->direct($user_id);
                if ($result) {
                    $this->success(__('Logged in successful'), '/index/user/index');
                } else {
                    $this->error(__('登录失败'), null);
                }
            } else {
                //手机号未被注册--注册账号
                $username = $mobile;
                $password = 'vv' . $mobile;
                if ($this->auth->register($username, $password, $email='', $mobile)) {

                    $this->success(__('登录成功'), $url ? $url : url('user/index'));
                } else {
                    $this->error($this->auth->getError(), null);
                }
                //$this->error(__('请先注册'), '/index/user/mobilereg');
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }

    /**
     * 三方登录
     */
    public function otherLogin(){
        if ($this->request->isPost()) {
            $mobile = $this->request->post('mobile');
            $captcha = $this->request->post('captcha');
            $keeplogin = (int)$this->request->post('keeplogin');
            $type = $this->request->post('type');
            $nickname = $this->request->post('nickname');
            $avatar = $this->request->post('avatar');
            if($type == 'qq'){
                $QQ_openid = $this->request->post('QQ_openid');
            }
            if($type == 'weixin'){
                $WX_openid = $this->request->post('WX_openid');
                $WX_unionid = $this->request->post('WX_unionid');
            }
//            return $_POST;
            //验证规则
            $rule = [
                'mobile'   => 'require|regex:/^1\d{10}$/',
                'captcha'  => 'require',
            ];
            //提示信息
            $msg = [
                'mobile.require'  => '手机号不能为空',
                'mobile.regex'    => 'Mobile is incorrect',
                'captcha.require' => '验证码不能为空',
            ];
            //验证数据
            $data = [
                'mobile'   => $mobile,
                'captcha'  => $captcha,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null);
                return FALSE;
            }

            //校验验证码
            $sms_check = Sms::check($mobile, $captcha, 'login', false);
            if(!$sms_check){
                $this->error(__('验证码不正确'));
            }

            $userInfo = Db::name('user')->where(['mobile'=>$mobile])->find();
            //是否已经注册
            if ($userInfo) {//手机号已被注册--保存登录

                if($type == 'qq'){
                    $userData['QQ_openid'] = $QQ_openid;
                    if($userInfo['avatar'] == ''){
                        $userData['avatar'] = $avatar;
                    }
                    if(strpos($userInfo['nickname'],'VV') !== false){
                        $userData['nickname'] = $nickname;
                    }
                }

                if($type == 'weixin'){
                    $userData['WX_openid'] = $WX_openid;
                    $userData['WX_unionid'] = $WX_unionid;
                    if($userInfo['avatar'] == ''){
                        $userData['avatar'] = $avatar;
                    }
                    if(strpos($userInfo['nickname'],'VV') !== false){
                        $userData['nickname'] = $nickname;
                    }
                }
                $res = Db::name('user')->where(['mobile'=>$mobile])->update($userData);
                $result = $this->auth->direct($userInfo['id']);
                if ($res&&$result) {
                    $this->success(__('绑定成功'), '/index/user/index');
                } else {
                    $this->error(__('绑定失败'), '/index/user/login');
                }
            }
            else
            {//手机号未被注册--注册账号

                $username = $mobile;
                $password = 'vv' . $mobile;
                if($type == 'qq'){
                    $extend = [
                        'QQ_openid'  =>  $QQ_openid,
                        'WX_openid'  =>  '',
                        'WX_unionid' =>  '',
                        'nickname'   =>  $nickname,
                        'avatar'     =>  $avatar,
                        'referee'    =>  ''
                    ];
                }
                if($type == 'weixin'){
                    $extend = [
                        'QQ_openid'  =>  '',
                        'WX_openid'  =>  $WX_openid,
                        'WX_unionid' =>  $WX_unionid,
                        'nickname'   =>  $nickname,
                        'avatar'     =>  $avatar,
                        'referee'    =>  ''
                    ];
                }
                if ($this->auth->register($username, $password, $email='', $mobile, $extend)) {
                    $this->success(__('绑定成功'), '/index/user/index');
                } else {
                    $this->error($this->auth->getError(), null);
                }
            }
        }
        $this->view->assign('title', __('账号绑定'));
        return $this->view->fetch();
    }

    /**
     * 重置密码
     */
    public function resetpwd(){
        $url = $this->request->request('url');
        if ($this->auth->id)
            $this->success(__('You\'ve logged in, do not login again'), $url);
        if ($this->request->isPost()) {
            $password = $this->request->post('password');
            $confirmpw = $this->request->post('confirmpw');
            $mobile = $this->request->post('mobile', '');
            $captcha = $this->request->post('captcha');
            //验证规则
            $rule = [
                'password'   => 'require|length:6,30|regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,30}$/',
                'confirmpw'  => 'require|confirm:password',
                'mobile'     => 'require|regex:/^1\d{10}$/',
                'captcha'    => 'require',
            ];
            //验证信息
            $msg = [
                'password.require'  => 'Password can not be empty',
                'password.length'   => 'Password must be 6 to 30 characters',
                'password.regex'    => '密码格式不正确',
                'confirmpw.require' => 'Password can not be empty',
                'confirmpw.confirm' => '两次密码不一致',
                'captcha.require'   => 'Captcha can not be empty',
                'mobile.require'    => '手机号不能为空',
                'mobile.regex'      => 'Mobile is incorrect',
            ];
            //验证数组
            $data = [
                'password'      => $password,
                'confirmpw'     => $confirmpw,
                'mobile'        => $mobile,
                'captcha'       => $captcha,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                $this->error(__($validate->getError()), null);
            }

            //校验验证码
            $sms_check = Sms::check($mobile, $captcha, 'resetpwd', false);
            if(!$sms_check){
                $this->error(__('验证码不正确'));
            }

            //return $sms_check;

            $user_data = Db::name('user')->where(['mobile'=>$mobile])->find();
            $newpassword = $this->auth->getEncryptPassword($password, $user_data['salt']);
            $ret = Db::name('user')->where(['mobile'=>$mobile])->update(['password'=>$newpassword, 'updatetime'=>time()]);
            /*echo '<pre>';
            print_r($newpassword);
            exit;*/
            if ($ret) {
                $this->success(__('Reset password successful'), url('user/login'));
            } else {
                $this->error($this->auth->getError(), null);
            }
            /*echo '<pre>';
            print_r($result);
            exit;*/
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    function logout()
    {
        //注销本站
        $this->auth->logout();
        $this->success(__('注销成功'), url('user/index'));
    }

    /**
     * 个人信息
     */
    public function profile()
    {
        $user =$this->auth->getUserinfo();
        $uid = $user['id'];
        $userInfo = Db::name('user')
            ->alias('u')
            ->join('__USER_LEVEL__ l ','l.level=u.level','LEFT')
            ->where(['u.id'=>$uid])
            ->field('u.*,l.name')
            ->find();
        //md5(md5($password) . $salt);
        /*echo '<pre>';
        print_r($userInfo);
        exit;*/
        $mobile = substr_replace($user['mobile'], '****', 3, 4);
        $this->view->assign('mobile', $mobile);
        $this->view->assign('userInfo', $userInfo);
        $this->view->assign('title', __('Profile'));
        return $this->view->fetch();
    }

    /**
     * 修改昵称
     */
    public function changenkn(){
        if ($this->request->isPost()) {
            $user = $this->auth->getUser();
            $nickname = $this->request->post("nickname");
            $rule = [
                'nickname'   => 'require|length:2,5|chsAlpha',
            ];

            $msg = [
                'nickname.require'     =>  '昵称不能为空',
                'nickname.length'      =>  '请输入2~5个字母、汉字',
                'nickname.chsAlpha'    =>  '请输入2~5个字母、汉字',
            ];
            $data = [
                'nickname'      => $nickname,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null);
                return FALSE;
            }
            //return $result;
            if($user->nickname == $nickname){
                $this->success(__('修改昵称成功'), url('user/index'));
            }
            $user->nickname = $nickname;
            $ret = $user->save();
            if ($ret) {
                $this->success(__('修改昵称成功'), url('user/index'));
            } else {
                $this->error($this->auth->getError(), null);
            }
        }
        $this->view->assign('title', __('修改昵称'));
        return $this->view->fetch();
    }

    /**
     * 等级详情
     */
    public function showlevel(){
        $this->view->assign('title', __('用户等级'));
        return $this->view->fetch();
    }

    /**
     * 修改性别
     */
    public function changesex(){
        if ($this->request->isPost()) {
            $user = $this->auth->getUser();
            $gender = $this->request->post("gender");
            $rule = [
                'gender'        => 'require',
            ];

            $msg = [
                'gender.require'   =>  '请选择性别',
            ];
            $data = [
                'gender'      => $gender,
            ];

            //return $data;

            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null);
                return FALSE;
            }
            if($user->gender == $gender){
                $this->success(__('修改性别成功'), url('user/index'));
            }
            $user->gender = $gender;
            $ret = $user->save();
            if ($ret) {
                $this->success(__('修改性别成功'), url('user/index'));
            } else {
                $this->error($this->auth->getError(), null);
            }
        }
    }

    /**
     * 修改手机号
     */
    public function changemobile(){
        if ($this->request->isPost()) {

            $user = $this->auth->getUser();
            $mobile = $this->request->post("mobile");
            $captcha = $this->request->post("captcha");
            $rule = [
                'mobile'     => 'require|regex:/^1\d{10}$/',
                'captcha'    => 'require',
            ];

            $msg = [
                'captcha.require'   => 'Captcha can not be empty',
                'mobile.require'    => '手机号不能为空',
                'mobile.regex'      => 'Mobile is incorrect',
            ];
            $data = [
                'mobile'       => $mobile,
                'captcha'      => $captcha,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null);
                return FALSE;
            }
            //校验验证码
            $sms_check = Sms::check($mobile, $captcha, 'changemobile', false);
            if(!$sms_check){
                $this->error(__('验证码不正确'));
            }

            $user->mobile = $mobile;
            $user->username = $mobile;
            $ret = $user->save();
            if ($ret) {
                $this->success(__('修改手机号成功'), url('user/index'));
            } else {
                $this->error($this->auth->getError(), null);
            }
        }

        $user =$this->auth->getUserinfo();
        $mobile = substr_replace($user['mobile'], '****', 3, 4);

        /*echo '<pre>';
        print_r($mobile);
        exit;*/

        $this->view->assign('mobile', $mobile);
        $this->view->assign('title', __('修改手机号'));
        return $this->view->fetch();
    }

    /**
     * 修改密码
     */
    public function changepwd()
    {
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post("oldpassword");
            $newpassword = $this->request->post("newpassword");
            $renewpassword = $this->request->post("renewpassword");
            $rule = [
                'oldpassword'   => 'require',
                'newpassword'   => 'require|length:6,30|regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,30}$/',
                'renewpassword' => 'require|confirm:newpassword',
            ];

            $msg = [
            ];
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
            ];
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null);
                return FALSE;
            }

            /*if($oldpassword != md5($newpassword).){

            }*/

            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $this->success(__('Reset password successful'), url('user/login'));
            } else {
                $this->error($this->auth->getError(), null);
            }
        }
        $userInfo =$this->auth->getUser();
        $this->view->assign('userInfo', $userInfo);
        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }

    /**
     * 用户设置
     */
    public function memberset(){
        if ($this->request->isPost()) {
            $user = $this->auth->getUser();
            $isincubator = $this->request->post("isincubator");
            $iscall = $this->request->post("iscall");
            $isvoiceprompt = $this->request->post("isvoiceprompt");

            if($isincubator != ''){
                $user->isincubator = $isincubator;
            }
            if($iscall != ''){
                $user->iscall = $iscall;
            }
            if($isvoiceprompt != ''){
                $user->isvoiceprompt = $isvoiceprompt;
            }
            $ret = $user->save();
            if ($ret) {
                $this->success();
            } else {
                $this->error();
            }
        }
        $userInfo = $this->auth->getUser();

        $this->view->assign('userInfo', $userInfo);

        /*echo '<pre>';
        print_r($userInfo);
        exit;*/

        $this->view->assign('title', __('用户设置'));
        return $this->view->fetch();
    }

    /**
     * 支付顺序
     */
    public function paymentsort(){
        if ($this->request->isPost()) {
            $user = $this->auth->getUser();
            $paymentsort = $this->request->post("paymentsort");
            $user->paymentsort = $paymentsort;
            $ret = $user->save();
            if ($ret) {
                $this->success();
            } else {
                $this->error();
            }
        }
        $userInfo = $this->auth->getUser();
        $paymentsort = $userInfo['paymentsort'];
        $first = $this->cut_str($paymentsort,',',0);
        $second = $this->cut_str($paymentsort,',',1);
        $third = $this->cut_str($paymentsort,',',2);
        $firsticon = $this->get_icon($first);
        $secondicon = $this->get_icon($second);
        $thirdicon = $this->get_icon($third);

        $this->view->assign('first', $first);
        $this->view->assign('firsticon', $firsticon);
        $this->view->assign('second', $second);
        $this->view->assign('secondicon', $secondicon);
        $this->view->assign('third', $third);
        $this->view->assign('thirdicon', $thirdicon);
        $this->view->assign('title', __('支付顺序'));
        return $this->view->fetch();
    }

    /**
     * 使用帮助
     */
    public function userhelp(){
        $this->view->assign('title', __('使用帮助'));
        return $this->view->fetch();
    }

    /**
     * 帮助详情
     */
    public function helpShow(){
        $title = $this->request->get("title");
        $id = $this->request->get("id");

        $data = Db::name('help_document')->where(['id'=>$id])->find();

        $this->view->assign('data', $data);
        $this->view->assign('title', $title);
        return $this->view->fetch();
    }

    /**
     * 邀请奖励
     */
    public function invitingawards(){
        $this->view->assign('title', __('邀请奖励'));
        return $this->view->fetch();
    }

    /**
     * 邀请二维码
     */
    public function inviteQR(){
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';//判断url地址是http还是https
        vendor("phpQrcode.phpQrcode");
        //$url=$http_type . $_SERVER['HTTP_HOST'] . '/index/user/mobilereg';//二维码内容
        $url='下载地址';//二维码内容
        $qrcode = new \QRcode();
        //生成二维码图片
        $qrcode::png($url,false, 'H', 6, 2);
        $icon = '../logo.png';
        if($icon){
            $code = ob_get_clean();
            $code = imagecreatefromstring($code);
            $logo = imagecreatefrompng($icon);
            $QR_width = imagesx($code);//二维码图片宽度
            $QR_height = imagesy($code);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 4;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($code, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
            header ( "Content-type: image/png" );
            ImagePng($code);die;
        }
    }

    /**
     * 意见反馈
     */
    public function advise(){
        if ($this->request->isPost()) {
            $user = $this->auth->getUser();
            $img = $this->request->post("img/a");
            $content = $this->request->post("content");
            if($content==null){
                $this->error('请输入意见反馈后，再提交！');
            }

            $data = [
                'user_id'       =>  $user['id'],
                'content'       =>  $content,
                'img'           =>  json_encode($img),
                'create_time'   =>  time(),
            ];
            $ret = Db::name('feedback')->insert($data);
            if ($ret) {
                $this->success('提交成功', url('user/index'));
            } else {
                $this->error('提交失败');
            }
        }
        $this->view->assign('title', __('意见反馈'));
        return $this->view->fetch();
    }

    /**
     * 意见反馈
     */
    public function oneLogin(){

        $s_token = Session::get('token');
        $c_token = Cookie::get('token');
        $c_uid = Cookie::get('uid');
        $d_token = Db::name('user')->where(['id'=>Cookie::get('uid')])->value('token');

        /*$ret = [
            's_token'  =>  $s_token,
            'c_token'   =>  $c_token,
//            'c_uid'  =>  $c_uid,
            'd_token'   =>  $d_token,
        ];
        return $ret;*/

        if($s_token == $d_token){
            $ret = [
                'code'  =>  0,
                'msg'   =>  '不变',
                'data'  =>  '',
                'url'   =>  '',
                'wait'  =>  3
            ];
        }else{
            $ret = [
                'code'  =>  1,
                'msg'   =>  '刷新',
                'data'  =>  '',
                'url'   =>  '/index/user/login',
                'wait'  =>  3
            ];
        }
        return $ret;


    }



    /**
     * 按符号截取字符串的指定部分
     * @param string $str 需要截取的字符串
     * @param string $sign 需要截取的符号
     * @param int $number 如是正数以0为起点从左向右截 负数则从右向左截
     * @return string 返回截取的内容
     */
    public function cut_str($str,$sign,$number){
        $array=explode($sign, $str);
        $length=count($array);
        if($number<0){
            $new_array=array_reverse($array);
            $abs_number=abs($number);
            if($abs_number>$length){
                return 'error';
            }else{
                return $new_array[$abs_number-1];
            }
        }else{
            if($number>=$length){
                return 'error';
            }else{
                return $array[$number];
            }
        }
    }

    /**
     * 按支付方式获取对应icon名称
     * @param string $str 支付方式名称字符串
     * @return string 返回icon名称（前台CSS设置,前台改动这里必须改动）
     */
    public function get_icon($str){
        if(strpos($str,'支付宝') !== false){
            $icon = 'icon-zhifubaozhifu';
        }elseif(strpos($str,'微信') !== false){
            $icon = 'icon-weixinzhifu';
        }elseif(strpos($str,'余额') !== false){
            $icon = 'icon-yue';
        }
        return $icon;
    }

    /**
     * 地图上附近的跑男
     * @param int $lon 经度
     * @param int $lat 纬度
     * @return array 返回符合条件的跑男的经纬度数组
     */
    public function runmen(){
        $metre = '888';
        $lon = $this->request->post('lon');
        $lat = $this->request->post('lat');
        //$lon = 113.7119335266;
        //$lat = 34.769042609696;

        $sql = "select * FROM zs_runmen having ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$lon}*PI()/180-lon*PI()/180)/2),2)))*1000) <= {$metre} and status = 1";

        $runmen =Db::query($sql);
        if($runmen){
            foreach($runmen as $rowdata){
                $points[]=[
                    'lon'=>$rowdata['lon'],
                    'lat'=>$rowdata['lat'],
                ];
            }
            return $this->success('成功','',$points);
        }else{
            return $this->error('失败','');
            //return json_encode($points);
        }
    }

    /**
     * 用户等级提升
     * @param string $days 用户创建天数
     * @return int 返回等级（user_level 表中 level 字段）
     */
    public function user_level($days){
        $user =$this->auth->getUser();
        $uid = $user['id'];
        $levelInfo = Db::name('user_level')->where(['status'=>1,'level'=>['>',$user['level']]])->order('level desc')->select();
        //return $levelInfo;
        foreach($levelInfo as $level){
            if($days >= $level['condition']){
                Db::name('user')->where(['id'=>$uid])->setField('level',$level['level']);
                return;
            }
        }
    }

    /**
     * 更新用户位置信息
     * @param string $province 省
     * @param string $city 市
     * @param string $county 县
     * @return int 返回等级（user_level 表中 level 字段）
     */
    public function updateArea(){
        $user =$this->auth->getUser();
        $province = $this->request->post('province');
        $city = $this->request->post('city');
        $county = $this->request->post('county');
        $uid = $user['id'];
        $provinceId = Db::name('area')->where(['areaname'=>$province])->value('id');
        $cityId = Db::name('area')->where(['areaname'=>$city])->value('id');
        $countyId = Db::name('area')->where(['areaname'=>$county])->value('id');
        $area = [
            'province'  =>  $provinceId,
            'city'  =>  $cityId,
            'county'  =>  $countyId,
        ];
        $res = Db::name('user')->where(['id'=>$uid])->update($area);
        if($res){
            return $this->success('成功','');
        }else{
            return $this->error('失败','');
        }
    }

    /**
     * 检测用户是否绑定
     * @param string $type 类型
     * @param string $openid 标记值
     * @return array $data 判断的信息
     */
    public function chenkBinding(){
        $type = $this->request->post('type');
        $openid = $this->request->post('openid');
        if($type == 'qq'){
            $user_id = Db::name('user')->where(['QQ_openid'=>$openid])->value('id');
        }
        if($type == 'weixin'){
            $user_id = Db::name('user')->where(['WX_openid'=>$openid])->value('id');
        }
        if($user_id){
            $result = $this->auth->direct($user_id);
            if ($result) {
                $this->success(__('登录成功'), '/index/user/index');
            } else {
                $this->error(__('登录失败'), null);
            }
        }else{
            $data = [
                'code'  =>  3,
                'msg'   =>  '请先绑定',
                'data'  =>  '',
                'url'   =>  '',
                'wait'  =>  3,
            ];
            return $data;
        }

    }

    /*public function test(){

        $arrorder = [];
        for ($x=0; $x<=100; $x++) {
            $arrorder[$this->getName()] = $this->getorder();
        }
        echo '<pre>';
        print_r($arrorder);
    }*/

    /*随机订单类型*/
    public function getorder(){
        $arrType=array(
            '帮我买','帮我取','帮我送','帮我办','代排队');
        //名总数
        $numbType = count($arrType);
        return $arrType[mt_rand(0,$numbType-1)];
    }


    // 获取姓
    private function getXing()
    {
        $arrXing=array(
            '赵','钱','孙','李','周','吴','郑','王','冯','陈','褚','卫','蒋',
            '沈','韩','杨','朱','秦','尤','许','何','吕','施','张','孔','曹','严','华','金','魏',
            '陶','姜','戚','谢','邹','喻','柏','水','窦','章','云','苏','潘','葛','奚','范','彭',
            '郎','鲁','韦','昌','马','苗','凤','花','方','任','袁','柳','鲍','史','唐','费','薛',
            '雷','贺','倪','汤','滕','殷','罗','毕','郝','安','常','傅','卞','齐','元','顾','孟',
            '平','黄','穆','萧','尹','姚','邵','湛','汪','祁','毛','狄','米','伏','成','戴','谈',
            '宋','茅','庞','熊','纪','舒','屈','项','祝','董','梁','杜','阮','蓝','闵','季','贾',
            '路','娄','江','童','颜','郭','梅','盛','林','钟','徐','邱','骆','高','夏','蔡','田',
            '樊','胡','凌','霍','虞','万','支','柯','管','卢','莫','柯','房','裘','缪','解','应',
            '宗','丁','宣','邓','单','杭','洪','包','诸','左','石','崔','吉','龚','程','嵇','邢',
            '裴','陆','荣','翁','荀','于','惠','甄','曲','封','储','仲','伊','宁','仇','甘','武',
            '符','刘','景','詹','龙','叶','幸','司','黎','溥','印','怀','蒲','邰','从','索','赖',
            '卓','屠','池','乔','胥','闻','莘','党','翟','谭','贡','劳','逄','姬','申','扶','堵',
            '冉','宰','雍','桑','寿','通','燕','浦','尚','农','温','别','庄','晏','柴','瞿','阎',
            '连','习','容','向','古','易','廖','庾','终','步','都','耿','满','弘','匡','国','文',
            '寇','广','禄','阙','东','欧','利','师','巩','聂','关','荆','司马','上官','欧阳','夏侯',
            '诸葛','闻人','东方','赫连','皇甫','尉迟','公羊','澹台','公冶','宗政','濮阳','淳于','单于',
            '太叔','申屠','公孙','仲孙','轩辕','令狐','徐离','宇文','长孙','慕容','司徒','司空');

        $numbXing = count($arrXing); //姓总数
        // mt_rand() 比rand()方法快四倍，而且生成的随机数比rand()生成的伪随机数无规律。
        return $arrXing[mt_rand(0,$numbXing-1)];

    }

    // 获取名
    private function getMing()
    {
        $arrMing=array('先生','女士');

        //名总数
        $numbMing = count($arrMing);
        return $arrMing[mt_rand(0,$numbMing-1)];
    }


    // 获取名字
    public function getName($type=0)
    {
        $name = '' ;
        switch($type)
        {
            case 1:    //2字
                $name = $this->getXing().$this->getMing();
                break;
            case 2:    //随机2、3个字
                $name = $this->getXing().$this->getMing();
                if(mt_rand(0,100)>50)$name .= $this->getMing();
                break;
            case 3: //只取姓
                $name = $this->getXing();
                break;
            case 4: //只取名
                $name = $this->getMing();
                break;
            case 0:
            default: //默认情况 1姓+2名
                $name = $this->getXing().$this->getMing();


        }

        return $name;
    }

}
