<?php
/*=============================================================================
#     FileName: tmp_action.down_speed.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/down_speed.php)数据存在数据库存中
#		  Desc: 有些记录也是在/tvapi{2,3}/game_speed.php 里记录过来的
#       Author: cai
#        Email: ddcai@163.com
#   LastChange: 2015-01-15 16:57:48
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
$tmp_arr = read_file_random($tmp_aa,"down_speed",true);
if(is_array($tmp_arr)){
	$tmp_table_name = "kyx_game_down_speed_log";//数据表名字
	$str_sql = "insert into ".$tmp_table_name."(`gdsl_in_date`, `gv_id`, `gdsl_ip`, `gdsl_mac`, 
			`gdsl_title`, `gdsl_in_time`, `gdsl_brand`, `gdsl_model`, `gdsl_packname`, 
			`gdsl_use_time`, `gdsl_cdn_type`, `gdsl_speed`, `gdsl_down_len`)values";
   
    $str_sql_2 = "";
    $i = 0;
    foreach($tmp_arr as $val){
        if(!empty($val)){
            $val = str_replace(chr(10),"",$val);
            $val = str_replace(chr(13),"",$val);
            $tmp_in_arr = explode("|",$val);
            
            $str_sql_2 .= "(";
            
            $tmp_sql_val = $tmp_in_arr[5];//日期
            $tmp_sql_val .= ",".intval($tmp_in_arr[0]);//gv_id
            $tmp_sql_val .= ",'".$tmp_in_arr[1]."'";//IP
            $tmp_sql_val .= ",'".$tmp_in_arr[2]."'";//mac
            $tmp_sql_val .= ",'".$tmp_in_arr[3]."'";//title
            $tmp_sql_val .= ",".$tmp_in_arr[4];//时间
            $tmp_sql_val .= ",'".$tmp_in_arr[6]."'";//brand
            $tmp_sql_val .= ",'".$tmp_in_arr[7]."'";//model
            $tmp_sql_val .= ",'".$tmp_in_arr[8]."'";//packagename
            $tmp_sql_val .= ",".$tmp_in_arr[9];//useTime
            $tmp_sql_val .= ",'".$tmp_in_arr[10]."'";//cdnType
            $tmp_sql_val .= ",".intval($tmp_in_arr[11]);//speed
            $tmp_sql_val .= ",".intval($tmp_in_arr[12]);//downloadLength

            $str_sql_2 .= $tmp_sql_val."),";

            if($i!=500){//每500条数据插入一次
                $i++;
            }else{
                $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
                $conn->query($tmp_sql_3);
				//echo($tmp_sql_3.chr(10)."<hr>");
                $i = 0;
                $str_sql_2 = "";
            }
        }
    }

    if($str_sql_2!=""){
        $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
        $conn->query($tmp_sql_3);
		//echo($tmp_sql_3.chr(10)."<hr>");
    }
    echo('导入down_speed数据成功');
}else{
	echo('没有可导入的down_speed数据');
}
