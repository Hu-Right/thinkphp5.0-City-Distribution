<?php
// +----------------------------------------------------------------------
// | vv跑腿配送端
// | 会员中心
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
use think\config;
use fast\Random;
class Order extends Vvptd{
    public function index(){
        return $this->fetch('order');
    }
    //订单页面展示
    public function order_main1(){
        return $this->fetch();
    }
    //进行中
    public function order_main2(){
        return $this->fetch();
    }
    //已完成
    public function order_main3(){
        return $this->fetch();
    }
    //已取消
    public function order_main4(){
        return $this->fetch();
    }
    //获取订单数据
    public function getMyOrderList(){
        $page = $this->request->post('page')?:'1';
        //订单状态
        $status = $this->request->post('status')?:'888';
        //$where['rid'] = $this->rid;
        if($status!='888'){
            $where['status'] = ['in','('.$status.')'];
        }
        $order_list = Db::name('order')->where('status in ('.$status.') and rid='.$this->rid)->page($page)->limit(10)->order('create_time desc')->select();
        for($i=0;$i<count($order_list);$i++){
            $order_list[$i]['content']  =json_decode($order_list[$i]['content'],true);
            $order_list[$i]['create_time']  =date('Y-m-d H:i:s',$order_list[$i]['create_time']);
            $order_list[$i]['type_info'] = Db::name('service_type')->where('id',$order_list[$i]['type'])->find();
        }
        $this->assign('order_list',$order_list);
        return $this->fetch('ajax_buy');
    }
    //订单详情页面
    public function order_detail(){
        if($this->request->isAjax()){
            $order_id = $this->request->post('order_id');

            $order = Db::name('order')->where('id',$order_id)->find();

            $order['type_info'] = Db::name('service_type')->where('id',$order['type'])->find();
            $order['runmen'] = Db::name('user')->where('id',$order['uid'])->find();
            $order['content'] = json_decode($order['content'],true);
            $this->success('查询成功','',$order);
        }

        $order_id = $this->request->get('order_id');
        $order = Db::name('order')->where('id',$order_id)->find();
        $order['type_info'] = Db::name('service_type')->where('id',$order['type'])->find();
        $order['runmen'] = Db::name('user')->where('id',$order['uid'])->find();
        $order['content'] = json_decode($order['content'],true);
        $order['create_time'] = date('Y-m-d H:i:s',$order['create_time']);
  
        $this->assign('order',$order);

        return $this->fetch('order-detail');
    }
    //回复评价
    public function huifupingjia(){
        $order_id = $this->request->post('order_id');
        $user_id = $this->request->post('user_id');
        $reply = $this->request->post('reply');
        $data['reply'] = $reply;
        $data['reply_time'] = time();
        $where['order_id'] = $order_id;
        $where['user_id'] = $user_id;
        $where['rid'] = $this->rid;
        $res = Db::name('evaluate')->where($where)->update($data);
        if($res === false){
            $this->error('回复失败');
        }else{
            $this->success('回复成功');
        }
    }
    //开关是否接单
    public function is_open(){
        $type = $this->request->post('type');
        if($type==''){
            $this->error('操作失败');
        }
        //检测资料状态
        if ($this->checkMemberStatus() == true) {
            $this->error('资料审核中不能开始接单！');die;
        }
        $res = Db::name('runmen')->where('id',$this->rid)->update(['is_receipt'=>$type]);
        if($res ===false){
            $this->error('操作失败');
        }else{
            //操作成功
            $this->success('操作成功','',$type);
        }
    }
    //抢单模式接单
    public function manual(){
        //检测资料状态
        if ($this->checkMemberStatus() == true) {
            $this->error('资料审核中不能开始接单！');
        }
        //检测是否开启接单
        $receipt = $this->getRunMen('is_receipt');
        if($receipt == 0){
            $this->error('没有开启接单');
        }
        $order_id = $this->request->post('order_id');
        if($order_id){
            $is_jd = Db::name('order')->where('rid',$this->rid)->where('status',1)->find();
            if($is_jd){
                $this->error('您有未完成的订单，不能继续接单');
            }
            //检测是否已经被别人抢走
            $order = Db::name('order')->lock(true)->where('id',$order_id)->find();

            if($order['rid'] == 0){
                $res = $this->jiedan($order_id,$this->rid,1);
                if($res === false){
                    $this->error('接单失败');
                }else{
                    Db::name('runmen')->where('id',$this->rid)->update(['is_order'=>1]);
                    $this->orderNotice($order_id,$this->rid,'您有一个新的订单！');
                    $this->success('接单成功');
                }
            }else{
                if($order['rid'] == $this->rid){
                    Db::name('runmen')->where('id',$this->rid)->update(['is_order'=>1]);
                    $this->success('接单成功');
                }
                // $this->error('改单已经被抢，或者用户取消了订单');
            }

        }else{
            $this->error('抢单失败');
        }
    }
    //自动抢单
    public function autoOrder(){
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        $a = array();
        //检测资料状态
        if ($this->checkMemberStatus() == true) {
            $a['order_id'] = '';
            $a['msg'] = '资料审核中不能开始接单！';
        }else {
            //检测是否开启接单
            $receipt = $this->getRunMen('is_receipt');
            if ($receipt == 0) {
                $a['order_id'] = '';
                $a['msg'] = '没有开启接单';
            } else {
                $receipt = $this->getRunMen('receipt');
                if ($receipt == 2) {
                    $receipt_time = $this->getRunMen('receipt_time');
                    if ($receipt_time) {
                        $time = explode('-', $receipt_time);
                        $nowtime = date('H:i');
                        if ($nowtime < $time[0] || $nowtime > $time[1]) {
                            $a['order_id'] = '';
                            $a['msg'] = '不在自动接单的时间范围內';
                        }
                    }
                    //检测是否已有单
                    $is_cunzai = Db::name('order')->where('rid', $this->rid)->where('status', 0)->find();
                    if (empty($is_cunzai)) {
                        $is_jd = Db::name('order')->where('rid', $this->rid)->where('status', 1)->find();
                        if ($is_jd) {
                            $a['order_id'] = '';
                            $a['msg'] = '还有未完成单orderNotice不能继续接单';
                        } else {
                            //查询范围内的所有订单
                            $lon = $this->getRunMen('lon');
                            $lat = $this->getRunMen('lat');
                            if ($lon && $lat) {
                                $km = '2000';
                                $sql = "select * FROM zs_order having ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-start_lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(start_lat*PI()/180)*POW(SIN(({$lon}*PI()/180-start_lon*PI()/180)/2),2)))*1000) <= {$km} and status = 0 and rid = 0 ";
                                $orders = Db::query($sql);
                                $aaa = array();
                                if (!empty($orders)) {
                                    for ($i = 0; $i < count($orders); $i++) {
                                        $refuse_rid = explode(',', $orders[$i]['refuse_rid']);
                                        if (!in_array($this->rid, $refuse_rid)) {
                                            $aaa[] = $orders[$i];
                                        }
                                    }
                                    $orders = $aaa;
                                    if (count($orders) > 0) {
                                        $orders = array_merge($orders);
                                        $order = $orders[0];
                                        if ($order) {
                                            if ($order['rid'] == 0) {
                                                $res = Db::name('order')->where('id', $order['id'])->update(['rid' => $this->rid]);
                                                if ($res === false) {
                                                    $a['order_id'] = '';
                                                    $a['msg'] = 'error';
                                                } else {
                                                    $this->orderNotice($order['id'], $this->rid, '您有一个新的订单！');
                                                    $a['order_id'] = $order['id'];
                                                    $a['msg'] = 'success';
                                                }
                                            } else {
                                                $a['order_id'] = '';
                                                $a['msg'] = '附近没有订单';
                                            }
                                        } else {
                                            $a['order_id'] = '';
                                            $a['msg'] = '附近没有订单';
                                        }
                                    }
                                } else {
                                    $a['order_id'] = '';
                                    $a['msg'] = '附近没有订单';
                                }
                            } else {
                                $a['order_id'] = '';
                                $a['msg'] = '该跑男还没有坐标点';
                            }
                        }
                    } else {
                        $a['order_id'] = '';
                        $a['msg'] = '还有未完成单不能继续接单';
                    }
                } else {
                    $a['order_id'] = '';
                    $a['msg'] = '没开启自动接单';
                }
            }
        }
        $a = json_encode($a);
        echo "data:{$a}\n\n";die;
        flush();
    }
    //自动接单的
    public function thisOrderStatus(){
        $order_id = $this->request->post('order_id');
        $type = $this->request->post('type');
        if($order_id){
            $order = Db::name('order')->where('id',$order_id)->find();
            if($order){
                if($order['refuse_rid']){
                    $data['refuse_rid'] = $order['refuse_rid'].','.$this->rid;
                }else{
                    $data['refuse_rid'] = $this->rid;
                }
            }
            $res = Db::name('order')->where('id',$order_id)->update($data);
            if($res === false){
                $this->error('拒绝失败','',Db::name('order')->getLastSql());
            }else{
                $this->success('拒绝成功');
            }
        }

    }
    //首页范围内的 订单 跟跑男
    public function getOrderAndRunmen(){
        $lon = $this->request->post('lon')?:"132.0";
        $lat = $this->request->post('lat')?:"132.0";
        $km = '2000';
        $sql = "select * FROM zs_order having ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-start_lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(start_lat*PI()/180)*POW(SIN(({$lon}*PI()/180-start_lon*PI()/180)/2),2)))*1000) <= {$km} and status = 0 and rid = 0";
        $order =Db::query($sql);
        $sqls = "select * FROM zs_runmen having ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$lon}*PI()/180-lon*PI()/180)/2),2)))*1000) <= {$km}";
        $runmen =Db::query($sqls);

        $data['order'] = $order;
        $data['runmen'] = $runmen;
        $this->success('查询成功','',$data);
    }
    //热力图
    public function getHotMap(){
        $thiswork = Db::name('order')
            ->field('start_lat as lat,start_lon as lng')
            ->where(' ( status=5 or status = 6 ) and rid = '.$this->rid)
            ->select();
        for($i=0;$i<count($thiswork);$i++){
            $thiswork[$i]['count'] = 100;
        }
        $this->success('成功','',$thiswork);
    }
    //实时更新跑男当前的位置
    public function updateRunmenPlace(){
        $lng = $this->request->post('lng');
        $lat = $this->request->post('lat');
        $data['lon'] = $lng;
        $data['lat'] = $lat;
        $res = Db::name('runmen')->where('id',$this->rid)->update($data);
        if($res === false){
            $this->error('更新失败');
        }else{
            $this->success('更新成功');
        }
    }
    //检测是否有订单
    public function checkOrder(){
        $where['rid'] = $this->rid;
        $where['status'] = 1;
        $map['rid'] = $this->rid;
        $map['status'] = 0;
        $order = Db::name('order')->where(" ( rid={$this->rid} and status = 1 ) or ( rid={$this->rid} and status=0 ) ")->find();
        if(!empty($order)) {
            $order['type_info'] = Db::name('service_type')->where('id', $order['type'])->find();
            $order['runmen'] = Db::name('user')->where('id', $order['uid'])->find();
            $order['content'] = json_decode($order['content'], true);
            $order['create_time'] = date('Y-m-d', $order['create_time']);
            $this->success('查询成功','',$order);
        }else{
            $this->error('查询成功');
        }

    }
    //生成订单验证码
    public function addOrderCode(){
        $order_id = $this->request->post('order_id');
        $code = rand(rand(1000,9999),rand(9999,1000));
        $res = Db::name('order')->where('id',$order_id)->update(['order_code'=>$code]);
        if($res === false){
            $this->error('生成失败');
        }else{
            $this->success('生成成功');
        }
    }
    //检测订单验证码
    public function checkOrderCode(){
        $order_id = $this->request->post('order_id');
        $code = $this->request->post('code');
        $is_admin = $this->request->post('is_admin');
        if($is_admin == 1){
            $res = Db::name('order')->where('id',$order_id)->where('status',1)->find();
        }else{
            $res = Db::name('order')->where('id',$order_id)->where('order_code',$code)->where('status',1)->find();
        }
        if($res){
            if($is_admin == 1) {
                if ($res['content']) {
                    $content = json_decode($res['content'], true);
                    //校验验证码
                    $sms_check = Sms::check($content['mobile'], $code, 'vvptd-send', false);
                    if (!$sms_check) {
                        return $this->error(__('验证码不正确'));
                    }
                }
            }
            // 启动事务
            Db::startTrans();
            try{
                if($is_admin == 1){
                    Db::name('order')->where('id',$order_id)->update(['status'=>5,'finish_time'=>time()]);
                }else{
                    //验证码正确修改订单状态
                    Db::name('order')->where('id',$order_id)->where('order_code',$code)->update(['status'=>5,'finish_time'=>time()]);
                }

                //检测是否上传报单
                $baodan = $this->getRunMen('baodan_img');
                if($baodan == ''  || $baodan ==null){
                    //没有报单者每天第一单扣除2元单费
                    $money = $res['money']-2;
                    //生成账户变更记录
                    $dataw['type'] = 2;
                    $dataw['money'] = 2;
                    $dataw['detail'] = '没有上传报单扣除';
                    $dataw['rid'] = $this->rid;
                    $dataw['add_time'] = time();
                    $this->insertRunMenRecode($dataw);
                }else{
                    $money = $res['money'];
                }
                //更新账户余额
                Db::name('runmen')->where('id',$this->rid)->setInc('money',$money);
                Db::name('runmen')->where('id',$this->rid)->setInc('lj_money',$money);
                //生成账户变更记录
                $datas['type'] = 1;
                $datas['money'] = $money;
                $datas['detail'] = '跑腿订单金额';
                $datas['rid'] = $this->rid;
                $datas['add_time'] = time();
                $this->insertRunMenRecode($datas);
                //累计跑男竟然值
                Db::name('runmen')->where('id',$this->rid)->setInc('experience_num');
                //添加经验值记录
                $jingyan['add_time'] = time();
                $jingyan['runmen_id'] = $this->rid;
                $jingyan['detail'] = '跑单子';
                $jingyan['num'] = 1;
                $jingyan['type'] = 1;
                Db::name('runmen_level_experience_recode')->insert($jingyan);

                Db::name('runmen')->where('id',$this->rid)->update(['is_order'=>0]);
                // 提交事务
                Db::commit();
                $this->success('验证码正确');
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->success('验证码正确');
            }
        }else{
            $this->error('验证码错误11111');
        }
    }
    //更新订单
    public function updateOrder(){
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        $order = [];
        //查询范围内的所有订单
        $lon = $this->getRunMen('lon');
        $lat = $this->getRunMen('lat');
        if($lon&&$lat) {
            $km = '2000';
            $sql = "select * FROM zs_order having ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-start_lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(start_lat*PI()/180)*POW(SIN(({$lon}*PI()/180-start_lon*PI()/180)/2),2)))*1000) <= {$km} and status = 0 and rid = 0";
            $order =Db::query($sql);
            for($i=0;$i<count($order);$i++){
                $order[$i]['type_info'] = Db::name('service_type')->where('id',$order[$i]['type'])->find();
            }

        }
        $order = json_encode($order);
        echo "data:{$order}\n\n";die;
        flush();
    }
    public function changeOrderimg(){
        $order_id = $this->request->post('order_id');
        $img = $this->request->post('img');
        $res = Db::name('order')->where('id',$order_id)->update(['img'=>$img]);
        if($res===false){
            $this->error('操作失败');
        }else{
            $this->success('操作成功');
        }
    }

