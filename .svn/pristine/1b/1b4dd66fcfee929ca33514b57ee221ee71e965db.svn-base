<?php
/*=============================================================================
#     FileName: tmp_action.game_down.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/game_down.php)数据存在数据库存中
#		  Desc: 有些记录也是在/tvapi{2,3}/game_down.php 里记录过来的
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
$tmp_arr = read_file_random($tmp_aa,"game_down",true);
if(is_array($tmp_arr)){
	$tmp_table_name = "kyx_game_down_log";//数据表名字
	$str_sql = "insert into ".$tmp_table_name."(`gdl_in_date`, `gv_id`, `gdl_ip`, `gdl_gpu`, `gdl_cpu`,
			 `gdl_in_time`, `gdl_brand`, `gdl_model`, `gdl_sysversion`)values";
   
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
            $tmp_sql_val .= ",'".$tmp_in_arr[2]."'";//GPU
            $tmp_sql_val .= ",'".$tmp_in_arr[3]."'";//CPU
            $tmp_sql_val .= ",".$tmp_in_arr[4];//时间
            $tmp_sql_val .= ",'".$tmp_in_arr[6]."'";//brand
            $tmp_sql_val .= ",'".$tmp_in_arr[7]."'";//model
            $tmp_sql_val .= ",'".$tmp_in_arr[8]."'";//sysversion

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
    echo('导入game_down数据成功');
}else{
	echo('没有可导入的game_down数据');
}
