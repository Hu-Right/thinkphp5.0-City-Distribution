<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/7
 * Time: 15:06
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Session;
class Report extends Backend
{
    public function index()
    {
        $admin = session::get('admin');
        if($admin['id']==1){
        //用户总数
        $user_total = Db::name('user') -> count();
        //跑男总数
        $runman_total = Db::name('runmen') -> count();
        //订单总数
        $order_total = Db::name('order') -> count();
        //后台派单数
        $admin_order_total = Db::name('order') -> where(['uid' => 0]) -> count();
        //拒单数
        $refuse_order_total = Db::name('order') -> where(['status' => 4]) -> count();
        //消单数
        $cancel_order_total = Db::name('order') -> where(['status' => 3]) -> count();
        //跑男收入排序(降序)
        $runman_id = Db::name('runmen') -> column('id');
        foreach($runman_id as $v){
            $each_runman_income = Db::name('runmen_account')
                -> where(['rid' => $v])
                -> where(['type' => 1])
                -> sum('money');
            $rankings = Db::name('rankings') -> where(['rid' => $v]) -> find();
            if (!empty($rankings)){
                $data['income'] = $each_runman_income;
                Db::name('rankings') -> where(['rid' => $v]) -> update($data);
            }else{
                $data['rid'] = $v;
                $data['income'] = $each_runman_income;
                Db::name('rankings') -> insert($data);
            }
        }
            //$runman_income = array_multisort($each_runman_income,SORT_DESC);
            $runman_income = Db::name('rankings')
                -> alias('a')
                -> join('zs_runmen b','b.id = a.rid','LEFT')
                -> field('a.*,b.truename')
                -> order('income desc')
                -> paginate('15');
            //配送员上线率(今日)
            $today_start = strtotime(date('Y-m-d',time()));//今天零点时间戳
            $today_end = $today_start + (24*60*60);//今天二十四点时间戳
            $online_total = Db::name('runmen') -> where('prevtime','between time',[$today_start,$today_end]) -> count();
            if ($runman_total == 0){
                $percent_online = 0;
            }else {
                $percent_online = $online_total / $runman_total * 100;
            }
       }else{
           $groupas = Db::name('auth_group_access')->where(array('uid' => $admin['id']))->find(); //取出分组规则id
           $cityList = Db::name('auth_group')->where(array('id' => $groupas['group_id']))->find(); //取出分组表city
            //用户总数
            $user_total = Db::name('user')->where(array('city'=>$cityList['city']))->count();
            // $user_total = Db::name('user') -> count();
            //跑男总数
            $runman_total = Db::name('runmen')->where(array('city'=>$cityList['city']))->count(); 
            //订单总数
            $order_total = Db::name('order') ->where(array('city'=>$cityList['city']))->count(); 
            // $order_total = Db::name('order') -> count();
            //后台派单数--用户表
            $admin_order_total = Db::name('order') 
            -> where(array('city'=>$cityList['city']))
            -> where(['uid' => 0]) -> count();

            //拒单数【状态（9待支付0待接单1已接单2待确认5待评价6订单完成3取消订单4拒绝订单）】
            $refuse_order_total = Db::name('order') 
            -> where(['status' => 4]) 
            -> where(array('city'=>$cityList['city']))
            -> count();
            //消单数
            $cancel_order_total = Db::name('order') 
            -> where(['status' => 3])
            -> where(array('city'=>$cityList['city'])) 
            -> count();
            //跑男收入排序(降序)
            $runman_id = Db::name('runmen')
            -> where(array('city'=>$cityList['city']))
            -> column('id');
            foreach($runman_id as $v){
                $each_runman_income = Db::name('runmen_account')
                    -> where(['rid' => $v])
                    -> where(['type' => 1])
                    -> sum('money');
                $rankings = Db::name('rankings') -> where(['rid' => $v]) -> find();
                if (!empty($rankings)){
                    $data['income'] = $each_runman_income;
                    Db::name('rankings') -> where(['rid' => $v]) -> update($data);
                }else{
                    $data['rid'] = $v;
                    $data['income'] = $each_runman_income;
                    Db::name('rankings') -> insert($data);
                }
          }
            //$runman_income = array_multisort($each_runman_income,SORT_DESC);
            $runman_income = Db::name('rankings')
                -> alias('a')
                -> join('zs_runmen b','b.id = a.rid','LEFT')
                -> field('a.*,b.truename,b.city')
                -> where(array('b.city'=>$cityList['city']))
                -> order('income desc')
                -> paginate('15');
            //配送员上线率(今日)
            $today_start = strtotime(date('Y-m-d',time()));//今天零点时间戳
            $today_end = $today_start + (24*60*60);//今天二十四点时间戳
            $online_total = Db::name('runmen') 
            -> where('prevtime','between time',[$today_start,$today_end])
            -> where(array('city'=>$cityList['city']))
            -> count();
            if ($runman_total == 0){
                $percent_online = 0;
            }else {
                $percent_online = $online_total / $runman_total * 100;
            }
    }
        $this -> assign('user_total',$user_total);
        $this -> assign('runman_total',$runman_total);
        $this -> assign('order_total',$order_total);
        $this -> assign('admin_order_total',$admin_order_total);
        $this -> assign('refuse_order_total',$refuse_order_total);
        $this -> assign('cancel_order_total',$cancel_order_total);
        $this -> assign('runman_income',$runman_income);
        $this -> assign('page',$runman_income -> render());
        $this -> assign('count',$runman_income -> total());
        $this -> assign('percent_online',$percent_online);
        return view();
    }
}