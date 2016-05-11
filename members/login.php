<?php

/**
 * @copyright: @快游戏 2015
 * @description: 用户登录接口
 * @file: login.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-09-16  15:47
 * @version 1.0
 **/
include("../config.inc.php");
include('../api/ucenter.config.inc.php');
include("../uc_client/client.php");

// 判断是否加密
$encryp = get_param('encrypt');
$encryp = empty($encryp) ? true : false;

$key=trim(get_param('key')); //验证KEY
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($key,$request);
if(empty($key_auth) || empty($key)){
    exit(ResponseJson(array('code'=>9,'msg'=>'key error','error'=>10001), $encryp));
}

$username = new_decrypt(trim(get_param('username')));
$pwd = new_decrypt(trim(get_param('pwd')));
if(empty($username) ){
	exit(ResponseJson(array('code'=>9,'msg'=>'嘿，手机号还没有填写','error'=>10004), $encryp));
}

if( empty($pwd)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，密码还没有填写','error'=>10012), $encryp));
}

if(!check_phoneformat($username)){
    exit(ResponseJson(array('code'=>9,'msg'=>'正确的手机号为11位','error'=>10005), $encryp));
}

$loginip  =  get_onlineip();
$arr = uc_user_login($username,$pwd, 0,0,'','',$loginip);
if(empty($arr)){
    exit(ResponseJson(array('code'=>9,'msg'=>'该手机号还没有注册','error'=>10007), $encryp));
}
$uid = intval($arr[0]);
$username =  trim($arr[1]);
$phone = floatval(trim($arr[2]));
if($uid>0){
    
    if(defined("MEMCACHE_IS_OPEN") && MEMCACHE_IS_OPEN){
        ini_set('session.save_handler','memcache');
        ini_set('session.save_path',MEMCACHE_SESSION_HOST);
    }
    
    session_start();
    $_SESSION['kyx_username'] = $username;
    exit(ResponseJson(array('code'=>0,'token'=>session_id(),'data'=>array('uid'=>$uid)), $encryp));
}else{
    switch ($uid) {
    	case -1:
    	   exit(ResponseJson(array('code'=>9,'msg'=>'该手机号还没有注册','error'=>10007), $encryp));
    	break;
    	case -2:
    	    exit(ResponseJson(array('code'=>9,'msg'=>'登录密码错误','error'=>20011), $encryp));
    	break;
    	default:
    	    exit(ResponseJson(array('code'=>9,'msg'=>'其他错误','error'=>20012), $encryp));
    	break;
    }
}



