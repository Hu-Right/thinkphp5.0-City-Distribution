<?php
// +----------------------------------------------------------------------
// | vv跑腿配送端
// | 登录注册
// +----------------------------------------------------------------------
// | 2018-10-31
// +----------------------------------------------------------------------
// | Author: Mc小张
// +----------------------------------------------------------------------
namespace app\vvptd\controller;
use app\common\controller\Vvptd;
use think\Db;
use think\Validate;
use app\common\library\Sms;
class Login extends Vvptd{
    //登录
    public function login(){
      	if(session('rid')){
        	$this->redirect('Member/index');
        }
        if($this->request->isAjax()) {
            $data = $this->request->post();
            //验证规则
            $rule = [
                'password' => 'require|regex:/^[\S]{6,16}$/',
                'mobile' => 'require|regex:/^\d+$/',
            ];
            //验证信息
            $msg = [
                'password.require' => __('password_empty'),
                'mobile.require' => __('mobile'),
            ];
            //验证数组
            $data = [
                'password' => $data['password'],
                'mobile' => $data['mobile']
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                return $this->error(__($validate->getError()));
            }
            $data['password'] = md5(md5($data['password']));
            $data = Db::name('runmen')->where($data)->find();
            if ($data) {
                session('rid', $data['id']);
                $token = md5(md5(time().$data['id'].$data['create_time']));
                session('token',$token);
                //登录成功生成默认的地图出行配置
                $this->setRunMenMap($data['id']);
                //更新登录时间
                Db::name('runmen')->where($data)->update(array('logintime'=>time()));
                return $this->success(__('login'),'',$token);
            } else {
                return $this->error(__('login_error'));
            }
        }
        return $this->fetch('login');
    }
    //注册
    public function register(){
        if($this->request->isAjax()) {
            $data = $this->request->post();
            //验证规则
            $rule = [
                'password' => 'require|regex:/^[\S]{6,16}$/',
                'qrpassword' => 'require|regex:/^[\S]{6,16}$/',
                'mobile' => 'require|regex:/^1[3-9]\d{9}$/',
                'city' => 'require|regex:/^\d+$/',
                'code' => 'require|regex:/^\d+$/',
                'province'=>'require|regex:/^\d+$/',
                'county'=>'require|regex:/^\d+$/'
            ];
            //验证信息
            $msg = [
                'password.require' => __('password_empty'),
                'mobile.require' => __('mobile'),
                'qrpassword.require' => __('qrpassword'),
                'city.require' => __('city'),
                'code.require' => __('code'),
                'province.require' => __('city'),
                'county.require' => __('city'),
            ];
            //验证数组
            $data = [
                'password' => $data['password'],
                'mobile' => $data['mobile'],
                'qrpassword' => $data['password'],
                'city' => $data['city'],
                'code' => $data['code'],
                'province' => $data['province'],
                'county' => $data['county']
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                return $this->error(__($validate->getError()));
            }
            //校验验证码
            $sms_check = Sms::check($data['mobile'], $data['code'], 'register', false);
            if (!$sms_check) {
                return $this->error(__('验证码不正确'));
            }
            //检测手机号是否已经注册
            if ($this->checkMobile($data['mobile'])) {
                return $this->error(__('该手机号已经被注册'));
            }
            unset($data['code']);
            unset($data['qrpassword']);
            $tjrmobile = $this->request->post('tjrmobile');
            if($tjrmobile){
                $aa = Db::name('runmen')->where('mobile',$tjrmobile)->find();
                if(!empty($aa)){
                    $data['pid'] = $aa['id'];
                }
            }
            $data['password'] = md5(md5($data['password']));
            $data['create_time'] = time();
            $res = Db::name('runmen')->insert($data);
            if ($res) {
                return $this->success('操作成功');
            } else {
                return $this->error('操作失败');
            }
        }
        $city = $this->getCity();
        $this->assign('city',$city);
        return $this->fetch();
    }
    //找回密码
    public function find_password(){
        if($this->request->isAjax()) {
            $data = $this->request->post();
            //验证规则
            $rule = [
                'password' => 'require|regex:/^[\S]{6,16}$/',
                'qrpassword' => 'require|regex:/^[\S]{6,16}$/',
                'mobile' => 'require|regex:/^1[3-9]\d{9}$/',
                'code' => 'require|regex:/^\d+$/'
            ];
            //验证信息
            $msg = [
                'password.require' => __('password_empty'),
                'mobile.require' => __('mobile'),
                'qrpassword.require' => __('qrpassword'),
                'code.require' => __('code'),
            ];
            //验证数组
            $data = [
                'password' => $data['password'],
                'mobile' => $data['mobile'],
                'qrpassword' => $data['password'],
                'code' => $data['code']
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                return $this->error(__($validate->getError()));
            }
            //校验验证码
            $sms_check = Sms::check($data['mobile'], $data['code'], 'zhaohuimima', false);
            if (!$sms_check) {
                return $this->error(__('验证码不正确'));
            }
            $arr['password'] = md5(md5($data['password']));
            $where['mobile'] = $data['mobile'];
            $res = Db::name('runmen')->where($where)->update($arr);
            if ($res === false) {
                return $this->error('操作失败');
            } else {
                return $this->success('操作成功');
            }
        }
        return $this->fetch('findPassword');
    }
    //分享页面下载页面
    public function downloadApp(){
        return $this->fetch('index');
    }
    //城市信息
    public function getCitys(){
        $data = $this->request->post();
        $datas = Db::name('area')->where('parentid',$data['id'])->select();
        $str = '';
        for($i = 0;$i<count($datas);$i++){
            $str.='<option value="'.$datas[$i]['id'].'">'.$datas[$i]['areaname'].'</option>';
        }
        $this->success('成功','',$str);
    }
}