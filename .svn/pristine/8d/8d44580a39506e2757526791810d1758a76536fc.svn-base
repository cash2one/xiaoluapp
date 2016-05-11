<?php
/*=============================================================================
#     FileName: tmp_action.keyword.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/keyword.php)数据存在数据库存中
#		  Desc: 有些记录也是在/include/search.class.php 里记录过来的
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
$tmp_arr = read_file_random($tmp_aa,"keyword",true);
if(is_array($tmp_arr)){
	$tmp_table_name = "kyx_keyword_log";//数据表名字
	$str_sql = "insert into ".$tmp_table_name."(`kd_in_date`, `kd_keyword`, `kd_in_time`, `kd_is_ok`,
			 `kd_key_md5`, `kd_is_cache`,`kd_source`)values";
   
    $str_sql_2 = "";
    $i = 0;
    foreach($tmp_arr as $val){
        if(!empty($val)){
            $val = str_replace(chr(10),"",$val);
            $val = str_replace(chr(13),"",$val);
            $tmp_in_arr = explode("|",$val);
            
            $str_sql_2 .= "(";
            
            $tmp_sql_val = $tmp_in_arr[3];//日期
            $tmp_sql_val .= ",'".$tmp_in_arr[0]."'";//关键字
            $tmp_sql_val .= ",".$tmp_in_arr[2];//时间
            $tmp_sql_val .= ",".$tmp_in_arr[1];//搜索是否成功
            $tmp_sql_val .= ",'".$tmp_in_arr[4]."'";//关键字MD5值
            $tmp_sql_val .= ",".$tmp_in_arr[5];//是否从缓存获得
            $tmp_sql_val .= ",".(isset($tmp_in_arr[6]) ? intval($tmp_in_arr[6]) : 1);//搜索来源（1:游戏搜索 2：视频搜索）

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
    echo('导入keyword数据成功');
}else{
	echo('没有可导入的keyword数据');
}
