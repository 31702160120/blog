<?php
namespace app\admin\controller;
use app\admin\model\Column;
use app\admin\model\Admin;
use think\Controller;
use think\Db;
use think\Request;
use \think\Session;
class ColumnList extends Base{

    private $obj;

    public function _initialize() {
        $this->obj = new Column;
        $this -> checkUser();
    }

    // 栏目管理菜单
    public function index(){
        $code = input('param.order', 0 ,'intval');
        $data = $this->get_column_message($code);
        $num  = $this->obj->selectColummsNumder();
         
        $this->assign('data', $data);
        $this->assign('code', $code);
        $this->assign('num', $num);
        return $this->fetch('column/index');
    }

    // 栏目添加
    public function columnAdd(){
        return $this->fetch('column/add');
    }

    // 栏目修改
    public function columnAlter(){
        $data = $this->get_select_column();
        $this->assign('data',$data);
        return $this->fetch('column/alter');
    }

     // 博主资料
     public function aboutMe(){
        $data = $this->getMyData();

        $this->assign('data',$data);
        return $this->fetch('column/aboutMe');
    }

    public function getMyData(){
         $myDay = new Admin();
         $data = $myDay->select_My_data();
         return $data;
    }

    public function get_column_message($code){
        switch($code){
            case 1:
                $order = [
                            'column_time' => 'asc',
                        ];
                break;
            case 2:
                $order = [
                            'column_time' => 'desc',
                        ];
                break;
            case 3:
                $order = [
                         'column_status' => 'asc',
                        ];
                break;
            case 4:
                $order = [
                    'column_status' => 'desc',
                ];     
                break;
            default:
                $order = [
                         'id' => 'asc',
                       ];  
            break;
        }
        $data = $this->obj->select_columms($order);
        return $data;
    }

    public function sort(){
        $order = input('param.order');
        $index = $_SERVER['HTTP_REFERER'];
        $url = str_replace('.html','',$index);
        $url .= '/order/'.$order;
        $this->result($url, 1);
    }

    public function select_start_column_quantity(){
            $list = $this->obj->where("column_status", "1")
                              ->count();
            return $list;      
    }

    public function update_status(){
        $data = input("post.");
        $data["column_status"] = $data["column_status"]== 0? 1:0;
        $list = $this->select_start_column_quantity();
        $min = Db::table('tp_column')->where(['id'=> $data['id'],'column_status'=>'1'])->select();
        if($min){
            if($list == 3){
                return json(array(
                    'error' => 0,
                    'msg'   => '最少要开启三个栏目',
                ));
            }
        }
        if($data["column_status"] == 1){
            if($list >= 3){
                return json(array(
                    'error' => 0,
                    'msg'   => '你最多只能开启三个栏目',
                ));
              }
        }
        $code = $this->obj->update_column_status($data);
        if($code){
            return json(array(
                'error' => 1,
                'status' => $data["column_status"],
            ));
        }
        return json(array(
            'error' => 0,
            'msg'   => '状态更新失败',
        ));
    }