    //通知所有跑男
    public function noticeOrderRunMen(){
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        $a = array();
        //检测资料状态
        if ($this->checkMemberStatus() == true) {
            $a['order_id'] = '';
            $a['msg'] = '资料审核中不能开始接单！';
        }else {
            //检测是否开启接单
            $receipt = $this->getRunMen('is_receipt');
            if ($receipt == 0) {
                $a['order_id'] = '';
                $a['msg'] = '没有开启接单';
            } else {
                //检测是否已有单
                $is_jd = Db::name('order')->where('rid', $this->rid)->where('status',1)->find();
                if ($is_jd) {
                    $a['order_id'] = '';
                    $a['msg'] = '还有未完成单orderNotice不能继续接单';
                } else {
                    //查询范围内的所有订单
                    $lon = $this->getRunMen('lon');
                    $lat = $this->getRunMen('lat');
                    if ($lon && $lat) {
                        $km = '2000';
                        $sql = "select * FROM zs_order having ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-start_lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(start_lat*PI()/180)*POW(SIN(({$lon}*PI()/180-start_lon*PI()/180)/2),2)))*1000) <= {$km} and status = 0 and rid = 0 ";
                        $orders = Db::query($sql);
                        $aaa = array();
                        if (!empty($orders)) {
                            for ($i = 0; $i < count($orders); $i++) {
                                $refuse_rid = explode(',', $orders[$i]['refuse_rid']);
                                if (!in_array($this->rid, $refuse_rid)) {
                                    $aaa[] = $orders[$i];
                                }
                            }
                            $orders = $aaa;
                            if (count($orders) > 0) {
                                $orders = array_merge($orders);
                                $order = $orders[0];
                                if ($order) {
                                   $a['order_id'] = $order['id'];
                                   $a['msg'] = 'success';
                                } else {
                                    $a['order_id'] = '';
                                    $a['msg'] = '附近没有订单';
                                }
                            }
                        } else {
                            $a['order_id'] = '';
                            $a['msg'] = '附近没有订单';
                        }
                    } else {
                        $a['order_id'] = '';
                        $a['msg'] = '该跑男还没有坐标点';
                    }
                }
                    
                
            }
        }
        $a = json_encode($a);
        echo "data:{$a}\n\n";die;
        flush();
    }
    //语音播报在线合成
    public function audioStart(){
        $order_id = $this->request->post('order_id');
        $order = Db::name('order')->where('id',$order_id)->find();
        $order['type_info'] = Db::name('service_type')->where('id',$order['type'])->find();
        $order['runmen'] = Db::name('user')->where('id',$order['uid'])->find();
        $order['content'] = json_decode($order['content'],true);
        $order['create_time'] = date('Y-m-d H:i:s',$order['create_time']);

        $text = "";
        $text.='起点,'.$order['start_address'];
        if($order['end_address']){
            $text.=",终点,".$order['end_address'];
        }
        $text.=",类型,".$order['type_info']['service_name'];
        $text.=",距离,".$order['start_end_distance'].'公里';
        $text.=",价格,".$order['money'];
        
        if($order['content']['prepayment']>0){
            $text.=",需要预付,".$order['content']['prepayment'];
        }
        
        # 填写网页上申请的appkey 如 $apiKey="g8eBUMSokVB1BHGmgxxxxxx"
        $apiKey = "7k46Y8DffkPfLSMG3myFdaxf";
        # 填写网页上申请的APP SECRET 如 $secretKey="94dc99566550d87f8fa8ece112xxxxx"
        $secretKey = "AwTrsdyLFeM6RwWUCZKtteCpCofb0SFm";

        # text 的内容为"欢迎使用百度语音合成"的urlencode,utf-8 编码
        # 可以百度搜索"urlencode" 

        $text2 = iconv("UTF-8", "GBK", $text);

        #发音人选择, 0为普通女声，1为普通男生，3为情感合成-度逍遥，4为情感合成-度丫丫，默认为普通女声
        $per = 0;
        #语速，取值0-15，默认为5中语速
        $spd = 5;
        #音调，取值0-15，默认为5中语调
        $pit = 5;
        #音量，取值0-9，默认为5中音量
        $vol = 15;
        // 下载的文件格式, 3：mp3(default) 4： pcm-16k 5： pcm-8k 6. wav
        $aue = 3;

        $formats = array(3 => 'mp3', 4 => 'pcm', 5 =>'pcm', 6 => 'wav');
        $format = $formats[$aue];

        $cuid = "123456PHP";
   
        /** 公共模块获取token开始 */
        $response = $this->getAtoken($apiKey,$secretKey);
        $token = $response['access_token'];
        /** 公共模块获取token结束 */

        /** 拼接参数开始 **/
        // tex=$text&lan=zh&ctp=1&cuid=$cuid&tok=$token&per=$per&spd=$spd&pit=$pit&vol=$vol
        $params = array(
            'tex' => urlencode($text), // 为避免+等特殊字符没有编码，此处需要2次urlencode。
            'per' => $per,
            'spd' => $spd,
            'pit' => $pit,
            'vol' => $vol,
            'aue' => $aue,
            'cuid' => $cuid,
            'tok' => $token,
            'lan' => 'zh', //固定参数
            'ctp' => 1, // 固定参数
        );
        $paramsStr =  http_build_query($params);
        $url = 'http://tsn.baidu.com/text2audio';
        $urltest = $url . '?' . $paramsStr;
        echo $urltest;die; // 反馈请带上此url
    }
    public function getAtoken($apiKey,$secretKey){
        if(!session('arr')){
            $auth_url = "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=".$apiKey."&client_secret=".$secretKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $auth_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 检查证书中是否设置域名,0不验证
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            $res = curl_exec($ch);
            if(curl_errno($ch))
            {
                print curl_error($ch);
            }
            curl_close($ch);
            session('arr',$res);
            
        }else{
            $res = session('arr');
        }
        $response = json_decode($res, true);
        return $response;
    }
}