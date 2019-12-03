<?php
namespace app\index\model;
use think\Model;
class History extends Model{
    
    public function selectShieldNewsId($uid){
        $result = $this->where(['uid' => $uid,'status' => '-1'])
                       ->select();
        if($result){
            $str = '';
            foreach($result as $val){
                $str .= $val['nid'].',';
            }
            $str = rtrim($str,',');
            return $str;
        }
        return false;
    }
}