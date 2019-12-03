<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\Images;
use app\index\model\News;
use app\index\model\Column;
use \think\Session;
use app\index\model\History;
class Index extends Base
{
    private $news;
    
    
    public function index()
    {
        
        $this->news = new News();
       $slideshow = $this->getSlideshow(); //轮播图
       $uid = Session::get('uid');
       if($uid > 1){
           $history = new History();
           $nid = $history->selectShieldNewsId($uid);
           if($nid){
                $this->getColunm(1,$nid);   //三个展示栏目
                $news = $this->getNewNewsYes($nid);  //最新文章
                $love = $this->getLoveColumnYes($nid); //大家都喜欢
           }else{
                $this->getColunm();   //三个展示栏目
                $news = $this->getNewNews();  //最新文章
                $love = $this->getLoveColumn(); //大家都喜欢
           }
       }else{
            $this->getColunm();   //三个展示栏目
            $news = $this->getNewNews();  //最新文章
            $love = $this->getLoveColumn(); //大家都喜欢
       }

       $this->assign('news', $news);
       $this->assign('slideshow', $slideshow);
       $this->assign('love', $love);
       return $this->fetch();
    }

    public function getSlideshow(){
        $img = new Images();
        $data = $img->select_images();
       return $data;
    } 

    public function getNewNews(){
        $list = News::all(function($query){
            $query->where('news_status', 1)
                    ->order('id desc')
                     ->limit(4);
        });
        return $list;
    }

    public function getNewNewsYes($id){
        $list = News::all(function($query)use($id){
            $query->where('news_status', 1)
                    ->where('id', 'not in', $id)
                    ->order('id desc')
                    ->limit(4);
            });
        return $list;
    }

    public function getStatusON($origin, $num){
        $column = new Column();
        $list = $column->where("column_status", "eq", "1")
                        ->order("column_status asc")
                        ->limit($origin,$num)
                        ->select();
        return $list;                
    }

    public function getColunm($code = 0, $nid = ''){
        $data1 = $this->getStatusON(0,1);
        $data2 = $this->getStatusON(1,1);
        $data3 = $this->getStatusON(2,1);

        $list1 = $this->getColumnCid($data1);
        $list2 = $this->getColumnCid($data2);
        $list3 = $this->getColumnCid($data3);

        if($code == 1){
            $oneColumn = $this->news->selectNewsYes($list1['cid'], $nid);
            $twoColumn = $this->news->selectNewsYes($list2['cid'], $nid);
            $threeColumn = $this->news->selectNewsYes($list3['cid'], $nid);
            
        }else{
            $oneColumn = $this->news->selectNews($list1['cid']);
            $twoColumn = $this->news->selectNews($list2['cid']);
            $threeColumn = $this->news->selectNews($list3['cid']);
        }

        $this->assign('list1', $list1);
        $this->assign('list2', $list2);
        $this->assign('list3', $list3);

        $this->assign('oneColumn', $oneColumn); //第一个栏目
        $this->assign('twoColumn', $twoColumn); //第二个栏目
        $this->assign('threeColumn', $threeColumn); //第三个栏目
        $num = 0;
        $this->assign('num', $num); //计数器
    }

    public function getColumnCid($data){
        foreach($data as $val){
            $list['cid'] = $val['column_id'];
            $list['name'] = $val['column_name'];
        }
        return $list;
    }

    public function getLoveColumn(){
        $result = $this->news->selectLoveNews();
        return $result;
    }
    
    public function getLoveColumnYes($id){
        $result = $this->news->selectLoveNewsYes($id);
        return $result;
    }

    public function ce(){
        $data = input("get.");
       return json(array(
           'data' => $data,
       ));
    }
}
