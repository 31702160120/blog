<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function status($status)
{
	if ($status == 0) {
        $str = '<span class="label label-defaunt radius">封禁中</span>';
	}else {
		$str = '<span class ="label label-success radius">正常</span>';
	}
	return $str;
}

function otherStatus($status)
{
	if ($status == 0) {
        $str = '<span class="label label-defaunt radius">已停用</span>';
	}else {
		$str = '<span class ="label label-success radius">启用中</span>';
	}
	return $str;
}

function commentStatus($status)
{
	if ($status == 0) {
        $str = '<span class="label label-defaunt radius">未审核</span>';
	}else {
		$str = '<span class ="label label-success radius">已审核</span>';
	}
	return $str;
}

function label($str, $url, $key=1){
	$str = trim($str, "'");
	$pieces = explode("#", $str);
	$lab = '';
	foreach($pieces as $val){
		if($key == $val){
			$lab .= '  <a href="'.$url.'?keyword='.$val.'"  style="color: red"><i class="Hui-tags-icon Hui-iconfont">&#xe64b;</i>
				<span>'.$val.'</span></a>&nbsp;';
		}else{
			$lab .= '  <a href="'.$url.'?keyword='.$val.'"><i class="Hui-tags-icon Hui-iconfont">&#xe64b;</i>
			<span>'.$val.'</span></a>&nbsp;';
		}
	}
	return $lab;
}

