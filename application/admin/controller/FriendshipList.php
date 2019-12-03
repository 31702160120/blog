<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\Link;
class FriendshipList extends Base{
    private $obj;

    public function _initialize(){
        $this->obj = new Link();
        $this -> checkUser();
    }
    // 友情链接菜单
    public function index(){
        $this->get_link_message();
        return $this->fetch('friendship/index');
    }

    // 友情链接修改
    public function friendshipAlter(){
        $this->get_some_Friendship();
        return $this->fetch('friendship/alter');
    }

    // 友情链接添加
    public function friendshipAdd(){
        return $this->fetch('friendship/add');
    }

    public function get_link_message(){
        $data = $this->obj->select_link();
        $num = $this->obj->selectLinkNumder();
        $this->assign('data', $data);
        $this->assign('num', $num);
    }

    public function select_start_friendship_quantity(){
        $list = $this->obj->where("link_status", "1")
                          ->count();
        return $list;
      
    }

    public function update_friendship_status(){
        $data = input("post.");
        $data["link_status"] = $data["link_status"]== 0? 1:0;
        if($data["link_status"] == 1){
            $list = $this->select_start_friendship_quantity();
            if($list >= 4){
                return json(array(
                    'error' => 0,
                    'msg'   => '你最多只能开启四个栏目',
                ));
              }
        }
        $code = $this->obj->update_link_status($data);
        if($code){
            return json(array(
                'error' => 1,
                'status' => $data["link_status"],
            ));
        }
        return json(array(
            'error' => 0,
            'msg'   => '状态更新失败',
        ));
    } 
    
    public function delete_current_friendship(){
        $id = input("post.");
        $condition = ['id' => $id['id']];
        $code = $this->obj->delete_link($condition);
        if($code){
            return json([
                'msg' => "删除成功",
                'status' => 1,
            ]);
        }
        return json([
            'msg' => "删除失败",
            'status' => 0,
        ]);
    }

    public function del_some_friendship(){
        $data = input("post.");
        $str = implode(",",$data["id"]);
        $condition = ["id" => ["in",$str]];
        $code = $this->obj->delete_link($condition);
        if($code){
            return json([
                'msg' => "删除成功",
                'status' => 1,
            ]);
        }
        return json([
            'msg' => "删除失败",
            'status' => 0,
        ]);
    }

    public function add_newFriendship(){
        $data = input("post.");
        $msg = "";
        if($data["link_status"] == 1){
            $num = $this->select_start_friendship_quantity();
            if($num >= 4){
                $data["link_status"] = 0; 
                $msg = "添加成功由于已经启动了四个链接，所以此链接需要手动启动";
            }
        }
        if(!($this->obj->get(["link_name" => $data["link_name"], "link_status"=>['neq', '-1']]))){
            if($this->obj->insertInto_newLink($data)){
                $msg = $msg== "" ? "链接添加成功": $msg;
                return json(array(
                    "error" => 1,
                    "msg" => $msg,
                ));
            }
            return json(array(
                "error" => 0,
                "msg"   => "链接添加失败",
            ));
        }
        return json(array(
            "error" => 0,
            "msg"   => "链接名已存在",
        ));
    }

    public function get_some_Friendship(){
        $id = input("param.id");
        $data = $this->obj->select_some_link($id);
        $this->assign('data', $data);
    }

    public function update_some_friendship(){
        $data = input("post.");
        $status = $data["link_status"];
       if($status == 1){
        $num = $this->select_start_friendship_quantity();
        if($num >= 4){
             return json(array(
                 'error' => 0,
                 'msg'   => '你最多只能开启四个链接',
             ));
         }
     }  
       if($status == 2){
        $data['link_status'] = 1;
       }
       $code = $this->obj->update_link($data);
        if($code){
            return json(array(
                'error' => 1,
                'msg'   => '链接信息修改成功',
            ));
        }
        return json(array(
            'error' => 0,
            'msg'   => '链接修改失败',
        )); 
    }
}