<?php
namespace app\admin\controller;
use think\Controller;
use \think\Session;
class Base extends Controller
{
    public function _initialize() {
        $this -> checkUser();
    }

    public function checkUser(){
        $name = Session::get('name');
        $this->assign('name', $name);
        $privilege = Session::get('status');
        if($privilege != 1){
            $this->success('你未登录...','index/index/index');
        }
    }
}
