<?php
/**
 * Created by PhpStorm.
 * User: EZ
 * Date: 2018/11/26
 * Time: 14:06
 */
namespace app\index\controller;

use think\Controller;
class Index extends Controller
{
    public function index()
    {
        $this ->redirect('/admin/index/login');
    }
}