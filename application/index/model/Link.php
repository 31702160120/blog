<?php
namespace app\index\model;
use think\Model;

class Link extends Model
{
    public function selectLink(){
        $result = $this->where('link_status','1')
                       ->limit(4)
                       ->select();
        return $result;
    }
}