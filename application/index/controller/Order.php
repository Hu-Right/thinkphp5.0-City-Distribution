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
 * 订单中心
 */
class Order extends Frontend
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
     * 订单中心
     */
    public function index()
    {
        $user = $this->auth->getUser();
        $orders = DB::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->where(['uid'=>$user['id']])
            ->field('o.*,s.service_name')
            ->page(1,10)
            ->order('create_time desc')
            ->select();

        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
            /*if($order['status'] == 9){
                $over_time = $order['create_time'] + 15 * 60;
                if(time() > $over_time){
                    Db::name('order')->where(['id'=>$order['id']])->setField('status',3);
                    $order['status'] = 3;
                }
            }*/
        }

        /*echo '<pre>';
        print_r($orders);
        exit;*/
        
        $this->view->assign('orders', $orders);
        $this->view->assign('title', __('订单中心'));
        return $this->view->fetch();
    }

    /**
     * 全部订单
     */
    public function all()
    {
        $user = $this->auth->getUser();
        $orders = DB::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->where(['uid'=>$user['id']])
            ->field('o.*,s.service_name')
            ->page(1,10)
            ->order('create_time desc')
            ->select();

        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
            /*if($order['status'] == 9){
                $over_time = $order['create_time'] + 15 * 60;
                if(time() > $over_time){
                    Db::name('order')->where(['id'=>$order['id']])->setField('status',3);
                    $order['status'] = 3;
                }
            }*/
        }

        /*echo '<pre>';
        print_r($orders);
        exit;*/

        $this->view->assign('orders', $orders);
        $this->view->assign('title', __('订单中心'));
        return $this->view->fetch();
    }

    /**
     * 待支付
     */
    public function pay()
    {
        $user = $this->auth->getUser();
        $orders = DB::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->join('user u','o.uid = u.id','LEFT')
            ->where(['uid'=>$user['id'],'o.status'=>9])
            ->field('o.*,s.service_name,u.mobile')
            ->page(1,10)
            ->order('create_time desc')
            ->select();
        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
            /*if($order['status'] == 9){
                $over_time = $order['create_time'] + 15 * 60;
                if(time() > $over_time){
                    Db::name('order')->where(['id'=>$order['id']])->setField('status',3);
                    $order['status'] = 3;
                }
            }*/
        }
        $this->view->assign('orders', $orders);
        $this->view->assign('title', __('订单中心'));
        return $this->view->fetch();
    }

    /**
     * 待接单
     */
    public function wait()
    {
        $user = $this->auth->getUser();
        $orders = DB::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->join('user u','o.uid = u.id','LEFT')
            ->where(['uid'=>$user['id'],'o.status'=>0])
            ->field('o.*,s.service_name,u.mobile')
            ->page(1,10)
            ->order('create_time desc')
            ->select();
        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
        }
        $this->view->assign('orders', $orders);
        $this->view->assign('title', __('订单中心'));
        return $this->view->fetch();
    }

    /**
     * 待就位
     */
    public function take()
    {
        $user = $this->auth->getUser();
        $orders = DB::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->join('user u','o.uid = u.id','LEFT')
            ->where(['uid'=>$user['id'],'o.status'=>1,'order_code'=>null])
            ->field('o.*,s.service_name,u.mobile')
            ->page(1,10)
            ->order('create_time desc')
            ->select();
        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
        }
        $this->view->assign('orders', $orders);
        $this->view->assign('title', __('订单中心'));
        return $this->view->fetch();
    }

    /**
     * 已接单
     */
    public function firm()
    {
        $user = $this->auth->getUser();
        $orders = DB::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->join('user u','o.uid = u.id','LEFT')
            ->where(['uid'=>$user['id'],'o.status'=>1,'order_code'=>['<>','']])
            ->field('o.*,s.service_name,u.mobile')
            ->page(1,10)
            ->order('create_time desc')
            ->select();
        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
        }

        $this->view->assign('orders', $orders);
        $this->view->assign('title', __('订单中心'));
        return $this->view->fetch();
    }

    /**
     * 待评价
     */
    public function grade()
    {
        $user = $this->auth->getUser();
        $orders = DB::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->join('user u','o.uid = u.id','LEFT')
            ->where(['uid'=>$user['id'],'o.status'=>5])
            ->field('o.*,s.service_name,u.mobile')
            ->page(1,10)
            ->order('create_time desc')
            ->select();
        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
        }
        $this->view->assign('orders', $orders);
        $this->view->assign('title', __('订单中心'));
        return $this->view->fetch();
    }

    /**
     * 订单完成
     */
    public function done()
    {
        $user = $this->auth->getUser();
        $orders = DB::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->join('user u','o.uid = u.id','LEFT')
            ->where(['uid'=>$user['id'],'o.status'=>6])
            ->field('o.*,s.service_name,u.mobile')
            ->page(1,10)
            ->order('create_time desc')
            ->select();
        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
        }
        $this->view->assign('orders', $orders);
        $this->view->assign('title', __('订单中心'));
        return $this->view->fetch();
    }

    /**
     * 已取消
     */
    public function canc()
    {
        $user = $this->auth->getUser();
        $orders = DB::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->join('user u','o.uid = u.id','LEFT')
            ->where(['uid'=>$user['id'],'o.status'=>3])
            ->field('o.*,s.service_name,u.mobile')
            ->page(1,10)
            ->order('create_time desc')
            ->select();
        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
        }
        $this->view->assign('orders', $orders);
        $this->view->assign('title', __('订单中心'));
        return $this->view->fetch();
    }

    /**
     * 删除订单
     */
    public function delete()
    {
        $id = $this->request->post("id");
        $res = Db::name('order')->where(['id'=>$id])->delete();
        return $res;
    }

    /**
     * 取消订单
     */
    public function cancel()
    {
        $user = $this->auth->getUser();
        $id = $this->request->post("id");
        $order = Db::name('order')->where(['id'=>$id])->find();
        /*if($order['status'] != 0){
            $res = [
                'code'  =>  0,
                'msg'   =>  '取消成功',
                'data'  =>  '',
                'url'   =>  'index/order/index',
                'wait'  =>  3,
            ];
            return $res;
        }*/
        $res = Db::name('order')->where(['id'=>$id])->update(['status'=>3]);
        if($order['status'] == 0 || $order['status'] == 9/*&& $res*/){//0-带接单；9-待支付
            $data = [
                'user_id'    =>  $user['id'],
                'order_num'  =>  $order['order_num'],
                'money'      =>  $order['money'],
                'order_type' =>  $order['type'],
                'create_time'=>  time(),
            ];
            $res = Db::name('bill')->insert($data);
            $res = Db::name('user')->where(['id'=>$user['id']])->setInc('balance',$order['money']);
        }
        return $res;
    }

    /**
     * 支付订单（目前只余额支付）
     */
    public function payment()
    {
        //1 uesr扣钱 2 order 改状态 3 余额支付加流水,删除充值流水
        $user = $this->auth->getUser();
        $id = $this->request->post("id");
        $order = Db::name('order')->where(['id'=>$id])->find();
        $money = $order['money'];


        if($money>$user['balance']){
            return $this->error('余额不足','/index/recharge/index');
        }
        $orderInfo = Db::name('order')->where(['id'=>$id])->find();//订单数据

        $userRes = Db::name('user')->where(['id'=>$user['id']])->setDec('balance',$money);//扣除用户余额
        $orderRes = Db::name('order')->where(['id'=>$id])->setField('status',0);//支付成功更改订单状态为待接单

        $billData = [
            'user_id'    =>  $user['id'],
            'order_num'  =>  $orderInfo['order_num'],
            'money'      =>  0-$money,
            'order_type' =>  $orderInfo['type'],
            'province'   =>  $user['province'],
            'city'       =>  $user['city'],
            'county'     =>  $user['county'],
            'create_time' =>  time(),
        ];
        $billRes = Db::name('bill')->insert($billData);//新增用户账单

        if($userRes&&$orderRes&&$billRes){
            return $this->success('支付成功');
        }else{
            return $this->success('支付失败');
        }
    }

    /**
     * 评价订单
     */
    public function evaluate(){

        if ($this->request->isPost()) {
            $id = $this->request->post("id");
            $score = $this->request->post("score");
            $content = $this->request->post("content");
            $orderInfo = Db::name('order')->where(['id'=>$id])->find();
            $data = [
                'order_id'      =>  $orderInfo['id'],
                'user_id'       =>  $orderInfo['uid'],
                'content'       =>  $content,
                'create_time'   =>  time(),
                'rid'           =>  $orderInfo['rid'],
                'start'         =>  $score,
            ];
            $res = Db::name('evaluate')->insert($data);
            $orderRes = Db::name('order')->where(['id'=>$id])->setField('status',6);
            if($res&&$orderRes){
                $this->success('评价成功','/index/order/index');
            }else{
                $this->error('评价失败');
            }
        }else{
            $id = $this->request->get("id");
            $rid = Db::name('order')->where(['id'=>$id])->value('rid');
            $runmenInfo = Db::name('runmen')->where(['id'=>$rid])->find();
            $this->view->assign('id', $id);
            $this->view->assign('runmenInfo', $runmenInfo);
            $this->view->assign('title', __('评价订单'));
            return $this->view->fetch();
        }
    }



