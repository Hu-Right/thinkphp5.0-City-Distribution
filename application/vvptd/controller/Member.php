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
use think\Session;
class Member extends Vvptd{
    //获取跑男信息
    public function index(){
        //更新跑男级别
        $this->updateRunMenLevel();
        $data = Db::name('runmen')->where('id',$this->rid)->find();
        if($data['avatar']){
            $data['avatar'] = 'http://'.$_SERVER['HTTP_HOST'].$data['avatar'];
        }
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
        //今日数据
        $tody = $this->todyRunMenData();
        $this->assign('tody',$tody);
        $this->assign('data',$data);
        return $this->fetch();
    }
    //今日跑男首页数据
    public function todyRunMenData(){
        //今日收入
        $data =Db::name('order')
            ->field('sum(money) as tody_money,sum(start_end_distance) as tody_distance')
            ->where('FROM_UNIXTIME(finish_time,"%Y-%m-%d") = "'.date('Y-m-d').'"  and status in(1,2,5,6) and rid='.$this->rid)->select();
        //今日完成订单数
        $todycount =Db::name('order')
            ->where('FROM_UNIXTIME(finish_time,"%Y-%m-%d") = "'.date('Y-m-d').'"  and status in(1,2,5,6) and rid='.$this->rid)->count();
        //总订单数
        $totalcount =Db::name('order')
            ->where('status in(2,5,6) and rid='.$this->rid)->count();
        $tody['tody_money'] = $data[0]['tody_money'];
        $tody['tody_distance'] = $data[0]['tody_distance'];
        $tody['todyordercount'] = $todycount;
        $tody['totalordercount'] = $totalcount;
        return $tody;
    }
    //完善资料
    public function perfectInfo(){
        if($this->request->isAjax()) {
            $data = $this->request->post();
            $arr = $data;
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$data['truename'])){
                return $this->error(__('格式不正确'));
            }
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$data['mobile'])){
                return $this->error(__('格式不正确'));
            }
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$data['city'])){
                return $this->error(__('格式不正确'));
            }
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$data['gender'])){
                return $this->error(__('格式不正确'));
            }
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$data['id_number'])){
                return $this->error(__('格式不正确'));
            }
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$data['adress'])){
                return $this->error(__('格式不正确'));
            }
            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$data['urgent_phone'])){
                return $this->error(__('格式不正确'));
            }
            //验证规则
            $rule = [
                'truename' => 'require',
                'mobile' => 'require',
                'city' => 'require',
                'gender' => 'require',
                'id_number'=>'require',
                'adress'=>'require',
                'urgent_phone'=>'require',
                'photo'=>'require',
                'photo_hand'=>'require',
                'id_card_pos'=>'require',
                'id_card_con'=>'require'
            ];
            //验证信息
            $msg = [
                'truename.require' => __('真实姓名不能为空'),
                'mobile.require' => __('mobile'),
                'city.require' => __('city'),
                'gender.require' => __('性别不能为空'),
                'id_number.require' => __('身份证号不能为空'),
                'adress.require' => __('现居住地址不能为空'),
                'urgent_phone.require' => __('紧急联系人不能为空'),
                'photo.require' => __('个人形象不能为空'),
                'photo_hand.require' => __('手持身份证不能为空'),
                'id_card_pos.require' => __('身份证正面不能为空'),
                'id_card_con.require' => __('身份证反面不能为空'),
            ];
            //验证数组
            $data = [
                'truename' =>$data['truename'],
                'mobile' => $data['mobile'],
                'city' => $data['city'],
                'gender' =>$data['gender'],
                'id_number'=>$data['id_number'],
                'adress'=>$data['adress'],
                'urgent_phone'=>$data['urgent_phone'],
                'photo'=>$data['photo'],
                'photo_hand'=>$data['photo_hand'],
                'id_card_pos'=>$data['id_card_pos'],
                'id_card_con'=>$data['id_card_con']
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                return $this->error(__($validate->getError()));
            }

            //检测资料状态
