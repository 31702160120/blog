<?php
namespace app\admin\model;
use think\Model;

class News extends Model
{
    protected  $autoWriteTimestamp = true;
    protected  $updateTime = false;

    protected  $createTime = 'news_time';


    public function select_article($cid ,$condition,$order){
        $result = $this->where($condition)
                       ->order($order)
                       ->paginate(); //分页
        return $result;
    }

    public function selectArticleNumder($cid){
        $condition = [
            'news_cid' => $cid,
        ];
        $result = $this->where($condition)
                       ->count();
        return $result;
    }

    public function update_news_status($condition){
        $result = $this->where('id',$condition["id"])
                       ->update(['news_status' => $condition["news_status"]]);
        if($result){
            return 1;
        }
        return 0;
    }

    public function delete_news($condition){
        $result = $this->where($condition)
                       ->update(['news_status' => '-1']);
            if($result){
                return 1;
            }
            return 0;
    }

    public function insertIntoNews($data){
        $result = $this->insert($data);
        if($result){
            return 1;
        }
        return 0;
    }

    public function updateNews($id, $data){
        $result = $this->where('id', $id)
                       ->update($data);
        if($result){
            return 1;
        }
        return 0;
    }

    public function selectNewsNumder(){
        $condition = [
            'news_status' => ['neq','-1'],
        ];
        $result = $this->where($condition)
                       ->count();
        return $result;
    }
}
