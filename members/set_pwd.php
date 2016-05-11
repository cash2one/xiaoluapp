<?php
/**
 * @copyright: @快游戏 2015
 * @description: 忘记密码时修改密码的接口
 * @file:set_pwd.php
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
$encryp = empty($encryp) ? true : false;

$key=trim(get_param('key')); //验证KEY
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($key,$request);
if(empty($key_auth) || empty($key)){
    exit(ResponseJson(array('code'=>9,'msg'=>'key error','error'=>10001), $encryp));
}

$phonenumber = new_decrypt(trim(get_param('phonenumber')));
$pwd = new_decrypt(trim(get_param('pwd')));
$smscode = trim(get_param('smscode'));  //验证码

if(empty($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，手机号还没有填写','error'=>10004), $encryp));
}

if(empty($smscode)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，验证码还没有填写','error'=>10008), $encryp));
}

if(empty($pwd)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，新密码还没有填写','error'=>20013), $encryp));
}

if(!check_phoneformat($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'正确的手机号为11位','error'=>10005), $encryp));
}


//获取用户数据
$arr = uc_get_user_info($phonenumber);
if(empty($arr)){
	        exit(ResponseJson(array('code'=>10,'msg'=>'该手机号还没有注册','error'=>10007), $encryp));
}
unset($arr);

$arr = get_reset_pwd_code_by_phone($phonenumber);
$valid_time = UC_VALID_TIME;
if(empty($arr)){
    exit(ResponseJson(array('code'=>9,'msg'=>'输入的验证码不正确','error'=>10009), $encryp));
}else{
    $is_end = intval($arr['is_end']);
    $created = floatval($arr['created']);
    $code = trim($arr['code']);//验证码
    if($smscode <> $code){
        exit(ResponseJson(array('code'=>9,'msg'=>'输入的验证码不正确','error'=>10009), $encryp));
    }
    $username = $phonenumber;
    $status = uc_reset_pwd($username,$pwd);
    if($status>0){
        exit(ResponseJson(array('code'=>0), $encryp));
    }else{
        exit(ResponseJson(array('code'=>9,'msg'=>'更新失败','error'=>10010), $encryp));
    }
}
