<?php
namespace app\admin\model;
use think\Model;

class Column extends Model
{

    public function select_columm(){
        $condition =[
            "column_status" => ["neq",-1],
        ];
        
        $order = [
            "column_status" => 'desc',
            "id"            => 'asc',
        ];

        $result = $this->where($condition)
                       ->order($order)
                       ->select();
        return $result;
    }


    public function select_columms($order){
        $condition =[
            "column_status" => ["neq",-1],
        ];
        
        $result = $this->where($condition)
                       ->order($order)
                       ->paginate();
        return $result;
    }

    public function selectColummsNumder(){
        $condition =[
            "column_status" => ["neq",-1],
        ];
      

        $result = $this->where($condition)
                       ->count();
        return $result;
    }

    public function select_columnName($condition){
        return $this->where($condition)
                    ->find();
    }

    public function update_column_status($condition){
        $result = $this->where('id',$condition["id"])
                       ->update(['column_status' => $condition["column_status"]]);
        if($result){
            return 1;
        }
        return 0;
    }

    public function delete_column($condition){
        $result = $this->where($condition)
                       ->update(['column_status' => '-1']);
            if($result){
                return 1;
            }
            return 0;
    }

    public function insertInto_newColumn($data){
        $result = $this->insert($data);
        if($result){
            return 1;
        }
        return 0;
    }

    public function update_column($data){
        $result = $this->where('id', $data['id'])
                        ->update([
                                  'column_name' => $data['column_name'],
                                  'column_status' => $data['column_status'],
                        ]);
        if($result){
            return 1;
        }
        return 0;
    }
}
