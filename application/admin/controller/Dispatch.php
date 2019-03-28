<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/5
 * Time: 14:03
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Session;

class Dispatch extends Backend
{
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
    //private $universal_help='';//万能帮帮（元）
    //private $pets_help='';//照顾宠物（元）
    //private $hourly_help='';//小时工（元）
    //private $carry_help='';//搬运货物（元）
    //private $leaflet_help='';//传单派发（元）
    private $front_night='';//22:00-00:00（每单加）（元）
    private $back_night='';//00:00-06:00（每单加）（元）
    private $subscription_price='';//预约服务费（每单加）（元）
    private $is_weather='';//是否为特殊天气
    public function _initialize(){

        parent::_initialize();
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
        //$this->universal_help=Db::name('config')->where(['group'=>'vvpt','name'=>'universal_help'])->value('value');
        //$this->pets_help=Db::name('config')->where(['group'=>'vvpt','name'=>'pets_help'])->value('value');
        //$this->hourly_help=Db::name('config')->where(['group'=>'vvpt','name'=>'hourly_help'])->value('value');
        //$this->carry_help=Db::name('config')->where(['group'=>'vvpt','name'=>'carry_help'])->value('value');
        //$this->leaflet_help=Db::name('config')->where(['group'=>'vvpt','name'=>'leaflet_help'])->value('value');
        $this->front_night=Db::name('config')->where(['group'=>'vvpt','name'=>'front_night'])->value('value');
        $this->back_night=Db::name('config')->where(['group'=>'vvpt','name'=>'back_night'])->value('value');
        $this->subscription_price=Db::name('config')->where(['group'=>'vvpt','name'=>'subscription_price'])->value('value');
        $this->is_weather=Db::name('config')->where(['group'=>'vvpt','name'=>'is_weather'])->value('value');
    }


