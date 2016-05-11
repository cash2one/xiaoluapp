<?php
/*=============================================================================
#   FileName: video.app.count.in.php
#   Desc: 定期把记录在文件中的数据记录(/save_data/video.app.count.in.php)数据存在数据库存中
       包括入库，统计，归档这三个流程
#   Author: Xiongjianbang
#   使用方法：/usr/local/php/bin/php   video.app.count.in.php  video_app_home  20151011
#   LastChange: 2015-09-06 17:18:48
=============================================================================*/
if(php_sapi_name()<>'cli'){
	exit("请您在命令行使用");
}

set_time_limit(0);
ini_set("memory_limit","256M");
// define('WEBPATH_DIR','/mnt/hgfs/api.kuaiyouxi.com');
define('WEBPATH_DIR','/data/web/api.kuaiyouxi.com'); //整站系统路径
define('THIS_DATETIME', time());
define('DS', DIRECTORY_SEPARATOR);
define('LOG_TYPE', 'mo_client_statistics');//日志对象
define('LOG_FILE_PATH', '/data/logs/applications/api.kuaiyouxi.com/save_data');//日志目录
define('LOG_SWITCH', TRUE);//日志开关

include_once(WEBPATH_DIR."/include/mysql.class.php");//mysql 操作类
include_once(WEBPATH_DIR."/include/log.class.php");//日志处理类
include_once(WEBPATH_DIR."/db.save.config.inc.php");//数据表配置
include_once(WEBPATH_DIR."/video_app.event.config.inc.php");//导入事件配置表
include_once(WEBPATH_DIR."/save_data/count.in.class.php");//数据处理类


$action = isset($argv[1])?trim($argv[1]):''; //动作类别
$datetime= isset($argv[2])?strtotime(trim($argv[2])):THIS_DATETIME;//时间,如果想重新统计指定日期，这里需要填写指定日期的第二天
if(empty($action)){
    echo("事件名称不能为空！");
    exit;
}

$obj_log = Log::get_instance();
//昨天的时间
$last_date =  date("Ymd",$datetime - 24*60*60);
$arr_config = array();
if(isset($arr_event_config[$action]) && !empty($arr_event_config[$action])){
    $arr_config = $arr_event_config[$action];
}

