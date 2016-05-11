<?php
/*=============================================================================
#     FileName: update_handle_game_num.php
#         Desc: 定期统计手柄信息表里的手柄对应的游戏数量
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-02-06 15:10:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

include_once("../db.config.inc.php");
/*
$str_update = 'UPDATE `mzw_game_handle` gh SET `gh_game_num` = (
		SELECT count(DISTINCT gv.gv_id) as num FROM mzw_game_version gv 
		WHERE gv.gv_status=1 AND FIND_IN_SET(5,gv.gv_app_prop)>0 OR gv.gv_id IN 
		(SELECT DISTINCT ghr.gv_id FROM mzw_game_handle_relation ghr 
		WHERE ghr.mb_id=gh.mb_id AND (ghr.gh_id= gh.id OR ghr.gh_id=0)))';

$rs = $conn->query($str_update);
*/
$str_select = 'SELECT id,gh_pattern FROM `mzw_game_handle` WHERE gh_state=1';
$rs_select  = $conn->find($str_select);
$i = 0;
if($rs_select){
	$i = 0;
	foreach ($rs_select as $rs_select_val){
		$tmp_handle = explode(',', $rs_select_val['gh_pattern']);
		//如果有数据
		$where = ' WHERE gv.gv_status=1 ';
		$where_pattern = '';
		if(count($tmp_handle)>0){
			if(is_array($tmp_handle)){
				foreach ($tmp_handle as $tmp_handle_v){
					if( $tmp_handle_v > 0 ){
						$where_pattern .= " AND FIND_IN_SET(".$tmp_handle_v.",gv.gv_mb_pattern)>0 ";
					}
				}
			}else if($tmp_handle>0){
				$where_pattern .= " AND FIND_IN_SET(".$tmp_handle.",gv.gv_mb_pattern)>0 ";
			}
		}
		if(!is_empty($where_pattern)){
			$where .= $where_pattern .' OR ';
		}else{
			$where .= ' AND ';
		}
		//支持自定义按键
		$where .= ' (FIND_IN_SET(5,gv.gv_app_prop)>0 ';
		//去掉模拟器游戏
		$where .= " AND (gv.gv_nes_property='' OR gv.gv_nes_property is null OR FIND_IN_SET(1,gv.gv_nes_property)<1) ";
		$where .= ' AND gv.gv_status=1) ';
		
		$str_select2 = 'SELECT count(DISTINCT gv_id) as num FROM `mzw_game_version` gv '.$where;
		$rs_select2 = $conn->get_one($str_select2);
		if($rs_select2 && isset($rs_select2['num'])){
			$str_update = 'UPDATE `mzw_game_handle` SET `gh_game_num`='.intval($rs_select2['num']).' WHERE id='.$rs_select_val['id'];
			$conn->query($str_update);
			$i++;
		}
	}
	
}
if($rs_select){
	echo('更新手柄对应的游戏数量成功！'.$i);
}else{
	echo('更新手柄对应的游戏数量失败！');
}
