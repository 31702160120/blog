<?php
namespace app\index\controller;
use app\index\model\News;
use \think\Session;
use app\index\model\History;
use app\index\model\Column as currentColumn;
use think\Db;
class Column extends Base{


    public function index()
    {
        $this->getThisColunmNewsData();
        return $this->fetch("column/column");
    }

    public function getThisColunmNewsData(){
        $cid = input('param.news_cid');
        $news = new News();
        $uid = Session::get('uid');
        if($uid > 1){
            $history = new History();
            $nid = $history->selectShieldNewsId($uid);
            if($nid){
                $result = $this->selectNews($cid,1,$nid);
                $love = $this->getLoveColumnYes($news,$cid,$nid);
            }else{
                $result = $this->selectNews($cid);
                $love = $this->getLoveColumn($news,$cid);
            }
        }else{
            $result = $this->selectNews($cid);
            $love = $this->getLoveColumn($news,$cid);
        }
        $this->getColumtName($cid);
        $this->assign('result', $result);
        $this->assign('love', $love);
    }

    public function selectNews($cid,$code=0, $data= ''){
       if($code == 1){
        $result = Db::view('column',['column_name'=>'name'])
        ->view('news','news_img,id,news_time,news_intro,news_hits,news_name,news_label','column.column_id = news.news_cid')
        ->where('news_cid',$cid)
        ->where('id','not in', $data)
        ->where('news_status = 1')
        ->order('id','desc')
        ->paginate(5);
        return $result;
       }
       $result = Db::view('column',['column_name'=>'name'])
       ->view('news','news_img,id,news_time,news_intro,news_hits,news_name,news_label','column.column_id = news.news_cid')
       ->where('news_cid',$cid)
       ->where('news_status = 1')
       ->order('id','desc')
       ->paginate(5);
       return $result;
    }

    public function getColumtName($cid){
         
        $name = currentColumn::get(['column_id'=>$cid]);
        $this->assign('name', $name);
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

    public function search(){
        $data = input('param.keyword');
        $news = new News();
        if(!isset($data)){
            $data = "博客";
        }

        $uid = Session::get('uid');
        if($uid > 1){
            $history = new History();
            $nid = $history->selectShieldNewsId($uid);
            if($nid){
                $result = $this->searchYes($data,1,$nid);
                $love = $news->selectLoveNewsYes($nid);
            }else{
                $result = $this->searchYes($data);
                $love = $news->selectLoveNews();
            }
        }else{
            $result = $this->searchYes($data);
            $love = $news->selectLoveNews();
        }
        $name['column_name'] = $data;
        $love = $news->selectLoveNews();
        $this->assign('name', $name);
        $this->assign('result', $result);
        $this->assign('love', $love);
        return $this->fetch("column/column");
    }

    public function searchYes($data,$code = 0, $id=''){
        if($code == 1){
            $result = Db::view('column',['column_name'=>'name'])
                            ->view('news','news_img,id,news_time,news_intro,news_hits,news_name,news_label','column.column_id = news.news_cid')
                            ->where('id', 'not in', $id)
                            ->where('news_status = 1')
                            ->where('news_name|news|news_label','like','%'.$data.'%')
                            ->order('news_hits','desc')
                            ->paginate(5,false,['query'=>['keyword'=> $data]]);
            return $result;
        }
        $result = Db::view('column',['column_name'=>'name'])
        ->view('news','news_img,id,news_time,news_intro,news_hits,news_name,news_label','column.column_id = news.news_cid')
        ->where('news_status = 1')
        ->where('news_name|news|news_label','like','%'.$data.'%')
        ->order('news_hits','desc')
        ->paginate(5,false,['query'=>['keyword'=> $data]]);
        return $result;
    }

    public function label(){
        $data = input('param.keyword');
        $news = new News();
        if(!isset($data)){
            $data = "博客";
        }
        $uid = Session::get('uid');
        if($uid > 1){
            $history = new History();
            $nid = $history->selectShieldNewsId($uid);
            if($nid){
                $result = $this->labelYes($data,1,$nid);
                $love = $news->selectLoveNewsYes($nid);
            }else{
                $result = $this->labelYes($data);
                $love = $news->selectLoveNews();
            }
        }else{
            $result = $this->labelYes($data);
            $love = $news->selectLoveNews();
        }
        $name['column_name'] = $data;
        $love = $news->selectLoveNews();
        $this->assign('name', $name);
        $this->assign('result', $result);
        $this->assign('love', $love);
        return $this->fetch("column/column");
    }

    public function labelYes($data,$code = 0, $id=''){
        if($code == 1){
            $result = Db::view('column',['column_name'=>'name'])
                            ->view('news','news_img,id,news_time,news_intro,news_hits,news_name,news_label','column.column_id = news.news_cid')
                            ->where('id', 'not in', $id)
                            ->where('news_status = 1')
                            ->where('news_label','like','%'.$data.'%')
                            ->order('news_hits','desc')
                            ->paginate(5,false,['query'=>['keyword'=> $data]]);
            return $result;
        }
        $result = Db::view('column',['column_name'=>'name'])
        ->view('news','news_img,id,news_time,news_intro,news_hits,news_name,news_label','column.column_id = news.news_cid')
        ->where('news_status = 1')
        ->where('news_label','like','%'.$data.'%')
        ->order('news_hits','desc')
        ->paginate(5,false,['query'=>['keyword'=> $data]]);
        return $result;
    }
}