if(!empty($arr_config)){
        //根据不同模块将公共字段和私有字段合并起来
        $arr_config['import_fields'] = array_merge($arr_public_fields,$arr_config['import_fields']);
        $arr_config['last_date'] = $last_date;
        //创建对象
        $obj = new handle_data($arr_config,$conn);
        //导入
        $arr_create = $obj->create_table('import');
        $obj_log->log('notice', $arr_create['msg']);
        if($arr_create['status']==200){
            $arr_import = $obj->import_data();
            $obj_log->log('notice', $arr_import['msg']);
        }
        unset($arr_create,$arr_import);
        
        
        //统计操作
        $arr_create = $obj->create_table('statistics');
        $obj_log->log('notice', $arr_create['msg']);
        switch ($action) {
            //版块统计
            case 'video_app_section':
                $sql = "SELECT `vas_in_date`,`vas_pn`, `vas_title`,count(1) as num,`vas_module`,`vas_chl`  FROM kyx_video_app_section_log
                WHERE vas_in_date={$last_date} GROUP BY  `vas_pn`,`vas_module`, `vas_chl`";
                $res = $conn->find($sql);
                if(empty($res)){
                $obj_log->log('notice', "{$action}没有统计记录");
                }
                $sql = "DELETE FROM `kyx_video_app_section` WHERE vas_in_date={$last_date}";
                $conn->query($sql);
                foreach ($res as $value) {
                        $data['vas_in_date'] = trim($value['vas_in_date']);
                        $data['vas_pn'] = trim($value['vas_pn']);
                        $data['vas_title'] = trim($value['vas_title']);
                        $data['vas_num'] = trim($value['num']);
                        $data['vas_module'] = trim($value['vas_module']);
                        $data['vas_chl'] = trim($value['vas_chl']);
                        $conn->save('kyx_video_app_section', $data);
                }
            unset($data);
            break;
            
            //统计首页点击
        	case 'video_app_home':
        	    $sql = "SELECT `vah_in_date`,`vah_pn`, `vah_title`,count(1) as num,`vah_module`,`vah_clickpos`,`vah_chl`  FROM kyx_video_app_home_log 
        	    WHERE vah_in_date={$last_date} GROUP BY  `vah_pn`,`vah_module`,`vah_clickpos`,`vah_chl`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	        $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_video_app_home` WHERE vah_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	         $data['vah_in_date'] = trim($value['vah_in_date']);
        	         $data['vah_pn'] = trim($value['vah_pn']);
        	         $data['vah_title'] = trim($value['vah_title']);
        	         $data['vah_num'] = trim($value['num']);
        	         $data['vah_module'] = trim($value['vah_module']);
        	         $data['vah_clickpos'] = trim($value['vah_clickpos']);
        	         $data['vah_chl'] = trim($value['vah_chl']);
        	         $conn->save('kyx_video_app_home', $data);
        	    }
        	    unset($data);
        	break;
        	
        	//排行点击统计
        	case 'video_app_rank':
        	    $sql = "SELECT `var_in_date`,`var_pn`,`var_title`,count(1) as num,`var_chl`,`var_clickpos`,`var_ranktype`  FROM kyx_video_app_rank_log
        	    WHERE var_in_date={$last_date}  GROUP BY `var_pn`, `var_chl`,`var_clickpos`,`var_ranktype`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_video_app_rank` WHERE var_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
                	    $data['var_in_date'] = trim($value['var_in_date']);
                	    $data['var_pn'] = trim($value['var_pn']);
                	    $data['var_title'] = trim($value['var_title']);
        		         $data['var_num'] = trim($value['num']);
        		         $data['var_chl'] = trim($value['var_chl']);
        		         $data['var_clickpos'] = trim($value['var_clickpos']);
        		         $data['var_ranktype'] = trim($value['var_ranktype']);
        		         $conn->save('kyx_video_app_rank', $data);
        	    }
        	    unset($data);
        	break;
        	
        	
        	//视频作者点击统计
        	case 'video_app_author':
        	    $sql = "SELECT `vaa_in_date`,`vaa_pn`,`vaa_title`,count(1) as num,`vaa_authorid`,`vaa_author`,`vaa_chl`  FROM kyx_video_app_author_log
        	    WHERE vaa_in_date={$last_date} GROUP BY `vaa_pn`, `vaa_authorid`,`vaa_chl` ";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_video_app_author` WHERE vaa_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
            	    $data['vaa_in_date'] = trim($value['vaa_in_date']);
            	    $data['vaa_num'] = trim($value['num']);
            	    $data['vaa_pn'] = trim($value['vaa_pn']);
            	    $data['vaa_title'] = trim($value['vaa_title']);
            	    $data['vaa_authorid'] = trim($value['vaa_authorid']);
            	    $data['vaa_author'] = trim($value['vaa_author']);
            	    $data['vaa_chl'] = trim($value['vaa_chl']);
            	    $conn->save('kyx_video_app_author', $data);
        	    }
        	    unset($data);
        	    break;
        	
        	//统计专辑点击
        	case 'video_app_album':
        	    $sql = "SELECT `vaa_in_date`,`vaa_pn`,`vaa_title`,count(1) as num,`vaa_topicid`,`vaa_topictitle`,`vaa_authorid`,`vaa_author`,`vaa_chl`  FROM kyx_video_app_album_log
        	    WHERE vaa_in_date={$last_date}  GROUP BY `vaa_pn`, `vaa_topicid`, `vaa_authorid`, `vaa_chl`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_video_app_album` WHERE vaa_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	            $data['vaa_in_date'] = trim($value['vaa_in_date']);
        	            $data['vaa_pn'] = trim($value['vaa_pn']);
        	            $data['vaa_title'] = trim($value['vaa_title']);
                        $data['vaa_num'] = trim($value['num']);
                        $data['vaa_topicid'] = trim($value['vaa_topicid']);
                        $data['vaa_topictitle'] = trim($value['vaa_topictitle']);
                        $data['vaa_authorid'] = trim($value['vaa_authorid']);
                        $data['vaa_author'] = trim($value['vaa_author']);
                        $data['vaa_chl'] = trim($value['vaa_chl']);
                        $conn->save('kyx_video_app_album', $data);
        		 }
        		 unset($data);
            break;
        	
            //相关推荐点击统计
        	case 'video_app_related':
        	    $sql = "SELECT `var_in_date`,`var_pn`,`var_title`,`var_chl`,count(1) as num,`var_videoid`,`var_clickpos`,`var_relatedvideoid`,`var_relatedvideotitle`  FROM kyx_video_app_related_log
        	    WHERE var_in_date={$last_date} GROUP BY `var_pn`,`var_chl`,`var_videoid`,`var_clickpos`,`var_relatedvideoid`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	    $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_video_app_related` WHERE var_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	            $data['var_in_date'] = trim($value['var_in_date']);
        	            $data['var_pn'] = trim($value['var_pn']);
        	            $data['var_title'] = trim($value['var_title']);
        	            $data['var_num'] = trim($value['num']);
                        $data['var_chl'] = trim($value['var_chl']);
                        $data['var_videoid'] = trim($value['var_videoid']);
                        $data['var_clickpos'] = trim($value['var_clickpos']);
                        $data['var_relatedvideoid'] = trim($value['var_relatedvideoid']);
                        $data['var_relatedvideotitle'] = trim($value['var_relatedvideotitle']);
                        $conn->save('kyx_video_app_related', $data);
        		    }
        		    unset($data);
        		break;
        	
        	//统计播放
        	case 'video_app_play':
        	     $sql = "SELECT `ap_in_date`,`ap_pn`,`ap_title`,`ap_chl`,count(1) as num,`ap_videoid`,`ap_videotitle`  FROM kyx_video_app_play_log
        	    WHERE ap_in_date={$last_date} GROUP BY `ap_pn`, `ap_chl`,`ap_videoid`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_video_app_play` WHERE ap_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	         $data['ap_in_date'] = trim($value['ap_in_date']);
        	         $data['ap_title'] = trim($value['ap_title']);
        	         $data['ap_pn'] = trim($value['ap_pn']);
                     $data['ap_num'] = trim($value['num']);
                	 $data['ap_chl'] = trim($value['ap_chl']);
                	 $data['ap_videoid'] = trim($value['ap_videoid']);
                	 $data['ap_videotitle'] = trim($value['ap_videotitle']);
                	 $conn->save('kyx_video_app_play', $data);
        	    }
        	    unset($data);
        	break;
        	
        	//设置点击统计
        	case 'video_app_set':
        	     $sql = "SELECT `vas_in_date`,`vas_pn`,`vas_title`,count(1) as num,`vas_type`,`vas_state`,`vas_chl`  FROM kyx_video_app_set_log
        	    WHERE vas_in_date={$last_date} GROUP BY `vas_pn`, `vas_type`,`vas_chl`,`vas_state` ";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
	            $sql = "DELETE FROM `kyx_video_app_set` WHERE vas_in_date={$last_date}";
	            $conn->query($sql);
	            foreach ($res as $value) {
                	            $data['vas_in_date'] = trim($value['vas_in_date']);
                	            $data['vas_num'] = trim($value['num']);
                	            $data['vas_pn'] = trim($value['vas_pn']);
                	            $data['vas_title'] = trim($value['vas_title']);
                	            $data['vas_type'] = trim($value['vas_type']);
                	            $data['vas_state'] = trim($value['vas_state']);
                                $data['vas_chl'] = trim($value['vas_chl']);
	                            $conn->save('kyx_video_app_set', $data);
	            }
	            unset($data);
                break;
        	
        }
        //归档
        $arr_file = $obj->file_data();
        $obj_log->log('notice', $arr_file['msg']);
        $obj_log->close();
}
//非事件配置里的动作
else{
    switch ($action) {
        case 'mo_total_sum':
                //汇总小计操作
                $sql = "DELETE FROM `kyx_mo_total_sum` WHERE `in_date`={$last_date}";
                $conn->query($sql);
                $data['in_date'] = $last_date;
                //总用户
                $sql ="SELECT COUNT(1) AS ct FROM (
                                    SELECT DISTINCT(`msr_mac`) AS mac FROM `kyx_mo_sort_rank_log`
                                    UNION
                                    SELECT DISTINCT(`msp_mac`) AS mac FROM `kyx_mo_set_page_log`
                                    UNION
                                    SELECT DISTINCT(`mi_mac`) AS mac FROM `kyx_mo_install_log`
                                    UNION
                                    SELECT DISTINCT(`mu_mac`) AS mac FROM `kyx_mo_uninstall_log`
                                    UNION
                                    SELECT DISTINCT(`mhp_mac`) AS mac FROM `kyx_mo_home_page_log`
                                    UNION
                                    SELECT DISTINCT(`mcp_mac`) AS mac FROM `kyx_mo_category_page_log`
                                    UNION
                                    SELECT DISTINCT(`mdp_mac`) AS mac FROM `kyx_mo_detail_page_log`
                                    UNION
                                    SELECT DISTINCT(`md_mac`) AS mac FROM `kyx_mo_download_log`
                                    ) AS t ";
                $res = $conn->get_one($sql);
                $data['total_user'] = $res['ct'];
                if($data['total_user']<=0){
                    $obj_log->log('notice', '总用户为0，不统计');
                	exit;
                }
                $sql = "SELECT COUNT(1) AS ct FROM `kyx_mo_uninstall_log` WHERE  `mu_eventid`=1801 ";
                $res = $conn->get_one($sql);
                $data['uninstall_num'] = $res['ct'];
                $data['uninstall_rate'] = round($data['uninstall_num']/$data['total_user'],2)*100;
                //下载量：事件id,需要展示游戏的下载量　事件1603，占总用户的多少
                $sql = "SELECT COUNT(DISTINCT(`md_logSessionId`)) AS ct FROM `kyx_mo_download_log` WHERE  `md_eventid`=1603";
                $res = $conn->get_one($sql);
                $data['download_num'] = $res['ct'];
                $data['download_rate'] = round($data['download_num']/$data['total_user'],2)*100;
                //下载总数，根据logsessionid
                $sql = "SELECT COUNT(DISTINCT(`md_logSessionId`)) AS ct FROM `kyx_mo_download_log` ";
                $res = $conn->get_one($sql);
                $data['download_session_num'] = $res['ct'];
                //暂停:事件id1606，查看游戏暂停使用量，占总下载的多少。
                $sql = "SELECT COUNT(DISTINCT(`md_logSessionId`)) AS ct FROM `kyx_mo_download_log` WHERE  `md_eventid`=1606 ";
                $res = $conn->get_one($sql);
                $data['download_pause_num'] = $res['ct'];
                //取消下载：事件1607，占总下载的多少。
                $sql = "SELECT COUNT(DISTINCT(`md_logSessionId`)) AS ct FROM `kyx_mo_download_log` WHERE  `md_eventid`=1607 ";
                $res = $conn->get_one($sql);
                $data['download_cancel_num'] = $res['ct'];
                //下载失败情况：事件id1602，需要统计这各个出错情况的占比。
                $sql = "SELECT COUNT(DISTINCT(`md_logSessionId`)) AS ct FROM `kyx_mo_download_log` WHERE  `md_eventid`=1602 ";
                $res = $conn->get_one($sql);
                $data['download_failure_num'] = $res['ct'];
                //出错后重新下载情况：事件id1610 , 使用率，占总出错率的多少
                $sql = "SELECT COUNT(DISTINCT(`md_logSessionId`)) AS ct FROM `kyx_mo_download_log` WHERE  `md_eventid`=1610 ";
                $res = $conn->get_one($sql);
                $data['download_again_num'] = $res['ct'];
                //下载成功率1601：下载成功占总下载量的多少
                $sql = "SELECT COUNT(DISTINCT(`md_logSessionId`)) AS ct FROM `kyx_mo_download_log` WHERE  `md_eventid`=1601 ";
                $res = $conn->get_one($sql);
                $data['download_success_num'] = $res['ct'];
                if($data['download_session_num']==0){
                    $data['download_pause_rate'] = 0;
                    $data['download_cancel_rate'] = 0;
                    $data['download_failure_rate'] = 0;
                    $data['download_again_rate'] = 0;
                    $data['download_success_rate'] = 0;
                }else{
                    $data['download_pause_rate'] = round($data['download_pause_num']/$data['download_session_num'],2)*100;
                    $data['download_cancel_rate'] = round($data['download_cancel_num']/$data['download_session_num'],2)*100;
                    $data['download_failure_rate'] = round($data['download_failure_num']/$data['download_session_num'],2)*100;
                    $data['download_again_rate'] = round($data['download_again_num']/$data['download_session_num'],2)*100;
                    $data['download_success_rate'] = round($data['download_success_num']/$data['download_session_num'],2)*100;
                }
                //首页访问量
                $sql = "SELECT COUNT(*) AS ct FROM `kyx_mo_home_page_log` WHERE  `mhp_eventid`=1101  ";
                $res = $conn->get_one($sql);
                $data['home_views'] = $res['ct'];
                //分类访问量
                $sql = "SELECT COUNT(*) AS ct FROM `kyx_mo_category_page_log` WHERE  `mcp_eventid`=1201  ";
                $res = $conn->get_one($sql);
                $data['category_views'] = $res['ct'];
                //排行访问量
                $sql = "SELECT COUNT(*) AS ct FROM `kyx_mo_sort_rank_log` WHERE  `msr_eventid`=1301  ";
                $res = $conn->get_one($sql);
                $data['sort_views'] = $res['ct'];
                //管理访问量
                $sql = "SELECT COUNT(*) AS ct FROM `kyx_mo_manage_access_log` WHERE  `mma_eventid`=1401 ";
                $res = $conn->get_one($sql);
                $data['manage_views'] = $res['ct'];
                //搜索访问量
                $sql = "SELECT COUNT(*) AS ct FROM `kyx_mo_search_log` WHERE  `ms_eventid`=1501 ";
                $res = $conn->get_one($sql);
                $data['search_views'] = $res['ct'];
                //下载访问量
                $sql = "SELECT COUNT(*) AS ct FROM `kyx_mo_download_log` WHERE  `md_eventid`=1603  ";
                $res = $conn->get_one($sql);
                $data['download_views'] = $res['ct'];
                $conn->save('kyx_mo_total_sum', $data);
                unset($data);
            break;
            $obj_log->close();
    }
}


