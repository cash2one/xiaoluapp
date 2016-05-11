<?php
/**
 * @copyright: @快游戏 2015
 * @description: 登录后 重置密码的接口
 * @file:reset_pwd.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-09-17  15:58
 * @version 1.0
 **/

include("../config.inc.php");
include('../api/ucenter.config.inc.php');
include("../uc_client/client.php");

// 判断是否加密
$encryp = get_param('encrypt');
$encryp = empty($encryp) ? false : true;

if(defined("MEMCACHE_IS_OPEN") && MEMCACHE_IS_OPEN){
    ini_set('session.save_handler','memcache');
    ini_set('session.save_path',MEMCACHE_SESSION_HOST);
}

$key=trim(get_param('key')); //验证KEY
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($key,$request);
if(empty($key_auth) || empty($key)){
    exit(ResponseJson(array('code'=>9,'msg'=>'key error','error'=>10001), $encryp));
}


$sessionid = trim(trim(get_param('token')));
if(empty($sessionid)){
    exit(ResponseJson(array('code'=>9,'msg'=>'token错误','error'=>10002), $encryp));
}
session_id($sessionid);
session_start();
$sess_username=trim($_SESSION['kyx_username']);
if(empty($sess_username)){
    exit(ResponseJson(array('code'=>9,'msg'=>'登录已过期','error'=>10003), $encryp));
}

$phonenumber = new_decrypt(trim(get_param('phonenumber')));
$oldpwd = new_decrypt(trim(get_param('oldpwd')));//旧密码
$newpwd = new_decrypt(trim(get_param('newpwd')));//新密码
if(empty($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，手机号还没有填写','error'=>10004), $encryp));
}
if(empty($oldpwd)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，旧密码还没有填写','error'=>20008), $encryp));
}
if(empty($newpwd)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，新密码还没有填写','error'=>20009), $encryp));
}


if(!check_phoneformat($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'正确的手机号为11位','error'=>10005), $encryp));
}

//查找该手机是否在用户信息表里
$arr = uc_get_user($phonenumber);
if(empty($arr)){
    exit(ResponseJson(array('code'=>9,'msg'=>'该手机号还没有注册','error'=>10007), $encryp));
}

$pwd = $arr[3];
$salt = $arr[4];
//旧密码加密后的结果
$oldpwd = preg_match('/^\w{32}$/', $oldpwd) ? $oldpwd : md5($oldpwd);
$oldpwd = md5($oldpwd.$salt);

if($pwd <> $oldpwd ){
    exit(ResponseJson(array('code'=>9,'msg'=>'原来的密码输入错误','error'=>20010), $encryp));
}

$status = uc_reset_pwd($phonenumber,$newpwd);
if($status>0){
    exit(ResponseJson(array('code'=>0), $encryp));
}else{
    exit(ResponseJson(array('code'=>9,'msg'=>'更新失败','error'=>10010), $encryp));
}
