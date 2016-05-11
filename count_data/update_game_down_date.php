<?php
/*=============================================================================
#     FileName: update_game_down_date.php
#         Desc: 定期统计游戏下载信息（统计表kyx_game_down_log里的数据,存到kyx_game_down_time表里
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-01-20 11:57:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}
include_once(WEBPATH_DIR."db.save.config.inc.php");

//获取上一天的日期
$this_date = date('Ymd',THIS_DATETIME - 86400);

$sql = 'SELECT gdl_in_date,gv_id,count(*) as num,count(DISTINCT gdl_ip) as ipnum FROM kyx_game_down_log WHERE gdl_in_date='.$this_date.' GROUP BY gv_id';

$data = $conn->find($sql);
foreach ($data as $val){
	if(isset($val['gv_id']) && !is_empty($val['gv_id'])){

		$row = array(
		  "in_date"=>$this_date,//'下载日期',
		  "gameid"=>intval($val['gv_id']),//'游戏ID',
		  "down_num"=>intval($val['num']),//'总下载量',
		  "down_ip_num"=>intval($val['ipnum'])//'独立IP下载量',
		);
		$conn->save('kyx_game_down_time', $row);

	}else{
		echo("游戏下载数据为空");
	}
}
echo("成功更新游戏下载数据");
