<?php
// +----------------------------------------------------------------------
// | vv跑腿配送端
// +----------------------------------------------------------------------
// | 2018-10-31
// +----------------------------------------------------------------------
// | Author: Mc小张
// +----------------------------------------------------------------------
namespace app\common\controller;
use think\Controller;
use think\Lang;
use think\Db;
use think\Session;
header("Access-Control-Allow-Origin:*");
class Vvptd extends Controller{
    public $rid='';
    public function _initialize(){
        $controllername = strtolower($this->request->controller());
        $this->loadlang($controllername);
        if($controllername != 'login'){
            //$this->rid = 7;
            $this->isLogin();
        }
    }
    //检查登录状态
    public function isLogin(){

//        $token_f = session('token');
//        $token =  $this->request->header('token');
        if(!session('rid')){
            return $this->error('暂未登陆');
        }else{
            $this->rid = session('rid');
//            if($token) {
//                if ($token != $token_f) {
//                    return $this->error('暂未登陆');
//                } else {
//                    $this->rid = session('rid');
//                }
//            }else{
//                $this->error('暂未登录','/vvptd/login/login');
//            }

        }
    }
    //加载语言包
    protected function loadlang($name)
    {
        Lang::load(APP_PATH . $this->request->module() . '/lang/' . $this->request->langset() . '/' . str_replace('.', '/', $name) . '.php');
    }
    //验证码检验
    public function checkCode($mobile,$captcha,$action){

        //校验验证码
        $sms_check = Sms::check($mobile, $captcha,$action,false);
        if(!$sms_check){
            return false;
        }
    }
    //检测手机号是否已经注册会员
    public function checkMobile($mobile){
        return Db::name('runmen')->where('mobile',$mobile)->find();
    }
    //检测跑男资料的状态，处于未审核状态时不能进行任何接单操作
    public function checkMemberStatus(){
        $data = Db::name('runmen')->where('id',$this->rid)->find();
        if($data){
            if($data['status'] == 2){
                return true;
            }elseif($data['status'] == 1){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }
    //获取城市信息
    public function getCity(){
        $data = Db::name('area')->where('level',2)->select();
        return $data;
    }
    //查询跑男信息
    public function getRunMen($field =null){
        $data = Db::name('runmen')->where('id',$this->rid)->find();
        if($field!=null){
            return $data[$field];
        }
        return $data;
    }
    //生成跑男默认导航地图配置
    public function setRunMenMap($rid){
        $mapconfig = Db::name('runmen_map')->where('rid',$rid)->find();
        if(empty($mapconfig)){
            $data = array('rid'=>$rid,'add_time'=>time());
            $res = Db::name('runmen_map')->insert($data);
        }
    }
    //更新跑男的任务状态
    public function checkRunTaskStatus(){
        //查询跑男是否有未完成的任务
        $wData = Db::name('runmen_task')->where('rid',$this->rid)->where('status',1)->select();
        if(!empty($wData)){
            for($i=0;$i<count($wData);$i++){
                //查询任务类型
                $task = Db::name('task')->where('id',$wData[$i]['task_id'])->find();

                if(!empty($task)){
                    if($task['type'] ==1){
                        //按订单量发布任务
                        $count =Db::name('order')->where('FROM_UNIXTIME(finish_time,"%Y-%m") = "'.date('Y-m').'"')->count();
                        if($count>=$task['value']){
                            //任务达到标准修改任务状态
                            Db::name('runmen_task')->where('rid',$this->rid)->where('task_id',$wData[$i]['task_id'])->update(array('status'=>2,'success_time'=>time()));
                        }
                    }else{
                        //按公里数发布任务
                        $dis =Db::name('order')->field('sum(start_end_distance) as tody_distance')->where('FROM_UNIXTIME(finish_time,"%Y-%m") = "'.date('Y-m').'"')->select();
                        if($dis['tody_distance']>=$task['value']){
                            //任务达到标准修改任务状态
                            Db::name('runmen_task')->where('rid',$this->rid)->where('task_id',$wData[$i]['task_id'])->update(array('status'=>2,'success_time'=>time()));
                        }
                    }
                }
            }
        }
    }
    //新增跑男余额变更记录
    //$data 数据
    public function insertRunMenRecode($data){
        Db::name('runmen_account')->insert($data);
        //return Db::name('runmen_account')->getLastSql();
    }
    //通知消息
    public function sendNotice($msg,$rid){
        $data['detail'] = $msg;
        $data['rid'] = $rid;
        $data['time'] = time();
        Db::name('runmen_notice')->insert($data);
    }
    //累计排行榜数据
    public function leijiRankingList($money,$li,$ordernnum=1){
        Db::name('team')
            ->where('1=1')
            ->update([
                'day_money'  => Db::raw('day_money+'.$money),
                'day_licheng_num' => Db::raw('day_licheng_num+'.$li),
                'day_order_num' => Db::raw('day_order_num+'.$ordernnum),
                'week_money' => Db::raw('week_money+'.$money),
                'week_order_num' => Db::raw('week_order_num+'.$ordernnum),
                'week_licheng_num' => Db::raw('week_licheng_num+'.$li),
                'month_money' => Db::raw('month_money+'.$money),
                'month_order_num' => Db::raw('month_order_num+'.$ordernnum),
                'month_licheng_num' => Db::raw('month_licheng_num+'.$li),
            ]);
        Db::name('runmen')
            ->where('1=1')
            ->update([
                'day_money'  => Db::raw('day_money+'.$money),
                'day_licheng_num' => Db::raw('day_licheng_num+'.$li),
                'day_order_num' => Db::raw('day_order_num+'.$ordernnum),
                'week_money' => Db::raw('week_money+'.$money),
                'week_order_num' => Db::raw('week_order_num+'.$ordernnum),
                'week_licheng_num' => Db::raw('week_licheng_num+'.$li),
                'month_money' => Db::raw('month_money+'.$money),
                'month_order_num' => Db::raw('month_order_num+'.$ordernnum),
                'month_licheng_num' => Db::raw('month_licheng_num+'.$li),
            ]);
    }
    //接单处理
    public function jiedan($order_id,$rid,$status = 0){
        $res = Db::name('order')->where('id',$order_id)->update(array('rid'=>$rid,'status'=>$status,'accept_time'=>time()));
        return $res;
    }
    //订单消息
    public function orderNotice($order_id,$rid,$content){
        $data['order_id'] = $order_id;
        $data['rid'] = $rid;
        $data['content'] = $content;
        $data['add_time'] = time();
        $order = Db::name('order')->where('id',$order_id)->find();
        if($order){
            $data['user_id'] = $order['uid'];
        }
        Db::name('order_notice')->insert($data);
    }
}