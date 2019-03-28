<?php
// +----------------------------------------------------------------------
// | vv跑腿配送端
// | 定时任务
// +----------------------------------------------------------------------
// | 2018-10-31
// +----------------------------------------------------------------------
// | Author: Mc小张
// +----------------------------------------------------------------------
namespace app\vvptd\controller;
use think\Controller;
use think\Db;
class Planned extends Controller{
    public function index(){
        //清除每日的排行数据
        $day['day_order_num'] = 0;
        $day['day_licheng_num'] = 0;
        $day['day_money'] = 0.00;
        Db::name('team')->where('1=1')->update($day);
        Db::name('runmen')->where('1=1')->update($day);
        //清除每周的排行数据
        $week_str = mb_substr( "日一二三四五六",date("w"),1,"utf-8" );
        if($week_str == '一'){
            $week['week_order_num'] = 0;
            $week['week_licheng_num'] = 0;
            $week['week_money'] = 0.00;
            Db::name('team')->where('1=1')->update($week);
            Db::name('runmen')->where('1=1')->update($week);
        }
        //清除每月数据
        $month_str = date('d');
        if($month_str == '01'){
            $month['month_order_num'] = 0;
            $month['month_licheng_num'] = 0;
            $month['month_money'] = 0.00;
            Db::name('team')->where('1=1')->update($month);
            Db::name('runmen')->where('1=1')->update($month);
        }
        echo "清除成功";
    }
    //定时更新上次登录时间
    public function updateLastLoginTime(){
        $data = Db::name('runmen')->field('logintime as prevtime')->where('1=1')->select();
        $res = Db::name('runmen')->where('1=1')->update($data);
        if($res === false){
            echo "更新失败";die;
        }else{
            echo "更新成功";die;
        }
    }
}