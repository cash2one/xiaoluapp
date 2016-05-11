<?php

/*
    手柄返回JSON格式说明
    $arr = array(
            array(
                'h_m'=>'',//手柄品牌名字
                'h_m_n'=>'',//手柄品牌ID
                'h_n'=>'',//手柄型号名字
                'h_n_n'=>'',//手柄型号ID
                'h_url'=>'',//品牌+型号的连接
                'h_url2'=>''//单品牌的连接
            )
    );
*/

/**
 * @copyright: @快游戏 2014
 * @description: 获取手柄点评链接
 * @file: get_handle.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-1-23  11:44
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../config.open.api.php");
include_once("../../db.config.inc.php");
/*参数*/

$mydata = array();
$mydata['time'] = get_param('time');
$p_key = get_param('key');
$tmp_key = open_key_kyx($mydata,$GLOBALS['SYS_OPEN_API_KEY']['muzhiwan']);
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($p_key!=$tmp_key && $is_bug_show!=100){
	exit('KEY ERROR');
}


//返回数组
$json = array();

//获取所有手柄品牌信息数组
$h_sql = "SELECT `mb_id`,`mb_name` FROM `mzw_mobile_brand` WHERE `mb_type`=1 ORDER BY mb_dis_order DESC ";
$handle_data = $conn->find($h_sql,'mb_id');
$handle_data = empty($handle_data) ? array() : $handle_data;

//获取所有手柄型号信息数组
$h_m_sql = "SELECT `gh_title`,`id`,mb_id FROM `mzw_game_handle` WHERE `gh_state`=1 ORDER BY gh_order DESC,gh_use_num DESC";
$handle_mdoel_deta = $conn->find($h_m_sql,'id');
$handle_mdoel_deta = empty($handle_mdoel_deta) ? array() : array_merge($handle_mdoel_deta,array(0 => array()));

//拼装返回json数组
foreach($handle_data as $key => $val){

	$json[] = array(
			'h_m' => isset($val['mb_name']) ? $val['mb_name'] : '',     //手柄品牌名字
			'h_m_n'=>$key, //手柄品牌ID
			'h_n'=>'其它',   //手柄型号名字
			'h_n_n'=>0, //手柄型号ID
			'h_url'=>LOCAL_URL_WWW . '/handle/1-'.$key.'-0-0-1-0', //品牌+型号的连接
			'h_url2'=>LOCAL_URL_WWW . '/handle/1-'.$key.'-0-0-1-0'      //单品牌的连接
	);
	foreach($handle_mdoel_deta as $mkey => $mval){
    	if($mval['mb_id']==$key){
	        $json[] = array(
	            'h_m' => isset($val['mb_name']) ? $val['mb_name'] : '',     //手柄品牌名字
	            'h_m_n'=>$key, //手柄品牌ID
	            'h_n'=>isset($mval['gh_title']) ? $mval['gh_title'] : '',   //手柄型号名字
	            'h_n_n'=>$mkey, //手柄型号ID
	            'h_url'=>LOCAL_URL_WWW . '/handle/1-'.$key.'-0-0-1-'.$mkey, //品牌+型号的连接
	            'h_url2'=>LOCAL_URL_WWW . '/handle/1-'.$key.'-0-0-1-0'      //单品牌的连接
	        );
    	}
    }
}

$str_encode = responseJson($json,false);
exit($str_encode);



