<?php

/**
 * @copyright: @快游戏 2015
 * @description: 获取手机注册的验证码接口
 * @file:get_reg_phone_code.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-09-17  15:58
 * @version 1.0
 * http://faq.comsenz.com/library/UCenter/interface/interface_user.htm
 **/

include("../config.inc.php");
include('../api/ucenter.config.inc.php');
include('../sms.config.inc.php');
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
$type = intval(trim(get_param('type')));
if(empty($phonenumber) ){
    exit(ResponseJson(array('code'=>9,'msg'=>'嘿，手机号还没有填写','error'=>10004), $encryp));
}

if( !in_array($type,array(0,1))){
    exit(ResponseJson(array('code'=>9,'msg'=>'类型不正确','error'=>20001), $encryp));
}

if(!check_phoneformat($phonenumber)){
    exit(ResponseJson(array('code'=>9,'msg'=>'正确的手机号为11位','error'=>10005), $encryp));
}

$valid_time = UC_VALID_TIME;
switch ($type) {
	case 0://注册
	    $arr = uc_get_user_info($phonenumber);
	    if(!empty($arr)){
	        exit(ResponseJson(array('code'=>1,'msg'=>'该手机号已经注册过了','error'=>10006), $encryp));
	    }
	    unset($arr);
	    $arr = uc_get_reg_phone_code($phonenumber);
	    if(!empty($arr)){ //曾经注册过
	        $is_end = intval($arr['is_end']);
	        $created = floatval($arr['created']);
	        $created_date = date('Ymd',$created);
	        $today_date = date('Ymd');
	        $valid_sec = $valid_time/60;
    	    if($is_end){
    	        exit(ResponseJson(array('code'=>1,'msg'=>'该手机号已经注册过了','error'=>10006), $encryp));
    	    }else{
    	        if(time() - $created < $valid_time){
    	            exit(ResponseJson(array('code'=>9,'msg'=>'需要'.$valid_sec.'分后才能获取新的验证码','error'=>20002), $encryp));
    	        }
    	    }
	    }
       $model = htmlspecialchars(trim(get_param('model')));
       $brand = htmlspecialchars(trim(get_param('brand')));
       $gpu = htmlspecialchars(trim(get_param('gpu')));
       $systemversion = htmlspecialchars(trim(get_param('systemversion')));
       $cpu = htmlspecialchars(trim(get_param('cpu')));
       $deviceid = trim(get_param('deviceid'));
       $packagename = trim(get_param('packagename'));
       $code = get_n_number_code(4);
       $ip = get_onlineip();
       $sms_username = SMS_USERNAME;
       $sms_pwd = SMS_PWD;
       $sms_epid = SMS_EPID;
       $message = "【小鹿视频】验证码：{$code}，{$valid_sec}分钟内注册有效。";
       $message =  iconv("UTF-8","GB2312//IGNORE",$message);
       $message = urlencode($message);
       $sms_url = "http://114.255.71.158:8061/?username={$sms_username}&password={$sms_pwd}&message={$message}&phone={$phonenumber}&epid={$sms_epid}&linkid=&subcode=";
       $sms_status = curl_get($sms_url);
       if($sms_status=='00'){//短信发送成功
           $id=uc_insert_phone_code($phonenumber,$code,0,$model,$brand,$gpu,$systemversion,$cpu,$deviceid,$packagename,$ip);
           if($id>0){
               exit(ResponseJson(array('code'=>0,'msg'=>'短信发送成功','valid_time'=>UC_VALID_TIME), $encryp));
           }else{
               exit(ResponseJson(array('code'=>9,'msg'=>'数据保存失败','error'=>20003), $encryp));
           }
       }else{
           exit(ResponseJson(array('code'=>9,'msg'=>"SMS ERROR {$sms_status}"), $encryp));
       }
	break;
	case 1://忘记密码
	    $arr = uc_get_user_info($phonenumber);
	    if(empty($arr)){
	        exit(ResponseJson(array('code'=>10,'msg'=>'该手机号还没有注册','error'=>10007), $encryp));
	    }
	    $arr = get_reset_pwd_code_by_phone($phonenumber);
	    if(!empty($arr)){ //曾经跑过忘记密码的任务
	        $is_end = intval($arr['is_end']);
	        $created = floatval($arr['created']);
	        $created_date = date('Ymd',$created);
	        $today_date = date('Ymd');
	        $valid_sec = $valid_time/60;
	        //固定时间内没有完成修改密码的话
	        if(!$is_end  &&  (time() - $created) <= $valid_time){
	            exit(ResponseJson(array('code'=>11,'msg'=>'需要'.$valid_sec.'分后才能获取新的验证码','error'=>20002), $encryp));
	        }
	    }
	    $model = htmlspecialchars(trim(get_param('model')));
	    $brand = htmlspecialchars(trim(get_param('brand')));
	    $gpu = htmlspecialchars(trim(get_param('gpu')));
	    $systemversion = htmlspecialchars(trim(get_param('systemversion')));
	    $cpu = htmlspecialchars(trim(get_param('cpu')));
	    $deviceid = trim(get_param('deviceid'));
	    $packagename = trim(get_param('packagename'));
	    $code = get_n_number_code(4);
	    $ip = get_onlineip();
	    $sms_username = SMS_USERNAME;
	    $sms_pwd = SMS_PWD;
	    $sms_epid = SMS_EPID;
	    $message = "【小鹿视频】验证码：{$code}，{$valid_sec}分钟内修改密码有效。";
	    $message =  iconv("UTF-8","GB2312//IGNORE",$message);
	    $message = urlencode($message);
	    $sms_url = "http://114.255.71.158:8061/?username={$sms_username}&password={$sms_pwd}&message={$message}&phone={$phonenumber}&epid={$sms_epid}&linkid=&subcode=";
	    $sms_status = curl_get($sms_url);
	    if($sms_status=='00'){//短信发送成功
	        $id=uc_insert_phone_code($phonenumber,$code,1,$model,$brand,$gpu,$systemversion,$cpu,$deviceid,$packagename,$ip);
	        if($id>0){
	            exit(ResponseJson(array('code'=>0,'msg'=>'短信发送成功','valid_time'=>UC_VALID_TIME), $encryp));
	        }else{
	            exit(ResponseJson(array('code'=>9,'msg'=>'数据保存失败','error'=>20003), $encryp));
	        }
	    }else{
	        exit(ResponseJson(array('code'=>9,'msg'=>"SMS ERROR {$sms_status}"), $encryp));
	    }
	break;
	
	default:
		exit(ResponseJson(array('code'=>9,'msg'=>'error'), $encryp));
	break;
}



