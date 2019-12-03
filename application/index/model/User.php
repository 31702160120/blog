<?php
namespace app\index\model;
use think\Model;
use app\index\model\Column;
class User extends Model
{

    public function selectAccount($condition){
        $account = $this->get($condition);
        return $account;
    }

    public function updateAccount($id){
        $result = $this->where('id',$id)
                       ->update(['temporary_status' => '1', 'temporary_paws' => '']);
    }

    public function updateUser($id,$data){
        $result = $this->where('id',$id)
                       ->update($data);
        if($result){
            return 1;
        }
        return 0;
    }
}