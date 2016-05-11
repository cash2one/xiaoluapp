<?php
/*=============================================================================
#     FileName: update_sdk_game_uninstall_date.php
#         Desc: 定期统计sdk游戏取消安装信息（统计表kyx_sdk_game_uninstall_log里的数据,存到kyx_sdk_game_uninstall_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-05-29 10:54:48
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

$sql = 'SELECT `sgul_in_date`,`sgul_title`,`sgul_vc`,`sgul_pn`,count(*) as num,count(DISTINCT `sgul_mac`) as ipnum FROM `kyx_sdk_game_uninstall_log` WHERE sgul_in_date='.$this_date.' GROUP BY `sgul_vc`,`sgul_pn`';

$data = $conn->find($sql);
foreach ($data as $val){
	if((isset($val['sgul_vc']) && !is_empty($val['sgul_vc'])) && (isset($val['sgul_pn']) && !is_empty($val['sgul_pn']))){

		$row = array(
		  "in_date"=>$this_date,//'下载日期',
          "game_name" => $val['sgul_title'],
		  "game_vc"=>$val['sgul_vc'],//'游戏版本号',
          "game_pn"=>$val['sgul_pn'],//'游戏包名称',
		  "uninstall_num"=>intval($val['num']),//'总取消安装数',
		  "mac_unstall_num"=>intval($val['ipnum'])//'独立取消安装数',
		);
		$conn->save('kyx_sdk_game_uninstall_time', $row);

	}else{
		echo("SDK游戏取消安装数据为空");
	}
}
echo("成功更新SDK游戏取消安装数据");
