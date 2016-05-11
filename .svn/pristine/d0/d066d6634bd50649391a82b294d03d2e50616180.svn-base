<?php
/*=============================================================================
#   FileName: action.count.in.php
#   Desc: 定期把记录在文件中的数据记录(/save_data/action.count.in.php)数据存在数据库存中
       包括入库，统计，归档这三个流程
#   Author: Xiongjianbang
#   LastChange: 2015-07-20 11:18:48
=============================================================================*/
if(php_sapi_name()<>'cli'){
	exit("请您在命令行使用");
}

set_time_limit(0);
define('WEBPATH_DIR','/data/web/api.kuaiyouxi.com'); //整站系统路径
// define('WEBPATH_DIR','/mnt/hgfs/api.kuaiyouxi.com');
define('THIS_DATETIME', time());
define('DS', DIRECTORY_SEPARATOR);
define('LOG_TYPE', 'tv_client_statistics');//日志对象
define('LOG_FILE_PATH', '/data/logs/applications/api.kuaiyouxi.com/save_data');//日志目录
define('LOG_SWITCH', TRUE);//日志开关

include_once(WEBPATH_DIR."/include/mysql.class.php");//mysql 操作类
include_once(WEBPATH_DIR."/include/log.class.php");//日志处理类
include_once(WEBPATH_DIR."/db.save.config.inc.php");//数据表配置
include_once(WEBPATH_DIR."/tv_client.event.config.inc.php");//导入事件配置表
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
        	case 'tv_install':
        	    $sql = "SELECT `ti_in_date`,`ti_packagename`,`ti_mac`,`ti_title`,`ti_eventid`,count(1) as num  FROM kyx_tv_install_log 
        	    WHERE ti_in_date={$last_date} GROUP BY `ti_eventid`, `ti_packagename`,`ti_mac`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	        $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_tv_install` WHERE ti_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	         $data['ti_in_date'] = trim($value['ti_in_date']);
        	         $data['ti_title'] = trim($value['ti_title']);
        	         $data['ti_packagename'] = trim($value['ti_packagename']);
        	         $data['ti_mac'] = trim($value['ti_mac']);
        	         $data['ti_num'] = trim($value['num']);
        	         $data['ti_eventid'] = trim($value['ti_eventid']);
        	         $conn->save('kyx_tv_install', $data);
        	    }
        	    unset($data);
        	break;
        	
        	//统计卸载
        	case 'tv_uninstall':
        	    $sql = "SELECT `tu_in_date`,`tu_packagename`,`tu_title`,`tu_eventid`,count(1) as num  FROM kyx_tv_uninstall_log
        	    WHERE tu_in_date={$last_date} GROUP BY `tu_eventid`, `tu_packagename`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	    $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_tv_uninstall` WHERE tu_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
                	    $data['tu_in_date'] = trim($value['tu_in_date']);
                	    $data['tu_title'] = trim($value['tu_title']);
                	    $data['tu_packagename'] = trim($value['tu_packagename']);
        		         $data['tu_num'] = trim($value['num']);
        		         $data['tu_eventid'] = trim($value['tu_eventid']);
        		         $conn->save('kyx_tv_uninstall', $data);
        	    }
        	    unset($data);
        	break;
        	
        	//统计启动
        	case 'tv_boot':
        	    $sql = "SELECT `tb_in_date`,`tb_packagename`,`tb_title`,`tb_eventid`,count(1) as num  FROM kyx_tv_boot_log
        	    WHERE tb_in_date={$last_date} GROUP BY `tb_eventid`, `tb_packagename`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	    $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_tv_boot` WHERE tb_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	           $data['tb_in_date'] = trim($value['tb_in_date']);
        	            $data['tb_title'] = trim($value['tb_title']);
        	            $data['tb_packagename'] = trim($value['tb_packagename']);
        	            $data['tb_num'] = trim($value['num']);
                        $data['tb_eventid'] = trim($value['tb_eventid']);
                        $conn->save('kyx_tv_boot', $data);
        		    }
        		unset($data);
        		break;
        	
        	//首页，可以查看搜索，分类排行
        	case 'tv_home_page':
        	    $sql = "SELECT `thp_in_date`,`thp_mac`,`thp_eventid`,count(1) as num,`thp_location`,`thp_title`  FROM kyx_tv_home_page_log
        	    WHERE thp_in_date={$last_date} GROUP BY `thp_eventid`, `thp_location`,`thp_title` ";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_tv_home_page` WHERE thp_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
            	    $data['thp_in_date'] = trim($value['thp_in_date']);
            	    $data['thp_num'] = trim($value['num']);
            	    $data['thp_eventid'] = trim($value['thp_eventid']);
            	    $data['thp_location'] = trim($value['thp_location']);
            	    $data['thp_title'] = trim($value['thp_title']);
            	    $conn->save('kyx_tv_home_page', $data);
        	    }
        	    unset($data);
        	    break;
        	
        	//统计分类页
        	case 'tv_category_page':
        	    $sql = "SELECT `tcp_in_date`,`tcp_cateogryid`,`tcp_eventid`,count(1) as num  FROM kyx_tv_category_page_log
        	    WHERE tcp_in_date={$last_date} GROUP BY `tcp_eventid`, `tcp_cateogryid`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_tv_category_page` WHERE tcp_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	            $data['tcp_in_date'] = trim($value['tcp_in_date']);
        	            $data['tcp_cateogryid'] = trim($value['tcp_cateogryid']);
                        $data['tcp_num'] = trim($value['num']);
                        $data['tcp_eventid'] = trim($value['tcp_eventid']);
                        $conn->save('kyx_tv_category_page', $data);
        		 }
        		 unset($data);
            break;
        	
            //统计详情页
        	case 'tv_detail_page':
        	    $sql = "SELECT `tdp_in_date`,`tdp_packagename`,`tdp_title`,`tdp_eventid`,count(1) as num,`tdp_location`  FROM kyx_tv_detail_page_log
        	    WHERE tdp_in_date={$last_date} GROUP BY `tdp_eventid`, `tdp_packagename`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	    $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_tv_detail_page` WHERE tdp_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	            $data['tdp_in_date'] = trim($value['tdp_in_date']);
        	            $data['tdp_title'] = trim($value['tdp_title']);
        	            $data['tdp_packagename'] = trim($value['tdp_packagename']);
        	            $data['tdp_num'] = trim($value['num']);
                        $data['tdp_eventid'] = trim($value['tdp_eventid']);
                        $conn->save('kyx_tv_detail_page', $data);
        		    }
        		    unset($data);
        		break;
        	
        	//统计下载
        	case 'tv_download':
        	     $sql = "SELECT `td_in_date`,`td_packagename`,`td_title`,`td_eventid`,count(1) as num,`td_gameSize`  FROM kyx_tv_download_log
        	    WHERE td_in_date={$last_date} GROUP BY `td_eventid`, `td_packagename`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_tv_download` WHERE td_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	         $data['td_in_date'] = trim($value['td_in_date']);
        	         $data['td_title'] = trim($value['td_title']);
        	         $data['td_packagename'] = trim($value['td_packagename']);
                     $data['td_num'] = trim($value['num']);
                	 $data['td_eventid'] = trim($value['td_eventid']);
                	 $data['td_gameSize'] = trim($value['td_gameSize']);
                	 $conn->save('kyx_tv_download', $data);
        	    }
        	    unset($data);
        	break;
        	
        	//统计设置页面
        	case 'tv_set_page':
        	    $sql = "SELECT `tsp_in_date`,`tsp_mac`,`tsp_eventid`,count(1) as num,`tsp_status`  FROM kyx_tv_set_page_log
        	    WHERE tsp_in_date={$last_date} GROUP BY `tsp_eventid`,`tsp_status`";
        	    $res = $conn->find($sql);
        	    if(empty($res)){
        	       $obj_log->log('notice', "{$action}没有统计记录");
        	    }
        	    $sql = "DELETE FROM `kyx_tv_set_page` WHERE tsp_in_date={$last_date}";
        	    $conn->query($sql);
        	    foreach ($res as $value) {
        	           $data['tsp_in_date'] = trim($value['tsp_in_date']);
                        $data['tsp_status'] = trim($value['tsp_status']);
                        $data['tsp_num'] = trim($value['num']);
                        $data['tsp_eventid'] = trim($value['tsp_eventid']);
                        $conn->save('kyx_tv_set_page', $data);
        	    }
        	    unset($data);
        	    break;
        	    
        	    //统计游戏筛选排序
        	    case 'tv_select_order':
        	        $sql = "SELECT `tso_in_date`,`tso_mac`,`tso_eventid`,count(1) as num  FROM kyx_tv_select_order_log
        	        WHERE tso_in_date={$last_date} GROUP BY `tso_eventid`";
        	        $res = $conn->find($sql);
        	        if(empty($res)){
        	        $obj_log->log('notice', "{$action}没有统计记录");
        	        }
        	        $sql = "DELETE FROM `kyx_tv_select_order` WHERE tso_in_date={$last_date}";
        	        $conn->query($sql);
        	        foreach ($res as $value) {
                	        $data['tso_in_date'] = trim($value['tso_in_date']);
                	        $data['tso_num'] = trim($value['num']);
                	        $data['tso_eventid'] = trim($value['tso_eventid']);
                	        $conn->save('kyx_tv_select_order', $data);
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
        case 'tv_total_sum':
                //汇总小计操作
                $sql = "DELETE FROM `kyx_tv_total_sum` WHERE `in_date`={$last_date}";
                $conn->query($sql);
                $data['in_date'] = $last_date;
                //总用户
                $sql ="SELECT COUNT(1) AS ct FROM (
                SELECT DISTINCT(`tso_mac`) AS mac FROM `kyx_tv_select_order_log` 
                UNION
                SELECT DISTINCT(`tsp_mac`) AS mac FROM `kyx_tv_set_page_log` 
                UNION
                SELECT DISTINCT(`ti_mac`) AS mac FROM `kyx_tv_install_log`
                UNION
                SELECT DISTINCT(`tu_mac`) AS mac FROM `kyx_tv_uninstall_log` 
                UNION
                SELECT DISTINCT(`tb_mac`) AS mac FROM `kyx_tv_boot_log` 
                UNION
                SELECT DISTINCT(`thp_mac`) AS mac FROM `kyx_tv_home_page_log` 
                UNION
                SELECT DISTINCT(`tcp_mac`) AS mac FROM `kyx_tv_category_page_log` 
                UNION
                SELECT DISTINCT(`tdp_mac`) AS mac FROM `kyx_tv_detail_page_log` 
                UNION
                SELECT DISTINCT(`td_mac`) AS mac FROM `kyx_tv_download_log` 
                ) AS t";
                $res = $conn->get_one($sql);
                $data['total_user'] = $res['ct'];
                if($data['total_user']<=0){
                    $obj_log->log('notice', '总用户为0，不统计');
                	exit;
                }
                //搜索使用率：事件id为1035，搜索量占总用户的多少
                $sql = "SELECT COUNT(1) AS ct FROM `kyx_tv_home_page_log` WHERE  `thp_eventid`=1035 ";
                        $res = $conn->get_one($sql);
                $data['so_num'] = $res['ct'];
                $data['so_rate'] = round($data['so_num']/$data['total_user'],2)*100;
                //每周排行的使用率：用户点击量，占总用户的多少，事件id1037
                $sql = "SELECT COUNT(1) AS ct FROM `kyx_tv_home_page_log` WHERE  `thp_eventid`=1037 ";
                $res = $conn->get_one($sql);
                $data['home_week_num']  = $res['ct'];
                $data['home_week_rate'] = round($data['home_week_num']/$data['total_user'],2)*100;
                //游戏分类里的使用率：打开游戏情况，事件id1004，占总用户的多少
                $sql = "SELECT COUNT(1) AS ct FROM `kyx_tv_category_page_log` WHERE  `tcp_eventid`=1004 ";
                $res = $conn->get_one($sql);
                $data['category_num'] = $res['ct'];
                $data['category_rate'] = round($data['category_num']/$data['total_user'],2)*100;
                //卸载：事件id１０３４，１０３３，占总用户的多少
                $sql = "SELECT COUNT(1) AS ct FROM `kyx_tv_uninstall_log` WHERE  `tu_eventid` IN(1033,1034) ";
                $res = $conn->get_one($sql);
                $data['uninstall_num'] = $res['ct'];
                $data['uninstall_rate'] = round($data['uninstall_num']/$data['total_user'],2)*100;
                //下载量：事件id,需要展示游戏的下载量　事件2001，占总用户的多少
                $sql = "SELECT COUNT(DISTINCT(`td_logSessionId`)) AS ct FROM `kyx_tv_download_log` WHERE  `td_eventid`=2001 ";
                $res = $conn->get_one($sql);
                $data['download_num'] = $res['ct'];
                $data['download_rate'] = round($data['download_num']/$data['total_user'],2)*100;
                //下载总数，根据logsessionid
                $sql = "SELECT COUNT(DISTINCT(`td_logSessionId`)) AS ct FROM `kyx_tv_download_log` ";
                $res = $conn->get_one($sql);
                $data['download_session_num'] = $res['ct'];
                //暂停:事件id　２００４，查看游戏暂停使用量，占总下载的多少。
                $sql = "SELECT COUNT(DISTINCT(`td_logSessionId`)) AS ct FROM `kyx_tv_download_log` WHERE  `td_eventid`=2004 ";
                $res = $conn->get_one($sql);
                $data['download_pause_num'] = $res['ct'];
                //取消下载：事件　２００５，占总下载的多少。
                $sql = "SELECT COUNT(DISTINCT(`td_logSessionId`)) AS ct FROM `kyx_tv_download_log` WHERE  `td_eventid`=2005 ";
                $res = $conn->get_one($sql);
                $data['download_cancel_num'] = $res['ct'];
                //下载失败情况：事件id１０１７，需要统计这各个出错情况的占比。
                $sql = "SELECT COUNT(DISTINCT(`td_logSessionId`)) AS ct FROM `kyx_tv_download_log` WHERE  `td_eventid`=1017 ";
                $res = $conn->get_one($sql);
                $data['download_failure_num'] = $res['ct'];
                //出错后重新下载情况：事件id2008 , 使用率，占总出错率的多少
                $sql = "SELECT COUNT(DISTINCT(`td_logSessionId`)) AS ct FROM `kyx_tv_download_log` WHERE  `td_eventid`=2008 ";
                $res = $conn->get_one($sql);
                $data['download_again_num'] = $res['ct'];
                //下载成功率：下载成功占总下载量的多少
                $sql = "SELECT COUNT(DISTINCT(`td_logSessionId`)) AS ct FROM `kyx_tv_download_log` WHERE  `td_eventid`=1016 ";
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
                $conn->save('kyx_tv_total_sum', $data);
                unset($data);
            break;
            $obj_log->close();
    }
}


