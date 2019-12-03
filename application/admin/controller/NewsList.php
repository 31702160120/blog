<?php
namespace app\admin\controller;
use app\admin\model\News;
use app\admin\model\Column;
use app\admin\model\System;
use \think\Session;
use think\Db;
use think\Request;
use app\admin\model\Comment;
class NewsList extends Base{
    private $obj;

    public function _initialize(){
        $this->obj = new News();
        $this -> checkUser();
    }

    // 编写博文
    public function newNews(){
        $this->get_column_message();
        $name = Session::get('name');
        $this->assign('name', $name);
        return $this->fetch('news/blogs-add');
    }
    // 博文菜单
    public function index(){
        $code = input('param.order', 0 ,'intval');
        $news = input('param.news');
        $cid = input('param.cid');
        $this->get_article_message($code, $cid, $news);
        return $this->fetch('news/index');
    }
    // 博文添加
    public function newsAdd(){
        $cid = input("param.cid");
        $this->assign('cid', $cid);
        $name = Session::get('name');
        $this->assign('name', $name);
        $this->get_column_message();
        return $this->fetch('news/add');
    }
    // 博文修改
    public function newsAlter(){
        $this->AlterCurrentNews();
        $this->get_column_message();
        return $this->fetch('news/alter');
    }

    // 收款码
    public function newsCopyright(){
        $er = Db::table("tp_system")->where('id','1')->find();
        $this->assign('er', $er);
        return $this->fetch('news/qrcode');
    }

    public function get_article_message($code, $cid, $news=''){
        $cObj = new Column();
        switch($code){
            case 1:
                $order = [
                            'news_time' => 'asc',
                        ];
                break;
            case 2:
                $order = [
                            'news_time' => 'desc',
                        ];
                break;
            case 3:
                $order = [
                         'news_hits' => 'asc',
                        ];
                break;
            case 4:
                $order = [
                    'news_hits' => 'desc',
                ];     
                break;
            case 5:
                $order = [
                         'news_status' => 'asc',
                        ];
                break;
            case 6:
                $order = [
                    'news_status' => 'desc',
                ];     
                break;
            default:
                $order = [
                         'id' => 'asc',
                       ];  
                break;
        }
        if(!empty($news)){
            $condition = [
                'news_cid' => $cid,
                'news_status' => ['neq',-1],
                'news|news_name' => ['like', '%'.$news.'%'],
            ];
        }else{
            $condition = [
                'news_cid' => $cid,
                'news_status' => ['neq',-1],
            ];
        }
        $title = $cObj->select_columnName(['column_id' => $cid]);
        $data = $this->obj->select_article($cid, $condition, $order);
        $num = $this->obj->selectArticleNumder($cid);
        $this->assign('code', $code);
        $this->assign('news', $news);
        $this->assign('data', $data);
        $this->assign('title', $title);
        $this->assign('num', $num);
    }

    public function sort(){
        $order = input('param.order');
        $index = $_SERVER['HTTP_REFERER'];
        $url = str_replace('.html','',$index);
        $url .= '/order/'.$order;
        $this->result($url, 1);
    }

    public function get_column_message(){
        $banner = new Column();
        $order = [
            'id' => 'asc',
          ];  
        $columnName = $banner->select_columms($order);
       $this->assign("columnName", $columnName);
    }

    public function update_status(){
        $data = input("post.");
        $data["news_status"] = $data["news_status"]== 0? 1:0;
        $code = $this->obj->update_news_status($data);
        if($code){
            return json(array(
                'error' => 1,
                'status' => $data["news_status"],
            ));
        }
        return json(array(
            'error' => 0,
            'msg'   => '状态更新失败',
        ));
    }

    public function delete_current_news(){
        $id = input("post.");
        $condition = ['id' => $id['id']];
        $code = $this->obj->delete_news($condition);
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

    public function del_some_news(){
        $data = input("post.");
        $str = implode(",",$data["id"]);
        $condition = ["id" => ["in",$str]];
        $code = $this->obj->delete_news($condition);
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

    public function addNewNews(Request $request){
        $file = $request->file('file');
        $data = $request->except(['news_img']);
       if(isset($data['news_comment'])){
        $comment = 1;
       }else{
        $comment = 0;  
       }
       if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'news');
            $imgSrc = '/uploads/news/'.str_replace("\\","/",$info->getSaveName());
          if( $data["editorValue"]){
                $list = [
                    "news_author" => $data["news_author"],
                    "news_cid" => $data["news_cid"],
                    "news_intro" => $data["news_intro"],
                    "news_name" => $data["news_name"],
                    "news_status" => 1,
                    "news" => $data["editorValue"],
                    "news_img" => $imgSrc,
                    "news_label" => $data['news_label'],
                    "news_comment" => $comment,
                ];
                $code = $this->obj->insertIntoNews($list);
                if($code){
                    return json(array(
                        'error' => 1,
                        'msg' => '文章添加成功',
                    ));
                }
                return json(array(
                    'error' => 0,
                    'msg' => '文章添加失败',
                ));
          }
            return json(array(
                'error' => 0,
                'msg' => '内容不能为空',
            ));
        }
        return json(array(
            'error' => 0,
            'msg' => '图片上传失败',
            'data' => $data,
        ));
    }

    public function AlterCurrentNews(){
        $id = input("param.id");
        $data = $this->obj->get($id);
        $this->assign('data', $data);
    }