/*    /**
     * 检测待支付是否过期
     *!/
    public function checkPay(){
        $user = $this->auth->getUser();
        $orders = DB::name('order')->where(['uid'=>$user['id']])->select();
        foreach($orders as $key => $order){
            $orders[$key]['content'] = json_decode($order['content'],true);
            if($order['status'] == 9){
                $over_time = $order['create_time'] + 15 * 60;
                if(time() > $over_time){
                    Db::name('order')->where(['id'=>$order['id']])->setField('status',3);
                    $order['status'] = 3;
                }
            }
        }
    }*/

    /**
     * 下拉刷新
     */
    public function pullRefresh()
    {
        if ($this->request->isPost()) {
            $page = $this->request->post("page");
            $type = $this->request->post("type");
            $user = $this->auth->getUser();

            if ($type == '') {
                $where = ['uid' => $user['id']];
            } else {
                $where = ['uid' => $user['id'], 'o.status' => $type];
            }
            $orderData = DB::name('order')
                ->alias('o')
                ->join('service_type s', 'o.type = s.id', 'LEFT')
                ->where($where)
                ->field('o.*,s.service_name')
                ->page($page, 10)
                //->page($page,1)//测试
                ->order('create_time desc')
                ->select();
            foreach ($orderData as $key => $row) {
                $orderData[$key]['create_date'] = date("Y-m-d H:i:s", $row['create_time']);
                $orderData[$key]['content'] = json_decode($orderData[$key]['content'], true);
            }
            return $orderData;
        }
    }

    /**
     * 订单列表ajax刷新
     */
    public function ajaxPushList()
    {
        if ($this->request->isPost()) {
            $user = $this->auth->getUser();
            $statusView = $this->request->post("status/a");//当前页面中订单状态值--索引数组
            $idView = $this->request->post("id/a");//当前页面中订单id--索引数组
            $view = array_combine($idView,$statusView);//订单id与订单状态对应组成关联数组
            $Data = Db::name('order')->where(['uid'=>$user['id']])->column('id,status');//当前数据库中订单id与状态--关联数组

            //循环比较状态值是否相同，不同的值记录在数组中
            $arrayRet = [];
            foreach($view as $k => $v){
                if($v != $Data[$k]){// 状态不等记录订单ID和状态码
                    $arrayRet[]=$k;
                }
            }

            //数组为空则不刷新，反之刷新
            if(count($arrayRet) != 0){
                $res = [
                    'code'      =>  1,
                    'msg'       =>  '刷新',
                    'arrayRet'  =>  $arrayRet,
                ];
            }else{
                $res = [
                    'code'      =>  0,
                    'msg'       =>  '不变',
                    'arrayRet'  =>  $arrayRet,
                ];
            }
            return $res;
        }
    }

    /**
     * 订单详情ajax刷新
     */
    public function ajaxPushDetail()
    {
        $id = $this->request->post("id");//当前页面中订单id
        $statusView = $this->request->post("status");//当前页面中订单状态值
        $codeView = $this->request->post("code");//当前页面中订单验证码
        //当前页面中订单验证码如果为‘等待就位’，将状态码置null
        if($codeView == '等待就位'){
            $codeView = null;
        }

        $data = Db::name('order')->where(['id'=>$id])->find();//当前数据库中订单数据

        $statusData = $data['status'];

        if($statusView != $statusData){//状态不同，刷新
            $res = [
                'code'      =>  1,
                'msg'       =>  '刷新'
            ];
        }else{//状态相同

            if($statusData != 1){//状态是否为1，不是1 不变
                $res = [
                    'code'      =>  0,
                    'msg'       =>  '不变'
                ];
            }else{//状态为1
                if($codeView != $data['order_code']){//验证码不同，刷新
                    $res = [
                        'code'      =>  1,
                        'msg'       =>  '刷新'
                    ];
                }else{//验证码相同，不变
                    $res = [
                        'code'      =>  0,
                        'msg'       =>  '不变'
                    ];
                }
            }

            /*if($codeView != $data['order_code']){//验证码不同，刷新
                $res = [
                    'code'      =>  1,
                    'msg'       =>  '刷新1'
                ];
            }else{//验证码相同，不变
                $res = [
                    'code'      =>  0,
                    'msg'       =>  '不变'
                ];
            }*/
        }
        return $res;
    }

}