//            if ($this->checkMemberStatus() == true) {
//                return $this->error(__('存在正在审核的资料，不能重复提交'));
//            }
            $arr['status'] = 2;
            $res = Db::name('runmen')->where('id',$this->rid)->update($arr);
            if ($res) {
                return $this->success('操作成功');
            } else {
                return $this->error('操作失败');
            }
        }
        $data = Db::name('runmen')->where('id',$this->rid)->find();
        $this->assign('data',$data);
        return $this->fetch('perfectInfo');
    }
    //修改头像
    public function changeHeadimg(){
        $img = $this->request->post('img');
        if(!$img){
            $this->error('图片不能为空');
        }
        $data['avatar'] = $img;
        $res = Db::name('runmen')->where('id',$this->rid)->update($data);
        if($res === false){
            $this->error('修改失败');
        }else{
            $url = 'http://'.$_SERVER['HTTP_HOST'].$img;
            $this->success('修改成功','',$url);
        }
    }
    //等级管理
    public function manageLevel(){
        $data = Db::name('runmen')->where('id',$this->rid)->find();
        //等级信息
        $level = Db::name('runmen_level')->where('id',$data['level'])->find();
        //经验值增长近30天记录
        $where = "( add_time < DATE_SUB(NOW(),INTERVAL 30 DAY) ) and runmen_id = ".$this->rid;
        $recode = Db::name('runmen_level_experience_recode')->where($where)->select();
        $arr['level'] = $level;
        $this->assign('recode',$recode);
        $this->assign('data',$data);
        $this->assign('arr',$arr);
        return $this->fetch('manageLevel');
    }
    //检测更新跑男级别
    public function updateRunMenLevel(){
        // 启动事务
        Db::startTrans();
        try{
            $data = Db::name('runmen')->where('id',$this->rid)->find();
            $level = Db::name('runmen_level')->where('upgrading_condit','<=',$data['experience_num'])->order('level desc')->find();
            if(!empty($level)) {
                Db::name('runmen')->where('id', $this->rid)->update(array('level' => $level['level']));
            }
//            Db::name('runmen')->where('id',$this->rid)->update(array('level'=>$level['level']));
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }

    }
    //添加跑男经验记录
    //data = array(
    //  'detail'=>'描述'，
    //  'num'=>'数量'，
    //  'type'=>'类型 1增长  0减'，
    //);
    public function addRunMenRecode($data){
        // 启动事务
        Db::startTrans();
        try{
            $data['add_time'] = time();
            $data['runmen_id'] = $this->rid;
            Db::name('runmen_level_experience_recode')->insert($data);
            //更改会员累计经验值
            Db::name('runmen')->where('id',$this->rid)->setInc('experience_num',$data['num']);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
    }
    //任务中心
    public function getTaskList(){
        $this->checkRunTaskStatus();
        return $this->fetch('task_head');
    }
    public function task_main(){
        if($this->request->isAjax()){
            $page = $this->request->post('page')?:1;

            $task = Db::name('task')->where('status',1)->page($page)->limit(10)->order('id desc')->select();

            for($i=0;$i<count($task);$i++){
                //检测任务是否可接
                $task[$i]['status'] = $this->checkTask($task[$i]['id'],$task[$i]['claim_type']);
            }

            array_multisort(array_column($task,'status'),SORT_ASC,$task);
            $this->success('查询成功','',$task);
        }
        return $this->fetch();
    }
    //领取任务
    public function getLingTask(){
        $tid = $this->request->post('tid');
        $data['rid'] = $this->rid;
        $data['task_id'] = (int)$tid;
        $data['add_time'] = time();
        //检测是否存在相关没有完成的任务
        $arr = Db::name('runmen_task')->where(array('rid'=>$this->rid,'task_id'=>$data['task_id'],'status'=>1))->find();
        if(!empty($arr)){
            $this->error('相同未完成的任务只能领取一次');
        }
        $res = Db::name('runmen_task')->insert($data);
        if($res){
            $this->success(__('领取成功'));
        }else{
            $this->error(__('领取失败'));
        }
    }
    //跑男领取的任务
    public function getRunMenTask(){
        return $this->fetch('run_task_head');
    }
    //跑男领取的任务
    public function run_task_main(){
        if($this->request->isAjax()) {
            $page = $this->request->post('page') ?: 1;
            $data = Db::name('runmen_task')->field('zs_runmen_task.status,zs_task.title,zs_task.reward_num')->join('zs_task', 'zs_task.id = zs_runmen_task.task_id')->where('zs_runmen_task.rid', $this->rid)->page($page)->limit(10)->select();
            $this->success('查询成功', '', $data);
        }
        return $this->fetch();
    }
    //检测任务是否可接
    //任务ID task_id
    //任务类型 $claim_type
    public function checkTask($task_id,$claim_type)
    {
        if ($claim_type == 1) {
            //固定任务 只能领取一次,查询当前跑男是否领取过
            $where['rid'] = $this->rid;
            $where['task_id'] = $task_id;
            $taskStatus = Db::name('runmen_task')->where($where)->find();
            if (empty($taskStatus)) {
                return 1;//当前用户没有接收过该任务 可以接，状态为  未领取
            } else {
                return 2;//已领取
            }
        } else {
            //每月领取一次
            $datas = Db::name('runmen_task')
                ->where('status = 1 and rid=' . $this->rid.' and task_id='.$task_id)->find();
            if(!empty($datas)){
                return 2;//已领取
            }else{
                $data = Db::name('runmen_task')
                    ->where('FROM_UNIXTIME(add_time,"%Y-%m") = "'.date('Y-m').'"  and rid='.$this->rid.' and task_id='.$task_id)->find();
                if (empty($data)) {
                    return 1;//未领取
                } else {
                    return 2;//已领取
                }
            }

        }
    }
    //账户余额
    public function myAccount(){
        $money = $this->getRunMen('money');
        $lj_money = $this->getRunMen('lj_money');
        //今日收入
        $data =Db::name('order')
            ->field('SUM(money) as tody_money ')
            ->where('FROM_UNIXTIME(finish_time,"%Y-%m-%d") = "'.date('Y-m-d').'" and status in(1,2,5,6) and rid='.$this->rid)->find();
        //今日收入
//        $data =Db::name('order')
//            ->field('sum(money) as tody_money,sum(start_end_distance) as tody_distance')
//            ->where('FROM_UNIXTIME(finish_time,"%Y-%m-%d") = "'.date('Y-m-d').'"  and status in(1,2,5,6) and rid='.$this->rid)->select();
        $this->assign('money',$money);
        $this->assign('lj_money',$lj_money);
        if(!$data['tody_money']){
            $data['tody_money'] = 0.00;
        }
        $this->assign('todymoney',$data['tody_money']);
        return $this->fetch('account_head');
    }
    //账户变更记录
    public function getRunMenAccount(){
        if($this->request->isAjax()) {
            $data = Db::name('runmen_account')->where('rid', $this->rid)->order('add_time desc')->limit(100)->select();
            for($i=0;$i<count($data);$i++){
                $data[$i]['add_time'] = date('Y-m-d H:i:s',$data[$i]['add_time']);
            }
            $this->success('查询成功', '', $data);
        }
        return $this->fetch('account_main');
    }
    //账户变更记录详情
    public function bill_details(){
        $id = $this->request->get('id');
        $data = Db::name('runmen_account')->where('id',(int)$id)->find();
        $this->assign('data',$data);
        return $this->fetch();
    }
    //余额提现
    public function putForward(){
        if($this->request->isAjax()){
            $arr = $this->request->post();
            //验证规则
            $rule = [
                'mobile' => 'require|regex:/^1[3-9]\d{9}$/',
                'money' => 'require|regex:/^\d+$/',
                'reflect_id' => 'require|regex:/^\d+$/',
            ];
            //验证信息
            $msg = [
                'mobile.require' => __('提现手机号不能为空'),
                'money.require' => __('提现金额不能为空'),
                'reflect_id.require' => __('提现账户不能为空'),
            ];
            //验证数组
            $data = [
                'mobile' =>$arr['mobile'],
                'money' => $arr['money'],
                'reflect_id' => $arr['account']
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);//验证结果
            if (!$result) {
                return $this->error(__($validate->getError()));
            }
            if($data['money']%50>0){
                $this->error('提现金额只能为50的整数');
            }
            $money = $this->getRunMen('money');
            if($data['money']>$money){
                $this->error('余额不足');
            }
            $data['rid'] =$this->rid;
            $data['add_time'] = time();
            // 启动事务
            Db::startTrans();
            try{
                //提现操作
                Db::name('runmen_putforward')->insert($data);
                //扣除余额 提现失败加回来
                Db::name('runmen')->where('id',$this->rid)->setDec('money',$data['money']);

                //生成账户变更记录
                $datas['type'] = 2;
                $datas['money'] = $data['money'];
                $datas['detail'] = '提现扣除余额';
                $datas['rid'] = $this->rid;
                $datas['add_time'] = time();
                $this->insertRunMenRecode($datas);
                // 提交事务
                Db::commit();
                $this->success('申请提现成功');
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error('申请提现成功');
            }
        }
        $data = Db::name('set_reflect_ptd')->where('runuser_id',$this->rid)->select();
        for($i=0;$i<count($data);$i++){
            $start = substr($data[$i]['bank_card'],'0','4');
            $end = substr($data[$i]['bank_card'],'-4');
            $data[$i]['bank_card']  = $start.'****'.$end;
        }
        $this->assign('data',$data);
        $this->assign('money',$this->getRunMen('money'));
        return $this->fetch('cash');
    }
    //消息中心
    public function news(){
        return $this->fetch();
    }
    //系统消息
    public function news_main1(){
        if($this->request->isAjax()){
            $page = $this->request->post('page')?:1;
            $data = Db::name('runmen_notice')->where('rid',$this->rid)->page($page)->limit(10)->order('add_time desc')->select();
            for($i=0;$i<count($data);$i++){
                $data[$i]['add_time'] = date('Y-m-d H:i:s',$data[$i]['add_time']);
            }
            $this->success('查询成功','',$data);
        }
        return $this->fetch();
    }
    //订单消息
    public function news_main3(){
        if($this->request->isAjax()){
            $page = $this->request->post('page')?:1;
            $data = Db::name('order_notice')
                ->field('zs_user.avatar,zs_service_type.service_name,zs_order_notice.*')
                ->join('zs_user','zs_user.id = zs_order_notice.user_id')
                ->join('zs_order','zs_order.id = zs_order_notice.order_id')
                ->join('zs_service_type','zs_service_type.id = zs_order.type','LEFT')
                ->where('zs_order_notice.rid',$this->rid)
                ->page($page)
                ->limit(10)
                ->order('add_time desc')
                ->select();
            for($i=0;$i<count($data);$i++){
                $data[$i]['add_time'] = date('Y-m-d H:i:s',$data[$i]['add_time']);
            }
            $this->success('查询成功','',$data);
        }
        return $this->fetch();
    }
    //评价消息
    public function news_main2(){
        if($this->request->isAjax()){
            $page = $this->request->post('page')?:1;
            $data = Db::name('evaluate')
                ->field('zs_user.avatar,zs_user.nickname,zs_evaluate.*,zs_service_type.service_name,zs_order.order_num')
                ->join('zs_user','zs_user.id = zs_evaluate.user_id')
                ->join('zs_order','zs_order.id = zs_evaluate.order_id')
                ->join('zs_service_type','zs_service_type.id = zs_order.type','LEFT')
                ->where('zs_evaluate.rid',$this->rid)
                ->page($page)
                ->limit(10)
                ->order('create_time desc')
                ->select();
            for($i=0;$i<count($data);$i++){
                $data[$i]['create_time'] = date('Y-m-d H:i:s',$data[$i]['create_time']);
            }
            $this->success('查询成功','',$data);
        }
        return $this->fetch();
    }
    //个人中心
    public function personal(){
      	$data = Db::name('runmen')->where('id',$this->rid)->find();
        $level = Db::name('runmen_level')->where('id',$data['level'])->find();
        $data['level'] = $level;
        $this->assign('data',$data);
        return $this->fetch();
    }
    //提现记录表
    public function cash_details_head(){
        return $this->fetch();
    }
    //提现记录
    public function cash_details_main(){
        if($this->request->isAjax()){
            $page = $this->request->post('page')?:'1';
//            $data = Db::name('set_reflect_ptd')->where('runuser_id',$this->rid)->select();
            $data = Db::name('runmen_putforward')
                ->field('zs_set_reflect_ptd.bank_name,zs_set_reflect_ptd.bank_card,zs_runmen_putforward.add_time,zs_runmen_putforward.money,zs_runmen_putforward.status')
                ->join('zs_set_reflect_ptd','zs_set_reflect_ptd.id = zs_runmen_putforward.reflect_id')
                ->where('zs_runmen_putforward.rid',$this->rid)
                ->page($page)->limit(10)->order('zs_runmen_putforward.add_time desc')->select();
            for($i=0;$i<count($data);$i++){
                $start = substr($data[$i]['bank_card'],'0','4');
                $end = substr($data[$i]['bank_card'],'-4');
                $data[$i]['bank_card']  = $start.'****'.$end;
                $data[$i]['add_time'] = date('Y-m-d H:i:s',$data[$i]['add_time']);
            }
            $this->success('查询成功','',$data);
        }
        return $this->fetch();
    }
    //评价管理
    public function pingjia(){
        return $this->fetch('pingjiaguanli');
    }
    //回复评价
    public function huifuPingjia(){
        $reply = $this->request->post('reply');
        $id = $this->request->post('id','','intval');
        if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$reply)){
            return $this->error(__('格式不正确'));
        }
        $where['id'] = $id;
        $where['rid'] = $this->rid;
        $data['reply'] = $reply;
        $data['reply_time'] = time();
        $res = Db::name('evaluate')->where($where)->update($data);
        if($res === false){
            $this->error('回复失败');
        }else{
            $this->success('回复成功');
        }
    }
    //退出登录
    public function logout(){
        session(null);
        $this->success('退出成功');
    }
    //分享
    public function shareMember(){
        return $this->fetch('member-reward');
    }
    //热力图
    public function hotMap(){
        return $this->fetch('heatMap');
    }
    //跑腿教程
    public function vvptHelp(){
        $data = Db::name('help_document')->where('id',3)->find();
        $this->assign('data',$data);
        return $this->fetch('pro');
    }
    //跑腿教程
    public function guize(){
        return $this->fetch('lvguize');
    }
    /**
     * 上传文件
     *
     * @param File $file 文件流
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = Config::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        //
        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);
        if ($splInfo) {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            $params = array(
                'admin_id'    => 0,
                'user_id'     => (int)$this->rid,
                'filesize'    => $fileInfo['size'],
                'imagewidth'  => $imagewidth,
                'imageheight' => $imageheight,
                'imagetype'   => $suffix,
                'imageframes' => 0,
                'mimetype'    => $fileInfo['type'],
                'url'         => $uploadDir . $splInfo->getSaveName(),
                'uploadtime'  => time(),
                'storage'     => 'local',
                'sha1'        => $sha1,
            );
            $attachment = model("attachment");
            $attachment->data(array_filter($params));
            $attachment->save();
            \think\Hook::listen("upload_after", $attachment);
            $ss = $this->request->post('type');
            if($ss){
                echo $uploadDir . $splInfo->getSaveName();exit;
            }else {
                $this->success(__('Upload successful'), '', [
                    'url' => $uploadDir . $splInfo->getSaveName()
                ]);
            }
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }
}