    public function updateCurrentNews(Request $request){
        $file = $request->file('file');
        $data = $request->param();
        $id = $data['id'];
        $result = $this->obj->where('id',$id)->find();
        if(isset($data['news_comment'])){
            $comment = 1;
           }else{
            $comment = 0;  
           }
        if($result["news_img"] == $data["news_img"]){
            $list = [
                "news_author" => $data["news_author"],
                "news_cid" => $data["news_cid"],
                "news_intro" => $data["news_intro"],
                "news_name" => $data["news_name"],
                "news" => $data["editorValue"],
                "news_label" => $data['news_label'],
                "news_comment" => $comment,
            ];
            $code = $this->obj->updateNews($id, $list);
            if($code){
                $msg = "修改成功";
                $error = 1;
            }else{
                $msg = "修改失败";
                $error = 0;
            }

            return json(array(
                'error' => $error,
                'msg'  => $msg,
            ));
        }else{
            // 图片有修改
            if($file){
                $info = $file->validate(['ext'=>'jpg,png,gif'])
                ->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'news');
                if($info){
                    $imgSrc = '/uploads/news/'.str_replace("\\","/",$info->getSaveName());
                    $list = [
                        "news_author" => $data["news_author"],
                        "news_cid" => $data["news_cid"],
                        "news_intro" => $data["news_intro"],
                        "news_name" => $data["news_name"],
                        "news" => $data["editorValue"],
                        "news_img" => $imgSrc,
                        "news_comment" => $comment,
                    ];
                    $code = $this->obj->updateNews($id, $list);
                    if($code){
                        $msg = "修改成功";
                        $error = 1;
                    }else{
                        $msg = "修改失败";
                        $error = 0;
                    }
        
                    return json(array(
                        'error' => $error,
                        'msg'  => $msg,
                    ));
                   
                }else{
                    return json(array(
                        'error' => 0,
                        'msg' => $file->getError(),
                    ));
                }
            }else{
                return json(array(
                    'error' => 0,
                    'msg' => "图片上传失败",
                ));
            }
        }
    }

     //    评论

     public function getNewsComment(){
        $id = input('param.id');
        $this->getNewsCommentMessege($id);
        $this->getNewsNumder($id);
        $this->assign('id', $id);
        return $this->fetch('news/comment');
    }
    
    public function getNewsCommentMessege($id){
        $code = input('param.order', 0 ,'intval');
        $comment = new Comment();
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
                         'id' => 'asc',
                       ];  
            break;
        }
        $condition =[
            "comment_status" => ['neq',-1],
            "comment_check"  => ['eq', 1],
            "comment_nid" => $id,
        ];
        $data = Db::view('user',['username,namestatus'])
        ->view('comment','*','user.id = comment.comment_nameid')
        ->where($condition)
        ->order($order)
        ->paginate();
        // $data = $comment->select_commentOrder($condition, $order);
        $this->assign('data', $data);
        $this->assign('code', $code);
    }

    public function getNewsNumder($id){
        $comment = new Comment();
        $condition =[
            "comment_status" => ['neq',-1],
            "comment_check"  => ['eq', 1],
            "comment_nid" => $id,
        ];
        $num = $comment->seletcNewsCommentNumder($condition);
        $this->assign('num', $num);
    }

    public function setQRCode(Request $request){
        $file = $request->file('file');
        $data = $request->param();
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif'])
            ->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'wx');
            if($info){
                $imgSrc = '/uploads/wx/'.str_replace("\\","/",$info->getSaveName());
              if(isset($data['news_wx'])){
                $data['news_wx'] = $imgSrc;
              }else{
                $data['news_zfb'] = $imgSrc;
              }
              $result = Db::table("tp_system")->where('id','1')->update($data);
              if($request){
                return json(array(
                    'error' => 1,
                    'msg' => '修改成功',
                ));
              }
              return json(array(
                'error' => 0,
                'msg' => '修改失败',
            ));
            }else{
                return json(array(
                    'error' => 0,
                    'msg' => $file->getError(),
                ));
            }
        }
        return json(array(
            'error' => 0,
            "data" => "图片上传失败",
        ));
    }

    public function recommend(Request $request){
        $file = $request->file('file');
        $data = $request->param();
       if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif'])
            ->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'wx');
            if($info){
                $imgSrc = '/uploads/wx/'.str_replace("\\","/",$info->getSaveName());
                $list = [
                    'news' => $data['news'],
                    'news_gzh' => $imgSrc,
                    'news_title' => $data['news_title'],
                ];
                $result = Db::table("tp_system")->where('id','1')->update($list);
                if($request){
                    return json(array(
                        'error' => 1,
                        'msg' => '修改成功',
                    ));
                }
                return json(array(
                    'error' => 0,
                    'msg' => '修改失败',
                ));
            }
            return json(array(
                'error' => 0,
                'msg' => $file->getError(),
            ));
       }else{
            $list = [
                'news' => $data['news'],
                'news_title' => $data['news_title'],
            ];
            $result = Db::table("tp_system")->where('id','1')->update($list);
            if($request){
                return json(array(
                    'error' => 1,
                    'msg' => '修改成功',
                ));
            }
            return json(array(
                'error' => 0,
                'msg' => '修改失败',
            ));
       }
       return json(array(
            'error' => 0,
            'msg' => '链接失败',
        ));
    }

}