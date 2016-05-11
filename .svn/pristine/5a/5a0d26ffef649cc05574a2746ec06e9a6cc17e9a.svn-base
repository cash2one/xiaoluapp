<?php
/**
 * @copyright: @快游戏 2015
 * @description: 提供给乐视CDN白名单的接口，主要接收同步完成后的回调方法
 * @file:callback.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-11-17  15:58
 * @version 1.0
 **/
include("../config.inc.php");

$message = file_get_contents("php://input");


if(!empty($message)){
    error_log($message,3,'../data/1.txt');
    exit;
}

$user = trim(get_param('user'));
$status = trim(get_param('status'));
$outkey = trim(get_param('outkey'));
$fsize = trim(get_param('fsize'));
$storeurl = trim(get_param('storeurl'));

if($status==200){
	$arr = array(
		'user' => $user,
	    'status'  => $status,
	    'outkey' => $outkey,
	    'fsize'  => $fsize,
	    'storeurl' => $storeurl,
	    'msg' => '同步完成'
	);
	echo json_encode($arr);
}else{
	echo json_encode(array('status'=>$status,'msg'=>'同步失败'));
}