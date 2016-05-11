<?php
/*=============================================================================
#     FileName: tmp_action.show.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/count.php)数据存在数据库存中
#       Author: cai
#        Email: ddcai@163.com
#   LastChange: 2014-12-17 15:57:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

include_once("../db.save.config.inc.php");
$tmp_aa = isset($_GET["myid"])?intval($_GET["myid"]):'';
if(empty($tmp_aa)){
    $tmp_aa = rand(1,5);
}

//随机取内容
$tmp_arr = read_file_random($tmp_aa,"show",true);
if(is_array($tmp_arr)){
	$tmp_table_name = "kyx_count_log";//数据表名字
	$str_sql = "insert into ".$tmp_table_name."(`cl_date`, `cl_rooted`, `cl_width`, `cl_height`, 
			`cl_model`, `cl_brand`, `cl_density`, `cl_gpu`, `cl_systemversion`, `cl_softwareversion`,
			 `cl_cpu`, `cl_firmwire`, `cl_mac`, `cl_time`, `cl_eventid`, 
			`cl_numbder`, `cl_packagename`, `cl_versionname`, 
			`cl_versioncode`, `cl_code`, `cl_title`, `cl_path`,
			`cl_installdate`, `cl_url`, `cl_location`, `cl_appid`, `cl_cateogryid`, 
			`cl_ip`, `cl_intime`, `cl_devicename`,`cl_memorysize`,`cl_insdcardsize`,
			`cl_issdcard`,`cl_vid`,`cl_pid`,`cl_mid`,`cl_name`,`cl_keys`,`cl_logsessionid`,`cl_downloadpath`,
			`cl_storagesize`,`cl_downloadpoint`,`cl_downloadurl`,`cl_backupurl`,`cl_gamesize`,`cl_statuscode`,
			`cl_serverip`,`cl_errormsg`,`cl_timestr`)values";
   
    $str_sql_2 = "";
    $i = 0;
    foreach($tmp_arr as $val){
        if(!empty($val)){
            $val = str_replace(chr(10),"",$val);
            $val = str_replace(chr(13),"",$val);
            $tmp_in_arr = explode("|",$val);
            
            $str_sql_2 .= "(";
            $tmp_sql_val = date("Ymd",$tmp_in_arr[27]);
            $tmp_in_arr[12] = substr($tmp_in_arr[12],0,10);//时间 因为客户端传回来的是毫秒的//1418797503659
            $tmp_in_arr[21] = substr($tmp_in_arr[21],0,10);//安装时间 因为客户端传回来的是毫秒的//1418797503659
            $j = 1;
            foreach ($tmp_in_arr as $v){
            	if(is_empty($v)){
            		$tmp_sql_val .= ",'0'";
            	}else{
            		$tmp_sql_val .= ",'".mysql_real_escape_string($v)."'";
            	}
            	$j++;
            	if($j==49){//等于49个字段的时候退出
            		break;
            	}
            }
            //如果不够49个字段，则补够
            while($j<49){
            	$tmp_sql_val .= ",'0'";
            	$j++;
            }
            
            $str_sql_2 .= $tmp_sql_val."),";

            if($i!=500){//每500条数据插入一次
                $i++;
            }else{
                $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
                $conn->query($tmp_sql_3);
				echo($tmp_sql_3.chr(10)."<hr>");
                $i = 0;
                $str_sql_2 = "";
            }
        }
    }

    if($str_sql_2!=""){
        $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
        $conn->query($tmp_sql_3);
//		echo($tmp_sql_3.chr(10)."<hr>");
    }
    echo('导入show数据成功');
}else{
	echo('没有可导入的show数据');
}
