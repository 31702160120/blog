<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\System;
use \think\Validate;
class SystemList extends Base{

    public function index(){
        $this->getSystem();
        return $this->fetch("system/system");
    }

    public function getSystem(){
        $system = System::get(1);
        $this->assign('system', $system);
    }

    public function ce(){
        $data = input("post.");

        $rule= [
            'copyright' =>'require|max:80',
            'describe' =>'require',
            'email' =>'require|email',
            'host' =>'require',
            'htmlTitle' =>'require|max:25',
            'keyword' =>'require|max:8',
            'name' =>'require',
            'record' =>'require',
            'title' =>'require',
            'token' =>'require',
        ];

        $msg = [
             'copyright.require'  =>'版权信息不能为空',
             'copyright.max'  =>'版权信息不能超过80个字',
            'describe.require'  =>'网站描述不能为空',
            'email.require'  =>'邮箱账户不能为空',
            'email.email'  =>'邮箱格式错误',
            'host.require'  =>'SMTP服务器不能为空',
            'htmlTitle.require'  =>'网站名称不能为空',
            'htmlTitle.max'  =>'网页标题不能超过25个字',
            'keyword.require'  =>'关键词不能为空',
            'keyword.max'  =>'关键词不能超过8个字',
            'name.require'  =>'邮件作者不能为空',
            'record.require'  =>'备案号不能为空',
            'title.require'  =>'邮件标题不能为空',
            'token.require'  =>'邮箱密码不能为空',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($data)) {
            return json(array(
                'error' => 0,
                'msg'=>$validate->getError()
            ));
        }

        $system = new System();
        $stem = $system->where('id', 1)->update($data);
        if($stem){
            return json(array(
                'error' => 1,
                'msg'=>'信息修改成功'
            ));
        }
        return json(array(
            'error' => 0,
            'msg'=>'信息修改失败'
        ));
    }
}