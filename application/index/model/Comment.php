<?php
namespace app\index\model;
use think\Model;

class Comment extends Model{
    
    public function selectCommentNumder($condition){
        $number = $this->where($condition)
                       ->where('comment_status', '1')
                       ->where('comment_check', '1')
                       ->count();
        return $number;
    }

    public function inSerintoComment($data){
        $rel = $this->insert($data);
        if($rel){
            return 1;
        }
        return 0;
    }

    public function diGuiComment($nid){
       $result = $this->where('comment_status','1')
                      ->where('comment_pid', '0')
                      ->where('comment_nid', $nid)
                      ->order('comment_time desc')
                      ->paginate(5);

        return $result;
    }
}