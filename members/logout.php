<?php
include("../config.inc.php");
include('../api/ucenter.config.inc.php');
include("../uc_client/client.php");

if(defined("MEMCACHE_IS_OPEN") && MEMCACHE_IS_OPEN){
    ini_set('session.save_handler','memcache');
    ini_set('session.save_path',MEMCACHE_SESSION_HOST);
}

//验证KEY
$key=trim(get_param('key'));
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($key,$request);
if(empty($key_auth) || empty($key)){
    exit(ResponseJson(array('code'=>9,'msg'=>'key error','error'=>10001)));
}

//检查是否登录
$sessionid = trim(get_param('token'));
if(empty($sessionid)){
    exit(ResponseJson(array('code'=>9,'msg'=>'token错误','error'=>10002)));
}
session_id($sessionid);
session_start();
unset($_SESSION['kyx_username']);
session_destroy();
exit(ResponseJson(array('code'=>0,'msg'=>'退出成功')));