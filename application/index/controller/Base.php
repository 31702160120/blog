<?php 
namespace app\index\controller;
use think\Controller;
use \think\Session;
use \think\Db;
use app\index\model\Admin;
use app\index\model\Column;
use app\index\model\Link;
use app\index\model\System;
class Base extends Controller
{
    public function _initialize() {
        $message = $this->getName();
        $wx = $this->getWx(); //个人微信
        $head = $this->getHeadColumn();
        $link = $this->getLink();  //友情链接
        $privilege = Session::get('status');
        $this->getSystem();

        $this->assign('head', $head);
        $this->assign('privilege', $privilege);
        $this->assign('message', $message);
        $this->assign('link', $link);
        $this->assign('wx', $wx);
    }

    public function getName(){
        $uid = Session::get('uid');
        if(!empty($uid)){
            $img = Db::table('tp_user')->where('id', $uid)->value('user_img');
            $data['img'] = $img;
            $data["name"] = Session::get('name');
            $data["logo"] = Session::get('logo');
       }else{
            $data['img'] = '';
            $data["name"] = '';
            $data["logo"] = '';
       }
        return $data;
    }

    public function getWx(){
        $result = Admin::get(1);
        return $result;
    }

    public function getHeadColumn(){
        $column = new Column();
        $data = $column->selectHeadColumn();
        return $data;
    }

    public function getLink(){
        $link = new Link();
        $data = $link->selectLink();
        return $data;
    }

    public function getSystem(){
        $stem = System::get(1);
        $this->assign('stem', $stem);
    }
}
