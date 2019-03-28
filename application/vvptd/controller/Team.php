<?php
// +----------------------------------------------------------------------
// | vv跑腿配送端
// | 战队
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
use think\Session;
class Team extends Vvptd{
    public function index(){
        return $this->fetch('tab-webview-main');
    }
    //
    //我的战队
    public function myTeam(){
        $where['team_men'] = ['like','%'.$this->rid.','.'%'];
        $myTeam = Db::name('team')->where($where)->find();
        if(!empty($myTeam)){
            $team_men = substr($myTeam['team_men'],0,strlen($myTeam['team_men'])-1);
            //战队中所有的人员
            $men = Db::name('runmen')
                ->field('zs_runmen.truename,zs_runmen.id,zs_runmen.mobile,zs_runmen_level.level,zs_runmen_level.name')
                ->join('zs_runmen_level','zs_runmen_level.id = zs_runmen.level')
                ->where('zs_runmen.id in ('.$team_men.')')
                ->select();
            if(!empty($men)){
                for($i=0;$i<count($men);$i++){
                    if($men[$i]['id'] == $myTeam['rid']){
                        $men[$i]['team_type'] = 1;
                    }else{
                        $men[$i]['team_type'] = 2;
                    }
                }
            }
            array_multisort(array_column($men,'team_type'),SORT_ASC,$men);
            for($i=0;$i<count($men);$i++){
                $start = substr($men[$i]['mobile'],'0','4');
                $end = substr($men[$i]['mobile'],'-4');
                $men[$i]['mobile']  = $start.'****'.$end;
            }
            $this->assign('men',$men);
            //战队本周数据
            $thiswork = Db::name('order')
                ->field('sum(start_end_distance) as distance,sum(money) as total_money')
                ->where(' (YEARWEEK(date_format(finish_time,"%Y-%m-%d")) = YEARWEEK(now())) and status=2 and rid in ('.$team_men.')')
                ->find();
            $thisworkcount = Db::name('order')
                ->where(' (YEARWEEK(date_format(finish_time,"%Y-%m-%d")) = YEARWEEK(now())) and status=2 and rid in ('.$team_men.')')
                ->count();
            if(!$thiswork['distance']){
                $thiswork['distance'] = 0;
            }
            if(!$thiswork['total_money']){
                $thiswork['total_money'] = 0.00;
            }
            //是不是群组区分展示战队消息
            if($myTeam['rid'] == $this->rid){
                $this->assign('is_qun',1);
            }else{
                $this->assign('is_qun',0);
            }
            $this->assign('order_num',$thisworkcount);
            $this->assign('distance',$thiswork['distance']);
            $this->assign('money',$thiswork['total_money']);
            $this->assign('myTeam',$myTeam);
            $this->assign('time',date('Y-m-d'));
            return $this->fetch('tab-webview-subpage-chat2');
        }
        return $this->fetch('tab-webview-subpage-chat');
    }
    //我的战报
    public function myWarBao(){
        //我的本周数据
        $thiswork = Db::name('order')
            ->field('sum(start_end_distance) as distance,sum(money) as total_money')
            ->where(' (YEARWEEK(date_format(finish_time,"%Y-%m-%d")) = YEARWEEK(now())) and status=2 and rid ='.$this->rid)
            ->find();
        $thisworkcount = Db::name('order')
            ->where(' (YEARWEEK(date_format(finish_time,"%Y-%m-%d")) = YEARWEEK(now())) and status=2 and rid ='.$this->rid)
            ->count();
        if(!$thiswork['distance']){
            $thiswork['distance'] = 0;
        }
        if(!$thiswork['total_money']){
            $thiswork['total_money'] = 0.00;
        }
        $thiswork['count'] = $thisworkcount;
        $this->assign('thiswork',$thiswork);
        //总数据
        $total = Db::name('order')
            ->field('sum(start_end_distance) as distance,sum(money) as total_money')
            ->where('status=2 and rid ='.$this->rid)
            ->find();
        $totalcount = Db::name('order')
            ->where('status=2 and rid ='.$this->rid)
            ->count();
        if(!$total['distance']){
            $total['distance'] = 0;
        }
        if(!$total['total_money']){
            $total['total_money'] = 0.00;
        }
        $total['count'] = $totalcount;
        $this->assign('total',$total);
        $this->assign('time',date('Y-m-d'));
        $runmen = $this->getRunMen();
        $str = '';
        for($i=0;$i<$runmen['score'];$i++){
            $str.='<img src="http://'.$_SERVER['HTTP_HOST'].'/assets/vvptd/member/img/xing.png" alt="" />';
        }
        if($runmen['score']<5){
            for($i=0;$i<5-$runmen['score'];$i++){
                $str.='<img src="http://'.$_SERVER['HTTP_HOST'].'/assets/vvptd/member/img/hui.png" alt="" />';
            }
        }
        $runmen['score'] = $str;
        $runmen['level_info'] = Db::name('runmen_level')->where('id',$runmen['level'])->find();
        $this->assign('run',$runmen);
        return $this->fetch('tab-webview-subpage-contact');
    }
    //战队列表
    public function teamList(){
        if($this->request->isAjax()) {
            $page = $this->request->post('page') ?: 1;
            $keyword = $this->request->post('keyword') ?: '';
            $where = array();
            if ($keyword) {
                $where['name'] = ['like', '%' . $keyword . '%'];
            }
            $data = Db::name('team')->where($where)->page($page)->limit(10)->select();
            $this->success('查询成功', '', $data);
        }
    }
    //申请加入战队
    public function applyTeam(){
        $team_id = $this->request->post('team_id','','intval')?:'';
        $apply_content = $this->request->post('apply_content')?:'';
        if($apply_content){
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$apply_content)){
                return $this->error(__('格式不正确'));
            }
        }
        $data['team_id'] = $team_id;
        $data['apply_content'] = $apply_content;
        $data['add_time'] = time();
        $data['rid'] = $this->rid;

        $where['team_men'] = ['like','%'.$this->rid.','.'%'];
        $myTeam = Db::name('team')->where($where)->find();
        if($myTeam){
            $this->error('已经加入一个战队');
        }
        $res = Db::name('team_rid')->insert($data);
        if($res){
            $this->success('申请成功,请耐心等待审核');
        }else{
            $this->error('申请失败');
        }
    }
    //同意加入战队
    public function agreeJoinTeam(){
        if($this->request->isAjax()) {
            $team_id = $this->request->post('team_id') ?: '';//战队ID
            $rid = $this->request->post('rid') ?: '';//加入人ID
            //检测是不是队长操作
            $data = Db::name('team')->where(array('rid' => $this->rid,'id' => $team_id))->find();
           // print_r($data);die;
            if (empty($data)) {
                $this->error('不是队长没办法操作');
            }
            $teamMen = explode(',', substr($data['team_men'], 0, strlen($data['team_men']) - 1));
            if (in_array($rid,$teamMen)) {
                $this->error('您已经是该战队人员，无需再次申请');
            }
            $teamMen[] = $rid;
            $rid_str = implode(',', $teamMen) . ',';
            $update['team_men'] = $rid_str;
            $update['num'] = $data['num']+1;
            // 启动事务
            Db::startTrans();
            try {
                //同意加入战队
                Db::name('team')->where(array('rid' => $this->rid, 'id' => $team_id))->update($update);
                //修改申请状态
                Db::name('team_rid')->where(array('team_id'=>$team_id,'rid'=>$rid))->update(['status'=>2]);
                //给申请人通知一条消息
                $this->sendNotice('您申请加入的战队同意了您的申请',$rid);
                // 提交事务
                Db::commit();
                $this->success('操作成功');
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error('操作成功','',$e->getMessage());
            }
        }
        $id = $this->request->get('id');
        $teamid = Db::name('team_rid')->where('id',$id)->find();
        $data = Db::name('runmen')->where('id',$teamid['rid'])->find();
        $str = '';
        for($i=0;$i<$data['score'];$i++){
            $str.='<img src="http://'.$_SERVER['HTTP_HOST'].'/assets/vvptd/member/img/xing.png" alt="" />';
        }
        if($data['score']<5){
            for($i=0;$i<5-$data['score'];$i++){
                $str.='<img src="http://'.$_SERVER['HTTP_HOST'].'/assets/vvptd/member/img/hui.png" alt="" />';
            }
        }
        $data['score'] = $str;
        $data['level_info'] = Db::name('runmen_level')->where('id',$data['level'])->find();
        $this->assign('team',$teamid);
        $this->assign('data',$data);
        return $this->fetch('add-zhandui');
    }
    //拒绝加入战队
    public function refuseJoin(){
        $rid = $this->request->post('rid')?:'';//加入人ID
        $team_id = $this->request->post('team_id')?:'';//加入人ID
        Db::startTrans();
        try{
           Db::name('team_rid')->where(array('team_id'=>$team_id,'rid'=>$rid))->update(['status'=>3]);
            //给申请人通知一条消息
            $this->sendNotice('您申请加入的战队拒绝了您的申请',$rid);
            // 提交事务
            Db::commit();
            $this->success('操作成功');
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作成功');
        }
    }

    //添加战队
    public function addTeam(){
        if($this->request->isAjax()) {
            $float = $this->checkCreateConditions();
            if($float == 0){
                $this->error('没资格创建战队');
            }
            $name = $this->request->post('name') ?: '';
            if ($name) {
                if (preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/", $name)) {
                    return $this->error(__('格式不正确'));
                }
            }
            $data['name'] = $name;
            $data['rid'] = $this->rid;
            $data['num'] = 1;
            $data['team_men'] = $this->rid . ',';
            $data['add_time'] = time();
            $team = Db::name('team')->where('rid',$this->rid)->find();
            if($team){
                $this->error('已经创建过战队');
            }
            $res = Db::name('team')->insert($data);
            if ($res) {
                $this->success('创建成功');
            } else {
                $this->error('创建失败');
            }
        }
        return $this->fetch('VVzdcj');
    }
    //检测是否有资格创建战队
    public function  checkCreateConditions(){
        $float = 0;
        //注册3个月
        $create_time = $this->getRunMen('create_time');
        $month = date('m');
        $day = date('d');
        $reg_month = date('m',$create_time);
        $reg_day = date('d',$create_time);
        if($month-$reg_month<3){
            $float = 0;
        }else{
            if($day<$reg_day){
                $float = 0;
            }else{
                $float = 1;
            }
        }
        //订单累计
        $where['rid'] = $this->rid;
        $where['status'] = 2;
        $count = Db::name('order')->where($where)->count();
        if($count>500){
            $float = 1;
        }else{
            $float = 0;
        }
        //好评率 暂时不考虑

        return $float;
    }
    //战队排行榜
    public function teamRankingList(){
        if($this->request->isAjax()) {
            $type = $this->request->post('type') ?: '1';
            $status = $this->request->post('status') ?: '1';
            if ($type == 1) {
                //日
                if ($status == 1) {
                    //单数
                    $order = ['day_order_num','id'=>'desc'];
                } elseif ($status == 1) {
                    //里数
                    $order = ['day_licheng_num','id'=>'desc'];
                } else {
                    //金额
                    $order =['day_money','id'=>'desc'];
                }

            } elseif ($type == 2) {
                //周
                if ($status == 1) {
                    //单数
                    $order = ['day_order_num','id'=>'desc'];
                } elseif ($status == 1) {
                    //里数
                    $order =['day_licheng_num','id'=>'desc'];
                } else {
                    //金额
                    $order = ['day_money','id'=>'desc'];
                }
            } else {
                //月
                if ($status == 1) {
                    //单数
                    $order = ['day_order_num','id'=>'desc'];
                } elseif ($status == 1) {
                    //里数
                    $order = ['day_licheng_num','id'=>'desc'];
                } else {
                    //金额
                    $order = ['day_money','id'=>'desc'];

                }
            }

            $data = Db::name('team')->order($order)->limit(30)->select();
            for($i=0;$i<count($data);$i++){
                $runmen = Db::name('runmen')->field('avatar,truename')->where('id',$data[$i]['rid'])->find();
                $data[$i]['username'] = $runmen['truename'];
                $data[$i]['avatar'] = $runmen['avatar'];
            }
            $this->success('查询成功', '', $data);
        }
        $this->assign('time',date('Y-m-d'));
        return $this->fetch('tab-webview-subpage-about');
    }
    //个人榜
    public function runmenRankingList(){
        $type = $this->request->post('type') ?: '1';
        $status = $this->request->post('status') ?: '1';
        if ($type == 1) {
            //日
            if ($status == 1) {
                //单数
                $order = ['day_order_num','id'=>'desc'];
            } elseif ($status == 1) {
                //里数
                $order = ['day_licheng_num','id'=>'desc'];
            } else {
                //金额
                $order =['day_money','id'=>'desc'];
            }

        } elseif ($type == 2) {
            //周
            if ($status == 1) {
                //单数
                $order = ['day_order_num','id'=>'desc'];
            } elseif ($status == 1) {
                //里数
                $order =['day_licheng_num','id'=>'desc'];
            } else {
                //金额
                $order = ['day_money','id'=>'desc'];
            }
        } else {
            //月
            if ($status == 1) {
                //单数
                $order = ['day_order_num','id'=>'desc'];
            } elseif ($status == 1) {
                //里数
                $order = ['day_licheng_num','id'=>'desc'];
            } else {
                //金额
                $order = ['day_money','id'=>'desc'];
            }
        }
        $data = Db::name('runmen')->order($order)->limit(30)->select();
        $this->success('查询成功','',$data);
    }
    //战队介绍
    public function teamJieshao(){
        return $this->fetch('VVzdjsl');
    }
    //战队创建条件
    public function teamCjgz(){
        return $this->fetch('VVzdcjgz');
    }
    //战队消息
    public function teamnew(){
        if($this->request->isAjax()){
            $page = $this->request->post('page','','intval')?:1;
            $myTeam = Db::name('team')->where('rid',$this->rid)->find();
            $data = Db::name('team_rid')
                ->field('zs_runmen.truename,zs_team_rid.*')
                ->join('zs_runmen','zs_runmen.id = zs_team_rid.rid')
                ->where('zs_team_rid.team_id',$myTeam['id'])
                ->page($page)
                ->limit(10)
                ->order('zs_team_rid.status asc ,zs_team_rid.add_time')
                ->select();
            for($i=0;$i<count($data);$i++){
                $data[$i]['add_time'] = date('Y-m-d',$data[$i]['add_time']);
            }
            $this->success('查询成功','',$data);
        }
        return $this->fetch();
    }
    //退出战队
    public function tuichuTeam(){
        $where['team_men'] = ['like','%'.$this->rid.','.'%'];
        $myTeam = Db::name('team')->where($where)->find();
        if(empty($myTeam)){
            $this->error('已退出战队');
        }
        if($myTeam['rid'] == $this->rid){
            $this->error('队长不能退出');
        }
        $teamMen = explode(',',substr($myTeam['team_men'],0,strlen($myTeam['team_men'])-1));
        if(in_array($this->rid,$teamMen)){
            for($i=0;$i<count($teamMen);$i++){
                if($teamMen[$i] == $this->rid){
                    unset($teamMen[$i]);
                }
            }
            $data['team_men'] = implode(',',$teamMen).',';
            $data['num'] = $myTeam['num']-1;
            $res = Db::name('team')->where('id',$myTeam['id'])->update($data);
            if($res === false){
                $this->error('退出失败');
            }else{
                $this->success('退出成功');
            }
        }
    }
}