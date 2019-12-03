<?php
namespace app\admin\model;
use think\Model;

class User extends Model{

    public function select_user_message($condition, $order){ 
        $result = $this->where($condition)
			->order($order)
            ->paginate(10); //分页

        return $result;
    }

    public function selectUserNumder(){
        $condition = [
            'user_status' => ['neq', -1],
            'privilege' => ['neq', 1], //不等于-1
		];
        
        $result = $this->where($condition)
                       ->count();

        return $result;
    }


    public function update_user_status($condition){
        if($condition["user_status"] == 0){
            $result = $this->where('id',$condition["id"])
                       ->update(
                           ['user_status' => $condition["user_status"],
                           'relieve_time' => strtotime('+15 day'),
                           ]);
        }else{
            $result = $this->where('id',$condition["id"])
                       ->update(['user_status' => $condition["user_status"],
                                    'relieve_time' => '',
                       ]);
        }
        if($result){
            return 1;
        }
        return 0;
    }

    public function delete_user($condition){
        $result = $this->where($condition)
                       ->update(['user_status' => '-1']);
            if($result){
                return 1;
            }
            return 0;
    }

    public function login($data){
        $captcha = new \think\captcha\Captcha();
        if (!$captcha->check($data['verifycode'])) {
            return 1;
        }
        $user = $this->where('id','1')->find();
        if($user["username"] == $data["username"] || $user["email"] == $data["username"] ){
            if($user['password'] == MD5($data['password'])){
                session('name',$user["username"]);
                session('logo','博主');
                session('uid', $user['id']);
                return 3;
            }
            return 2;
        }
        return 0;
    }

    public function selectNumder($condition){
        $result = $this->where($condition)
                       ->count();

        return $result;
    }
    
}

