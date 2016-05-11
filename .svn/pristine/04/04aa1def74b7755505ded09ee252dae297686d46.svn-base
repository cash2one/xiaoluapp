<?php
include("../config.inc.php");
include('../api/ucenter.config.inc.php');
include("../uc_client/client.php");

// 判断是否加密
$encryp = get_param('encrypt');
$encryp = empty($encryp) ? true : false;

if(defined("MEMCACHE_IS_OPEN") && MEMCACHE_IS_OPEN){
    ini_set('session.save_handler','memcache');
    ini_set('session.save_path',MEMCACHE_SESSION_HOST);
}

//验证KEY
$key=trim(get_param('key')); 
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($key,$request);
if(empty($key_auth) || empty($key)){
    exit(ResponseJson(array('code'=>9,'msg'=>'key error','error'=>10001), $encryp));
}

//检查是否登录
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

if(empty($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，手机号还没有填写','error'=>10004), $encryp));
}

if(!check_phoneformat($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'正确的手机号为11位','error'=>10005), $encryp));
}

if($sess_username <> $phonenumber){
    exit(ResponseJson(array('code'=>9,'msg'=>'登录账号不一致','error'=>10013), $encryp));
}


$nickname = strip_tags(trim(get_param('nickname')));//昵称


//检查昵称是否重复
if(!empty($nickname)){
    //检查昵称长度
    if(mbstrlen($nickname) >15){
        exit(ResponseJson(array('code'=>9,'msg'=>'昵称不能超过15个字','error'=>20015), $encryp));
    }
    //检查昵称是否惟一
    $status = uc_get_same_nickname($nickname,$phonenumber);
    if($status){
        exit(ResponseJson(array('code'=>9,'msg'=>'已存在相同的昵称','error'=>20016), $encryp));
    }
}
unset($status);


$gender = intval(trim(get_param('gender')));//性别
if(!empty($gender) && !in_array($gender, array(1,2,3))){
    exit(ResponseJson(array('code'=>9,'msg'=>'性别选择错误','error'=>20017), $encryp));
}

$desc = strip_tags(trim(get_param('desc')));//描述
if(!empty($desc) && mbstrlen($desc) >50){
    exit(ResponseJson(array('code'=>9,'msg'=>'简介不能超过50个字','error'=>20018), $encryp));
}

$arr_result = uc_get_user_info($phonenumber);
if(empty($arr_result)){
    exit(ResponseJson(array('code'=>9,'msg'=>'该手机号还没有注册','error'=>10007), $encryp));
}
//拿到旧的用户数据
$old_nickname = $arr_result['nickname'];
$old_gender = $arr_result['gender'];
$old_desc = $arr_result['desc'];

if(empty($nickname)){
    $nickname = $old_nickname;
}
if(empty($gender)){
    $gender = $old_gender;
}
if(empty($desc)){
    $desc = $old_desc;
}
$status = uc_user_info_edit($phonenumber, $nickname,$gender,$desc);
exit(ResponseJson(array('code'=>0,'msg'=>'资料更新成功'), $encryp));