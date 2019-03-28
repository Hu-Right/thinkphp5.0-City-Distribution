<?php
// +----------------------------------------------------------------------
// | vv跑腿配送端
// | 设置中心
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
class Setting extends Vvptd{
    //设置中心
    public function index(){
        return $this->fetch('setting');
    }
    //下载离线地图
    public function downloadMap(){}
    //体现账户列表
    public function setReflect(){
        $user = Db::name('runmen')->where('id',$this->rid)->find();
        $data = Db::name('set_reflect_ptd')->where('runuser_id',$this->rid)->select();
        for($i=0;$i<count($data);$i++){
            $start = substr($data[$i]['bank_card'],'0','4');
            $end = substr($data[$i]['bank_card'],'-4');
            $data[$i]['bank_card']  = $start.'****'.$end;
        }
        $this->assign('data',$data);
        $this->assign('user',$user);
        return $this->view->fetch('account');
    }
    //添加账户
    public function add_bank(){
        return $this->fetch('addBank');
    }
    //体现账户操作
    public function operationReflect(){
        $arr = $this->request->post();

        if($arr['type'] =='add'){
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$arr['true_name'])){
                return $this->error(__('格式不正确'));
            }
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$arr['bank_name'])){
                return $this->error(__('格式不正确'));
            }
            //验证规则
            $rule = [
                'true_name' => 'require',
                'bank_name' => 'require',
                'bank_card' => 'require|regex:/^\d+$/'
            ];
            //验证信息
            $msg = [
                'true_name.require' => __('真实姓名不能为空'),
                'bank_name.require' => __('银行卡名称不能为空'),
                'bank_card.require' => __('银行卡号不能为空')
            ];
            //验证数组
            $data = [
                'true_name' => $arr['true_name'],
                'bank_name' => $arr['bank_name'],
                'bank_card' => $arr['bank_card']
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                return $this->error(__($validate->getError()));
            }
            $arr['runuser_id'] = $this->rid;
            unset($arr['type']);
            //检测是否已经添加了5个体现账号
            if($this->checkReflectNum($this->rid)) {
                return $this->error(__('ReflectNum'));
            }
            $res = Db::name('set_reflect_ptd')->insert($arr);
        }else{
            $where['runuser_id'] = $this->rid;
            $where['id'] = $arr['reflect_id'];
            $res = Db::name('set_reflect_ptd')->where($where)->delete();
        }
        if($res === false) {
            return $this->error(__('option_error'));
        }else {
            return $this->success(__('option_success'));
        }
    }
    //检测跑男是否已经添加了5个体现账号
    public function checkReflectNum(){
        $where['runuser_id'] = $this->rid;
        $count = Db::name('set_reflect_ptd')->where($where)->count();
        if($count<5){
            return false;
        }else{
            return true;
        }
    }
    //修改登录密码
    //找回密码
    public function update_password(){
        if($this->request->isAjax()) {
            $data = $this->request->post();
            //验证规则
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
            //检测手机号是不是需要验证的手机号
            $isP =  Db::name('runmen')->where('mobile',$data['mobile'])->where('id',$this->rid)->find();
            if(empty($isP)){
                $this->error('该手机号不是已经绑定的手机号');
            }
            //校验验证码
            $sms_check = Sms::check($data['mobile'], $data['code'], 'register', false);
            if (!$sms_check) {
                return $this->error(__('验证码不正确'));
            }
            $arr['password'] = md5(md5($data['password']));
            $where['mobile'] = $data['mobile'];
            $res = Db::name('runmen')->where($where)->update($arr);
            if ($res === false) {
                return $this->error('操作失败');
            } else {
                session(null);
                return $this->success('操作成功');
            }
        }
        return $this->fetch();
    }
    //申请解约
    public function dissolution(){
        if($this->request->isAjax()) {
            $password = $this->request->post('password');
            //验证规则
            $rule = [
                'password' => 'require|regex:/^[\S]{6,16}$/'
            ];
            //验证信息
            $msg = [
                'password.require' => __('password_empty')
            ];
            //验证数组
            $data = [
                'password' => $password
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                return $this->error(__($validate->getError()));
            }
            $now_pass = $this->getRunMen('password');
            if (md5(md5($password)) != $now_pass) {
                $this->error('密码输入错误');
            }
            unset($data['password']);
            $data['baodan_img'] = '';
            $data['id_card_con'] = '';
            $data['id_card_pos'] = '';
            $data['photo'] = '';
            $data['photo_hand'] = '';
            $data['id_number'] = '';
            $data['adress'] = '';
            $data['truename'] = '';
            $data['status'] = 2;
            $res = Db::name('runmen')->where('id',$this->rid)->update($data);
            if ($res === false) {
                $this->error('申请失败');
            } else {
                session('rid', '');
                $this->success('申请成功');
            }
        }
        return $this->fetch();
    }
    //设置接单类型跟时间段
    public function setReceipt(){
        if($this->request->isAjax()){
            $arr = $this->request->post();
            //验证规则
            $rule = [
                'receipt' => 'require|regex:/^\d+$/'
            ];
            //验证信息
            $msg = [
                'receipt.require' => __('password_empty')
            ];
            //验证数组
            $data = [
                'receipt' => $arr['receipt']
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                return $this->error(__($validate->getError()));
            }
            if($arr['start']&&$arr['end']){
                $start = explode(':',$arr['start']);
                $end = explode(':',$arr['end']);
                if($start[0]>$end[0]){
                    $this->error('结束时间不能小于开始时间');
                }
                $arr['receipt_time'] = $arr['start'].'-'.$arr['end'];
                unset($arr['start']);
                unset($arr['end']);
            }else{
                unset($arr['start']);
                unset($arr['end']);
                $arr['receipt_time'] = '';
            }

            $res = Db::name('runmen')->where('id',$this->rid)->update($arr);
            if($res === false){
                $this->error('设置失败');
            }else{
                $this->success('设置成功');
            }
        }
        $receipt = $this->getRunMen('receipt');
        if($receipt ==2){
            $this->assign('receipt',0);
        }else{
            $this->assign('receipt',1);
        }
        $receipt_time = $this->getRunMen('receipt_time');
        if($receipt_time){
            $re = explode('-',$receipt_time);
            $this->assign('start',$re[0]);
            $this->assign('end',$re[1]);
        }else{
            $this->assign('start','');
            $this->assign('end','');
        }
        return $this->fetch('receipt-set');
    }
    //获取银行
    public function getBank(){
        $arrs = array();
        $data = Db::name('bank')->select();
        for($i=0;$i<count($data);$i++){
            $arr['value'] = $data[$i]['id'];
            $arr['text'] = $data[$i]['bank_name'];
            $arrs[$i] = $arr;
        }
        $this->success('查询成功','',$arrs);
    }
    //设置导航方式
    public function setMapType(){
        if($this->request->isAjax()){
            $data = $this->request->post();
            //验证规则
            $rule = [
                'map_type' => 'require|regex:/^\d+$/',
                'travel_mode'=>'require|regex:/^\d+$/'
            ];
            //验证信息
            $msg = [
                'map_type.require' => __('导航类型不能为空'),
                'travel_mode.require'=>__('出行方式不能为空')
            ];
            //验证数组
            $data = [
                'map_type' => $data['map_type'],
                'travel_mode'=>$data['travel_mode']
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                return $this->error(__($validate->getError()));
            }
            $res = Db::name('runmen_map')->where('rid',$this->rid)->update($data);
            if($res === false){
                $this->error('设置失败');
            }else{
                $this->success('设置成功');
            }
        }
        $maptype = Db::name('runmen_map')->where('rid',$this->rid)->find();
        $this->assign('map',$maptype['travel_mode']);
        return $this->fetch('map-set');
    }
    //关于我们
    public function aboutWe(){
        return $this->fetch('pro');
    }
}