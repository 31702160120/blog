<?php
namespace app\index\controller;
use think\Db;
use app\index\model\Admin;
use \think\Session;
class Message extends Base
{
    public function index()
    {
        $this->getBloggerMessage();
        return $this->fetch("message/message");
    }

    public function getBloggerMessage(){
        $result = Admin::get(1);
        $user = Db::table('tp_user')->where('id', 1)->value('user_img');
       $this->assign('user', $user);
        $this->assign('result', $result);
    }
}
