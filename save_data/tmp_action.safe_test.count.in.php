<?php
/*=============================================================================
#     FileName: tmp_action.app_game_connect.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/app_handle_count.php)数据存在数据库存中（模拟手柄连接游戏统计）
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-09 16:05:48
#      History:
=============================================================================*/

include_once("../config.inc.php");
$tmp_ip = get_onlineip();//获取客户端的IP
sys_log_write_content('IP：'.$tmp_ip,'safe_test');
