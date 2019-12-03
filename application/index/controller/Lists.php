<?php
namespace app\index\controller;
use think\Db;
use app\index\model\News;
use app\index\model\History;
use app\index\model\Column;
use app\index\model\Comment;
use \think\Session;
class Lists extends Base
{
    public function index()
    {
        $id = input('param.id');
        $status = Session::get('name');
        $praise = 0;
        if(Session::get('name')){
            if(Session::get('status') == 0){
                $uid = Session::get('uid');
                $hit = new History();
                $result = History::get(['uid' => $uid, 'nid' => $id]);
                if($result){
                    $res = Db::table("tp_history")->where('id', $result->id)->update([
                        'time' => time()
                    ]);
                    $praise = $result->praise;
                }else{
                    $rel = Db::table("tp_history")->insert([
                        'nid' => $id,
                        'uid' => $uid,
                        'time' => time(),
                    ]);
                }
            }
        }
         $this->getComment($id);
         $this->getCurrentName($id);
        $this->getCommentNumber($id);
        $this->copyright();

        $this->assign('status', $status);
        $this->assign('praise', $praise);
        return $this->fetch('list/list');
    }

    public function getCurrentName($id){
        $data = new News();
        $news = $data->get($id);
        $num = $news->news_hits;
        ++ $num;
        $news->where('id' ,$id)->update(['news_hits' => $num]);
        $colummName = Column::get($news->news_cid);
        $this->getNewsSide($data, $news);
        $uid = Session::get('uid');
        if($uid > 1){
            $history = new History();
            $nid = $history->selectShieldNewsId($uid);
            if($nid){
                $love = $this->getLoveColumnYes($news,$news->news_cid,$nid);    
            }else{
                $love = $this->getLoveColumn($data,$news->news_cid);
            }
        }else{
            $love = $this->getLoveColumn($data,$news->news_cid);
        }
        $this->assign('colummName' ,$colummName);
        $this->assign('love', $love);
        $this->assign('news', $news);
    }
    public function getLoveColumn($news,$cid){
        $data= [
            'news_cid' => $cid,
        ];
        $result = $news->selectColumnLoveNews($data);
        return $result;
    }

    public function getLoveColumnYes($news,$cid,$id){
        $data= [
            'news_cid' => $cid,
        ];
        $result = $news->selectColumnLoveNewsYes($data,$id);
        return $result;
    }

    public function copyright(){
        $copyright = Db::table('tp_system')->where('id',1)->find();
        $this->assign('copyright', $copyright);
    }
   public function getNewsSide($obj, $news){
        $upCondition = [
            'news_cid' => $news->news_cid,
            'id'   => ['Gt',$news->id],
        ];
        $upOrder= [
            "id" => 'asc',
        ];
        $upNews = $obj->selectCurremtNewsSideNews($upCondition , $upOrder);
        if($upNews == null){
            $upNews = $news;
        }
        $belowCondition = [
            'news_cid' => $news->news_cid,
            'id'   => ['Lt',$news->id],
        ];
        $belowOrder= [
            "id" => 'desc',
        ];
        $belowNews = $obj->selectCurremtNewsSideNews($belowCondition, $belowOrder);
        if($belowNews == null){
            $belowNews = $news;
        }

        $this->assign('upNews', $upNews);
        $this->assign('belowNews', $belowNews);
    }

    public function getComment($id){
        $comment = new Comment();
        $result = Db::view('user',['ni,namestatus,user_img'])
                     ->view('comment','*','user.id = comment.comment_nameid')
                      ->where('comment_status','1')
                      ->where('comment_pid', '0')
                      ->where('comment_nid', $id)
                      ->order('comment_time desc')
                      ->paginate(5);
        $this->assign('result',$result);
        $data = array();
        foreach($result as $val){ 
            $data[] = $val;
            $this->getReplyComment($val['id'], $data, $id);
        }
        $this->assign('data',$data);
    }

    public function getReplyComment($id, &$array,  $nid){
                $data =  Db::view('user',['ni,namestatus,user_img'])
                                ->view('comment','*','user.id = comment.comment_nameid')
                                ->where('comment_status','1')
                                ->where('comment_pid',$id)
                                ->where('comment_nid', $nid)
                                ->select();
            foreach($data as $val){
                $array[] = $val;
                $this->getReplyComment($val['id'], $array, $nid);
            }
            return $array;
    }

    public function getCommentNumber($id){
        $comment = new Comment();
        $gross = $comment->selectCommentNumder(['comment_nid' => $id]);
        $blogger  = $comment->selectCommentNumder(['comment_nid' => $id , 'comment_privilege' => '1']);
        $visitor = $comment->selectCommentNumder(['comment_nid' => $id, 'comment_privilege' => '0']);
        $this->assign('gross',$gross);
        $this->assign('blogger',$blogger);
        $this->assign('visitor', $visitor);
    }

    public function praise(){
        $id = input("param.nid");
        if(Session::get('name') == ''){
            return json(array(
                'error' => 0
            ));
        }
       if(Session::get('status') == 1){
            return json(array(
                'error' => 0
            ));
       }
       Db::startTrans();
       try{
           Db::table('tp_news')->where('id',$id)->setInc('news_praise');
           Db::table('tp_history')->where('nid',$id)->setInc('praise');
           Db::commit();    
       } catch (\Exception $e) {
           Db::rollback();
       }
    }

    public function shield(){
        $id = input('param.id');
        if(Session::get('uid') == 1){
            return json(array(
                'error' => 0,
                'msg'  => '博主不能屏蔽自己的文章',
            ));
        }
        $uid = Session::get('uid');
       $rel = Db::table('tp_history')->where(['uid' => $uid, 'nid' => $id])->update(['status' => '-1']);
       if($rel){
        return json(array(
            'error' => 1,
            'msg'  => '文章已被屏蔽',
        ));
       }
       return json(array(
        'error' => 0,
        'msg'  => '文章已屏蔽失败',
    ));
    }

}
