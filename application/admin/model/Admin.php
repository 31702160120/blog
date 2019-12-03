<?php
namespace app\admin\model;
use think\Model;

class Admin extends Model{

    public function select_My_data(){
        return $this->get(1);
    }

    public function update_admin($data){
        $result = $this->where('id','1')
                       ->update($data);
        if($result){
            return 1;
        }
        return 0;
    }

}

