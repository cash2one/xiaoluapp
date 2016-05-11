<?php
/**
 * @copyright: @快游戏 2014
 * @description: 二维码扫描跳转（每个二维码限定跳3次）
 * @file: code_scan.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-03-18  11:24
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

//参数
$user_ip = get_onlineip(); //访问IP地址
$user_ua = $_SERVER['HTTP_USER_AGENT']; //访问UA地址
$user_md5 = md5($user_ip.$user_ua);  //访问IP地址加UA地址MD5值

//检测用户是否重复扫描,是则返回上次扫描地址给用户
$sql = "SELECT `id`,`code_id` FROM `code_scan_log` WHERE `user_md5` = '".$user_md5."' LIMIT 1";
$check = $conn->find($sql);
if(isset($check[0]['code_id']) && !empty($check[0]['code_id'])){
    //获取扫描的地址
    $sql = "SELECT `code_url` FROM `code_scan` WHERE `id` = ".intval($check[0]['id']);
    $url_data = $conn->find($sql);
    $url = isset($url_data[0]['code_url']) ? $url_data[0]['code_url'] : '';

    //更新扫描次数
    $last_scan_time = time();
    $sql = "UPDATE `code_scan_log` SET `last_scan_time` = ".$last_scan_time.",`scan_num` = `scan_num` + 1 WHERE id = ".intval($check[0]['id']);
    $conn->query($sql);
}else{
    //获取可用扫描地址
    $sql = "SELECT `id`,`code_url`,`sacn_num` FROM `code_scan` WHERE `status` = 1 ORDER BY `id` ASC LIMIT 1";
    $check = $conn->find($sql);

    //有可用的扫描地址
    if(isset($check[0]['id']) && !empty($check[0]['id'])){
        $id = isset($check[0]['id']) ? intval($check[0]['id']) : 0;
        $url = isset($check[0]['code_url']) ? $check[0]['code_url'] : '';
        $scan_num = isset($check[0]['sacn_num']) ? intval($check[0]['sacn_num']) : 0;
        $new_scan_num = $scan_num + 1;

        //更新数组
        $update_arr = array(
            'id' => intval($check[0]['id']),
            'sacn_num' => $new_scan_num
        );

        //地址失效
        if($new_scan_num >= 4){
            $update_arr['status'] = 2;
        }

        //更新扫描地址信息
        $conn->update('code_scan',$update_arr);

    }else{
        //获取最后一个地址信息
        $sql = "SELECT `id`,`code_url` FROM `code_scan` ORDER BY `id` DESC LIMIT 1";
        $url_data = $conn->find($sql);
        $id = isset($check[0]['id']) ? intval($check[0]['id']) : 0;
        $url = isset($url_data[0]['code_url']) ? $url_data[0]['code_url'] : '';
    }

    //记录扫描日志
    $save_arr = array(
        'user_ip' => $user_ip,
        'user_ua' => $user_ua,
        'user_md5' => $user_md5,
        'code_id' => $id,
        'last_scan_time' => time(),
        'scan_num' => 1
    );

    $conn->save('code_scan_log',$save_arr);
}

//无相应数据返回404
if(empty($url)){
    header('HTTP/1.1 404 Not Found');
    exit;
}

header("Location: $url");
exit;
