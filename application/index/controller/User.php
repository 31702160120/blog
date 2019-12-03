<?php
namespace app\index\controller;
use think\Controller;
use \think\Session;
use app\index\model\User as userModel;
use \think\Validate;
use \think\Db;
use app\index\model\System;
class User extends Controller
{
    public function index()
    {
        return $this->fetch('user/user');
    }

    public function login(){
        $data = input('post.');
       $user = new userModel();

       $rule = [
        'username' => 'require',
        'password'=>'require',
        'verifycode' => 'require',
        ];
         $msg = [
            'username.require' => '账户不能为空',
            'password.require' => '密码不能为空' ,
            'verifycode.require' => '验证码不能为空',
        ];
        $pasw = new Validate($rule, $msg);
        if (!$pasw->check($data)) {
            return json(array(
                'error' => 0,
                'msg'=>$pasw->getError()
            ));
        }
        $captcha = new \think\captcha\Captcha();
        if (!$captcha->check($data['verifycode'])) {
            return json(array(
                'error' => 0,
                'msg'=>'验证码错误',
            ));
        }
       $condition = [
            'username' => $data['username'],
            'user_status' => ['neq','-1']
       ];
       $rel = $user->selectAccount($condition);
       if(!isset($rel)){
            $email = [
                'email' => $data['username'],
                'user_status' => ['neq','-1']
            ];   
            $rel = $user->selectAccount($email);   
            if(!isset($rel)){
                return json(array(
                    'error' => 0,
                    'msg' => '账号不存在',
                    ));
            }
       }

       if($rel->user_status == 0){
            $time = time();
            $goTime = $rel->relieve_time;
            if($time < $goTime){
                return json(array(
                    'error' => 0,
                    'msg' => '账号处于封禁状态解封时间为'.date("y-m-d h:i:s", $goTime),
                ));
            }
            $user->where('id',$rel->id)
                ->update([
                    'user_status' => '1',
                    'relieve_time' => '',
                ]);
       }
       switch($rel->temporary_status){
            case 0:
                if(md5($data['password']) != $rel->temporary_paws){
                    if( md5($data['password']) != $rel->password){
                        return json(array(
                            'error' => 0,
                            'msg' => '密码错误',
                            ));
                    }
                }
            break;
            default:
            if(md5($data['password']) != $rel->password){
                return json(array(
                    'error' => 0,
                    'msg' => '密码错误',
                    ));
            }
            break;
        }
        $msg = '';
        if(md5($data['password']) == $rel->password){
            $user->updateAccount($rel->id);
           $msg = "你的密码已找回临时密码已失效";
        }

        $msg = $msg==''? "登录成功": $msg;
        session('name',$rel->ni);
        session('uid', $rel->id);
        session('status', $rel->privilege);
        if($rel->privilege == 1){
            session('logo','博主');
        }else{
            session('logo','游客');
        }
       return json(array(
        'error' => 1,
        'msg' => $msg,
        ));
    }

    public function head(){
        return $this->fetch("public/head");
    }

    public function quit(){
        Session::clear('think');
        $this->success('退出成功...','index/index');
    }

    public function register(){
        $data = input('post.');
        $rule = [
            'username' => 'require|max:5|min:3',
            'email'=>'require|email|min:10',
            'password'=>'require|min:6',
            'password2'=>'require|confirm:password',
            ];
            
             $msg = [
                'username.require' => '账户不能为空',
                'username.max' => '账户名不能大于5位',
                'username.min' => '账户名不能小于3位',
                'email.require' => '邮箱不能为空' ,
                'email.email' => '邮箱格式错误' ,
                'email.min' => '请输入正确的邮箱地址',
                'password.require' => '密码不能为空' ,
                'password.min' => '密码不能少于6位数',
                'password2.require' => '确认密码不能为空' ,
                'password2.confirm' => '两次输入的密码不一致'
            ];
            $pasw = new Validate($rule, $msg);
            if (!$pasw->check($data)) {
                return json(array(
                    'error' => 0,
                    'msg'=>$pasw->getError()
                ));
            }
        $user = new userModel();
        $name = $user->where('username', $data['username'])
                     ->where('user_status', 'neq','-1')
                     ->select();
        if($name){
            return json(array(
                'error' => 0,
                'msg' => "账户已存在",
            ));
        }
        $email =  $user->where('email', $data['email'])
                       ->where('user_status', 'neq', '-1')
                       ->select();
                      
        if($email){
            return json(array(
                'error' => 0,
                'msg' => '邮箱已被注册',
            ));
        }
        $time = time();
        $message= [
            'username' => $data['username'],
            'ni' => $data['username'],
            'password' => md5($data['password2']),
            'create_time' => $time,
            'email' => $data['email'],
        ];
       $result = Db::name('user')->insertGetId($message);
       if($result){
        session('name',$data['username']);
        session('uid', $result);
        session('status', 0);
        session('logo','游客');
        return json(array(
            'error' => 1,
            'msg' => '注册成功',
            ));
       }
       return json(array(
        'error' => 0,
        'msg' => '注册失败',
    ));
    }

    public function resetPassword(){
        $data = input('post.');
        $rule = [
            'email'=>'require|email|min:10',
            ];
            
             $msg = [
                'email.require' => '邮箱不能为空' ,
                'email.email' => '邮箱格式错误' ,
                'email.min' => '请输入正确的邮箱地址',
            ];
            $validate = new Validate($rule, $msg);
            if (!$validate->check($data)) {
                return json(array(
                    'error' => 0,
                    'msg'=>$validate->getError()
                ));
            }
            $user = userModel::get(['email' => $data['email'], 'user_status' => ['neq', '-1']]);
            if(!isset($user)){
                return json(array(
                    'error' => 0,
                    'msg' => '该邮箱不存在',
                ));
            }
            if($user->temporary_status == 0){
                return json(array(
                    'error' => 0,
                    'msg' => '密码已被重置',
                ));
            }
             $email = $data['email'];
             $pasw = str_pad(mt_rand(0, 999), 6, STR_PAD_BOTH);
             $sel = Db::table('tp_user')->where('email', $email)->update([
                 'temporary_status' => '0',
                 'temporary_paws' => md5($pasw),
             ]); 
             
             if(!isset($sel)){
                return json(array(
                    'error' => '0',
                    'msg' => '发送失败',
                ));
             }

             $system = System::get(1);
             $host = $system->host;
             $emialUrl = $system->email;
             $token = $system->token;
             $name = $system->name;
             $receiverName = $user->username;
             $title = $system->title;
             $news = "<p> 你的重置密码为 </p> <p>".$pasw."</p> <p>请及时更改你的密码</p>";
            $newEmail = \PHPMailer\Email::send(
               $host, $emialUrl, $token, $name, $email, $receiverName, $title, $news
            );
            if($newEmail){
                return json(array(
                    'error' => 1,
                     'msg' => '发送成功',
                 ));
            }else{
                return json(array(
                    'error' => '0',
                    'msg' => '发送失败',
                ));
            }

    }

    public function getOut(){
        Session::clear('think');
        return json(array(
            'error' => '1',
        ));
    }
}
