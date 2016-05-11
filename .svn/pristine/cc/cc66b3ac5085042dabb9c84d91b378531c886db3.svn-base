<?php
/**
 * @copyright: @快游戏 2015
 * @description:注册接口
 * @file:reg.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-09-18  15:58
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
$smscode = trim(get_param('smscode'));  //验证码
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

$arr = uc_get_phone_code($phonenumber,$smscode);//检查手机是否存在临时注册表，并检查验证码是否正确
if(empty($arr)){
    exit(ResponseJson(array('code'=>9,'msg'=>'输入的验证码不正确','error'=>10009), $encryp));
}else{ //找到注册时生成验证码的记录
    $is_end = intval($arr['is_end']);
    $created = floatval($arr['created']);
    $valid_time = UC_VALID_TIME;
    if($is_end){
        exit(ResponseJson(array('code'=>1,'msg'=>'该手机号已经注册过了','error'=>10006), $encryp));
    }else{
        if(time() - $created > $valid_time){
            exit(ResponseJson(array('code'=>1,'msg'=>'手机已经超过注册的有效期','error'=>20005), $encryp));
        }else{
            //验证成功
            $id = isset($arr['id'])?intval($arr['id']):0;
            if(empty($id)){
                exit(ResponseJson(array('code'=>9,'msg'=>'参数错误','error'=>10011), $encryp));
            }
            $ip = get_onlineip();
            $data = array('last_ip'=>$ip);//更新的键名要和数据表字段一致
            $source = 2;//表示手机注册
            $reg_status = uc_user_register($phonenumber,$password,$mac,$source);//正式的用户注册
            if($reg_status>0){
                uc_update_phone_code($id,$data);//更新注册表的状态
                exit(ResponseJson(array('code'=>0), $encryp));
            }
            elseif($reg_status==-1){
                exit(ResponseJson(array('code'=>9,'msg'=>'用户名不合法','error'=>20006), $encryp));
            }
            elseif($reg_status==-2){
                exit(ResponseJson(array('code'=>9,'msg'=>'包含不允许注册的词语','error'=>20007), $encryp));
            }
            elseif($reg_status==-3){
                exit(ResponseJson(array('code'=>9,'msg'=>'用户名已经存在','error'=>10006), $encryp));
            }
        }
    }
}
