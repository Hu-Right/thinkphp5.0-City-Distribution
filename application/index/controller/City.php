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
 * 城市信息
 */
class City extends Frontend
{

    protected $layout = 'default';
    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];

    /**
     * 城市信息
     */
    public function index()
    {
        $cityData = Db::name('area')->where(['initial'=>['<>','']])->select();

        /*echo '<pre>';
        print_r($cityletter);
        exit;*/

        $this->view->assign('cityData', $cityData);
        $this->view->assign('title', __('城市列表'));
        return $this->view->fetch();
    }

    public function test(){
        $data = Db::name('area')->where(['areaname'=>['like','%%']])->select();
        echo '<pre>';
        print_r($data);
        exit;
    }
}
