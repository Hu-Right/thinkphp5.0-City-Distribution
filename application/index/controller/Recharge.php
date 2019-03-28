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
 * 账户充值
 */
class Recharge extends Frontend
{

    protected $layout = 'default';
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 账户充值
     */
    public function index()
    {
        $isLogin = $this->auth->isLogin();
        if($isLogin == false){
            $userInfo = '';
        }else{
            $userInfo = $this->auth->getUser();
            $paymentarr = explode(",",$userInfo['paymentsort']);
            $paymentsort = [];
            foreach($paymentarr as $key=>$pay){
                if(strpos($pay,'支付宝') !== false){
                    $paymentsort[] = ['name'=>'支付宝','icon'=>'icon-zhifubaozhifu'];
                }elseif(strpos($pay,'微信') !== false){
                    $paymentsort[] = ['name'=>'微信','icon'=>'icon-weixinzhifu'];
                }
            }
            $this->view->assign('paymentsort', $paymentsort);
        }
        $this->view->assign('userInfo', $userInfo);
        $this->view->assign('title', __('账户充值'));
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

}