    public function index()
    {
        if (\think\Request::instance()->isPost()){
            if ($_POST['top_service'] == 1){//------------------------------------帮我买
                $details = $_POST["matter"];
                $nearby = $_POST['nearbuy'];
                $prepayment = '';
                $service_date = 0;
                $start_address = $_POST['buy_pick_lineup_address'];
                $start_lon = $_POST['start_lon'];
                $start_lat = $_POST['start_lat'];
                $end_address = $_POST['collect_address'];
                $end_lon = $_POST['end_lon'];
                $end_lat = $_POST['end_lat'];
                $mileage = $_POST['spacing'];
                $payment = 5;

                //描述不能为空
                if($details==''){
                    $this->error('请填写您的需求');
                }
                //非就近购买，起始地址、经纬度不能为空
                if($nearby == 0){
                    if($start_address==''||$start_lon==''||$start_lat==''){
                        $this->error('请选择购买地址');
                    }
                }
                //结束地址不能为空
                if($end_address==''||$end_lon==''||$end_lat==''){
                    $this->error('请选择收货地址');
                }

                //里程价格
                if(0<$mileage&&$mileage<=2){
                    $money = $this->two_km;
                }elseif(2<$mileage&&$mileage<=3){
                    $money = $this->three_km;
                }elseif(3<$mileage&&$mileage<=10){
                    $money = ($mileage-3)*$this->three_ten_km+$this->three_km;
                }elseif(10<$mileage&&$mileage<=30){
                    $money = ($mileage-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
                }elseif(30<$mileage){
                    $money = ($mileage-30)*$this->over_thirty_km+(30-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
                }

                //预约加钱
                if($service_date==0){
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


                $content = json_encode([
                    'details'       =>  $details,//订单详细内容
                    'service_time'  =>  $service_time,//订单执行时间
                    'prepayment'    =>  $prepayment,//帮我买预付款
                    'weight'        =>  '',//物品重量(帮我取/送)
                    'line_time'     =>  '',//排队时长(代排队)
                    'name'          =>  $_POST['user_name'],//联系人
                    'mobile'        =>  $_POST['user_mobile'],//联系人手机
                    'coupon'        =>  '',//优惠券表ID
                    'discount'      =>  0,//优惠了多少钱
                    'is_admin'      =>  '1',//是否为后台下单
                    'admin_id'      =>  session('admin.id'),//后台用户id
                ]);

                $data = [
                    'order_num'          => $this -> getOrderSn(),
                    'province'           => $_POST['province'],
                    'city'               => $_POST['city'],
                    'county'             => $_POST['county'],
                    'uid'                 =>  0,
                    'type'               => $_POST['top_service'],
                    'content'            => $content,
                    'money'              => $money,
                    'create_time'        => time(),
                    'status'             => 0,
                    'payment'            => $payment,
                    'start_address'      => $start_address,
                    'start_lon'          => $start_lon,
                    'start_lat'          => $start_lat,
                    'end_address'        => $end_address,
                    'end_lon'            => $end_lon,
                    'end_lat'            => $end_lat,
                    'start_end_distance' => $mileage,
                ];

                $res = Db::name('order') -> insertGetId($data);
                if ($res != ''){
                    $this -> redirect('Dispatch/chooseRunman',['id' => $res]);
                }else{
                    $this -> error('提交失败');
                }

            }elseif ($_POST['top_service'] == 2){//------------------------------------帮我送
                $details = $_POST['matter'];
                $weight = $_POST["weight"];
                $service_date = 0;
                $start_address = $_POST['buy_pick_lineup_address'];
                $start_lon = $_POST['start_lon'];
                $start_lat = $_POST['start_lat'];
                $end_address = $_POST['collect_address'];
                $end_lon = $_POST['end_lon'];
                $end_lat = $_POST['end_lat'];
                $mileage = $_POST['spacing'];
                $payment = 5;

                //描述不能为空
                if($details==''){
                    $this->error('请填写您的需求');
                }
                //起始地址、经纬度不能为空
                if($start_address==''||$start_lon==''||$start_lat==''){
                    $this->error('请选择购买地址');
                }
                //结束地址不能为空
                if($end_address==''||$end_lon==''||$end_lat==''){
                    $this->error('请选择收货地址');
                }

                //里程价格
                if(0<$mileage&&$mileage<=2){
                    $money = $this->two_km;
                }elseif(2<$mileage&&$mileage<=3){
                    $money = $this->three_km;
                }elseif(3<$mileage&&$mileage<=10){
                    $money = ($mileage-3)*$this->three_ten_km+$this->three_km;
                }elseif(10<$mileage&&$mileage<=30){
                    $money = ($mileage-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
                }elseif(30<$mileage){
                    $money = ($mileage-30)*$this->over_thirty_km+(30-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
                }

                //重量加钱
                if(25<$weight&&$weight<=30){
                    $money += 5;
                }elseif(30<$weight&&$weight<=40){
                    $money += 10;
                }elseif(40<$weight){
                    $money += 10+($weight-40)*5;
                }

                //预约加钱
                if($service_date==0){
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

                //帮我送
                $content = json_encode([
                    'details'       =>  $details,//订单详细内容
                    'service_time'  =>  $service_time,//订单执行时间
                    'prepayment'    =>  '',//预付款(帮我买)
                    'weight'        =>  $weight,//物品重量
                    'line_time'     =>  '',//排队时长(代排队)
                    'name'          =>  $_POST['consignee'],//收货人
                    'mobile'        =>  $_POST['consignee_mobile'],//收货人手机
                    'coupon'        =>  '',//优惠券表ID
                    'discount'      =>  0,//优惠了多少钱
                    'is_admin'      =>  '1',//是否为后台下单
                    'admin_id'      =>  session('admin.id'),//后台用户id
                ]);

                //订单数据
                $data = [
                    'order_num'           =>  $this->getOrderSn(),
                    'province'            =>  $_POST['province'],
                    'city'                =>  $_POST['city'],
                    'county'              =>  $_POST['county'],
                    'uid'                 =>  0,
                    'type'                =>  $_POST['top_service'],
                    'content'             =>  $content,
                    'money'               =>  $money,
                    'payment'             =>  $payment,
                    'status'              =>  0,
                    'create_time'         =>  time(),
                    'name'                =>  $_POST['user_name'],//起始人
                    'mobile'              =>  $_POST['user_mobile'],//起始电话
                    'start_address'       =>  $start_address,
                    'end_address'         =>  $end_address,
                    'start_lon'           =>  $start_lon,
                    'start_lat'           =>  $start_lat,
                    'end_lon'             =>  $end_lon,
                    'end_lat'             =>  $end_lat,
                    'start_end_distance'  =>  $mileage,
                ];

                $res = Db::name('order') -> insertGetId($data);
                if ($res != ''){
                    $this -> redirect('Dispatch/chooseRunman',['id' => $res]);
                }else{
                    $this -> error('提交失败');
                }
            }elseif ($_POST['top_service'] == 3){//------------------------------------帮我办
                $details = $_POST['matter'];
                $service_date = 0;
                $end_address = $_POST['buy_pick_lineup_address'];
                $end_lon = $_POST['start_lon'];
                $end_lat = $_POST['start_lat'];
                $payment = 5;
                $son_id = $_POST['middle_service'];

                //描述不能为空
                if($details==''){
                    $this->error('请填写您的需求');
                }
                //结束地址不能为空
                if($end_address==''||$end_lon==''||$end_lat==''){
                    $this->error('请选择收货地址');
                }

                //帮帮价格
                //$money = $this->universal_help;
                $money = Db::name('service_type')->where(['id'=>$son_id])->value('starting_price');

                //预约加钱
                if($service_date==0){
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

                //帮我办
                $content = json_encode([
                    'details'       =>  $details,//订单详细内容
                    'service_time'  =>  $service_time,//订单执行时间
                    'prepayment'    =>  '',//预付款(帮我买)
                    'weight'        =>  '',//物品重量(帮我取/送)
                    'line_time'     =>  '',//排队时长(代排队)
                    'name'          =>  '',//联系人
                    'mobile'        =>  '',//联系人手机
                    'coupon'        =>  '',//优惠券表ID
                    'discount'      =>  0,//优惠了多少钱
                    'is_admin'      =>  '1',//是否为后台下单
                    'admin_id'      =>  session('admin.id'),//后台用户id
                    'son_id'        =>  $son_id,
                ]);

                //订单数据
                $data = [
                    'order_num'           =>  $this->getOrderSn(),
                    'province'            =>  $_POST['province'],
                    'city'                =>  $_POST['city'],
                    'county'              =>  $_POST['county'],
                    'uid'                 =>  0,
                    'type'                =>  $_POST['top_service'],
                    'content'             =>  $content,
                    'money'               =>  $money,
                    'payment'             =>  $payment,
                    'status'              =>  0,
                    'create_time'         =>  time(),
                    'end_address'         =>  $end_address,
                    'end_lon'             =>  $end_lon,
                    'end_lat'             =>  $end_lat,
                ];
                $res = Db::name('order') -> insertGetId($data);
                if ($res != ''){
                    $this -> redirect('Dispatch/chooseRunman',['id' => $res]);
                }else{
                    $this -> error('提交失败');
                }
            }elseif ($_POST['top_service'] == 4){//------------------------------------帮我排
                $details = $_POST['matter'];
                $line_time = $_POST["line_time"];
                $linetime_text = $_POST["linetime_text"];
                $service_date = 0;
                $end_address = $_POST['buy_pick_lineup_address'];
                $end_lon = $_POST['start_lon'];
                $end_lat = $_POST['start_lat'];
                $payment = 5;

                //描述不能为空
                if($details==''){
                    $this->error('请填写您的需求');
                }
                //结束地址不能为空
                if($end_address==''||$end_lon==''||$end_lat==''){
                    $this->error('请选择收货地址');
                }

                //时间价格
                if($line_time==1){
                    $money = $this->lineup_one_ten_price;
                }elseif(1<$line_time&&$line_time<=20){
                    $money = $this->lineup_one_ten_price*$line_time;
                }elseif(20<$line_time){
                    $money = ($line_time-20)*$this->lineup_one_ten_price+($this->lineup_one_ten_price*20);
                }

                //预约加钱
                if($service_date==0){
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

                //帮我排
                $content = json_encode([
                    'details'       =>  $details,//订单详细内容
                    'service_time'  =>  $service_time,//订单执行时间
                    'prepayment'    =>  '',//预付款(帮我买)
                    'weight'        =>  '',//物品重量(帮我取/送)
                    'line_time'     =>  $linetime_text,//排队时长
                    'name'          =>  $_POST['user_name'],//联系人
                    'mobile'        =>  $_POST['user_mobile'],//联系人手机
                    'coupon'        =>  '',//优惠券表ID
                    'discount'      =>  0,//优惠了多少钱
                    'is_admin'      =>  '1',//是否为后台下单
                    'admin_id'      =>  session('admin.id'),//后台用户id
                ]);

                //订单数据
                $data = [
                    'order_num'           =>  $this->getOrderSn(),
                    'province'            =>  $_POST['province'],
                    'city'                =>  $_POST['city'],
                    'county'              =>  $_POST['county'],
                    'uid'                 =>  0,
                    'type'                =>  $_POST['top_service'],// service_type 表中帮我排id
                    'content'             =>  $content,
                    'money'               =>  $money,
                    'payment'             =>  $payment,
                    'status'              =>  0,
                    'create_time'         =>  time(),
                    'end_address'         =>  $end_address,
                    'end_lon'             =>  $end_lon,
                    'end_lat'             =>  $end_lat,
                ];
                $res = Db::name('order') -> insertGetId($data);
                if ($res != ''){
                    $this -> redirect('Dispatch/chooseRunman',['id' => $res]);
                }else{
                    $this -> error('提交失败');
                }
            }elseif ($_POST['top_service'] == 5){//------------------------------------帮我取
                $details = $_POST['matter'];
                $weight = $_POST["weight"];
                $service_date = 0;
                $start_address = $_POST['buy_pick_lineup_address'];
                $start_lon = $_POST['start_lon'];
                $start_lat = $_POST['start_lat'];
                $end_address = $_POST['collect_address'];
                $end_lon = $_POST['end_lon'];
                $end_lat = $_POST['end_lat'];
                $mileage = $_POST['spacing'];
                $payment = 5;

                //描述不能为空
                if($details==''){
                    $this->error('请填写您的需求');
                }
                //起始地址、经纬度不能为空
                if($start_address==''||$start_lon==''||$start_lat==''){
                    $this->error('请选择购买地址');
                }
                //结束地址不能为空
                if($end_address==''||$end_lon==''||$end_lat==''){
                    $this->error('请选择收货地址');
                }

                //里程价格
                if(0<$mileage&&$mileage<=2){
                    $money = $this->two_km;
                }elseif(2<$mileage&&$mileage<=3){
                    $money = $this->three_km;
                }elseif(3<$mileage&&$mileage<=10){
                    $money = ($mileage-3)*$this->three_ten_km+$this->three_km;
                }elseif(10<$mileage&&$mileage<=30){
                    $money = ($mileage-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
                }elseif(30<$mileage){
                    $money = ($mileage-30)*$this->over_thirty_km+(30-10)*$this->ten_thirty_km+(10-3)*$this->three_ten_km+$this->three_km;
                }

                //重量加钱
                if(25<$weight&&$weight<=30){
                    $money += 5;
                }elseif(30<$weight&&$weight<=40){
                    $money += 10;
                }elseif(40<$weight){
                    $money += 10+($weight-40)*5;
                }

                //预约加钱
                if($service_date==0){
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

                //帮我取
                $content = json_encode([
                    'details'       =>  $details,//订单详细内容
                    'service_time'  =>  $service_time,//订单执行时间
                    'prepayment'    =>  '',//预付款(帮我买)
                    'weight'        =>  $weight,//物品重量
                    'line_time'     =>  '',//排队时长(代排队)
                    'name'     =>  $_POST['consignee'],//收货人
                    'mobile' => $_POST['consignee_mobile'],//收货人电话
                    'coupon'        =>  '',//优惠券表ID
                    'discount'      =>  0,//优惠了多少钱
                    'is_admin'      =>  '1',//是否为后台下单
                    'admin_id'      =>  session('admin.id'),//后台用户id
                ]);

                //订单数据
                $data = [
                    'order_num'           =>  $this->getOrderSn(),
                    'province'            =>  $_POST['province'],
                    'city'                =>  $_POST['city'],
                    'county'              =>  $_POST['county'],
                    'uid'                 =>  0,
                    'type'                =>  $_POST['top_service'],
                    'content'             =>  $content,
                    'money'               =>  $money,
                    'payment'             =>  $payment,
                    'status'              =>  0,
                    'create_time'         =>  time(),
                    'name'                =>  $_POST['user_name'],//起始人
                    'mobile'              =>  $_POST['user_mobile'],//起始电话
                    'start_address'       =>  $start_address,
                    'end_address'         =>  $end_address,
                    'start_lon'           =>  $start_lon,
                    'start_lat'           =>  $start_lat,
                    'end_lon'             =>  $end_lon,
                    'end_lat'             =>  $end_lat,
                    'start_end_distance'  =>  $mileage,
                ];

                $res = Db::name('order') -> insertGetId($data);
                if ($res != ''){
                    $this -> redirect('Dispatch/chooseRunman',['id' => $res]);
                }else{
                    $this -> error('提交失败');
                }
            }

        }else {
            $top_service = Db::name('service_type')->where(['pid' => 0]) ->order('sort')->select();
            $province = Db::name('area') -> where(['level' => 1]) -> select();

            $this -> assign('province',$province);
            $this->assign('top_service', $top_service);
            return view();
        }
    }

    public function getOrderSn()
    {
        //生成订单号
            $code = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
            $orderSn = $code[intval(date('Y')) - 2018] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));

            return $orderSn;
    }

    public function get_middle_service()
    {
        $where['pid'] = input('param.high_id');
        $middle_service = Db::name('service_type') -> where($where) -> field('id,service_name') -> select();
        $option = '';
        foreach ($middle_service as $v){
            $option .= '<option value='.$v['id'].'>'.$v['service_name'].'</option>';
        }
        return $option;
    }

    public function get_city()
    {
        $where['parentid'] = input('param.province_id');
        $where['level']    = 2;
        $city = Db::name('area') -> where($where) -> field('id,areaname') -> select();
        $option = '';
        foreach ($city as $v){
            $option .= '<option value='.$v['id'].'>'.$v['areaname'].'</option>';
        }
        return $option;
    }

    public function get_county()
    {
        $where['parentid'] = input('param.city_id');
        $where['level']    = 3;
        $county = Db::name('area') -> where($where) -> field('id,areaname') -> select();
        $option = '';
        foreach ($county as $v){
            $option .= '<option value='.$v['id'].'>'.$v['areaname'].'</option>';
        }
        return $option;
    }


    public function chooseRunman()
    {
        if (\think\Request::instance()->isPost()){
            if (empty($_POST['runman_id'])){
                $this -> error('请选择跑男');
            }
            $where['status'] = ['in','1,2'];
            $where['rid'] = $_POST['runman_id'];
            $result = Db::name('order') -> where($where) -> find();
            if (!empty($result)){
                $this -> error('请重新选择跑男');
            }
            $data = [
                'rid' => $_POST['runman_id'],
            ];
            $res = Db::name('order') -> where(['id' => $_POST['id']]) -> update($data);
            if ($res){
                $this -> success('派单成功','Dispatch/index');
            }else{
                $this -> error('派单失败');
            }
        }else {
            $order_id = input('param.id');
            $freeman = Db::name('runmen') -> where(['status' => 1,'is_order' => 0]) -> field('id,truename,lon,lat') -> select();

            $this -> assign('id',$order_id);
            $this -> assign('freeman',json_encode($freeman));
            return view();
        }
    }


}