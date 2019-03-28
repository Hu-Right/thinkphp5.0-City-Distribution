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
 * 会员账单
 */
class Bill extends Frontend
{

    protected $layout = 'default';
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 会员账单
     */
    public function index()
    {
        $isLogin = $this->auth->isLogin();
        if($isLogin == false){
            $userInfo = '';
        }else{
            $userInfo = $this->auth->getUser();
            $uid = $userInfo['id'];
            $billInfo = Db::name('bill')
                ->alias('b')
                ->join('__SERVICE_TYPE__ s','s.id=b.order_type','LEFT')
                ->where(['user_id'=>$uid])
                ->field('b.*,s.service_name')
                ->select();
            $this->view->assign('billInfo', $billInfo);
        }
        /*echo '<pre>';
        print_r($userInfo);
        exit;*/
        $this->view->assign('userInfo', $userInfo);
        $this->view->assign('title', __('账户明细'));
        return $this->view->fetch();
    }

}
