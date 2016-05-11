<?php
/*=============================================================================
#     FileName: update_reg_mac.php
#         Desc: 定期统计渠道MAC注册信息
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-03-17 19:57:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}
include_once("../db.save.config.inc.php");

//设置昨天统计的数据
//$mydata = date("Ymd",THIS_DATETIME - 86400);//20150317;
$mydata = date("Ymd",THIS_DATETIME - 86400);//20150317;

//查当天有登陆的用户
$sql = 'SELECT `sl_in_date`, `sl_md`, `sl_bd`, `sl_dc`,`sl_mac`, `sl_chl`,sum(`sl_ut`) as `sl_ut`, `sl_ip` FROM `kyx_sdk_login_log` WHERE sl_in_date='.$mydata.' group by sl_mac,sl_chl';
$row = $conn->find($sql);
if($row){
	//每天登陆日志
	$str_sql = "insert into kyx_reg_mac_login(`rml_login_date`, `rml_in_date`, `rml_mac`, `rml_login_chl`, `rml_login_ip`, `rml_ut`)values";
	$str_sql_2 = "";
	$i=0;
	foreach ($row as $val){
		//查下这个渠道下是否含有对应的MAC
		$sql_select = 'SELECT `rm_in_date` FROM `kyx_reg_mac` 
				WHERE  `rm_mac`="'.$val['sl_mac'].'" AND `rm_chl`="'.$val['sl_chl'].'"';
		$row_one = $conn->get_one($sql_select);
		//如果没有找到，则进行注册
		if(!$row_one){
			$row_arr = array(
				"rm_in_date"=>$val['sl_in_date'], 
				"rm_mac"=>$val['sl_mac'], 
				"rm_chl"=>$val['sl_chl'], 
				"rm_login_date"=>$val['sl_in_date'], 
				"rm_md"=>$val['sl_md'], 
				"rm_bd"=>$val['sl_bd'], 
				"rm_dc"=>$val['sl_dc'], 
				"rm_ip"=>$val['sl_ip'], 
				"rm_login_ip"=>$val['sl_ip'], 
				"rm_ut"=>$val['sl_ut']
			);
			$conn->save('kyx_reg_mac', $row_arr);
			unset($row_arr);
		}else{//如果有找到，则更新最后登陆时间
			//注册时间
			$val['rm_in_date'] = $row_one['rm_in_date'];
			
			$row_arr = array(
					"rm_login_date"=>$val['sl_in_date'],//最近登陆时间
					"rm_login_ip"=>$val['sl_ip'],//最近登陆IP
					"rm_ut"=>"rm_ut+".intval($val['sl_ut'])//增加玩游戏的时间
			);
			$where_row = array(
					"rm_mac"=>$val['sl_mac'],
					"rm_chl"=>$val['sl_chl']
			);
			$conn->update2('kyx_reg_mac', $row_arr,$where_row);
			unset($row_arr,$where_row);
		}
		//添加用户当天的登陆记录
		$str_sql_2 .= "(";
		$tmp_sql_val = $val['sl_in_date'].",";//登陆日期
		$tmp_sql_val .= isset($val['rm_in_date'])?$val['rm_in_date']:$val['sl_in_date'];//注册日期
		$tmp_sql_val .= ",'".$val['sl_mac']."',";//设备MAC地址
		$tmp_sql_val .= "'".$val['sl_chl']."',";//登陆渠道号
		$tmp_sql_val .= "'".$val['sl_ip']."',";//登陆IP
		$tmp_sql_val .= intval($val['sl_ut']);//登陆游戏时长
		
		$str_sql_2 .= $tmp_sql_val."),";
		if($i < 500){//每500条数据插入一次
			$i++;
		}else{
			$tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
			$conn->query($tmp_sql_3);
			//echo($tmp_sql_3);
			$i = 0;
			$str_sql_2 = "";
		}
	}
	if($str_sql_2!=""){
		$tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
		$conn->query($tmp_sql_3);
	}
	echo($mydata.'更新数据成功'.chr(10));
}else{
	echo($mydata.'没有查到数据！'.chr(10));
}
