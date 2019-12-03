<?php
namespace app\index\model;
use think\Model;

class Column extends Model
{
   public function selectHeadColumn(){
      $result = $this->where('column_status','1')
                     ->limit(3)
                     ->select();
      return $result;
   }

}