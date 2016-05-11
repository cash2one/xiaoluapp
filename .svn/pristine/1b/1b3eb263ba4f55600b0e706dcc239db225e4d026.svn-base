<?php
/*=============================================================================
#     FileName: tmp_action.video_play.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/tvapi4/video_play.php)数据存在数据库存中
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-05-21 10:52:48
#      History:
=============================================================================*/
include_once("../config.inc.php");
//echo('error');
//exit;
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
//组合要入库文件的路径
$path = WEBPATH_DIR."data/video_play/data".date("Ymd",THIS_DATETIME - 86400)."_".$tmp_aa.".dat";
//$path = WEBPATH_DIR."data/sdk_login/data20150318_".$tmp_aa.".dat";
//echo($path);
//exit;
//随机取内容
$tmp_arr = file($path);
if(is_array($tmp_arr)){
	$tmp_table_name = "kyx_video_play_log";//数据表名字
	$str_sql = "insert into ".$tmp_table_name."(`vp_in_date`, `vp_id`, `vp_cpu`, `vp_gpu`, `vp_source`,
			   `vp_locale`, `vp_density`, `vp_brand`, `vp_model`, `vp_mac`)values";
   
    $str_sql_2 = "";
    $i = 0;
    foreach($tmp_arr as $v){
        if(!empty($v)){
			$val = json_decode($v,true);
            $str_sql_2 .= "(";
            
            $tmp_sql_val = date("Ymd",THIS_DATETIME - 86400);//日期
            $tmp_sql_val .= ",".intval($val['appid'])."";//视频id
            $tmp_sql_val .= ",'".$val['cpu']."'";//播放的CPU
            $tmp_sql_val .= ",'".$val['gpu']."'";//播放的GPU
            $tmp_sql_val .= ",".intval($val['source'])."";//访问来源(1=>youku 2=>letv 3=>sohu 4=>qq 5=>tudou)
            $tmp_sql_val .= ",'".$val['locale']."'";//语言版本
            $tmp_sql_val .= ",'".$val['density']."'";//分辨率
            $tmp_sql_val .= ",'".$val['brand']."'";//品牌
            $tmp_sql_val .= ",'".$val['model']."'";//型号
            $tmp_sql_val .= ",'".$val['mac']."'";//MAX地址
            
            $str_sql_2 .= $tmp_sql_val."),";

            if($i!=500){//每500条数据插入一次
                $i++;
            }else{
                $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
                $conn->query($tmp_sql_3);
                $i = 0;
                $str_sql_2 = "";
            }
        }
    }

    if($str_sql_2!=""){
        $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
        $conn->query($tmp_sql_3);
    }
    unset($tmp_arr);
    echo('导入vide_play数据成功');
}else{
	echo('没有可导入的video_play数据');
}
