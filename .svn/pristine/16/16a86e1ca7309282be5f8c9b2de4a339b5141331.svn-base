<?php
/*=============================================================================
#     FileName: update_game_handle.php
#         Desc: 定期统计手柄信息（统计表kyx_count_log里的数据,存到mzw_game_handle_count表里
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2014-12-18 15:57:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

include_once("../db.config.inc.php");

$sql = "SELECT cl_name,cl_vid,cl_pid,cl_mid,max(cl_keys) as cl_keys FROM kyx_count_log WHERE cl_numbder='60010' AND cl_keys!='0' GROUP BY cl_vid,cl_pid,cl_mid,cl_name";
$data = $conn->find($sql);
foreach ($data as $data_val){
	if(isset($data_val['cl_name']) && !is_empty($data_val['cl_name'])){
		//查下是否已经存在这个手柄
		$sql_select = "SELECT id FROM mzw_game_handle_count WHERE ghc_vid='".mysql_real_escape_string($data_val['cl_vid'])."' AND ghc_pid='".mysql_real_escape_string($data_val['cl_pid'])
					."' AND ghc_mid='".mysql_real_escape_string($data_val['cl_mid'])."' AND ghc_device_name='".mysql_real_escape_string($data_val['cl_name'])."'";
		//echo($sql_select);
		$tmp_r = $conn->get_one($sql_select);
		if($tmp_r==false){//如果没有找到，则添加
			//查找这个手柄的品牌ID
			////查下是否已经存在这个手柄型号对应的品牌
			//$sql_select2 = "SELECT mb_id FROM mzw_mobile_brand WHERE mb_type=1 AND INSTR(mb_vid,'".mysql_real_escape_string($data_val['cl_vid'])."')>0 AND INSTR(mb_pid,'".mysql_real_escape_string($data_val['cl_pid'])."')>0 AND INSTR(mb_mid,'".mysql_real_escape_string($data_val['cl_mid'])."')>0
			//				 AND INSTR(mb_device_name,'".mysql_real_escape_string($data_val['cl_name'])."')>0";
			//$tmp_r2 = $conn->get_one($sql_select2);
			//求手柄支持的模式
			$tmp_ghc_pattern = get_handle_pattern($data_val['cl_keys']);

			//保存这个手柄型号的信息
			$row = array(
				"ghc_device_name"=>mysql_real_escape_string($data_val['cl_name']),// '手柄设备名称(客户端获取)'
				"ghc_pid"=>mysql_real_escape_string($data_val['cl_pid']),//产品ID
				"ghc_vid"=>mysql_real_escape_string($data_val['cl_vid']),//厂商ID
				"ghc_mid"=>mysql_real_escape_string($data_val['cl_mid']),//手柄描述信息
				"ghc_in_time"=>time(),//'获取时间(客户端第一次获取这个手柄的时间)'
				"ghc_keys"=>mysql_real_escape_string($data_val['cl_keys']),//手柄适配键值(json)
				"mbc_id"=>0,//关联手机品牌id
				"ghc_pattern"=>$tmp_ghc_pattern,//支持模式(1、BFM,2、360,3、数字,4、神鹰)
				"gh_id"=>0//手柄ID
			);
			$conn->save('mzw_game_handle_count', $row);
		}else{//如果有找到，则不管
			//echo("<br>手柄数据已经存在".$val['cl_devicename'].chr(10));
		}
	}else{
		echo("手柄数据为空");
	}
}
echo("成功更新手柄数据");
