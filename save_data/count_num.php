<?php
/*=============================================================================
 #     FileName: count_num.php
#         Desc: 用于进行数据统计的
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-03-14 15:24:35
#      History:
=============================================================================*/

include_once("../config.inc.php");
$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}




