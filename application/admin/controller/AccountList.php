<?php
namespace app\admin\controller;
use think\Controller;
use think\Image;
use think\Db;
use think\Request;
use app\admin\Model\User;
use \think\Session;
class AccountList extends Base{
    private $obj;

    public function _initialize(){
        $this->obj = new User();
        $this -> checkUser();
    }

    // 账户管理菜单
    public function index(){
        $keyword = input('param.keyword');
        $code = input('param.order', 0 ,'intval');
        $news = input('param.news');
        $this->get_user_message($code, $news);
        $this->getAccountNumder();
        return $this->fetch('account/index');
    }

    // 密码修改
    public function passwordAlter(){
        return $this->fetch('account/alter2');
    }

    // 头像修改
    public function headImg(){
        $user = Db::table('tp_user')->where('id', 1)->find();
       $this->assign('user', $user);
        return $this->fetch('account/tou');
    }

    public function getAccountNumder(){
        $numder =  $this->obj->selectUserNumder();
        $this->assign('numder', $numder);
    }

    public function get_user_message($code, $news=''){
        switch($code){
            case 1:
                $order = [
                            'create_time' => 'asc',
                        ];
                break;
            case 2:
                $order = [
                            'create_time' => 'desc',
                        ];
                break;
            case 3:
                $order = [
                         'id' => 'asc',
                        ];
                break;
            case 4:
                $order = [
                    'id' => 'desc',
                ];     
                break;
            case 5:
                $order = [
                         'user_status' => 'asc',
                        ];
                break;
            case 6:
                $order = [
                    'user_status' => 'desc',
                ];     
                break;
            default:
                $order = [
                         'id' => 'desc',
                       ];  
            break;
        }
        if(!empty($news)){
            $condition = [
                'user_status' => ['neq', -1],
                'privilege' => ['neq', 1], //不等于-1
                'username|email' => ['like', '%'.$news.'%'],
            ];
        }else{
            $condition = [
                'user_status' => ['neq', -1],
                'privilege' => ['neq', 1], //不等于-1
            ];
        }
         $data = $this->obj->select_user_message( $condition, $order);
         $this->assign('data', $data);
         $this->assign('news', $news);
         $this->assign('code', $code);
    }

    public function update_status(){
        $data = input("post.");
        $data["user_status"] = $data["user_status"]== 0? 1:0;
        $code = $this->obj->update_user_status($data);
        if($code){
            return json(array(
                'error' => 1,
                'status' => $data["user_status"],
            ));
        }
        return json(array(
            'error' => 0,
            'msg'   => '状态更新失败',
        ));
    }


    public function delete_active_user(){
        $id = input("post.");
        $condition = ["id" => $id["id"]];
        $code = $this->obj->delete_user($condition);
        if($code){
            return json([
                'msg' => "删除成功",
                'status' => 1,
            ]);
        }
        return json([
            'msg' => "删除失败",
            'status' => 0,
        ]);
    }

    public function update_adminPasw(){
        $request = Request::instance();
        $data = $request->param();
        $old = md5($data['password']);
        $presence = User::get(1);
        if($presence->temporary_status !=1){
            if($presence->temporary_paws == $old){
                $code = $this->obj->where('id', '1')->update([
                    "password" => md5( $data["password2"]),
                    'temporary_status' => '1',
                     'temporary_paws' => '' 
                    ]);
                 if($code){
                  Session::clear('think');
                     return json([
                         'error' => 1,
                          'msg'   => '密码修改成功，请重新登录',
                     ]);
                 }
                 return json([
                     'error' => 0,
                      'msg'   => '密码修改失败',
                 ]);
             }
             return json([
                'error' => 0,
                 'msg'   => '请输入邮件发送的密码',
            ]);
        }else{
            if($presence->password == $old){
                $code = $this->obj->where('id', '1')->update(["password" => md5( $data["password2"])]);
                 if($code){
                  Session::clear('think');
                     return json([
                         'error' => 1,
                          'msg'   => '密码修改成功，请重新登录',
                     ]);
                 }
                 return json([
                     'error' => 0,
                      'msg'   => '密码修改失败',
                 ]);
             }
             return json([
                'error' => 0,
                 'msg'   => '原密码不正确',
            ]);
        }
    }

    public function del_users(){
        $data = input("post.");
        $str = implode(",",$data["id"]);
        $condition = ["id" => ["in",$str]];
        $code = $this->obj->delete_user($condition);
        if($code){
            return json([
                'msg' => "删除成功",
                'status' => 1,
            ]);
        }
        return json([
            'msg' => "删除失败",
            'status' => 0,
        ]);
    }

    public function ce(){
        $order = input('param.order');
        $index = $_SERVER['HTTP_REFERER'];
        $url = str_replace('.html','',$index);
        $url .= '/order/'.$order;
        $this->result($url, 1);
    }

    public function userImg(Request $request){
        $file = $request->file('file');
        $data = $request->param();
        $user = User::get(1);
        if($data['username'] != $user->username){
            $username = Db::table('tp_user')->where('username', $data['username'])->find();
            if($username){
                return json([
                    'msg' => '用户名已存在',
                    'error' => 0,
                ]);
            }
        } 
        if($data['email'] != $user->email){
            $username = Db::table('tp_user')->where('email', $data['email'])->find();
            if($username){
                return json([
                    'msg' => '邮箱已被注册',
                    'error' => 0,
                ]);
            }
        }     
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif'])
            ->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'user');
            if($info){
                $imgSrc = '/uploads/user/'.str_replace("\\","/",$info->getSaveName()); 
                $list = [
                    'username' => $data['username'],
                    'ni' => $data['ni'],
                    'email' => $data['email'],
                    'user_img' => $imgSrc,
                ];
            }else{
                return json([
                    'msg' => $file->getError(),
                    'error' => 0,
                ]);
            }
        }else{
            $list = [
                'username' => $data['username'],
                'ni' => $data['ni'],
                'email' => $data['email']
            ];
        }
        $rel =  Db::table('tp_user')->where('id', '1')->update($list);
         if($rel){
            session('name',$data['ni']);
          return json([
              'msg' => '修改成功',
              'error' => 1,
          ]);
         }
         return json([
          'msg' => '修改失败',
          'error' => 0,
      ]);

    }
}