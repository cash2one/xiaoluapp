#! /usr/local/php/bin/php -q
<?php
/**
 * @copyright: @快游戏 2015
 * @description: 刷新昵称
 * @file:aauthor_refresh_nickname.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-10  16:39
 * @version 1.0
 **/

//exit('非法操作');

include_once(str_replace("cli_php","",dirname(__FILE__))."config.inc.php");
include_once(WEBPATH_DIR."db.ucenter.config.inc.php");

$sql = "SELECT `uid`,`nickname`,`username` FROM `uc_members`";
$data = $uconn->find($sql);
if(empty($data)){
	return FALSE;
}
foreach ($data as $value) {
     $nickname = trim($value['nickname']);
     $uid = $value['uid'];
     $username = trim($value['username']);
     if(!empty($nickname)){
     	continue;
     }
     $nickname_new = '用户'.substr($username,-6);//昵称默认为“用户＋手机后6位”
     $status = $uconn->update2('uc_members',array('nickname' => $nickname_new),array('uid' => $uid));
     if($status){
         echo("用户{$username}的ID为".$uid."的用户ID修改昵称'{$nickname_new}'成功".chr(10).chr(13));
     }else{
         echo("用户ID为".$uid."的用户ID修改昵称失败".chr(10).chr(13));
     }
}
