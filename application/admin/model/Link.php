<?php
namespace app\admin\model;
use think\Model;

class Link extends Model
{
    public function select_link(){
        $condition =[
            "link_status" => ["neq",-1],
        ];

        $order= [
            'link_status' => 'desc',
            'id' => 'desc',
        ];

        $result = $this->where($condition)
                       ->order($order)
                       ->paginate();
        return $result;
    }

    public function selectLinkNumder(){
        $condition =[
            "link_status" => ["neq",-1],
        ];
        $result = $this->where($condition)
                       ->count();
        return $result;
    }

    public function update_link_status($condition){
        $result = $this->where('id',$condition["id"])
                       ->update(['link_status' => $condition["link_status"]]);
        if($result){
            return 1;
        }
        return 0;
    }

    public function delete_link($condition){
        $result = $this->where($condition)
                       ->update(['link_status' => '-1']);
            if($result){
                return 1;
            }
            return 0;
    }

    public function insertInto_newLink($data){
        $result = $this->insert($data);
        if($result){
            return 1;
        }
        return 0;
    }

    public function select_some_link($id){
        $result = $this->where('id' , $id)
                       ->find();
        return $result;
    }

    public function update_link($data){
        $result = $this->where('id', $data['id'])
                        ->update([
                            'link_name' => $data['link_name'],
                            'link_url' => $data['link_url'],
                            'link_status' => $data['link_status'],
                        ]);
        if($result){
            return 1;
        }
        return 0;
    }
}
