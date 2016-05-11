<?php
/*=============================================================================
#     FileName: update_keyword_date.php
#         Desc: 定期统计关键词信息（统计表kyx_keyword_log里的数据,存到mzw_count_keyword表里
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-01-19 15:57:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

include_once("../db.save.config.inc.php");
//获取上一天的日期
$this_date = date('Ymd',THIS_DATETIME - 86400);
$sql = 'SELECT kd_keyword,kd_key_md5,count(kd_key_md5) as num,sum(if(`kd_is_ok`=1,1,0)) as is_ok,
		sum(if(`kd_is_cache`=1,1,0)) as is_cache,kd_source FROM kyx_keyword_log WHERE kd_in_date='.$this_date.' GROUP BY kd_key_md5,kd_source';
$data = $conn->find($sql);

include_once("../db.config.inc.php");
foreach ($data as $val){
	if(isset($val['kd_keyword']) && !is_empty($val['kd_keyword'])){
		//查下当天是否已经存在这个关键词
		$sql_select = "SELECT id FROM mzw_count_keyword WHERE ck_date=".$this_date." AND ck_key_md5='".$val['kd_key_md5']."' AND ck_source=".intval($val['kd_source']);
		//echo($sql_select);
		$tmp_r = $conn->get_one($sql_select);
		if($tmp_r==false){//如果没有找到，则添加
			$row = array(
			  "ck_date"=>$this_date,//'搜索日期',
			  "ck_key_md5"=>$val['kd_key_md5'],//'关键词MD5值',
			  "ck_keyword"=>$val['kd_keyword'],//'关键词',
			  "ck_cache_num"=>intval($val['is_cache']),//'缓存返回次数',
			  "ck_ok_num"=>intval($val['is_ok']),//'搜索成功次',
			  "ck_search_num"=>intval($val['num']),//'搜索次数',
			  "ck_update"=>THIS_DATETIME,//'更新时间',
              "ck_source"=>intval($val['kd_source']) //搜索来源（1：游戏搜索 2：广告搜索）
			);
			$conn->save('mzw_count_keyword', $row);
		}else{//如果有找到，则不管
			//echo("<br>关键词数据已经存在".$val['kd_keyword'].chr(10));
		}
	}else{
		echo("关键词数据为空");
	}
}
echo("成功更新关键词数据");