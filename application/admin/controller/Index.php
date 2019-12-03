<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\Column;
use app\admin\model\News;
use app\admin\model\User;
use app\admin\model\Comment;
use \think\Session;
use think\Db;

class Index extends Base
{
   
    public function index()
    {
       $data = $this->get_column();
        $name = Session::get('name');
        $logo = Session::get('logo');
        $this->assign('data', $data);
        $this->assign('name', $name);
        $this->assign('logo', $logo);
        return $this->fetch();
    }

    public function welcome()
    {
        $news = new News();
        $user = new User();
        $comment = new Comment;
        $newsNumb = $news->selectNewsNumder();
        $condition = [
            'user_status' => ['neq', -1],
		];
        $userNumb = $user->selectNumder($condition);

        $condition2 = [
            'user_status' => ['eq', 0],
		];
        $userNumb2 = $user->selectNumder($condition2);
        
        $data = [
            "comment_status" => ['neq',-1],
            "comment_check"  => '1',
        ];
        $num = $comment->seletcNewsCommentNumder($data);

        $data2 = [
            "comment_status" => ['neq',-1],
            "comment_check"  => '0',
        ];
        $num2 = $comment->seletcNewsCommentNumder($data2);
        $version = Db::query('SELECT VERSION() AS ver');
        $name = Session::get('name');
        $info = array(
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '服务器端口号' => $_SERVER['SERVER_PORT'],
            'PHP运行方式'=>php_sapi_name(),
            '网站目录' => $_SERVER['DOCUMENT_ROOT'],
            'MYSQL版本' => $version[0]['ver'],
            'PHP版本' => PHP_VERSION,
            'ThinkPHP版本'=>THINK_VERSION.' [ <a href="http://thinkphp.cn" target="_blank">查看最新版本</a> ]',
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '剩余空间'=>round((disk_free_space(".")/(1024*1024)),2).'M',
            );
            $this->assign('newsNumb', $newsNumb);
            $this->assign('userNumb', $userNumb);
            $this->assign('userNumb2', $userNumb2);
            $this->assign('info',$info);
            $this->assign('name', $name);
            $this->assign('num', $num);
            $this->assign('num2', $num2);
        return $this->fetch();
    }

    public function get_column(){
        $obj = new Column();
        $data = $obj->select_columm();
        return $data;
    }

    
}
