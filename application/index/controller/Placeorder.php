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
 * 下单流程
 */
class Placeorder extends Frontend
{

    protected $layout = 'default';
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    private $weather='';//特殊天气额外增加费用（元）
    private $two_km='';//2公里（元）--起步价
    private $three_km='';//3公里（元）
    private $three_ten_km='';//3-10公里（元/公里）
    private $ten_thirty_km='';//10-30公里（元/公里）
    private $over_thirty_km='';//超过30公里（元/公里）
    private $lower_twentyfive_kg='';//25公斤以内 -- 25kg以内不加价
    private $twentysix_thirty_kg='';//26-30公斤加（元）
    private $thirtyone_forty_kg='';//31-40公斤加（元）
    private $over_forty_kg='';//超过40公斤每公斤加（元）
    private $lineup_start_price='';//排队起步价（含30分钟）（元）
    private $lineup_one_ten_price='';//1-10小时（元/30分钟）
    private $lineup_delayed_price='';//延长排队（元/30分钟）
//    private $universal_help='';//万能帮帮（元）
//    private $pets_help='';//照顾宠物（元）
//    private $hourly_help='';//小时工（元）
//    private $carry_help='';//搬运货物（元）
//    private $leaflet_help='';//传单派发（元）
    private $front_night='';//22:00-00:00（每单加）（元）
    private $back_night='';//00:00-06:00（每单加）（元）
    private $subscription_price='';//预约服务费（每单加）（元）
    private $is_weather='';//是否为特殊天气
    public function _initialize(){

        parent::_initialize();
        $auth = $this->auth;

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

        $ucenter = get_addon_info('ucenter');
        if ($ucenter && $ucenter['state']) {
            include ADDON_PATH . 'ucenter' . DS . 'uc.php';
        }

        $this->weather=Db::name('config')->where(['group'=>'vvpt','name'=>'weather'])->value('value');
        $this->two_km=Db::name('config')->where(['group'=>'vvpt','name'=>'two_km'])->value('value');
        $this->three_km=Db::name('config')->where(['group'=>'vvpt','name'=>'three_km'])->value('value');
        $this->three_ten_km=Db::name('config')->where(['group'=>'vvpt','name'=>'three_ten_km'])->value('value');
        $this->ten_thirty_km=Db::name('config')->where(['group'=>'vvpt','name'=>'ten_thirty_km'])->value('value');
        $this->over_thirty_km=Db::name('config')->where(['group'=>'vvpt','name'=>'over_thirty_km'])->value('value');
        $this->lower_twentyfive_kg=Db::name('config')->where(['group'=>'vvpt','name'=>'lower_twentyfive_kg'])->value('value');
        $this->twentysix_thirty_kg=Db::name('config')->where(['group'=>'vvpt','name'=>'twentysix_thirty_kg'])->value('value');
        $this->thirtyone_forty_kg=Db::name('config')->where(['group'=>'vvpt','name'=>'thirtyone_forty_kg'])->value('value');
        $this->over_forty_kg=Db::name('config')->where(['group'=>'vvpt','name'=>'over_forty_kg'])->value('value');
        $this->lineup_start_price=Db::name('config')->where(['group'=>'vvpt','name'=>'lineup_start_price'])->value('value');
        $this->lineup_one_ten_price=Db::name('config')->where(['group'=>'vvpt','name'=>'lineup_one_ten_price'])->value('value');
        $this->lineup_delayed_price=Db::name('config')->where(['group'=>'vvpt','name'=>'lineup_delayed_price'])->value('value');
//        $this->universal_help=Db::name('config')->where(['group'=>'vvpt','name'=>'universal_help'])->value('value');
//        $this->pets_help=Db::name('config')->where(['group'=>'vvpt','name'=>'pets_help'])->value('value');
//        $this->hourly_help=Db::name('config')->where(['group'=>'vvpt','name'=>'hourly_help'])->value('value');
//        $this->carry_help=Db::name('config')->where(['group'=>'vvpt','name'=>'carry_help'])->value('value');
//        $this->leaflet_help=Db::name('config')->where(['group'=>'vvpt','name'=>'leaflet_help'])->value('value');
        $this->front_night=Db::name('config')->where(['group'=>'vvpt','name'=>'front_night'])->value('value');
        $this->back_night=Db::name('config')->where(['group'=>'vvpt','name'=>'back_night'])->value('value');
        $this->subscription_price=Db::name('config')->where(['group'=>'vvpt','name'=>'subscription_price'])->value('value');
        $this->is_weather=Db::name('config')->where(['group'=>'vvpt','name'=>'is_weather'])->value('value');
    }

/*------------------------------帮我买-------------------------------------*/
    //第一步
    public function helpbuystep1(){
        $id = $this->request->get("id");
        //子分类
        $soninfo = Db::name('service_type')->where(['pid'=>$id])->select();
        $this->view->assign('soninfo', $soninfo);
        $this->view->assign('title', __('帮我买·下单'));
        return $this->view->fetch();
    }

    //第二步
    public function helpbuystep2(){
        $user =$this->auth->getUser();
        //支付顺序
        $paymentsort = explode(",",$user['paymentsort']);
        $this->view->assign('paymentsort', $paymentsort);
        //计算价格参数
        //天气加价
        $this->view->assign('is_weather', $this->is_weather);
        $this->view->assign('weather', $this->weather);
        //距离价钱
        $this->view->assign('two_km', $this->two_km);
        $this->view->assign('three_km', $this->three_km);
        $this->view->assign('three_ten_km', $this->three_ten_km);
        $this->view->assign('ten_thirty_km', $this->ten_thirty_km);
        $this->view->assign('over_thirty_km', $this->over_thirty_km);
        //预约加价
        $this->view->assign('subscription_price', $this->subscription_price);
        //时段加价
        $this->view->assign('front_night', $this->front_night);
        $this->view->assign('back_night', $this->back_night);
        $this->view->assign('hour', date("H"));

        $this->view->assign('title', __('帮我买·下单'));
        return $this->view->fetch();
    }

