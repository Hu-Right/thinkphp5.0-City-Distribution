<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/19
 * Time: 9:22
 */
namespace app\admin\controller;

use think\Db;
use app\common\controller\Backend;
class Monitor extends Backend
{
    public function index()
    {
        return view();
    }

    public function busyman()
    {
        $busyman = Db::name('runmen')
            -> where(['status' => 1,'is_order' => 1])
            -> field('id,truename,lon,lat')
            -> select();

        return json($busyman);
    }

    public function freeman()
    {
        $freeman = Db::name('runmen')
            -> where(['status' => 1,'is_order' => 0])
            -> field('truename,lon,lat')
            -> select();

        return json($freeman);
    }

    public function getOrderId()
    {
        $runman_id = input('param.id');

        $where['status'] = ['in','1,2'];
        $where['rid'] = $runman_id;
        $order_id = Db::name('order') -> where($where) -> value('id');
        if (empty($order_id)){
            return $this -> error('查询失败');
        }

        $this -> redirect('/admin/ordermanagement/Ordermanagement/seek',['id' => $order_id]);
    }



}