    public function delete_current_column(){
        $id = input("post.");
        $condition = ['id' => $id['id']];
        $num = $this->select_start_column_quantity();
        $min = Db::table('tp_column')->where(['id'=> $id['id'],'column_status'=>'1'])->select();
        if($min){
            if($num == 3 ){
                return json(array(
                    'status' => 0,
                    'msg'   => '最少要需要三个栏目',
                ));
            }
        }
        $code = $this->obj->delete_column($condition);
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

    public function del_some_column(){
        $data = input("post.");
        $str = implode(",",$data["id"]);
        $num = $this->select_start_column_quantity();
        $min = Db::table('tp_column')->where(['id'=> ["in",$str],'column_status'=>'1'])->select();
        if($min){
            if($num == 3 ){
                return json(array(
                    'status' => 0,
                    'msg'   => '最少要需要三个栏目',
                ));
            }
        }
        $condition = ["id" => ["in",$str]];
        $code = $this->obj->delete_column($condition);
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

    public function add_newColumn(){
        $data = input("post.");
        $msg = "";
        if(!($this->obj->get(["column_id" => $data["column_id"]]))){
            $time = time();
            $datas= [
                'column_name' => $data['column_name'],
                'column_status' => $data['column_status'],
                'column_id'   => $data['column_id'],
                'column_time' => $time,
            ];
            $num = $this -> hui($datas);
            if($num == 1){
                return json(array(
                    "error" => 1,
                    "msg" => '添加成功' . $num,
                ));
            }
            return json(array(
                "error" => 0,
                "msg"   => "栏目添加失败" . $num,
            )); 
        }
        return json(array(
            "error" => 0,
            "msg"   => "栏目类别号已存在",
        ));
    }

    public function hui($data){
       Db::startTrans();
       $rel = Db::table('tp_column')->insert($data);
       $num=Db::table('tp_column')->where("column_status", "1")->count();
            // 提交事务
        if($num < 3){
            Db::rollback();  
            return 0; 
        }
        Db::commit();  
        return 1;
    }

    public function get_select_column(){
        $id = input('param.id');
        $data = $this->obj->select_columnName(['id' => $id]);
        return $data;
    }

    public function update_some_column(){
        $data = input('post.');
        $status = $data["column_status"];
        $num = $this->select_start_column_quantity();
        $min = Db::table('tp_column')->where(['id'=> $data['id'],'column_status'=>'1'])->select();
        if($min){
            if($num == 3 && $status == 0){
                return json(array(
                    'error' => 0,
                    'msg'   => '最少要开启三个栏目',
                ));
            }
        }
       if($status == 1){
        if($num >= 3){
             return json(array(
                 'error' => 0,
                 'msg'   => '你最多只能开启三个栏目',
             ));
         }
     }
       if($status == 2){  
        $data['column_status'] = 1;
       }
         $code = $this->obj->update_column($data);
        if($code){
            return json(array(
                'error' => 1,
                'msg'   => '栏目信息修改成功',
            ));
        }
        return json(array(
            'error' => 0,
            'msg'   => $data,
        )); 
    }

   

    public function add_newAboutMe(Request $request){
        $file = $request->file('file');
        $data = $request->param();

          // 图片有修改
            if($file){
                $info = $file->validate(['ext'=>'jpg,png,gif'])
                ->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'wx');
                if($info){
                    $imgSrc = '/uploads/wx/'.str_replace("\\","/",$info->getSaveName());
                    $list = [
                        "admin_email" => $data["admin_email"],
                        "admin_name"  => $data["admin_name"],
                        "admin_news"  => $data["admin_news"],
                        "admin_porfession" => $data["admin_porfession"],
                        "admin_wx" => $data["admin_wx"],
                        "admin_wximg" =>  $imgSrc,
                    ];
                }else{
                    return json(array(
                        'error' => 0,
                        'msg' => $file->getError(),
                    ));
                }
            }else{
                $list = [
                    "admin_email" => $data["admin_email"],
                    "admin_name"  => $data["admin_name"],
                    "admin_news"  => $data["admin_news"],
                    "admin_porfession" => $data["admin_porfession"],
                    "admin_wx" => $data["admin_wx"]
                ];
            }
            $code = $this->updateCurrentAdmin($list);
            if($code){
                return json(array(
                    'error' => 1,
                    'msg'   => '信息修改成功',
                ));
            }
             return json(array(
                'error' => 0,
                'msg'   => '信息修改失败',
            ));
    }

   public function updateCurrentAdmin($list){
    Db::startTrans();
    try{
        Db::table('tp_admin')->where('id','1')->update($list);
        Db::table('tp_user')->where('id','1')->update(["ni" => $list["admin_name"]]);
        // 提交事务
        Db::commit();    
    } catch (\Exception $e) {
        Db::rollback();
        return 0;
    }
    session('name',$list["admin_name"]);
    return 1;
   }
}