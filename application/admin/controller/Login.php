<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\User;
use app\admin\validate\User as vUser;
use \think\Session;
class Login extends Controller{
    public function quit(){
        Session::clear('think');
        $this->success('退出成功...','index/index/index');
    }
}
