<?php
/*=============================================================================
#     FileName: update_sdk_game_show_date.php
#         Desc: 定期统计sdk游戏展示次数信息（统计表kyx_sdk_game_show_log里的数据,存到kyx_sdk_game_show_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-06-02 17:31:48
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

$sql = 'SELECT `sgsl_in_date`,`sgsl_title`,`sgsl_vc`,`sgsl_pn`,count(*) as num,count(DISTINCT `sgsl_mac`) as ipnum FROM `kyx_sdk_game_show_log` WHERE sgsl_in_date='.$this_date.' GROUP BY `sgsl_vc`,`sgsl_pn`';

$data = $conn->find($sql);
foreach ($data as $val){
	if((isset($val['sgsl_vc']) && !is_empty($val['sgsl_vc'])) && (isset($val['sgsl_pn']) && !is_empty($val['sgsl_pn']))){

		$row = array(
		  "in_date"=>$this_date,//'下载日期',
          "game_name" => $val['sgsl_title'],
		  "game_vc"=>$val['sgsl_vc'],//'游戏版本号',
          "game_pn"=>$val['sgsl_pn'],//'游戏包名称',
		  "show_num"=>intval($val['num']),//'游戏展示次数',
		  "mac_show_num"=>intval($val['ipnum'])//'游戏独立展示次数',
		);
		$conn->save('kyx_sdk_game_show_time', $row);

	}else{
		echo("SDK游戏展示次数数据为空");
	}
}
echo("成功更新SDK游戏展示次数数据");
