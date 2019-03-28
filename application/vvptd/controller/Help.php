<?php
// +----------------------------------------------------------------------
// | vv跑腿配送端
// | 帮助
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
class Help extends Vvptd{
    //帮助说明
    public function help_list(){
        $data = Db::name('help_document_type')->where('status',1)->select();
        for($i=0;$i<count($data);$i++){
            $data[$i]['help_list'] = Db::name('help_document')->where('type',$data[$i]['id'])->select();
        }
        $this->assign('data',$data);
        return $this->fetch('bangzhu');
    }
    //帮助详情页
    public function help_detail(){
        $id = $this->request->get('id');
        $data = Db::name('help_document')->where('id',$id)->find();
        $this->assign('data',$data);
        return $this->fetch('pro');
    }
}