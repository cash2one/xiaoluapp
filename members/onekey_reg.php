<?php
/**
 * @copyright: @快游戏 2015
 * @description:一键注册接口
 * @file:onekey_reg.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-12-31  15:58
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

$phonenumber = new_decrypt(trim(get_param('phonenumber')));//手机号码
$password = new_decrypt(trim(get_param('pwd')));  //密码
$mac = trim(get_param('mac')); //注册MAC

if(empty($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，手机号还没有填写','error'=>10004), $encryp));
}
if(!check_phoneformat($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'正确的手机号为11位','error'=>10005), $encryp));
}

if(empty($password)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，密码还没有填写','error'=>10012), $encryp));
}

$source = 4;//表示手机一键注册
$reg_status = uc_user_register($phonenumber,$password,$mac,$source);//正式的用户注册
if($reg_status==-1){
    exit(ResponseJson(array('code'=>9,'msg'=>'用户名不合法','error'=>20006), $encryp));
}
elseif($reg_status==-2){
    exit(ResponseJson(array('code'=>9,'msg'=>'包含不允许注册的词语','error'=>20007), $encryp));
}
elseif($reg_status==-3){
    exit(ResponseJson(array('code'=>9,'msg'=>'用户名已经存在','error'=>10006), $encryp));
}
exit(ResponseJson(array('code'=>0), $encryp));
