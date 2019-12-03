<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\admin\model\Images;
class SlideshowList extends Base{
    private $obj;

    public function _initialize(){
        $this->obj = new Images();
        $this -> checkUser();
    }
    // 轮播图管理菜单
    public function index(){
        $this->get_slideshow_message();
        return $this->fetch('slideshow/index');
    }
    
    // 轮播图添加
    public function slideshowAdd(){
        return $this->fetch('slideshow/add');
    }

    public function get_slideshow_message(){
        $data = $this->obj->select_images();
        $num = $this->obj->selectImagesNumder();
        $this->assign('data', $data);
        $this->assign('num', $num);
    }

    public function select_start_slideshow_quantity(){
        $list = $this->obj->where("images_status", "1")
                          ->count();
        return $list;
    }

    public function update_slideshow_status(){
        $data = input("post.");
        $data["images_status"] = $data["images_status"]== 0? 1:0;
        if($data["images_status"] == 1){
            $list = $this->select_start_slideshow_quantity();
            if($list >= 4){
                return json(array(
                    'error' => 0,
                    'msg'   => '你最多只能开启四个栏目',
                ));
              }
        }
        $code = $this->obj->update_img_status($data);
        if($code){
            return json(array(
                'error' => 1,
                'status' => $data["images_status"],
            ));
        }
        return json(array(
            'error' => 0,
            'msg'   => '状态更新失败',
        ));
    }

    public function delete_current_slideshow(){
        $id = input("post.");
        $condition = ['id' => $id['id']];
        $code = $this->obj->delete_img($condition);
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

    public function del_some_slideshow(){
        $data = input("post.");
        $str = implode(",",$data["id"]);
        $condition = ["id" => ["in",$str]];
        $code = $this->obj->delete_img($condition);
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

    public function ajaxCe(Request $request){
        $file = $request->file('file');
       if($file){
         $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'slideshow');
         $imgSrc = '/uploads/slideshow/'.$info->getSaveName();
         $status = $request->param('images_status');
         $msg = '';
         if($status == 1){
                 $num = $this->select_start_slideshow_quantity();
                 if($num >= 4){
                     $status = 0; 
                     $msg = "添加成功由于已经启动了四个栏目，所以此栏目需要手动启动";
                 }
             }
        $list = [
                "images_url" => $imgSrc,
                "images_status" => $status,
        ];
        $code = $this->obj->insertInto_newImg($list);
         if($code){
                 $msg = $msg==''? "添加成功":$msg;
                 return json(array(
                     'error' => 1,
                     'msg' => $msg,
                 ));
             }
             return json(array(
                'error' => 1,
                'msg' => $imgSrc,
            ));
       }
        return json(array(
        'error' => 0,
        'msg' => "图片上传失败",
        ));
    }

}