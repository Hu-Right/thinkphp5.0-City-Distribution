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
class Message extends Frontend
{

    protected $layout = 'default';
    protected $noNeedLogin = ['msgListHead'];
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
     * 消息中心-头
     */
    public function msgListHead()
    {
        $this->view->assign('title', __('消息中心'));
        return $this->view->fetch();
    }

    /**
     * 消息中心-体
     */
    public function msgListBody()
    {
        //$user = $this->auth->getUser();
        $msgInfo = Db::name('notice')->where(['type'=>['in',[1,3]]])->page(1,10)->order('create_time desc')->select();
        /*echo '<pre>';
        print_r($msgInfo);
        exit;*/
        $this->view->assign('msgInfo', $msgInfo);
        $this->view->assign('title', __('消息中心'));
        return $this->view->fetch();
    }

    /**
     * 消息详情
     */
    public function msgShow()
    {
        $id = $this->request->get("id");
        $data = Db::name('notice')->where(['id'=>$id])->find();
        $this->view->assign('data', $data);
        $this->view->assign('title', __('消息详情'));
        return $this->view->fetch();
    }



    /**
     * 下拉刷新
     */
    public function pullRefresh()
    {
        $page = $this->request->post("page");

        $msgInfo = Db::name('notice')->where(['type'=>['in',[1,3]]])->page($page,10)->order('create_time desc')->select();

        foreach($msgInfo as $key => $row){
            $msgInfo[$key]['create_date'] = date("Y-m-d",$row['create_time']);
            $msgInfo[$key]['create_time'] = date("H:i:s",$row['create_time']);
        }
        return $msgInfo;
    }

}
