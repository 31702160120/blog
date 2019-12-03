<?php
namespace app\admin\controller;
use think\Db;
use think\Session;
use app\admin\model\Comment;
class CommentList extends Base{
    private $obj;

    public function _initialize(){
        $this->obj = new Comment();
        $this -> checkUser();
    }

    // 评论审核
    public function index(){
        $code = input('param.order', 0 ,'intval');
        $this->get_comment_message($code);
        return $this->fetch('comment/index');
    }

    // 添加留言
    public function lave_wordAdd(){
        $nid = input('param.id', 0 ,'intval');//子栏目
        $this->assign('nid', $nid);
        return $this->fetch('lave_word/add');
    }

    // 回复留言
    public function lave_wordAlter(){
        $id = input("param.id");
        $this->assign('id', $id);
        return $this->fetch('lave_word/alter');
    }

    public function get_comment_message($code){
        switch($code){
            case 1:
                $order = [
                            'comment_time' => 'asc',
                        ];
                break;
            case 2:
                $order = [
                            'comment_time' => 'desc',
                        ];
                break;
            default:
                $order = [
                         'comment_time' => 'desc',
                       ];  
            break;
        }
        $condition =[
            "comment_status" => ['neq',-1],
            "comment_check"  => '0',
        ];

        $data = Db::view('user',['username,namestatus'])
        ->view('comment','*','user.id = comment.comment_nameid')
        ->where($condition)
        ->order($order)
        ->paginate();
        
        $num = $this->obj->seletcNewsCommentNumder($condition);
        $this->assign('data', $data);
        $this->assign('num', $num);
        $this->assign('code', $code);
    }


    // 留言栏
    public function lave_word(){
        $code = input('param.order', 0 ,'intval');
        $this->get_leave_word_message($code);
        return $this->fetch('lave_word/index');
    } 

    public function get_leave_word_message($code){
        switch($code){
            case 1:
                $order = [
                            'comment_time' => 'asc',
                        ];
                break;
            case 2:
                $order = [
                            'comment_time' => 'desc',
                        ];
                break;
            case 3:
                $order = [
                         'comment_status' => 'asc',
                        ];
                break;
            case 4:
                $order = [
                    'comment_status' => 'desc',
                ];     
                break;
            default:
                $order = [
                         'comment_time' => 'desc',
                       ];  
            break;
        }
        $condition =[
            "comment_status" => ['neq',-1],
            "comment_check"  => ['neq', 0],
            "comment_nid" => "0",
        ];

        $data = Db::view('user',['username,user_img,namestatus'])
        ->view('comment','*','user.id = comment.comment_nameid')
        ->where($condition)
        ->order($order)
        ->paginate();
        $this->assign('data', $data);
        $this->assign('code', $code);
    }

    public function sort(){
        $order = input('param.order');
        $index = $_SERVER['HTTP_REFERER'];
        $url = str_replace('.html','',$index);
        $url .= '/order/'.$order;
        $this->result($url, 1);
    }

    public function update_status(){
        $data = input("post.");
        $data["comment_status"] = $data["comment_status"]== 0? 1:0;
        $code = $this->obj->update_comment_status($data);
        if($code){
            return json(array(
                'error' => 1,
                'status' => $data["comment_status"] ,
            ));
        }
        return json(array(
            'error' => 0,
            'msg'   => '状态更新失败',
        ));
    }


    public function delete_current_comment(){
        $id = input("post.");
        $condition = ['id' => $id['id']];
        $code = $this->obj->delete_comment($condition);
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

    public function del_some_comment(){
        $data = input("post.");
        $str = implode(",",$data["id"]);
        $condition = ["id" => ["in",$str]];
        $code = $this->obj->delete_comment($condition);
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

    public function check_some_comment(){
        $data = input("post.");
        $str = implode(",",$data["id"]);
        $condition = ["id" => ["in",$str]];
        $code = $this->obj->check_comment($condition);
        if($code){
            return json([
                'msg' => "通过成功",
                'status' => 1,
            ]);
        }
        return json([
            'msg' => "通过失败",
            'status' => 0,
        ]);
    }

    public function add_NewsLave_word(){
        $data = input("post.");    
        $message = [
            "comment_pid" =>  $data["comment_pid"],
            "comment_nid" =>  $data["comment_nid"],
            "comment_news" => $data["comment_news"],
            "comment_status" => '1',
            "comment_nameid" =>  '1',
            "comment_check" => '1',
            "comment_privilege" => '1',
        ];
        $code = $this->obj->insertIntoBloggerLeaveWord($message);
        if($code){
            return json(array(
                'error' => 1,
                'msg' => '留言添加成功',
            ));
        }
        return json(array(
            'error' => 0,
            'msg' => '留言添加失败',
        ));
    }

    public function replyLaveWord(){
        $data = input("post.");
        $message = $this->obj->get($data['id']);
        $list["comment_nid"] = $message->comment_nid;
        $list["comment_pid"] = $message->id;
        $list["comment_nameid"] = '1';
        $list["comment_status"] = '1';
        $list["comment_news"] = $data["comment_news"];
        $list["comment_check"] = '1';
        $list["comment_privilege"] = '1';
        $result = $this->obj->insertIntoBloggerLeaveWord($list);
        if($result){
            return json(array(
                'error' => 1,
                'msg' => "回复成功",
            ));
        }
        return json(array(
                'error' => 0,
                'msg' => "回复失败",
                'data' => $data,
        ));
    }

    public function update_check(){
        $data = input("post.");
        $code = $this->obj->update_comment_check($data);
        if($code){
            return json(array(
                'error' => 1,
                'status' =>'状态更新成功',
            ));
        }
        return json(array(
            'error' => 0,
            'msg'   => '状态更新失败',
        ));
    }
}