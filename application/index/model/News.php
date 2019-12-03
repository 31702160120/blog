<?php
namespace app\index\model;
use think\Model;
use app\index\model\Column;
class News extends Model
{
   
    public function selectNews($cid){
      $result = $this->where('news_cid', $cid)
                     ->where('news_status', '1')
                     ->order('id desc')
                     ->limit(5)
                     ->select();
      return $result;
    }

    public function selectNewsYes($cid, $id){
      $result = $this->where('news_cid', $cid)
                      ->where('id','not in', $id)
                     ->where('news_status', '1')
                     ->order('id desc')
                     ->limit(5)
                     ->select();
      return $result;
    }


    public function selectLoveNews(){
      $result = $this->where('news_status', '1')
                     ->order('news_hits desc')
                     ->limit(4)
                     ->select();
      return $result;
    }

    public function selectLoveNewsYes($id){
      $result = $this->where('news_status', '1')
                     ->where('id','not in', $id)
                     ->order('id desc')
                     ->limit(4)
                     ->select();
      return $result;
    }

    public function selectColumnLoveNews($data){
      $result = $this->where($data)
                     ->order('news_hits desc')
                     ->limit(4)
                     ->select();
      return $result;
    }

    public function selectColumnLoveNewsYes($data,$id){
      $result = $this->where($data)
                     ->where('id', 'not in', $id)
                     ->order('news_hits desc')
                     ->limit(4)
                     ->select();
      return $result;
    }

    public function selectColumnNews($cid){
      $result = $this->where('news_status', '1')
                     ->where('news_cid',$cid)
                     ->order('news_time desc')
                     ->paginate(6);
      return $result;
    }

    public function selectCurremtNewsSideNews($condition, $order){
           $result = $this->where($condition)
                          ->where('news_status','1')
                          ->order($order)
                          ->find();
            return $result;

    }


}