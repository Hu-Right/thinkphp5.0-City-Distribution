<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\Db;
use think\Session;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        /*$seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++) {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }*/
        //当前月的所有日期
        /*$j = date("t"); //获取当前月份天数
        $start_time = strtotime(date('Y-m-01'));  //获取本月第一天时间戳
        $array = array();
        for($i=0;$i<$j;$i++){
            $array[] = date('Y-m-d',$start_time+$i*86400); //每隔一天赋值给数组
        }*/

        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        $this->view->assign([
//            'totaluser'        => 35200,
//            'totalviews'       => 219390,
//            'totalorder'       => 32143,
//            'totalorderamount' => 174800,
//            'todayuserlogin'   => 321,
//            'todayusersignup'  => 430,
//            'todayorder'       => 2324,
//            'unsettleorder'    => 132,
//            'sevendnu'         => '80%',
//            'sevendau'         => '32%',
//            'array' => json_encode($array),
//            'paylist' => $paylist,
//            'createlist' => $createlist,
            'addonversion' => $addonVersion,
            'uploadmode' => $uploadmode,
        ]);
        $admin = session::get('admin');

        if ($admin['id'] == 1) {
            //echarts展示数据
            //查询日期  展示15条
            $charts_date = Db::name('order_num_statistics') -> where(['city' => 0]) -> order('id') -> limit(0,15) -> column('order_time');
            //所有订单数
            $charts_order_total = Db::name('order_num_statistics') -> where(['city' => 0]) -> order('id') -> limit(0,15) -> column('order_num');
            //完成数
            $charts_complete_order_total = Db::name('order_num_statistics') -> where(['city' => 0]) -> order('id') -> limit(0,15) -> column('complete_order_num');

            $this -> assign([
                'charts_date' => json_encode($charts_date),
                'charts_order_total' => json_encode($charts_order_total),
                'charts_complete_order_total' => json_encode($charts_complete_order_total),
            ]);

            //总用户数
            $user_total = Db::name('user')->count();
            //总跑男数
            $runman_total = Db::name('runmen')->count();
            //总订单数
            $order_total = Db::name('order')->count();
            //平台总收入
            $take_percent = Db::name('config')->where(['name' => 'take_percent'])->value('value');
            $order = Db::name('order')->where(['status' => 2])->sum('money');
            $income_total = $order * ($take_percent / 100);
            //今日注册人数（用户+跑男）
            $today_start = strtotime(date('Y-m-d', time()));
            $today_end = $today_start + 60 * 60 * 24;
            $user_reg = Db::name('user')->where('createtime', 'between time', [$today_start, $today_end])->count();
            $runman_reg = Db::name('runmen')->where('create_time', 'between time', [$today_start, $today_end])->count();
            $today_reg = $user_reg + $runman_reg;
            //今日登陆人数
            $user_login = Db::name('user')->where('logintime', 'between time', [$today_start, $today_end])->count();
            $runman_login = Db::name('runmen')->where('logintime', 'between time', [$today_start, $today_end])->count();
            $today_login = $user_login + $runman_login;
            //今日订单
            $today_order = Db::name('order')->where('create_time', 'between time', [$today_start, $today_end])->count();
            //今日未处理订单
            $today_pending_order = Db::name('order')->where('create_time', 'between time', [$today_start, $today_end])->where(['status' => 0])->count();
            //七日新增
            $sevendaystart = time() - 60 * 60 * 24 * 7;
            $sevendayend = time();
            $sevenday_new_user = Db::name('user')->where('createtime', 'between time', [$sevendaystart, $sevendayend])->count();
            $sevenday_new_runman = Db::name('runmen')->where('create_time', 'between time', [$sevendaystart, $sevendayend])->count();
            if ($user_total + $runman_total == 0){
                $sevenday_new_percent = 0;
            }else {
                $sevenday_new_percent = round(($sevenday_new_user + $sevenday_new_runman) / ($user_total + $runman_total) * 100);
            }
            //七日活跃
            $sevenday_active_user = Db::name('user')->where('logintime', 'between time', [$sevendaystart, $sevendayend])->count();
            $sevenday_active_runman = Db::name('runmen')->where('logintime', 'between time', [$sevendaystart, $sevendayend])->count();
            if ($user_total + $runman_total == 0){
                $sevenday_active_percent = 0;
            }else {
                $sevenday_active_percent = round(($sevenday_active_user + $sevenday_active_runman) / ($user_total + $runman_total) * 100);
            }
            //最新文章
            $article = Db::name('help_document')->where(['status' => 1])->limit(0, 10)->order('create_time desc')->select();

            //最新公告
            $notice = Db::name('notice')->where(['status' => 1])->limit(0, 10)->order('create_time desc')->select();
        } else {
            $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
            $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city

            //echarts展示数据
            //查询日期  展示15条
            $charts_date = Db::name('order_num_statistics') -> where(['city' => $cityList['city']]) -> order('create_time') -> limit(0,15) -> column('order_time');
            //所有订单数
            $charts_order_total = Db::name('order_num_statistics') -> where(['city' => $cityList['city']]) -> order('create_time') -> limit(0,15) -> column('order_num');
            //完成数
            $charts_complete_order_total = Db::name('order_num_statistics') -> where(['city' => $cityList['city']]) -> order('create_time') -> limit(0,15) -> column('complete_order_num');

            $this -> assign([
                'charts_date' => json_encode($charts_date),
                'charts_order_total' => json_encode($charts_order_total),
                'charts_complete_order_total' => json_encode($charts_complete_order_total),
            ]);

            //总用户数
            $user_total = Db::name('user')->where(array('city'=>$cityList['city']))->count();
            //总跑男数
            $runman_total = Db::name('runmen')->where(array('city'=>$cityList['city']))->count();
            //总订单数
            $order_total = Db::name('order')->where(array('city'=>$cityList['city']))->count();
            //平台总收入
            $take_percent = Db::name('config')->where(['name' => 'take_percent'])->value('value');
            $order = Db::name('order')->where(array('city'=>$cityList['city']))->where(['status' => 2])->sum('money');
            $income_total = $order * ($take_percent / 100);
            //今日注册人数（用户+跑男）
            $today_start = strtotime(date('Y-m-d', time()));
            $today_end = $today_start + 60 * 60 * 24;
            $user_reg = Db::name('user')->where(array('city'=>$cityList['city']))->where('createtime', 'between time', [$today_start, $today_end])->count();
            $runman_reg = Db::name('runmen')->where(array('city'=>$cityList['city']))->where('create_time', 'between time', [$today_start, $today_end])->count();
            $today_reg = $user_reg + $runman_reg;
            //今日登陆人数
            $user_login = Db::name('user')->where(array('city'=>$cityList['city']))->where('logintime', 'between time', [$today_start, $today_end])->count();
            $runman_login = Db::name('runmen')->where(array('city'=>$cityList['city']))->where('logintime', 'between time', [$today_start, $today_end])->count();
            $today_login = $user_login + $runman_login;
            //今日订单
            $today_order = Db::name('order')->where(array('city'=>$cityList['city']))->where('create_time', 'between time', [$today_start, $today_end])->count();
            //今日未处理订单
            $today_pending_order = Db::name('order')->where(array('city'=>$cityList['city']))->where('create_time', 'between time', [$today_start, $today_end])->where(['status' => 0])->count();
            //七日新增
            $sevendaystart = time() - 60 * 60 * 24 * 7;
            $sevendayend = time();
            $sevenday_new_user = Db::name('user')
            ->where(array('city'=>$cityList['city']))
            ->where('createtime', 'between time', [$sevendaystart, $sevendayend])->count();
            $sevenday_new_runman = Db::name('runmen')
            ->where(array('city'=>$cityList['city']))
            ->where('create_time', 'between time', [$sevendaystart, $sevendayend])->count();
            if ($user_total + $runman_total == 0){
                $sevenday_new_percent = 0;
            }else {
                $sevenday_new_percent = round(($sevenday_new_user + $sevenday_new_runman) / ($user_total + $runman_total) * 100);
            }
            //七日活跃
            $sevenday_active_user = Db::name('user')
            ->where(array('city'=>$cityList['city']))
            ->where('logintime', 'between time', [$sevendaystart, $sevendayend])->count();
            $sevenday_active_runman = Db::name('runmen')
            ->where(array('city'=>$cityList['city']))
            ->where('logintime', 'between time', [$sevendaystart, $sevendayend])->count();
            if ($user_total + $runman_total == 0){
                $sevenday_active_percent = 0;
            }else {
                $sevenday_active_percent = round(($sevenday_active_user + $sevenday_active_runman) / ($user_total + $runman_total) * 100);
            }
            //最新文章
            $article = Db::name('help_document')
            ->where(array('city'=>$cityList['city']))
            ->where(['status' => 1])->limit(0, 10)->order('create_time desc')->select();

            //最新公告
            $notice = Db::name('notice')
            ->where(array('city'=>$cityList['city']))
            ->where(['status' => 1])->limit(0, 10)->order('create_time desc')->select();

        }
        $this->assign('user_total', $user_total);
        $this->assign('runman_total', $runman_total);
        $this->assign('order_total', $order_total);
        $this->assign('income_total', $income_total);
        $this->assign('today_reg', $today_reg);
        $this->assign('today_login', $today_login);
        $this->assign('today_order', $today_order);
        $this->assign('today_pending_order', $today_pending_order);
        $this->assign('sevenday_new_percent', $sevenday_new_percent);
        $this->assign('sevenday_active_percent', $sevenday_active_percent);
        $this->assign('article', $article);
        $this->assign('notice', $notice);

        return view();
    }

    public function getDate()
    {
        $j = date("t"); //获取当前月份天数
        $start_time = strtotime(date('Y-m-01'));  //获取本月第一天时间戳
        $array = array();
        for($i=0;$i<$j;$i++){
            $array[] = date('Y-m-d',$start_time+$i*86400); //每隔一天赋值给数组
        }
        //$a = 1111;
        return $array;
    }

}
