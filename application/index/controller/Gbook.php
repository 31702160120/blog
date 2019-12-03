<?php
namespace app\index\controller;
use think\Db;
use app\index\model\Comment;
use \think\Session;
use app\index\model\History;
use app\index\model\News;
use \think\Validate;
class Gbook extends Base
{
    public $data =array();
    public function index()
    {
        $this->getComment();
        $this->getCommentNumber();

        $uid = Session::get('uid');
        if($uid > 1){
            $history = new History();
            $nid = $history->selectShieldNewsId($uid);
            if($nid){
                
                $love = $this->getLoveColumnYes($nid); //大家都喜欢
            }else{
               
                $love = $this->getLoveColumn(); //大家都喜欢
            }
        }else{
            
            $love = $this->getLoveColumn(); //大家都喜欢
        }

        $this->assign('love', $love);
        return $this->fetch("gbook/gbook");
    }

    public function getComment(){
        $comment = new Comment();
        $result = Db::view('user',['ni,namestatus,user_img'])
                     ->view('comment','*','user.id = comment.comment_nameid')
                      ->where('comment_status','1')
                      ->where('comment_pid', '0')
                      ->where('comment_nid', '0')
                      ->order('comment_time desc')
                      ->paginate(5);
        $this->assign('result',$result);
        $data = array();
        foreach($result as $val){ 
            $data[] = $val;
            $this->getReplyComment($val['id'], $data);
        }
        $this->assign('data',$data);
    }

    public function getReplyComment($id, &$array){
                $data = Db::view('user',['ni,namestatus,user_img'])
                                ->view('comment','*','user.id = comment.comment_nameid')
                                ->where('comment_status','1')
                                ->where('comment_pid',$id)
                                ->where('comment_nid', '0')
                                ->select();
            foreach($data as $val){
                $array[] = $val;
                $this->getReplyComment($val['id'], $array);
            }
            return $array;
    }

    public function getLoveColumn(){
        $news = new News();
        $result = $news->selectLoveNews();
        return $result;
    }

    public function getLoveColumnYes($id){
        $news = new News();
        $result = $news->selectLoveNewsYes($id);
        return $result;
    }
    
    public function getCommentNumber(){
        $comment = new Comment();
        $gross = $comment->selectCommentNumder(['comment_nid' => 0]);
        $blogger  = $comment->selectCommentNumder(['comment_nid' => 0 , 'comment_privilege' => '1']);
        $visitor = $comment->selectCommentNumder(['comment_nid' => 0, 'comment_privilege' => '0']);
        $this->assign('gross',$gross);
        $this->assign('blogger',$blogger);
        $this->assign('visitor', $visitor);
    }

    public function setComment(){
        $data = input('post.');
        $name = Session::get('name').'('.Session::get('logo').')';
        if(isset($name)){
            $comment = new Comment;
             $oldtime = Session::get('time');
            $time = time();
            if(!isset($oldtime)){
                session('time', $time);
            }else{
                $one = $this->prevent($oldtime,$time);
                if($one){
                    return json(array(
                        'error' => 0,
                        'msg' => '留言间隔不能少于10秒',
                    ));
                }
            }
            $status = Session::get('status');
            $uid = Session::get('uid');
            if(isset($data['comment_nid'])){
                $nid = $data['comment_nid'];
            }else{
                $nid = 0;
            }
            $datas =[
                'comment_pid'  =>  '0',
                'comment_nid' =>  $nid,
                'comment_nameid' => $uid,
                 'comment_news' => $data['comment_news'],
                'comment_status' => $status,
                'comment_check' => $status,
                'comment_privilege' => $status,
                'comment_time' => $time,
            ];
             $rel = $comment->inSerintoComment($datas);
            if($rel){
               if($status){
                    return json(array(
                        'error' => 1,
                        'msg' => '留言成功',
                    ));
               }
                return json(array(
                    'error' => 1,
                    'msg' => '留言成功等待博主审核通过'
                ));
            }
            return json(array(
                'error' => 0,
                'msg' => $datas,
            ));
        }
        return json(array(
            'error' => 0,
            'msg' => '参数错误'
        ));
    }

    public function setReply(){
        $data = input('post.');
        $name = Session::get('name').'('.Session::get('logo').')';
        if(isset($name)){
            $comment = new Comment;
            $rule = [
                'comment_news' => 'require|max:100',
                'comment_pid'=>'require',
             ];
             $msg = [
                 'comment_news.require' => '回复内容不能为空',
                 'comment_pid.require' => '参数错误' ,
                 'comment_news.max' => '回复字数不能超过100',
             ];
            $pasw = new Validate($rule, $msg);
            if (!$pasw->check($data)) {
                return json(array(
                    'error' => 0,
                    'msg'=>$pasw->getError()
                ));
            }
            $oldtime = Session::get('time');
            $time = time();
            if(!isset($oldtime)){
                session('time', $time);
            }else{
                $one = $this->prevent($oldtime,$time);
                if($one){
                    return json(array(
                        'error' => 0,
                        'msg' => '留言间隔不能少于30秒',
                    ));
                }
            }
            $reply = $comment::get($data['comment_pid']);
            $status = Session::get('status');
            $uid = Session::get('uid');
            $datas = [
                    'comment_pid'  =>  $data['comment_pid'],
                    'comment_nid' =>  $reply->comment_nid,
                    'comment_nameid' => $uid,
                    'comment_news' => $data['comment_news'],
                    'comment_status' => $status,
                    'comment_check' => $status,
                    'comment_privilege' => $status,
                    'comment_time' => $time,
                ];
            $rel = $comment->inSerintoComment($datas);
            if($rel){
               if($status){
                    return json(array(
                        'error' => 1,
                        'msg' => '留言成功'
                    ));
               }
                return json(array(
                    'error' => 1,
                    'msg' => '留言成功等待博主审核通过'
                ));
            }
            return json(array(
                'error' => 0,
                'msg' => '留言失败'
            ));
        }
        return json(array(
            'error' => 0,
            'msg' => '参数错误',
        ));
    } 


    public function prevent($oldtime,$time){
       if($time > ($oldtime+10)){
            session('time', $time);
            return false;
       }
       return true;
    }
}
