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
use payment\alipay\AopClient;
use payment\alipay\AlipayTradeAppPayRequest;

/**
 * 支付中心
 */
class Payment extends Frontend
{

    protected $layout = 'default';
    protected $noNeedLogin = [];
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
     * 空的请求
     * @param $name
     * @return mixed
     */
    public function _empty($name)
    {
        Hook::listen("user_request_empty", $name);
        return $this->view->fetch('user/' . $name);
    }

    /*--------------------------------------支付宝-开始------------------------------------*/
    public function alipay(){
        // 获取支付金额,以及类型
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $amount=$_POST['total'];
            $tip=' - ' . $_POST['tip'];
        }else{
            $amount=$_GET['total'];
            $tip=' - ' . $_GET['tip'];
        }

        $total = floatval($amount);
        if(!$total){
            $total = 1;
        }

        $aop = new AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';
        $aop->appId = '2018121362522726';//VV跑腿

        $aop->rsaPrivateKey = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCgTggQRA/bzGL5E4l9ZTRGhIH6Z1yPEPNQJyLfICm3hj3VS9JB1nWTYR6dkPbzSKnt1cKp7jbAvyxAGGLOca7Py/eC2WiqlD+WXUq+t55Uj4PnVORuGSEH8EX2YwIIVvVRvAFx4wO3dVvU0gF75kdQzs5uUtkbX4+aQNrsh3giNiFUPbAIIiL1roGVGy+V7WTGMqv3XfNit8fKrWk/0XcjZMyNf1/lB/8aoV4La93wvEv86EYWuCes0Srj/rDwvNEBZ09EpfVKjTyw2DvSkGqmYxly+lkdK6qBkBhgOn/tlrw7tB+eWVOTG83BLppw3alVz8kOi0GuSKdq/kTKsb2LAgMBAAECggEAM0D/vwOj9mjg9DZU7WzgS/OuAzvtEikfQm9g7OpBrFYViw8VCMvjB94zhbmF279N5adE+EQb6YC2S0AAx3T9qZ7TYljU6EVCF3d4oIXg77R7Pgmch78tGnauR9rcGYKEKT/tTlaBJ7U0SVVj+BNmleWphwiiNehV58H4vUuLE5StXl4VpSpKr9i4fxgKpzQjmxSIIXNAO4OnwWPbom/LOVpMPr3P7HQ97CJ8mf0BFGtAEBWnq5JNrY7IOOju/ZHZnIWNFEzSr5wWEwbMbERAD588amLF8DAkUpzyCt30Ek2UZIs6y+EbEUV4JNnKiZxrUMxDbNkEAt5pFCJl/9p94QKBgQDUi+zrFohpmrkILJZzzSVBOdDnyLEW+0qybEJjJWDpaPG3P1N2CibQzNoR699iuoF535cvinIYXPMnWOIzgzxIum7KrPzcmFjQsF2TsdqIDtIUXoDdjbco6F9ZfWyMp3afwFjZBzyDcn+4YNLfAT4U14RtzimAbRPGVVgC/RgwWQKBgQDBE+wl4m1tF/d/hOefeVXsF3YUvjX9uSkqIBcm9sv3lsGxn9r0NkM1F42r/5GfuVdUdMVscc+7meXoq1OEgfGHAIlSTt8VqTQVarvnTWSWbm1rGTmInv6USBOvX1KX9Yt3xH5Y5xA3uZZfe3T6/1XTUu2lw1hi0R7XCQwyL7kAgwKBgQClBXrNz7Hb5EATA6NQh4+MQ9pZi21LPZHyU7F7jvLeZhd9whIHzLv0U4hgb7UBz3JlcF7Oj3wkRE6ZVx5RBmyQvwb0Hzk4AKS8aqJM4MKd7nvXSsRcwAHcJgaZ0ZKs5fxo7gtNfZvTJtvZCHvQnwNXZTkxk+aPCqFW1L4/m8fjCQKBgDV4tP/Q84kxCAQy5IrP6bHW1YbtHrrD2tilxoOt+dL5126/3L2hgX9kpIGr58Kaa8siA8MCygsklf2X5StfaWqABYfb/ABdueTsiFmIn4Dh2D++3qYtkkeypnD9LzySbiufKXapl143caPD5yPULwq1fsdXkFTdoXLgOJZ1jQDNAoGAVbnBLoWcn7ncvxyc19rVw2FxyQyWInxzqwTw/mYbQy3DbirMEJtKzYSYx1SxS2kXj3Cig8slCRpLeKFMxCK4fTDOcH5A0V/vzrD7ZuYNkZLrHPad/6okNKLNRzoQ/dOv0gEkFgqgOcXU5AbddTR7LsFQihYySzYj1NiHV0EHVnU=';
        $aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoE4IEEQP28xi+ROJfWU0RoSB+mdcjxDzUCci3yApt4Y91UvSQdZ1k2EenZD280ip7dXCqe42wL8sQBhiznGuz8v3gtloqpQ/ll1KvreeVI+D51TkbhkhB/BF9mMCCFb1UbwBceMDt3Vb1NIBe+ZHUM7OblLZG1+PmkDa7Id4IjYhVD2wCCIi9a6BlRsvle1kxjKr913zYrfHyq1pP9F3I2TMjX9f5Qf/GqFeC2vd8LxL/OhGFrgnrNEq4/6w8LzRAWdPRKX1So08sNg70pBqpmMZcvpZHSuqgZAYYDp/7Za8O7QfnllTkxvNwS6acN2pVc/JDotBrkinav5EyrG9iwIDAQAB';//VV跑腿

        $aop->apiVersion = '1.0';
        $aop->postCharset='utf-8';
        $aop->format='json';
        $aop->signType = 'RSA2';
        //生成随机订单号
        $date=date("YmdHis");
        $arr=range(1000,9999);
        shuffle($arr);
        $request = new AlipayTradeAppPayRequest();
        //异步地址传值方式
        $request->setNotifyUrl('http://' . $_SERVER['SERVER_NAME']);//必须参数，目前没用
        $request->setBizContent("{\"out_trade_no\":\"".$date.$arr[0]."\",\"total_amount\":".$total.",\"product_code\":\"QUICK_MSECURITY_PAY\",\"subject\":\"VV跑腿".$tip."\"}");

        $result = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        //echo htmlspecialchars($result);//就是orderString 可以直接给客户端请求，无需再做处理。
        echo $result;

    }
    /*--------------------------------------支付宝-结束------------------------------------*/

    //充值--更改账户余额
    public function recharge(){
        $money = $this->request->post("money");//支付宝返回用户支付金额
        $money = '100';//目前测试阶段不过充多少都是100
        $user_id = $this->auth->id;
        $userInfo = Db::name('user')->where(['id'=>$user_id])->find();

        /*echo '<pre>';
        print_r($userInfo);
        exit;*/

        $billData = [
            'user_id'       =>  $user_id,
            'money'         =>  $money,
            'order_type'    =>  0,//代表充值
            'create_time'   =>  time(),
            'province'      =>  $userInfo['province'],
            'city'          =>  $userInfo['city'],
            'county'        =>  $userInfo['county'],
        ];

        $resLog = Db::name('bill')->insert($billData);

        $billId = Db::name('bill')->getLastInsID();

        $data = ['bill_id'=>$billId];

        $res = Db::name('user')->where(['id'=>$user_id])->setInc('balance',$money);
        if($resLog&&$res){
            return $this->success('更改成功','/index/user/index',$data);
        }else{
            return $this->error('更改失败','/index/recharge/index');
        }
    }
}
