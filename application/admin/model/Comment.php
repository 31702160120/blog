<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Comment extends Model{
    
    protected  $autoWriteTimestamp = true;
    protected  $updateTime = false;

    protected  $createTime = 'comment_time';

    public function select_comment($condition){
        $order = [
            "comment_status" => 'asc',
            "comment_time" => 'desc',
        ];

        $result = $this->where($condition)
                       ->order($order)
                       ->paginate();
        return $result;
    }

    public function select_commentOrder($condition, $order){
        $result = $this->where($condition)
                       ->order($order)
                       ->paginate();
        return $result;
    }

    public function seletcNewsCommentNumder($condition){
        $result = $this->where($condition)
                       ->count();
        return $result;
    }
    
    public function update_comment_status($condition){
        $result = $this->where('id',$condition["id"])
                       ->update(['comment_status' => $condition["comment_status"]]);
        if($result){
            return 1;
        }
        return 0;
    }

    public function delete_comment($condition){
        $result = $this->where($condition)
                       ->update(['comment_status' => '-1']);
            if($result){
                return 1;
            }
            return 0;
    }

    public function check_comment($condition){
        $result = $this->where($condition)
                       ->update(['comment_status' => '1', 'comment_check' => '1']);
            if($result){
                return 1;
            }
            return 0;
    }

    public function insertIntoBloggerLeaveWord($data){
        $result = $this->save($data);
        if($result){
            return 1;
        }
        return 0;
    }

    public function update_comment_check($condition){
        $result = $this->where('id',$condition["id"])
                       ->update(['comment_status' => '1', 'comment_check' => '1']);
        if($result){
            return 1;
        }
        return 0;
    }
}
