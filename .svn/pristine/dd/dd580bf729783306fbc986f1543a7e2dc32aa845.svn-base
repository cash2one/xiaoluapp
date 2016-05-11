<?PHP
/*
 * 各类数据统计执行计划任务的入口
 */
include_once("config.inc.php");//公共函数
$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
 echo($tmp_ip."已记录非法IP！");
 exit;	
}
$tmp_str = ' ';//执行的内容
$tmp_f	 = 'error';//日志文件名
$tmp_act = get_param('act');
switch ($tmp_act){
	case 1://
		$tmp_str = '更新手柄:';
		$tmp_f = 'handle';
		$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_game_handle.php');
		break;
		case 11:
			$tmp_str = '更新关键词统计:';
			$tmp_f = 'keyword_date';
			$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_keyword_date.php');
			break;
		case 12:
			$tmp_str = '更新游戏下载统计:';
			$tmp_f = 'game_down_date';
			$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_game_down_date.php');
			break;
		case 13:
			//$tmp_str = '更新SDK登入统计:';
			//$tmp_f = 'sdk_login_date';
			//$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_reg_mac.php');
			break;
		case 14:
			$tmp_str = '更新视频播放次数统计:';
			$tmp_f = 'video_play_date';
			$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_video_play_date.php');
			break;
        case 15:
            $tmp_str = '更新SDK游戏取消安装次数统计:';
            $tmp_f = 'sdk_game_uninstall';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_sdk_game_uninstall_date.php');
            break;
        case 16:
            $tmp_str = '更新SDK游戏下载次数统计:';
            $tmp_f = 'sdk_game_down';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_sdk_game_down_date.php');
            break;
        case 17:
            $tmp_str = '更新SDK游戏安装次数统计:';
            $tmp_f = 'sdk_game_insert';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_sdk_game_insert_date.php');
            break;
        case 18:
            $tmp_str = '更新SDK游戏展示次数统计:';
            $tmp_f = 'sdk_game_show';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_sdk_game_show_date.php');
            break;
        case 19:
            $tmp_str = '更新SDK游戏安装位置统计:';
            $tmp_f = 'sdk_game_install_pos';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_sdk_game_install_pos_date.php');
            break;
        case 110:
            $tmp_str = '模拟手柄二维码扫描下载统计:';
            $tmp_f = 'barcode_scanner';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_barcode_scanner_date.php');
            break;
        case 111:
            $tmp_str = '模拟手柄连接游戏统计:';
            $tmp_f = 'app_game_connect';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_app_game_connect_date.php');
            break;
        case 112:
            $tmp_str = '模拟手柄扫描统计:';
            $tmp_f = 'app_scanning';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_app_scanning_date.php');
            break;
        case 113:
            $tmp_str = '模拟手柄展示点击统计:';
            $tmp_f = 'app_show_click';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_app_show_click_date.php');
            break;
        case 114:
            $tmp_str = 'SDK视频录制统计:';
            $tmp_f = 'sdk_video_transcribe';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_sdk_video_transcribe_date.php');
            break;
        case 115:
            $tmp_str = 'SDK模拟手柄统计:';
            $tmp_f = 'sdk_sim_handle';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_sdk_sim_handle_date.php');
            break;
        case 116:
            $tmp_str = 'SDK实体模拟手柄连接统计:';
            $tmp_f = 'sdk_handle_connect';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_sdk_handle_connect_date.php');
            break;
        case 117:
            $tmp_str = 'SDK-redis通用统计:';
            $tmp_f = 'sdk_common_redis';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_sdk_common_redis_date.php');
            break;
        case 118:
            $tmp_str = 'app-redis通用统计:';
            $tmp_f = 'app_common_redis';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_app_common_redis_date.php');
            break;
        case 119:
            $tmp_str = 'video-vert-redis通用统计:';
            $tmp_f = 'video_vert_common_redis';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_video_vert_common_redis_date.php');
            break;
	case 2:
		$tmp_str = '手柄数据入库:';
		$tmp_f = 'show';
		for($i=1;$i<16;$i++){
			$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.show.count.in.php?myid='.$i);
		}
		break;
		case 21:
			$tmp_str = '关键词数据入库:';
			$tmp_f = 'keyword';
			for($i=1;$i<16;$i++){
				$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.keyword.count.in.php?myid='.$i);
			}
			break;
		case 22:
			$tmp_str = '下载日志数据入库:';
			$tmp_f = 'game_down';
			for($i=1;$i<16;$i++){
				$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.game_down.count.in.php?myid='.$i);
			}
			break;
		case 23:
			$tmp_str = '下载速度数据入库:';
			$tmp_f = 'down_speed';
			for($i=1;$i<16;$i++){
				$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.down_speed.count.in.php?myid='.$i);
			}
			break;
		case 24:
			$tmp_str = 'SDK登入数据数据入库:';
			$tmp_f = 'sdk_login';
			for($i=1;$i<16;$i++){
				$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_login.in.php?myid='.$i);
			}
			break;
		case 25:
			$tmp_str = 'SDK内游戏展示次数:';
			$tmp_f = 'sdk_game_show';
			for($i=1;$i<16;$i++){
				$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_game_show.in.php?myid='.$i);
			}
			break;
		case 26:
			$tmp_str = 'SDK内游戏下载次数:';
			$tmp_f = 'sdk_game_down';
			for($i=1;$i<16;$i++){
				$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_game_down.in.php?myid='.$i);
			}
			break;
		case 27:
			$tmp_str = 'SDK内游戏安装次数:';
			$tmp_f = 'sdk_game_insert';
			for($i=1;$i<16;$i++){
				$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_game_insert.in.php?myid='.$i);
			}
			break;
		case 28:
			$tmp_str = '视频播放数据入库次数:';
			$tmp_f = 'video_play';
			for($i=1;$i<16;$i++){
				$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.video_play.count.in.php?myid='.$i);
			}
			break;
        case 29:
            $tmp_str = 'SDK游戏取消安装数据入库次数:';
            $tmp_f = 'sdk_game_uninstall';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_game_uninstall.count.in.php?myid='.$i);
            }
            break;
        case 210:
            $tmp_str = 'SDK游戏安装位置数据入库次数:';
            $tmp_f = 'sdk_game_install_pos';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_game_install_pos.count.in.php?myid='.$i);
            }
            break;
        case 211:
            $tmp_str = '模拟手柄二维码扫描下载数据入库次数:';
            $tmp_f = 'barcode_scanner';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.barcode_scanner.count.in.php?myid='.$i);
            }
            break;
        case 212:
            $tmp_str = '模拟手柄连接游戏数据入库次数:';
            $tmp_f = 'app_game_connect';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.app_game_connect.count.in.php?myid='.$i);
            }
            break;
        case 213:
            $tmp_str = '模拟手柄扫描数据入库次数:';
            $tmp_f = 'app_scanning';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.app_scanning.count.in.php?myid='.$i);
            }
            break;
        case 214:
            $tmp_str = '模拟手柄展示点击数据入库次数:';
            $tmp_f = 'app_show_click';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.app_show_click.count.in.php?myid='.$i);
            }
            break;
        case 215:
            $tmp_str = '模拟手柄异常数据入库次数:';
            $tmp_f = 'app_socket_exception';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.app_socket_exception.count.in.php?myid='.$i);
            }
            break;
        case 216:
            $tmp_str = 'SDK视频录制数据入库次数:';
            $tmp_f = 'sdk_video_transcribe';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_video_transcribe.count.in.php?myid='.$i);
            }
            break;
        case 217:
            $tmp_str = 'SDK模拟手柄数据入库次数:';
            $tmp_f = 'sdk_sim_handle';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_sim_handle.count.in.php?myid='.$i);
            }
            break;
        case 218:
            $tmp_str = 'SDK实体手柄连接数据入库次数:';
            $tmp_f = 'sdk_handle_connect';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_handle_connect.count.in.php?myid='.$i);
            }
            break;
        case 219:
            $tmp_str = 'SDK通用统计数据入库次数:';
            $tmp_f = 'sdk_common';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.sdk_common.count.in.php?myid='.$i);
            }
            break;
        case 220:
            $tmp_str = 'APP通用统计数据入库次数:';
            $tmp_f = 'app_common';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.app_common.count.in.php?myid='.$i);
            }
            break;
        case 221:
            $tmp_str = '视频广告通用统计数据入库次数:';
            $tmp_f = 'video_vert_common';
            for($i=1;$i<16;$i++){
                $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action.video_vert_common.count.in.php?myid='.$i);
            }
            break;

    case 3://数据归档
		$tmp_str = '手柄数据归档:';
		$tmp_f = 'export.show';
		$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.show.count.in.php');
		break;
		case 31://关键词数据归档
			$tmp_str = '关键词数据归档:';
			$tmp_f = 'export.keyword';
			$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.keyword.count.in.php');
			break;
		case 32://游戏下载数据归档
			$tmp_str = '游戏下载数据归档:';
			$tmp_f = 'export.game_down';
			$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.game_down.count.in.php');
			break;
		case 33://SDK打开数据归档
			$tmp_str = 'SDK打开数据归档:';
			$tmp_f = 'export.sdk_login';
			$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_login.in.php');
			break;
		case 34://视频播放数据归档
			$tmp_str = '视频播放数据归档:';
			$tmp_f = 'export.video_play';
			$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.video_play.count.in.php');
			break;
        case 35://SDK游戏取消安装数据归档
            $tmp_str = 'SDK游戏取消安装数据归档:';
            $tmp_f = 'export.sdk_game_uninstall';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_game_uninstall.count.in.php');
            break;
        case 36://SDK游戏下载次数数据归档
            $tmp_str = 'SDK游戏下载次数数据归档:';
            $tmp_f = 'export.sdk_game_down';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_game_down.count.in.php');
            break;
        case 37://SDK游戏安装次数数据归档
            $tmp_str = 'SDK游戏安装次数数据归档:';
            $tmp_f = 'export.sdk_game_insert';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_game_insert.count.in.php');
            break;
        case 38://SDK游戏展示次数数据归档
            $tmp_str = 'SDK游戏展示次数数据归档:';
            $tmp_f = 'export.sdk_game_show';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_game_show.count.in.php');
            break;
        case 39://SDK游戏下载速度数据归档
            $tmp_str = 'SDK游戏下载速度数据归档:';
            $tmp_f = 'export.down_speed';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.down_speed.count.in.php');
            break;
        case 310://SDK游戏安装位置数据归档
            $tmp_str = 'SDK游戏安装位置数据归档:';
            $tmp_f = 'export.sdk_game_install_pos';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_game_install_pos.count.in.php');
            break;
        case 311://模拟手柄二维码扫描下载数据归档
            $tmp_str = '模拟手柄二维码扫描下载数据归档:';
            $tmp_f = 'export.barcode_scanner';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.barcode_scanner.count.in.php');
            break;
        case 312://模拟手柄二维码扫描下载数据归档
            $tmp_str = '模拟手柄连接游戏数据归档:';
            $tmp_f = 'export.app_game_connect';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.app_game_connect.count.in.php');
            break;
        case 313://模拟手柄扫描统计数据归档
            $tmp_str = '模拟手柄扫描统计数据归档:';
            $tmp_f = 'export.app_scanning';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.app_scanning.count.in.php');
            break;
        case 314://模拟手柄展示点击统计数据归档
            $tmp_str = '模拟手柄展示点击统计数据归档:';
            $tmp_f = 'export.app_show_click';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.app_show_click.count.in.php');
            break;
        case 315://SDK视频录制统计数据归档
            $tmp_str = 'SDK视频录制统计数据归档:';
            $tmp_f = 'export.sdk_video_transcribe';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_video_transcribe.count.in.php');
            break;
        case 316://SDK模拟手柄统计数据归档
            $tmp_str = 'SDK模拟手柄统计数据归档:';
            $tmp_f = 'export.sdk_sim_handle';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_sim_handle.count.in.php');
            break;
        case 317://SDK实体模拟手柄连接统计数据归档
            $tmp_str = 'SDK实体模拟手柄连接统计数据归档:';
            $tmp_f = 'export.sdk_handle_connect';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_handle_connect.count.in.php');
            break;
        case 318://SDK通用数据归档
            $tmp_str = 'SDK通用数据归档:';
            $tmp_f = 'export.sdk_common';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.sdk_common.count.in.php');
            break;
        case 319://APP通用数据归档
            $tmp_str = 'APP通用数据归档:';
            $tmp_f = 'export.app_common';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.app_common.count.in.php');
            break;
        case 320://视频广告通用数据归档
            $tmp_str = '视频广告通用数据归档:';
            $tmp_f = 'export.video_vert_common';
            $tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'save_data/tmp_action_export.video_vert_common.count.in.php');
            break;
	case 4://更新统计数据
		$tmp_str = '手柄包括的游戏数量更新:';
		$tmp_f = 'mzw_game_handle.gh_game_num';
		$tmp_str .= @file_get_contents(WEBPATH_DIR_INC.'count_data/update_handle_game_num.php');
		break;
	default:
		$tmp_str = '非法操作:';
		break;
}
sys_log_write_content($tmp_str,'sys_auto',$tmp_f);

?>