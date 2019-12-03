<?php
namespace app\index\controller;
use think\Controller;
use \think\Session;
use \think\Db;
use app\index\model\User;
use \think\Validate;
class Personal extends Base
{

    public function index()
    {
        $name = Session::get('name');
        if($name == ''){
            $this->success('你未登录...','index/index');
        }
        $id = Session::get("uid");
        $this->getUserMessage($id);
        $weekarray=array("日","一","二","三","四","五","六"); 
        $date = $weekarray[date("w")];
        $relieve = $this->relieve($id);
        $this->assign('relieve', $relieve);
        $this->assign('date' ,$date);
        $this->assign('name' ,$name);
        return $this->fetch('personal/personal');
    }

    public function getUserMessage($id){
        $result = User::get($id);
        $this->assign('result' ,$result);
    }

    public function alterUser(){
        $data = input("post.");
        $id = Session::get("uid");
        $user = new User();
        $sel = User::get($id);
        if($sel->email != $data['email']){
            $userEmail = $user->where('email', $data['email'])->value('id');
            if($userEmail){
                return json(array(
                   'error' => 0,
                   'msg' =>'邮箱已被注册',
                ));
            }
        }

        if($data['password'] == ''){
            $rule = [
                'email' => 'email|require',
                'ni'  =>   'require|max:8',
             ];
             $msg = [
                 'email.email' => '邮箱格式错误',
                 'email.require' => '邮箱不能为空',
                 'ni.require' => '昵称不能为空',
                 'ni.max' => '昵称长度不能大于8个字符',
             ];
            $validate = new Validate($rule, $msg);
            if (!$validate->check($data)) {
                return json(array(
                    'error' => 0,
                    'msg'=>$validate->getError()
                ));
            }
            $list = [
                'email' => $data['email'],
                'ni' => $data['ni'],
            ];
            $code = $user->updateUser($id,$list);
            if($code){
            session('name',$data['ni']);
            return json(array(
                'error' => 1,
               'msg' =>'修改成功',
                ));
            }
            return json(array(
                'error' => 0,
                'msg' =>'修改失败',
            ));
        }else{
           
            $rule = [
                'password1' => 'require|min:6',
                'password2'=>'require|confirm:password1',
                'email' => 'email|require',
                'ni'  =>   'require|max:8',
             ];
             $msg = [
                 'password1.require' => '新密码不能为空',
                 'password1.min'    => '新密码不能少于6位数',
                 'password2.require' => '确认密码不能为空' ,
                 'password2.confirm' => '新密码与确认密码不一致' ,
                 'email.email' => '邮箱格式错误',
                 'email.require' => '邮箱不能为空',
                 'ni.require' => '昵称不能为空',
                 'ni.max' => '昵称长度不能大于8个字符',
             ];
            $validate = new Validate($rule, $msg);
            if (!$validate->check($data)) {
                return json(array(
                    'error' => 0,
                    'msg'=>$validate->getError()
                ));
            }
           if($sel->temporary_status == 0 ){
                if(md5($data['password']) != $sel->temporary_paws){
                    return json(array(
                        'error' => 0,
                        'msg' => '请填入邮件发送的重置密码',
                    ));
                }
            }else{
                if(md5($data['password']) != $sel->password){
                    return json(array(
                        'error' => 0,
                        'msg' => '原密码错误',
                    ));
                }
            }

            $list = [
                'email' => $data['email'],
                'password' => md5($data['password2']),
                'temporary_status' => '1',
                'temporary_paws' => '',
                'ni'  => $data['ni'],
            ];
            $code = $user->updateUser($id,$list);
            if($code){
            session('name',$data['ni']);
            return json(array(
                'error' => 1,
               'msg' =>'修改成功',
                ));
            }
            return json(array(
                'error' => 0,
                'msg' =>'修改失败',
            ));
        }
    }

    public function relieve($id){
        $relieve = Db::view('history','time,status,nid,id')
                       ->view('news',['news_name' => 'name'],'history.nid = news.id')
                       ->where('news_status','1')
                       ->order('time desc')
                       ->paginate(16);
        return $relieve;
    }

    public function AlterRelieve(){
        $id = input('param.id');
        if(!isset($id)){
            return json(array(
                'error' => 0,
                'msg' =>'参数错误',
            ));
        }
        $rel = Db::table('tp_history')->where('id', $id)->update(['status' => '1']);
        if($rel){
            return json(array(
                'error' => 1,
                'msg' =>'修改成功',
            ));
        }
        return json(array(
            'error' => 0,
            'msg' =>'修改失败',
        ));
    }
    
}
