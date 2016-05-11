<?php
/**
 * @copyright: @快游戏 2015
 * @description: 获取用户信息的接口
 * @file:get_user_info.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-11-07  15:58
 * @version 1.0
 **/


include("../config.inc.php");
include('../api/ucenter.config.inc.php');
include('../sms.config.inc.php');
include("../uc_client/client.php");

if(defined("MEMCACHE_IS_OPEN") && MEMCACHE_IS_OPEN){
    ini_set('session.save_handler','memcache');
    ini_set('session.save_path',MEMCACHE_SESSION_HOST);
}

// 判断是否加密
$encryp = get_param('encrypt');
$encryp = empty($encryp) ? false : true;

$key=trim(get_param('key')); //验证KEY
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($key,$request);
if(empty($key_auth) || empty($key)){
    exit(ResponseJson(array('code'=>9,'msg'=>'key error','error'=>10001), $encryp));
}

$sessionid = trim(get_param('token'));
if(empty($sessionid)){
    exit(ResponseJson(array('code'=>9,'msg'=>'token错误','error'=>10002), $encryp));
}
session_id($sessionid);
session_start();
$sess_username=trim($_SESSION['kyx_username']);
if(empty($sess_username)){
    exit(ResponseJson(array('code'=>10,'msg'=>'登录已过期','error'=>10003), $encryp));
}

$phonenumber = new_decrypt(trim(get_param('phonenumber')));
if(empty($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，手机号还没有填写','error'=>10004), $encryp));
}

if(!check_phoneformat($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'正确的手机号为11位','error'=>10005), $encryp));
}

$arr = uc_get_user_info($phonenumber);
if (!empty($arr)) {
    //生产环境获取大头像md5的接口
    $get_img_url = UC_API . '/api/get_avatar_md5file.php';
    $arr_img = array('uid'=>$arr['uid']);
    //调用ucenter的头像处理接口
    $json = curl_post($get_img_url,$arr_img);
    $arr_return = json_decode($json,TRUE);
    $arr['b_pic'] = UC_API.'/avatar.php?uid='.$arr['uid'].'&type=real&size=big&md5file='.$arr_return['md5file'];
    $arr['m_pic'] = UC_API.'/avatar.php?uid='.$arr['uid'].'&type=real&size=middle&md5file='.$arr_return['md5file'];
    $arr['s_pic'] = UC_API.'/avatar.php?uid='.$arr['uid'].'&type=real&size=small&md5file='.$arr_return['md5file'];
    $arr['md5file'] = $arr_return['md5file'];//获取ucenter中心的大图md5值
    exit(ResponseJson(array('code'=>0,'msg'=>'用户账号信息获取成功','data'=>$arr), $encryp));
}else{
    exit(ResponseJson(array('code'=>9,'msg'=>'用户账号信息获取失败','error'=>20023), $encryp));
}
