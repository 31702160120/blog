<?php
namespace app\admin\model;
use think\Model;

class Images extends Model{
    
    public function select_images(){
        $condition= [
            'images_status' => ['neq',-1],
        ];

        $order = [
            'images_status' => 'desc',
        ];

        $result = $this->where($condition)
                       ->order($order)
                       ->paginate(4);
        return $result;
    }

    public function selectImagesNumder(){
        $condition= [
            'images_status' => ['neq',-1],
        ];

        $result = $this->where($condition)
                      ->count();
        return $result;
    }

    public function update_img_status($condition){
        $result = $this->where('id',$condition["id"])
                       ->update(['images_status' => $condition["images_status"]]);
        if($result){
            return 1;
        }
        return 0;
    }

    public function delete_img($condition){
        $result = $this->where($condition)
                       ->update(['images_status' => '-1']);
            if($result){
                return 1;
            }
            return 0;
    }

    public function insertInto_newImg($data){
        $result = $this->insert($data);
        if($result){
            return 1;
        }
        return 0;
    }
}