    //帮我买下单
    public function helpbuy(){
        $user = $this->auth->getUser();
        $details = $this->request->post("details");
        $prepayment = $this->request->post("prepayment");
        $service_date = $this->request->post("service_date");
        $start_address = $this->request->post("start_address");
        $start_lon = $this->request->post("start_lon");
        $start_lat = $this->request->post("start_lat");
        $end_address = $this->request->post("end_address");
        $end_lon = $this->request->post("end_lon");
        $end_lat = $this->request->post("end_lat");
        $linkname = $this->request->post("linkname");
        $mobile = $this->request->post("mobile");
        $mileage = $this->request->post("mileage");
        $user_min = $this->request->post("user_min")+10;
        $payment = $this->request->post("payment");
        $fee = $this->request->post("fee");
        $hot_box = $this->request->post("hot_box");//保温箱配送
        $orderTotal = $this->request->post("total");//前台传过来的订单金额
        $bill_id = $this->request->post("bill_id");//非余额支付，会先充值，充值记录 ID
        $remake = $this->request->post("remake");//备注

        //描述不能为空
        if($details==''){
            return $this->error('请填写您的需求');
        }
        //结束地址不能为空
        if($end_address==''|| $end_lon=='' || $end_lat==''){
            return $this->error('请选择收货地址');
        }
        //手机号为空时存下单人手机
        if($mobile==''){
            $mobile = $user['mobile'];
        }
        //就近购买起始地址，经纬度为空，将结束地址存入起始地址，结束地址置空
        if(strpos($start_address,'null') !== false || strpos($start_lon,'null') !== false || strpos($start_lat,'null') !== false){
            $start_address = null;
            $start_lon = null;
            $start_lat = null;
        }
        //预付款为空或者为负时置0
        if($prepayment=='' || $prepayment<0){
            $prepayment = 0;
        }
        //小费为空或者为负时置0
        if($fee=='' || $fee<0){
            $fee = 0;
        }
        //里程价格
        if(0<ceil($mileage)&&ceil($mileage)<=2){
            $money = $this->two_km;
        }elseif(2<ceil($mileage)&&ceil($mileage)<=3){
            $money = $this->three_km;
        }elseif(3<ceil($mileage)&&ceil($mileage)<=10){
            $money = (ceil($mileage)-3)*$this->three_ten_km+$this->three_km;
        }elseif(10<ceil($mileage)&&$mileage<=30){
            $money = (ceil($mileage)-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
        }elseif(30<ceil($mileage)){
            $money = (ceil($mileage)-30)*$this->over_thirty_km+(30-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
        }

        //预约加钱
        if($service_date==0||strpos($service_date,'立即') !== false){
            $service_time = '';
        }else{
            $service_time = strtotime($service_date);
            $money += $this->subscription_price;//加上预约费
        }

        //天气加钱
        if($this->is_weather == 1){
            $money += $this->weather;//加上天气费
        }

        //时段加钱
        if($service_date==0||strpos($service_date,'立即') !== false){
            $hour = date('H');
        }else{
            $hour = date('H',$service_time);
        }
        if(22<$hour&&$hour<24){
            $money += $this->front_night;
        }elseif(0<$hour&&$hour<6){
            $money += $this->back_night;
        }

        // return $this->error('前'.$orderTotal.'后'.$money + $prepayment + $fee);

        //比对前台传过来的订单价格和此时自己算的价格是否一样
        if($orderTotal != $money + $prepayment + $fee){
            return $this->error('参数有误', '/index/user/index');
        }

        //判断支付方式
        if(strpos($payment,'余额') !== false){
            $payment = 1;
        }elseif(strpos($payment,'支付宝') !== false){
            $payment = 2;
        }elseif(strpos($payment,'微信') !== false){
            $payment = 3;
        }else{
            return $this->error('无效支付', '/index/user/index');
        }

        //帮我买
        $content = json_encode([
            'details'       =>  $details,//订单详细内容
            'service_time'  =>  $service_time,//订单执行时间
            'prepayment'    =>  $prepayment,//预付款(帮我买)
            'fee'           =>  $fee,//小费
            'hot_box'       =>  $hot_box,//保温箱配送
            'weight'        =>  '',//物品重量(帮我取/送)
            'line_time'     =>  '',//排队时长(代排队)
            'name'          =>  $linkname,//联系人
            'mobile'        =>  $mobile,//联系人手机
            'coupon'        =>  '',//优惠券表ID
            'discount'      =>  0,//优惠了多少钱
            'is_admin'      =>  0,//是否为后台下单
            'admin_id'      =>  '',//后台下单人id
        ]);

        if($start_address == null || $start_lon == null || $start_lat == null){
            //订单数据
            $data = [
                'order_num'           =>  $this->getOrderSn(),
                'province'            =>  $user['province'],
                'city'                =>  $user['city'],
                'county'              =>  $user['county'],
                'uid'                 =>  $user['id'],
                'type'                =>  1,// service_type 表中帮我买id
                'content'             =>  $content,
                'money'               =>  $money,
                'payment'             =>  $payment,//1-余额 2-支付宝 3-微信
                'status'              =>  9,//待支付
                'create_time'         =>  time(),
                'start_address'       =>  $end_address,
                'end_address'         =>  $start_address,
                'start_lon'           =>  $end_lon,
                'start_lat'           =>  $end_lat,
                'end_lon'             =>  $start_lon,
                'end_lat'             =>  $start_lat,
                'start_end_distance'  =>  $mileage,
                'user_min'            =>  $user_min,
                'remake'              =>  $remake,
            ];
        }else{
            //订单数据
            $data = [
                'order_num'           =>  $this->getOrderSn(),
                'province'            =>  $user['province'],
                'city'                =>  $user['city'],
                'county'              =>  $user['county'],
                'uid'                 =>  $user['id'],
                'type'                =>  1,// service_type 表中帮我买id
                'content'             =>  $content,
                'money'               =>  $money,
                'payment'             =>  $payment,//1-余额 2-支付宝 3-微信
                'status'              =>  9,//待支付
                'create_time'         =>  time(),
                'start_address'       =>  $start_address,
                'end_address'         =>  $end_address,
                'start_lon'           =>  $start_lon,
                'start_lat'           =>  $start_lat,
                'end_lon'             =>  $end_lon,
                'end_lat'             =>  $end_lat,
                'start_end_distance'  =>  $mileage,
                'user_min'            =>  $user_min,
                'remake'              =>  $remake,
            ];
        }


        //地址加入常用地址
        $addressData = [
            'uid'           =>  $user['id'],
            'linkman'       =>  $linkname,
            'mobile'        =>  $mobile,
            'address'       =>  $end_address,
            'address_lon'   =>  $end_lon,
            'address_lat'   =>  $end_lat,
        ];
        Db::name('address')->insert($addressData);

        $orderRes = Db::name('order')->insert($data);

        $orderId = Db::name('order')->getLastInsID();

        if($orderRes){
            $total = $money + $prepayment + $fee;
            $this->payment($total, $orderId, $payment, $bill_id);
        }else{
            return $this->error('下单失败', '/index/user/index');
        }
    }

/*------------------------------帮我取-------------------------------------*/
    //第一步
    public function helptakestep1(){
        $id = $this->request->get("id");
        //子分类
        $soninfo = Db::name('service_type')->where(['pid'=>$id])->select();
        $this->view->assign('soninfo', $soninfo);
        $this->view->assign('title', __('帮我送·下单'));
        return $this->view->fetch();
    }

    //第二步
    public function helptakestep2(){
        $user =$this->auth->getUser();
        //支付顺序
        $paymentsort = explode(",",$user['paymentsort']);
        $this->view->assign('paymentsort', $paymentsort);
        //计算价格参数
        //天气加价
        $this->view->assign('is_weather', $this->is_weather);
        $this->view->assign('weather', $this->weather);
        //距离价钱
        $this->view->assign('two_km', $this->two_km);
        $this->view->assign('three_km', $this->three_km);
        $this->view->assign('three_ten_km', $this->three_ten_km);
        $this->view->assign('ten_thirty_km', $this->ten_thirty_km);
        $this->view->assign('over_thirty_km', $this->over_thirty_km);
        //重量加钱
        $this->view->assign('lower_twentyfive_kg', $this->lower_twentyfive_kg);
        $this->view->assign('twentysix_thirty_kg', $this->twentysix_thirty_kg);
        $this->view->assign('thirtyone_forty_kg', $this->thirtyone_forty_kg);
        $this->view->assign('over_forty_kg', $this->over_forty_kg);
        //预约加价
        $this->view->assign('subscription_price', $this->subscription_price);
        //时段加价
        $this->view->assign('front_night', $this->front_night);
        $this->view->assign('back_night', $this->back_night);
        $this->view->assign('hour', date("H"));

        $this->view->assign('title', __('帮我取·下单'));
        return $this->view->fetch();
    }

    //帮我取下单
    public function helptake(){
        $user = $this->auth->getUser();
        $details = $this->request->post("details");
        $weight = $this->request->post("weight");
        $service_date = $this->request->post("service_date");
        $s_name = $this->request->post("s_name");//起始联系人
        $s_phone = $this->request->post("s_phone");//起始电话
        $start_address = $this->request->post("start_address");
        $start_lon = $this->request->post("start_lon");
        $start_lat = $this->request->post("start_lat");
        $e_name = $this->request->post("e_name");//结束联系人
        $e_phone = $this->request->post("e_phone");//结束电话
        $end_address = $this->request->post("end_address");
        $end_lon = $this->request->post("end_lon");
        $end_lat = $this->request->post("end_lat");
        $mileage = $this->request->post("mileage");
        $user_min = $this->request->post("user_min")+10;
        $payment = $this->request->post("payment");
        $fee = $this->request->post("fee");
        $baojiaMoney = $this->request->post("baojiaMoney");
        $hot_box = $this->request->post("hot_box");//保温箱配送
        $orderTotal = $this->request->post("total");//前台传过来的订单金额
        $bill_id = $this->request->post("bill_id");//非余额支付，会先充值，充值记录 ID
        $remake = $this->request->post("remake");//备注

        //描述不能为空
        if($details==''){
            return $this->error('请填写您的需求');
        }
        //起始地址、经纬度不能为空
        if($start_address==''||$start_lon==''||$start_lat==''){
            return $this->error('请选择购买地址');
        }
        //结束地址不能为空
        if($end_address==''||$end_lon==''||$end_lat==''){
            return $this->error('请选择收货地址');
        }
        //小费为空或者为负时置0
        if($fee=='' || $fee<0){
            $fee = 0;
        }
        //保价为空或者为负时置0
        if($baojiaMoney=='' || $baojiaMoney<0){
            $baojiaMoney = 0;
        }

        //里程价格
        if(0<ceil($mileage)&&ceil($mileage)<=2){
            $money = $this->two_km;
        }elseif(2<ceil($mileage)&&ceil($mileage)<=3){
            $money = $this->three_km;
        }elseif(3<ceil($mileage)&&ceil($mileage)<=10){
            $money = (ceil($mileage)-3)*$this->three_ten_km+$this->three_km;
        }elseif(10<ceil($mileage)&&ceil($mileage)<=30){
            $money = (ceil($mileage)-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
        }elseif(30<ceil($mileage)){
            $money = (ceil($mileage)-30)*$this->over_thirty_km+(30-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
        }

        //重量加钱
        if(25<$weight&&$weight<=30){
            $money += $this->twentysix_thirty_kg;
        }elseif(30<$weight&&$weight<=40){
            $money += $this->thirtyone_forty_kg;
        }elseif(40<$weight){
            $money += $this->thirtyone_forty_kg+($weight-40)*$this->over_forty_kg;
        }

        //预约加钱
        if($service_date==0||strpos($service_date,'立即') !== false){
            $service_time = '';
        }else{
            $service_time = strtotime($service_date);
            $money += $this->subscription_price;//加上预约费
        }

        //天气加钱
        if($this->is_weather == 1){
            $money += $this->weather;//加上天气费
        }

        //时段加钱
        if($service_date==0){
            $hour = date('H');
        }else{
            $hour = date('H',$service_time);
        }
        if(22<$hour&&$hour<24){
            $money += $this->front_night;
        }elseif(0<$hour&&$hour<6){
            $money += $this->back_night;
        }

        //比对前台传过来的订单价格和此时自己算的价格是否一样
        if($orderTotal != $money + $fee + $baojiaMoney){
            return $this->error('参数有误', '/index/user/index');
        }

        //判断支付方式
        if(strpos($payment,'余额') !== false){
            $payment = 1;
        }elseif(strpos($payment,'支付宝') !== false){
            $payment = 2;
        }elseif(strpos($payment,'微信') !== false){
            $payment = 3;
        }else{
            return $this->error('无效支付', '/index/user/index');
        }

        //帮我送
        $content = json_encode([
            'details'       =>  $details,//订单详细内容
            'service_time'  =>  $service_time,//订单执行时间
            'prepayment'    =>  '',//预付款(帮我买)
            'fee'           =>  $fee,//小费
            'baojiaMoney'  =>  $baojiaMoney,//保价
            'hot_box'       =>  $hot_box,//保温箱配送
            'weight'        =>  $weight,//物品重量(帮我取/送)
            'line_time'     =>  '',//排队时长(代排队)
            'name'          =>  $e_name,//收货人
            'mobile'        =>  $e_phone,//收货电话
            'coupon'        =>  '',//优惠券表ID
            'discount'      =>  0,//优惠了多少钱
            'is_admin'      =>  0,//是否为后台下单
            'admin_id'      =>  '',//后台下单人id
        ]);

        //订单数据
        $data = [
            'order_num'           =>  $this->getOrderSn(),
            'province'            =>  $user['province'],
            'city'                =>  $user['city'],
            'county'              =>  $user['county'],
            'uid'                 =>  $user['id'],
            'type'                =>  5,// service_type 表中帮我取id
            'content'             =>  $content,
            'money'               =>  $money,
            'payment'             =>  $payment,//1-余额 2-支付宝 3-微信
            'status'              =>  9,//待支付
            'create_time'         =>  time(),
            'name'                =>  $s_name,//起始人
            'mobile'              =>  $s_phone,//起始电话
            'start_address'       =>  $start_address,
            'end_address'         =>  $end_address,
            'start_lon'           =>  $start_lon,
            'start_lat'           =>  $start_lat,
            'end_lon'             =>  $end_lon,
            'end_lat'             =>  $end_lat,
            'start_end_distance'  =>  $mileage,
            'user_min'            =>  $user_min,
            'remake'              =>  $remake,
        ];

        //地址加入常用地址
        $addressData = [
            'uid'           =>  $user['id'],
            'linkman'       =>  $e_name,
            'mobile'        =>  $e_phone,
            'address'       =>  $end_address,
            'address_lon'   =>  $end_lon,
            'address_lat'   =>  $end_lat,
        ];
        Db::name('address')->insert($addressData);

        $orderRes = Db::name('order')->insert($data);

        $orderId = Db::name('order')->getLastInsID();

        if($orderRes){
            $total = $money + $fee;
            $this->payment($total, $orderId, $payment, $bill_id);
        }else{
            return $this->error('下单失败', '/index/user/index');
        }
    }

/*------------------------------帮我送-------------------------------------*/
    //第一步
    public function helpdeliverstep1(){
        $id = $this->request->get("id");
        //子分类
        $soninfo = Db::name('service_type')->where(['pid'=>$id])->select();
        $this->view->assign('soninfo', $soninfo);
        $this->view->assign('title', __('帮我送·下单'));
        return $this->view->fetch();
    }

    //第二步
    public function helpdeliverstep2(){
        $user =$this->auth->getUser();
        //支付顺序
        $paymentsort = explode(",",$user['paymentsort']);
        $this->view->assign('paymentsort', $paymentsort);
        //计算价格参数
        //天气加价
        $this->view->assign('is_weather', $this->is_weather);
        $this->view->assign('weather', $this->weather);
        //距离价钱
        $this->view->assign('two_km', $this->two_km);
        $this->view->assign('three_km', $this->three_km);
        $this->view->assign('three_ten_km', $this->three_ten_km);
        $this->view->assign('ten_thirty_km', $this->ten_thirty_km);
        $this->view->assign('over_thirty_km', $this->over_thirty_km);
        //重量加钱
        $this->view->assign('lower_twentyfive_kg', $this->lower_twentyfive_kg);
        $this->view->assign('twentysix_thirty_kg', $this->twentysix_thirty_kg);
        $this->view->assign('thirtyone_forty_kg', $this->thirtyone_forty_kg);
        $this->view->assign('over_forty_kg', $this->over_forty_kg);
        //预约加价
        $this->view->assign('subscription_price', $this->subscription_price);
        //时段加价
        $this->view->assign('front_night', $this->front_night);
        $this->view->assign('back_night', $this->back_night);
        $this->view->assign('hour', date("H"));

        $this->view->assign('title', __('帮我送·下单'));
        return $this->view->fetch();
    }

    //帮我送下单
    public function helpdeliver(){
        $user = $this->auth->getUser();
        $details = $this->request->post("details");
        $weight = $this->request->post("weight");
        $service_date = $this->request->post("service_date");
        $s_name = $this->request->post("s_name");//起始联系人
        $s_phone = $this->request->post("s_phone");//起始电话
        $start_address = $this->request->post("start_address");
        $start_lon = $this->request->post("start_lon");
        $start_lat = $this->request->post("start_lat");
        $e_name = $this->request->post("e_name");//结束联系人
        $e_phone = $this->request->post("e_phone");//结束电话
        $end_address = $this->request->post("end_address");
        $end_lon = $this->request->post("end_lon");
        $end_lat = $this->request->post("end_lat");
        $mileage = $this->request->post("mileage");
        $user_min = $this->request->post("user_min")+10;
        $payment = $this->request->post("payment");
        $fee = $this->request->post("fee");
        $baojiaMoney = $this->request->post("baojiaMoney");
        $hot_box = $this->request->post("hot_box");//保温箱配送
        $orderTotal = $this->request->post("total");//前台传过来的订单金额
        $bill_id = $this->request->post("bill_id");//非余额支付，会先充值，充值记录 ID
        $remake = $this->request->post("remake");//备注

        //描述不能为空
        if($details==''){
            return $this->error('请填写您的需求');
        }
        //起始地址、经纬度不能为空
        if($start_address==''||$start_lon==''||$start_lat==''){
            return $this->error('请选择购买地址');
        }
        //结束地址不能为空
        if($end_address==''||$end_lon==''||$end_lat==''){
            return $this->error('请选择收货地址');
        }
        //小费为空或者为负时置0
        if($fee=='' || $fee<0){
            $fee = 0;
        }
        //保价为空或者为负时置0
        if($baojiaMoney=='' || $baojiaMoney<0){
            $baojiaMoney = 0;
        }

        //里程价格
        if(0<ceil($mileage)&&ceil($mileage)<=2){
            $money = $this->two_km;
        }elseif(2<ceil($mileage)&&ceil($mileage)<=3){
            $money = $this->three_km;
        }elseif(3<ceil($mileage)&&ceil($mileage)<=10){
            $money = (ceil($mileage)-3)*$this->three_ten_km+$this->three_km;
        }elseif(10<ceil($mileage)&&ceil($mileage)<=30){
            $money = (ceil($mileage)-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
        }elseif(30<ceil($mileage)){
            $money = (ceil($mileage)-30)*$this->over_thirty_km+(30-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
        }

        //重量加钱
        if(25<$weight&&$weight<=30){
            $money += $this->twentysix_thirty_kg;
        }elseif(30<$weight&&$weight<=40){
            $money += $this->thirtyone_forty_kg;
        }elseif(40<$weight){
            $money += $this->thirtyone_forty_kg+($weight-40)*$this->over_forty_kg;
        }

        //预约加钱
        if($service_date==0||strpos($service_date,'立即') !== false){
            $service_time = '';
        }else{
            $service_time = strtotime($service_date);
            $money += $this->subscription_price;//加上预约费
        }

        //天气加钱
        if($this->is_weather == 1){
            $money += $this->weather;//加上天气费
        }

        //时段加钱
        if($service_date==0){
            $hour = date('H');
        }else{
            $hour = date('H',$service_time);
        }
        if(22<$hour&&$hour<24){
            $money += $this->front_night;
        }elseif(0<$hour&&$hour<6){
            $money += $this->back_night;
        }

        //比对前台传过来的订单价格和此时自己算的价格是否一样
        if($orderTotal != $money + $fee + $baojiaMoney){
            return $this->error('参数有误', '/index/user/index');
        }

        //判断支付方式
        if(strpos($payment,'余额') !== false){
            $payment = 1;
        }elseif(strpos($payment,'支付宝') !== false){
            $payment = 2;
        }elseif(strpos($payment,'微信') !== false){
            $payment = 3;
        }else{
            return $this->error('无效支付', '/index/user/index');
        }

        //帮我送
        $content = json_encode([
            'details'       =>  $details,//订单详细内容
            'service_time'  =>  $service_time,//订单执行时间
            'prepayment'    =>  '',//预付款(帮我买)
            'fee'           =>  $fee,//小费
            'baojiaMoney'  =>  $baojiaMoney,//保价
            'hot_box'       =>  $hot_box,//保温箱配送
            'weight'        =>  $weight,//物品重量(帮我取/送)
            'line_time'     =>  '',//排队时长(代排队)
            'name'          =>  $e_name,//收货人
            'mobile'        =>  $e_phone,//收货电话
            'coupon'        =>  '',//优惠券表ID
            'discount'      =>  0,//优惠了多少钱
            'is_admin'      =>  0,//是否为后台下单
            'admin_id'      =>  '',//后台下单人id
        ]);

        //订单数据
        $data = [
            'order_num'           =>  $this->getOrderSn(),
            'province'            =>  $user['province'],
            'city'                =>  $user['city'],
            'county'              =>  $user['county'],
            'uid'                 =>  $user['id'],
            'type'                =>  2,// service_type 表中帮我送id
            'content'             =>  $content,
            'money'               =>  $money,
            'payment'             =>  $payment,//1-余额 2-支付宝 3-微信
            'status'              =>  9,//待支付
            'create_time'         =>  time(),
            'name'                =>  $s_name,//起始人
            'mobile'              =>  $s_phone,//起始电话
            'start_address'       =>  $start_address,
            'end_address'         =>  $end_address,
            'start_lon'           =>  $start_lon,
            'start_lat'           =>  $start_lat,
            'end_lon'             =>  $end_lon,
            'end_lat'             =>  $end_lat,
            'start_end_distance'  =>  $mileage,
            'user_min'            =>  $user_min,
            'remake'              =>  $remake,
        ];

        //地址加入常用地址
        $addressData = [
            'uid'           =>  $user['id'],
            'linkman'       =>  $e_name,
            'mobile'        =>  $e_phone,
            'address'       =>  $end_address,
            'address_lon'   =>  $end_lon,
            'address_lat'   =>  $end_lat,
        ];
        Db::name('address')->insert($addressData);

        $orderRes = Db::name('order')->insert($data);

        $orderId = Db::name('order')->getLastInsID();

        if($orderRes){
            $total = $money + $fee;
            $this->payment($total, $orderId, $payment, $bill_id);
        }else{
            return $this->error('下单失败', '/index/user/index');
        }
    }

/*------------------------------帮我办-------------------------------------*/
    //第一步
    public function helpdostep1(){
        $id = $this->request->get("id");
        //子分类
        $soninfo = Db::name('service_type')->where(['pid'=>$id])->select();
        $this->view->assign('soninfo', $soninfo);
        $this->view->assign('title', __('帮我办·下单'));
        return $this->view->fetch();
    }

    //第二步
    public function helpdostep2(){
        $user =$this->auth->getUser();
        //支付顺序
        $paymentsort = explode(",",$user['paymentsort']);
        $this->view->assign('paymentsort', $paymentsort);
        //计算价格参数
        //天气加价
        $this->view->assign('is_weather', $this->is_weather);
        $this->view->assign('weather', $this->weather);
        //预约加价
        $this->view->assign('subscription_price', $this->subscription_price);
        //时段加价
        $this->view->assign('front_night', $this->front_night);
        $this->view->assign('back_night', $this->back_night);
        $this->view->assign('hour', date("H"));

        $this->view->assign('title', __('帮我办·下单'));
        return $this->view->fetch();
    }
    public function servicePrice(){
        $id = $this->request->post("id");
        //帮帮价格
        $money = Db::name('service_type')->where(['id'=>$id])->value('starting_price');
        return $money;
    }

    //帮我办下单
    public function helpdo(){
        $user = $this->auth->getUser();
        $details = $this->request->post("details");
        $son_id = $this->request->post("son_id");
        $service_date = $this->request->post("service_date");
        $end_address = $this->request->post("end_address");
        $end_lon = $this->request->post("end_lon");
        $end_lat = $this->request->post("end_lat");
        $linkname = $this->request->post("linkname");
        $mobile = $this->request->post("mobile");
        $payment = $this->request->post("payment");
        $fee = $this->request->post("fee");
        $hot_box = $this->request->post("hot_box");//保温箱配送
        $orderTotal = $this->request->post("total");//前台传过来的订单金额
        $bill_id = $this->request->post("bill_id");//非余额支付，会先充值，充值记录 ID
        $remake = $this->request->post("remake");//备注

        //描述不能为空
        if($details==''){
            return $this->error('请填写您的需求');
        }
        //结束地址不能为空
        if($end_address==''||$end_lon==''||$end_lat==''){
            return $this->error('请选择收货地址');
        }
        //手机号为空时存下单人手机
        if($mobile==''){
            $mobile = $user['mobile'];
        }
        //小费为空或者为负时置0
        if($fee=='' || $fee<0){
            $fee = 0;
        }

        //帮帮价格
        $money = Db::name('service_type')->where(['id'=>$son_id])->value('starting_price');

        //预约加钱
        if($service_date==0||strpos($service_date,'立即') !== false){
            $service_time = '';
        }else{
            $service_time = strtotime($service_date);
            $money += $this->subscription_price;//加上预约费
        }

        //天气加钱
        if($this->is_weather == 1){
            $money += $this->weather;//加上天气费
        }

        //时段加钱
        if($service_date==0){
            $hour = date('H');
        }else{
            $hour = date('H',$service_time);
        }
        if(22<$hour&&$hour<24){
            $money += $this->front_night;
        }elseif(0<$hour&&$hour<6){
            $money += $this->back_night;
        }

        //比对前台传过来的订单价格和此时自己算的价格是否一样
        if($orderTotal != $money + $fee){
            return $this->error('参数有误', '/index/user/index');
        }

        //判断支付方式
        if(strpos($payment,'余额') !== false){
            $payment = 1;
        }elseif(strpos($payment,'支付宝') !== false){
            $payment = 2;
        }elseif(strpos($payment,'微信') !== false){
            $payment = 3;
        }else{
            return $this->error('无效支付', '/index/user/index');
        }

        //帮我办
        $content = json_encode([
            'details'       =>  $details,//订单详细内容
            'service_time'  =>  $service_time,//订单执行时间
            'prepayment'    =>  '',//预付款(帮我买)
            'fee'           =>  $fee,//小费
            'hot_box'       =>  $hot_box,//保温箱配送
            'weight'        =>  '',//物品重量(帮我取/送)
            'line_time'     =>  '',//排队时长(代排队)
            'name'          =>  $linkname,//联系人
            'mobile'        =>  $mobile,//联系人手机
            'coupon'        =>  '',//优惠券表ID
            'discount'      =>  0,//优惠了多少钱
            'is_admin'      =>  0,//是否为后台下单
            'admin_id'      =>  '',//后台下单人id
            'son_id'        =>  $son_id,//帮我办子类ID
        ]);

        //订单数据
        $data = [
            'order_num'           =>  $this->getOrderSn(),
            'province'            =>  $user['province'],
            'city'                =>  $user['city'],
            'county'              =>  $user['county'],
            'uid'                 =>  $user['id'],
            'type'                =>  3,// service_type 表中帮我办id
            'content'             =>  $content,
            'money'               =>  $money,
            'payment'             =>  $payment,//1-余额 2-支付宝 3-微信
            'status'              =>  9,//待支付
            'create_time'         =>  time(),
            'start_address'         =>  $end_address,
            'start_lon'             =>  $end_lon,
            'start_lat'             =>  $end_lat,
            'remake'              =>  $remake,
        ];

        //地址加入常用地址
        $addressData = [
            'uid'           =>  $user['id'],
            'linkman'       =>  $linkname,
            'mobile'        =>  $mobile,
            'address'       =>  $end_address,
            'address_lon'   =>  $end_lon,
            'address_lat'   =>  $end_lat,
        ];
        Db::name('address')->insert($addressData);

        $orderRes = Db::name('order')->insert($data);

        $orderId = Db::name('order')->getLastInsID();

        if($orderRes){
            $total = $money + $fee;
            $this->payment($total, $orderId, $payment, $bill_id);
        }else{
            return $this->error('下单失败', '/index/user/index');
        }
    }

/*------------------------------帮我排-------------------------------------*/
    //第一步
    public function helplinestep1(){
        $id = $this->request->get("id");
        //子分类
        $soninfo = Db::name('service_type')->where(['pid'=>$id])->select();
        $this->view->assign('soninfo', $soninfo);
        $this->view->assign('title', __('帮我排·下单'));
        return $this->view->fetch();
    }

    //第二步
    public function helplinestep2(){
        $user =$this->auth->getUser();
        //支付顺序
        $paymentsort = explode(",",$user['paymentsort']);
        $this->view->assign('paymentsort', $paymentsort);
        //计算价格参数
        //天气加价
        $this->view->assign('is_weather', $this->is_weather);
        $this->view->assign('weather', $this->weather);
        //排队价格
        $this->view->assign('lineup_start_price', $this->lineup_start_price);
        $this->view->assign('lineup_one_ten_price', $this->lineup_one_ten_price);
        $this->view->assign('lineup_delayed_price', $this->lineup_delayed_price);
        //预约加价
        $this->view->assign('subscription_price', $this->subscription_price);
        //时段加价
        $this->view->assign('front_night', $this->front_night);
        $this->view->assign('back_night', $this->back_night);
        $this->view->assign('hour', date("H"));

        $this->view->assign('title', __('帮我排·下单'));
        return $this->view->fetch();
    }

    //帮我排下单
    public function helpline(){
        $user = $this->auth->getUser();
        $details = $this->request->post("details");
        $line_time = $this->request->post("line_time");
        $linetime_text = $this->request->post("linetime_text");
        $service_date = $this->request->post("service_date");
        $end_address = $this->request->post("end_address");
        $end_lon = $this->request->post("end_lon");
        $end_lat = $this->request->post("end_lat");
        $linkname = $this->request->post("linkname");
        $mobile = $this->request->post("mobile");
        $payment = $this->request->post("payment");
        $fee = $this->request->post("fee");
        $hot_box = $this->request->post("hot_box");//保温箱配送
        $orderTotal = $this->request->post("total");//前台传过来的订单金额
        $bill_id = $this->request->post("bill_id");//非余额支付，会先充值，充值记录 ID
        $remake = $this->request->post("remake");//备注

        //描述不能为空
        if($details==''){
            return $this->error('请填写您的需求');
        }
        //结束地址不能为空
        if($end_address==''||$end_lon==''||$end_lat==''){
            return $this->error('请选择收货地址');
        }
        //手机号为空时存下单人手机
        if($mobile==''){
            $mobile = $user['mobile'];
        }
        //小费为空或者为负时置0
        if($fee=='' || $fee<0){
            $fee = 0;
        }

        //时间价格
        if($line_time==1){
            $money = $this->lineup_start_price;
        }elseif(1<$line_time&&$line_time<=20){
            $money = $this->lineup_one_ten_price*($line_time-1)+$this->lineup_start_price;
        }elseif(20<$line_time){
            $money = ($line_time-20)*$this->lineup_delayed_price+($this->lineup_one_ten_price*19)+$this->lineup_start_price;
        }

        //预约加钱
        if($service_date==0||strpos($service_date,'立即') !== false){
            $service_time = '';
        }else{
            $service_time = strtotime($service_date);
            $money += $this->subscription_price;//加上预约费
        }

        //天气加钱
        if($this->is_weather == 1){
            $money += $this->weather;//加上天气费
        }

        //时段加钱
        if($service_date==0){
            $hour = date('H');
        }else{
            $hour = date('H',$service_time);
        }
        if(22<$hour&&$hour<24){
            $money += $this->front_night;
        }elseif(0<$hour&&$hour<6){
            $money += $this->back_night;
        }

        //比对前台传过来的订单价格和此时自己算的价格是否一样
        //比对前台传过来的订单价格和此时自己算的价格是否一样
        if($orderTotal != $money + $fee){
            return $this->error('参数有误', '/index/user/index');
        }

        //判断支付方式
        if(strpos($payment,'余额') !== false){
            $payment = 1;
        }elseif(strpos($payment,'支付宝') !== false){
            $payment = 2;
        }elseif(strpos($payment,'微信') !== false){
            $payment = 3;
        }else{
            return $this->error('无效支付', '/index/user/index');
        }

        //帮我排
        $content = json_encode([
            'details'       =>  $details,//订单详细内容
            'service_time'  =>  $service_time,//订单执行时间
            'prepayment'    =>  '',//预付款(帮我买)
            'fee'           =>  $fee,//小费
            'hot_box'       =>  $hot_box,//保温箱配送
            'weight'        =>  '',//物品重量(帮我取/送)
            'line_time'     =>  $linetime_text,//排队时长(代排队)
            'name'          =>  $linkname,//联系人
            'mobile'        =>  $mobile,//联系人手机
            'coupon'        =>  '',//优惠券表ID
            'discount'      =>  0,//优惠了多少钱
            'is_admin'      =>  0,//是否为后台下单
            'admin_id'      =>  '',//后台下单人id
        ]);

        //订单数据
        $data = [
            'order_num'           =>  $this->getOrderSn(),
            'province'            =>  $user['province'],
            'city'                =>  $user['city'],
            'county'              =>  $user['county'],
            'uid'                 =>  $user['id'],
            'type'                =>  4,// service_type 表中帮我排id
            'content'             =>  $content,
            'money'               =>  $money,
            'payment'             =>  $payment,//1-余额 2-支付宝 3-微信
            'status'              =>  9,//待支付
            'create_time'         =>  time(),
            'start_address'         =>  $end_address,
            'start_lon'             =>  $end_lon,
            'start_lat'             =>  $end_lat,
            'remake'              =>  $remake,
        ];

        //地址加入常用地址
        $addressData = [
            'uid'           =>  $user['id'],
            'linkman'       =>  $linkname,
            'mobile'        =>  $mobile,
            'address'       =>  $end_address,
            'address_lon'   =>  $end_lon,
            'address_lat'   =>  $end_lat,
        ];
        Db::name('address')->insert($addressData);

        $orderRes = Db::name('order')->insert($data);

        $orderId = Db::name('order')->getLastInsID();

        if($orderRes){
            $total = $money + $fee;
            $this->payment($total, $orderId, $payment, $bill_id);
        }else{
            return $this->error('下单失败', '/index/user/index');
        }
    }
/*************************************************下单结束*******************************************************/
    //支付
    public function payment($money,$orderId,$payment,$bill_id){
        //1 uesr扣钱 2 order 改状态 3 余额支付加流水,删除充值流水
        $user = $this->auth->getUser();
        if($money>$user['balance']){
            return $this->error('余额不足','/index/recharge/index');
        }
        $orderInfo = Db::name('order')->where(['id'=>$orderId])->find();//订单数据

        $userRes = Db::name('user')->where(['id'=>$user['id']])->setDec('balance',$money);//扣除用户余额
        $orderRes = Db::name('order')->where(['id'=>$orderId])->setField('status',0);//支付成功更改订单状态为待接单

        if($payment == 1){
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

        }else{
            $billRes = Db::name('bill')->where(['id'=>$bill_id])->delete();//删除下单时的充值记录用户账单
        }
        if($userRes&&$orderRes&&$billRes){
            return $this->success('下单成功','/index/placeorder/orderdetails?id='.$orderId);
        }else{
            return $this->success('支付失败！', '/index/order/all');
        }
    }

    //订单详情
    public function orderdetails(){
        $id = $this->request->get("id");

        $orderInfo = Db::name('order')
            ->alias('o')
            ->join('service_type s','o.type = s.id','LEFT')
            ->where(['o.id'=>$id])
            ->field('o.*,s.service_name')
            ->find();
        $orderInfo['content'] = json_decode($orderInfo['content'],true);

        /*echo '<pre>';
        print_r($orderInfo);
        exit;*/

        //取消超时订单
        if($orderInfo['status'] == 9){
            $over_time = $orderInfo['create_time'] + 15 * 60;
            if(time() > $over_time){
                Db::name('order')->where(['id'=>$id])->setField('status',3);
                $orderInfo['status'] = 3;
            }
        }

        $runmenInfo = Db::name('runmen')->where(['id'=>$orderInfo['rid']])->find();
        if(!empty($runmenInfo)){
            $runmenInfo['value'] = 5 - $runmenInfo['score'];
        }

        $evaluateInfo = Db::name('evaluate')->where(['order_id'=>$orderInfo['id']])->find();
        if(!empty($evaluateInfo)){
            $evaluateInfo['value'] = 5 - $evaluateInfo['start'];
        }

        $waiting_sec = (time() - $orderInfo['create_time']);
        if($waiting_sec > 60*15)
        {
            $m = '等待';
            $s = '超时';
        }
        else
        {
            $m = floor((($waiting_sec % (3600*24)) % 3600) / 60);
            if($m<10){
                $m = '0'.$m;
            }
            $s = $waiting_sec%60;
            if($s<10){
                $s = '0'.$s;
            }
        }

        /*echo  '<pre>';
        print_r($runmenInfo );
        exit;*/

        $this->view->assign('m', $m);
        $this->view->assign('s', $s);
        $this->view->assign('evaluateInfo', $evaluateInfo);
        $this->view->assign('runmenInfo', $runmenInfo);
        $this->view->assign('orderInfo', $orderInfo);
        $this->view->assign('title', __('订单进程'));
        return $this->view->fetch();
    }

    //费用明细
    public function expenseDetail(){

        //天气
        $is_weather = $this->is_weather;
        $weather = $this->weather;
        //里程
        $two_km = $this->two_km;
        $three_km = $this->three_km;
        $three_ten_km = $this->three_ten_km;
        $ten_thirty_km = $this->ten_thirty_km;
        $over_thirty_km = $this->over_thirty_km;
        //重量
        $lower_twentyfive_kg = $this->lower_twentyfive_kg;
        $twentysix_thirty_kg = $this->twentysix_thirty_kg;
        $thirtyone_forty_kg = $this->thirtyone_forty_kg;
        $over_forty_kg = $this->over_forty_kg;
        //排队时间
        $lineup_start_price = $this->lineup_start_price;
        $lineup_one_ten_price = $this->lineup_one_ten_price;
        $lineup_delayed_price = $this->lineup_delayed_price;
        //时段
        $front_night = $this->front_night;
        $back_night = $this->back_night;
        //预约费
        $subscription_price = $this->subscription_price;

        //天气
        $this->view->assign('is_weather', $is_weather);
        $this->view->assign('weather', $weather);
        //里程
        $this->view->assign('two_km', $two_km);
        $this->view->assign('three_km', $three_km);
        $this->view->assign('three_ten_km', $three_ten_km);
        $this->view->assign('ten_thirty_km', $ten_thirty_km);
        $this->view->assign('over_thirty_km', $over_thirty_km);
        //重量
        $this->view->assign('lower_twentyfive_kg', $lower_twentyfive_kg);
        $this->view->assign('twentysix_thirty_kg', $twentysix_thirty_kg);
        $this->view->assign('thirtyone_forty_kg', $thirtyone_forty_kg);
        $this->view->assign('over_forty_kg', $over_forty_kg);
        //排队时长
        $this->view->assign('lineup_start_price', $lineup_start_price);
        $this->view->assign('lineup_one_ten_price', $lineup_one_ten_price);
        $this->view->assign('lineup_delayed_price', $lineup_delayed_price);
        //时段
        $this->view->assign('front_night', $front_night);
        $this->view->assign('back_night', $back_night);
        //预约费
        $this->view->assign('subscription_price', $subscription_price);

        //帮办收费规则
        $helpdo = Db::name('service_type')->where(['pid'=>3])->select();
        $this->view->assign('helpdo', $helpdo);

        /*echo '<pre>';
        print_r($helpdo);
        exit;*/

        $this->view->assign('title', __('费用明细'));
        return $this->view->fetch();
    }

    /**
     * 充值协议
     */
    public function agreement()
    {

//        $this->view->assign('userInfo', $userInfo);
        $title = $this->request->get("title");
        $id = $this->request->get("id");

        $data = Db::name('help_document')->where(['id'=>$id])->find();

        $this->view->assign('data', $data);
        $this->view->assign('title', $title);
        return $this->view->fetch();
    }

    //取消订单
    public function cancel(){
        $id = $this->request->post("id");
        $orderInfo = Db::name('order')->where(['id'=>$id])->find();
        $orderRes = Db::name('order')->where(['id'=>$id])->setField('status',3);
        $userRes = Db::name('user')->where(['id'=>$orderInfo['uid']])->setInc('balance',$orderInfo['money']);
        $data = [
            'user_id'       =>  $orderInfo['uid'],
            'order_num'     =>  $orderInfo['order_num'],
            'money'         =>  $orderInfo['money'],
            'order_type'    =>  $orderInfo['type'],
            'create_time'   =>  time(),
            'province'      =>  $orderInfo['province'],
            'city'          =>  $orderInfo['city'],
            'county'        =>  $orderInfo['county'],
        ];
        $billRes = Db::name('bill')->insert($data);

        if($orderRes&&$userRes&&$billRes){
            return $this->success('取消成功','/index/user/index');
        }else{
            return $this->error('取消失败','/index/placeorder/orderdetails?id='.$id);
        }
    }

    //获取订单信息
    public function getOrderInfo(){
        $id = $this->request->post("id");
        $orderInfo = Db::name('order')->where(['id'=>$id])->find();
        $orderInfo['content'] = json_decode($orderInfo['content'],true);
        if($orderInfo){
            return $this->success('','',$orderInfo);
        }else{
            return $this->error('','');
        }
    }



    //生成订单号
    public function getOrderSn()
    {
        $code = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $orderSn = $code[intval(date('Y')) - 2018] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }

}
