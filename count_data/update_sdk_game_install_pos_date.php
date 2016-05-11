<?php
/*=============================================================================
#     FileName: update_sdk_game_install_pos_date.php
#         Desc: 定期统计sdk内点击安装游戏位置信息（统计表kyx_sdk_game_install_pos_log里的数据,存到kyx_sdk_game_install_pos_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-06-01 11:45:48
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

$sql = 'SELECT `sgipl_in_date`,`sgipl_title`,`sgipl_vc`,`sgipl_chl`,`sgipl_pn`,`sgipl_pos`,count(*) as num,count(DISTINCT `sgipl_mac`) as ipnum FROM `kyx_sdk_game_install_pos_log` WHERE sgipl_in_date='.$this_date.' GROUP BY `sgipl_pos`,`sgipl_vc`,`sgipl_pn`,`sgipl_chl`';

$data = $conn->find($sql);
foreach ($data as $val){
	if((isset($val['sgipl_vc']) && !is_empty($val['sgipl_vc'])) && (isset($val['sgipl_pn']) && !is_empty($val['sgipl_pn']))){

		$row = array(
		  "in_date"=>$this_date,//'下载日期',
          "game_name" => $val['sgipl_title'], //游戏名称
          "chl_name" => $val['sgipl_chl'], //渠道名称
		  "game_vc"=>$val['sgipl_vc'],//'游戏版本号',
          "game_pn"=>$val['sgipl_pn'],//'游戏包名称',
		  "install_pos_num"=>intval($val['num']),//'点击安装游戏位置次数',
		  "mac_inst_pos_num"=>intval($val['ipnum']),//'点击安装游戏位置独立次数',
          "install_pos_name" => $val['sgipl_pos'] //'安装位置名称'
		);
		$conn->save('kyx_sdk_game_install_pos_time', $row);

	}else{
		echo("SDK内游戏点击安装位置次数数据为空");
        exit;
	}
}
echo("成功更新SDK内游戏点击安装位置次数数据");
