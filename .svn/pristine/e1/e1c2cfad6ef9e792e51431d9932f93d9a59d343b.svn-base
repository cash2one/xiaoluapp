<?php

/**
 * @copyright: @快游戏 2015
 * @description: 更新用户图片
 * @file:update_user_picture.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-11-07  15:58
 * @version 1.0
 **/

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

// 检查是否登录
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

$phonenumber =new_decrypt(trim(get_param('phonenumber')));

if(empty($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，手机号还没有填写','error'=>10004), $encryp));
}

if(!check_phoneformat($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'正确的手机号为11位','error'=>10005), $encryp));
}

if($sess_username <> $phonenumber){
    exit(ResponseJson(array('code'=>9,'msg'=>'登录账号不一致','error'=>10013), $encryp));
}

$arr = uc_get_user_info($phonenumber);
if(empty($arr)){
    exit(ResponseJson(array('code'=>9,'msg'=>'该手机号还没有注册','error'=>10007), $encryp));
}

$uid = intval($arr['uid']);
unset($arr);

//获取用户头像
$filename = basename( $_FILES['usericon']['name']);
if(empty($filename)){
    exit(ResponseJson(array('code'=>9,'msg'=>'您没有上传用户头像','error'=>20019), $encryp));
}
$target_path = LOCAL_AVATAR_PATH."/avatar/".date('Y/m/d/');//用户头像目录
if(!is_dir($target_path)){
    create_my_file_path($target_path);
}

$ext = pathinfo($filename,PATHINFO_EXTENSION);
$basename= md5(pathinfo($filename,PATHINFO_FILENAME));
$pic_full_path = $target_path . $basename. '.'.$ext;
if(move_uploaded_file($_FILES['usericon']['tmp_name'], $pic_full_path)) {
    if(!in_array($ext,array('jpg','png','gif'))){
        unlink($pic_full_path);
        exit(json_encode(array('msg'=>'文件必须为JPG,PNG,GIF格式','code'=>9,'error'=>20020), $encryp));
    }
    $filemd5 = md5_file($pic_full_path);
    //将非JPG图像转换为JPG
    if(in_array($ext, array('png','gif'))){
        $new_pic_full_path = $target_path . $basename.'.jpg';
        image_to_jpg($pic_full_path,$new_pic_full_path ,180,180);
        $pic_full_path = $new_pic_full_path;
        unset($new_pic_full_path);
    }
    if(is_file($pic_full_path)){
        $pic_url = 'http://' .$_SERVER['HTTP_HOST'] . '/uc_client/data'. str_replace(LOCAL_AVATAR_PATH, '', $pic_full_path);
        $data = file_get_contents($pic_url);
        $im = imagecreatefromstring($data);
        if($im == false){
             exit(json_encode(array('msg'=>'不是正常的头像文件','code'=>9,'error'=>20021), $encryp));
        }
        unset($data);
        //生产环境抓取图片的接口
        $get_img_url = UC_API . '/api/get_avatar_img.php';
        $arr_img = array('local_img'=>$pic_url,'uid'=>$uid);
        //调用ucenter的头像处理接口
        $json = curl_post($get_img_url,$arr_img);
        $arr = json_decode($json,TRUE);
        if($arr['status']==400){
            exit(json_encode(array('msg'=>'头像上传失败','code'=>9,'error'=>20022), $encryp));
        }else{
            $arr_return['b_pic'] = UC_API.'/avatar.php?uid='.$uid.'&type=real&size=big&md5file='.$arr['md5file'];
            $arr_return['m_pic'] = UC_API.'/avatar.php?uid='.$uid.'&type=real&size=middle&md5file='.$arr['md5file'];
            $arr_return['s_pic'] = UC_API.'/avatar.php?uid='.$uid.'&type=real&size=small&md5file='.$arr['md5file'];
            $arr_return['md5file'] = $arr['md5file'];//获取ucenter中心的大图md5值
            exit(json_encode(array('msg'=>'头像上传成功','code'=>0,'data'=>$arr_return), $encryp));
        }
    }
}