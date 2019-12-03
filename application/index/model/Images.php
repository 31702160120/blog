<?php
namespace app\index\model;
use think\Model;

class Images extends Model{

    public function select_images(){
        $condition= [
            'images_status' => ['eq',1],
        ];
        $result = $this->where($condition)
                       ->select();
        return $result;
    }

}