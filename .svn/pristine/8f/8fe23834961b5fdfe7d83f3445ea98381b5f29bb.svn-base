<?php
/*=============================================================================
#   FileName: mo.count.in.php
#   Desc: 定期把记录在文件中的数据记录(/save_data/mo.count.in.php)数据存在数据库存中
       包括入库，统计，归档这三个流程
#   Author: Xiongjianbang
#   使用方法：/usr/local/php/bin/php   mo.count.in.php  mo_download  20150906
#   LastChange: 2015-09-06 17:18:48
=============================================================================*/
if(php_sapi_name()<>'cli'){
	exit("请您在命令行使用");
}

set_time_limit(0);
define('WEBPATH_DIR','/data/web/api.kuaiyouxi.com'); //整站系统路径
// define('WEBPATH_DIR','/mnt/hgfs/api.kuaiyouxi.com');
define('THIS_DATETIME', time());
define('DS', DIRECTORY_SEPARATOR);
define('LOG_TYPE', 'mo_client_statistics');//日志对象
define('LOG_FILE_PATH', '/data/logs/applications/api.kuaiyouxi.com/save_data');//日志目录
define('LOG_SWITCH', TRUE);//日志开关

include_once(WEBPATH_DIR."/include/mysql.class.php");//mysql 操作类
include_once(WEBPATH_DIR."/include/log.class.php");//日志处理类
include_once(WEBPATH_DIR."/db.save.config.inc.php");//数据表配置
include_once(WEBPATH_DIR."/mo_client.event.config.inc.php");//导入事件配置表
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
            //统计安装
        	case 'mo_install':
        	    $sql = "SELECT `mi_in_date`,`mi_packagename`,`mi_mac`,`mi_title`,`mi_eventid`,count(1) as num  FROM kyx_mo_install_log 
        	    WHERE mi_in_date={$last_date} GROUP BY `mi_eventid`, `mi_packagename`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	        $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_mo_install` WHERE mi_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	         $data['mi_in_date'] = trim($value['mi_in_date']);
        	         $data['mi_title'] = trim($value['mi_title']);
        	         $data['mi_packagename'] = trim($value['mi_packagename']);
        	         $data['mi_num'] = trim($value['num']);
        	         $data['mi_eventid'] = trim($value['mi_eventid']);
        	         $conn->save('kyx_mo_install', $data);
        	    }
        	    unset($data);
        	break;
        	
        	//统计卸载
        	case 'mo_uninstall':
        	    $sql = "SELECT `mu_in_date`,`mu_packagename`,`mu_title`,`mu_eventid`,count(1) as num  FROM kyx_mo_uninstall_log
        	    WHERE mu_in_date={$last_date}  GROUP BY `mu_eventid`, `mu_packagename`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_mo_uninstall` WHERE mu_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
                	    $data['mu_in_date'] = trim($value['mu_in_date']);
                	    $data['mu_title'] = trim($value['mu_title']);
                	    $data['mu_packagename'] = trim($value['mu_packagename']);
        		         $data['mu_num'] = trim($value['num']);
        		         $data['mu_eventid'] = trim($value['mu_eventid']);
        		         $conn->save('kyx_mo_uninstall', $data);
        	    }
        	    unset($data);
        	break;
        	
        	
        	//首页，可以查看搜索，分类排行
        	case 'mo_home_page':
        	    $sql = "SELECT `mhp_in_date`,`mhp_mac`,`mhp_eventid`,count(1) as num,`mhp_type`,`mhp_title`  FROM kyx_mo_home_page_log
        	    WHERE mhp_in_date={$last_date} GROUP BY `mhp_eventid`, `mhp_type`,`mhp_title` ";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_mo_home_page` WHERE mhp_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
            	    $data['mhp_in_date'] = trim($value['mhp_in_date']);
            	    $data['mhp_num'] = trim($value['num']);
            	    $data['mhp_eventid'] = trim($value['mhp_eventid']);
            	    $data['mhp_type'] = trim($value['mhp_type']);
            	    $data['mhp_title'] = trim($value['mhp_title']);
            	    $conn->save('kyx_mo_home_page', $data);
        	    }
        	    unset($data);
        	    break;
        	
        	//统计分类页
        	case 'mo_category_page':
        	    $sql = "SELECT `mcp_in_date`,`mcp_categoryId`,`mcp_eventid`,count(1) as num,`mcp_categoryName`  FROM kyx_mo_category_page_log
        	    WHERE mcp_in_date={$last_date} GROUP BY `mcp_eventid`, `mcp_categoryId`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_mo_category_page` WHERE mcp_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	            $data['mcp_in_date'] = trim($value['mcp_in_date']);
        	            $data['mcp_cateogryid'] = trim($value['mcp_categoryId']);
        	            $data['mcp_categoryname'] = trim($value['mcp_categoryName']);
                        $data['mcp_num'] = trim($value['num']);
                        $data['mcp_eventid'] = trim($value['mcp_eventid']);
                        $conn->save('kyx_mo_category_page', $data);
        		 }
        		 unset($data);
            break;
        	
            //统计详情页
        	case 'mo_detail_page':
        	    $sql = "SELECT `mdp_in_date`,`mdp_packagename`,`mdp_title`,`mdp_eventid`,count(1) as num,`mdp_appid`  FROM kyx_mo_detail_page_log
        	    WHERE mdp_in_date={$last_date} GROUP BY `mdp_eventid`, `mdp_appid`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	    $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_mo_detail_page` WHERE mdp_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	            $data['mdp_in_date'] = trim($value['mdp_in_date']);
        	            $data['mdp_title'] = trim($value['mdp_title']);
        	            $data['mdp_packagename'] = trim($value['mdp_packagename']);
        	            $data['mdp_num'] = trim($value['num']);
                        $data['mdp_eventid'] = trim($value['mdp_eventid']);
                        $data['mdp_appid'] = trim($value['mdp_appid']);
                        $conn->save('kyx_mo_detail_page', $data);
        		    }
        		    unset($data);
        		break;
        	
        	//统计下载
        	case 'mo_download':
        	     $sql = "SELECT `md_in_date`,`md_packagename`,`md_title`,`md_eventid`,count(1) as num,`md_gameSize`  FROM kyx_mo_download_log
        	    WHERE md_in_date={$last_date} GROUP BY `md_eventid`, `md_packagename`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_mo_download` WHERE md_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	         $data['md_in_date'] = trim($value['md_in_date']);
        	         $data['md_title'] = trim($value['md_title']);
        	         $data['md_packagename'] = trim($value['md_packagename']);
                     $data['md_num'] = trim($value['num']);
                	 $data['md_eventid'] = trim($value['md_eventid']);
                	 $data['md_gameSize'] = trim($value['md_gameSize']);
                	 $conn->save('kyx_mo_download', $data);
        	    }
        	    unset($data);
        	break;
        	
        	//统计设置页面
        	case 'mo_set_page':
        	    $sql = "SELECT `msp_in_date`,`msp_mac`,`msp_eventid`,count(1) as num,`msp_status`  FROM kyx_mo_set_page_log
        	    WHERE msp_in_date={$last_date} GROUP BY `msp_eventid`,`msp_status`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_mo_set_page` WHERE msp_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	           $data['msp_in_date'] = trim($value['msp_in_date']);
                        $data['msp_status'] = trim($value['msp_status']);
                        $data['msp_num'] = trim($value['num']);
                        $data['msp_eventid'] = trim($value['msp_eventid']);
                        $conn->save('kyx_mo_set_page', $data);
        	    }
        	    unset($data);
        	    break;
        	    
        	    //统计游戏排行
        	    case 'mo_sort_rank':
        	        $sql = "SELECT `msr_in_date`,`msr_mac`,`msr_eventid`,count(1) as num  FROM kyx_mo_sort_rank_log
        	        WHERE msr_in_date={$last_date} GROUP BY `msr_eventid`";
        	        $res = $conn->find($sql);
        	        if(empty($res)){
        	        $obj_log->log('notice', "{$action}没有统计记录");
        	        }
        	        $sql = "DELETE FROM `kyx_mo_sort_rank` WHERE msr_in_date={$last_date}";
        	        $conn->query($sql);
        	        foreach ($res as $value) {
                	        $data['msr_in_date'] = trim($value['msr_in_date']);
                	        $data['msr_num'] = trim($value['num']);
                	        $data['msr_eventid'] = trim($value['msr_eventid']);
                	        $conn->save('kyx_mo_sort_rank', $data);
                	    }
        	    	unset($data);
        	    break;
        	    
        	    //统计搜索
        	    case 'mo_search':
        	        $sql = "SELECT `ms_in_date`,`ms_mac`,`ms_eventid`,count(1) as num,`ms_hotWords`  FROM kyx_mo_search_log
        	        WHERE ms_in_date={$last_date} GROUP BY `ms_eventid`,`ms_hotWords`";
        	        $res = $conn->find($sql);
        	        if(empty($res)){
        	           $obj_log->log('notice', "{$action}没有统计记录");
        	        }
    	            $sql = "DELETE FROM `kyx_mo_search` WHERE ms_in_date={$last_date}";
    	            $conn->query($sql);
    	            foreach ($res as $value) {
        	            $data['ms_in_date'] = trim($value['ms_in_date']);
        	            $data['ms_num'] = trim($value['num']);
    	                $data['ms_eventid'] = trim($value['ms_eventid']);
    	                $data['ms_hotwords'] = trim($value['ms_hotWords']);
    	                $conn->save('kyx_mo_search', $data);
    	            }
    	            unset($data);
    	         break;
    	         
    	         //管理访问
    	         case 'mo_manage_access':
    	             $sql = "SELECT `mma_in_date`,`mma_mac`,`mma_eventid`,count(1) as num  FROM kyx_mo_manage_access_log
    	             WHERE mma_in_date={$last_date} GROUP BY `mma_eventid`";
    	             $res = $conn->find($sql);
    	             if(empty($res)){
    	               $obj_log->log('notice', "{$action}没有统计记录");
    	             }
	                 $sql = "DELETE FROM `kyx_mo_manage_access` WHERE mma_in_date={$last_date}";
	                 $conn->query($sql);
	                 foreach ($res as $value) {
    	                 $data['mma_in_date'] = trim($value['mma_in_date']);
    	                 $data['mma_num'] = trim($value['num']);
                         $data['mma_eventid'] = trim($value['mma_eventid']);
                         $conn->save('kyx_mo_manage_access', $data);
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
                //搜索使用率：事件id为1035，搜索量占总用户的多少
/*                 $sql = "SELECT COUNT(1) AS ct FROM `kyx_mo_home_page_log` WHERE  `mhp_eventid`=1035 ";
                        $res = $conn->get_one($sql);
                $data['so_num'] = $res['ct'];
                $data['so_rate'] = round($data['so_num']/$data['total_user'],2)*100;
                //每周排行的使用率：用户点击量，占总用户的多少，事件id1037
                $sql = "SELECT COUNT(1) AS ct FROM `kyx_mo_home_page_log` WHERE  `mhp_eventid`=1037 ";
                $res = $conn->get_one($sql);
                $data['home_week_num']  = $res['ct'];
                $data['home_week_rate'] = round($data['home_week_num']/$data['total_user'],2)*100;
                //游戏分类里的使用率：打开游戏情况，事件id1004，占总用户的多少
                $sql = "SELECT COUNT(1) AS ct FROM `kyx_mo_category_page_log` WHERE  `mcp_eventid`=1004 ";
                $res = $conn->get_one($sql);
                $data['category_num'] = $res['ct'];
                $data['category_rate'] = round($data['category_num']/$data['total_user'],2)*100; */
                //卸载：事件id1081，占总用户的多少
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


