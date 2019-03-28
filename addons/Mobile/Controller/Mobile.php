<?php

// +----------------------------------------------------------------------

// | 河源市智辰科技有限公司 [ Simple Efficient Excellent ]

// +----------------------------------------------------------------------

// | Copyright (c) 2014 http://www.hychichen.com All rights reserved.

// +----------------------------------------------------------------------

// | Author: huangda <huang-da@qq.com>

// +----------------------------------------------------------------------

namespace Addons\Mobile\Controller;

use addons\third\library\Application;
use addons\third\library\Service;
use think\addons\Controller;
use think\Cookie;
use think\Hook;

/**

 * 短信验证码控制器

 * @author huangda <huang-da@qq.com>

 */

class Mobile extends Controller{

    /**

     * 短信发送函数

     * @param string $sms_data 短信信息结构

     * @$sms_data['mobile'] 收件人手机号码

     * @$sms_data['code']验证码内容

     * @return boolean

     * @author huangda <huang-da@qq.com>

     */

    function sendSms($mobile , $code, $ext=''){

        $url="http://dx.ipyy.net/sms.aspx?action=send&userid=&account=zs013&password=479TY29yt7tUy638Wh&mobile=".$mobile."&content=【VV跑腿】动态验证码".$code."（15分钟内有效），如非本人操作，请忽略本短信。";

        //file_put_contents('1.txt', $url);

        //初始化curl

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $url);

        //设置header

        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        //要求结果为字符串且输出到屏幕上

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式

        //运行curl

        $data = curl_exec($ch);

        //返回结果

        if($data){

            curl_close($ch);

            return TRUE;

        }else{

            curl_close($ch);

            return false;

        }

    }

}

