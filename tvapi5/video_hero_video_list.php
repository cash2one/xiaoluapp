<?php
/**
 * @copyright: @快游戏 2014
 * @description: 视频列表页，返回请求英雄ID对应的视频列表
 * @file: video_hero_video_list.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-04-29  17:12
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
if($mydata['pagenum']==0){
	$mydata['pagenum'] =1;
}
if($mydata['pagesize']==0){
	$mydata['pagesize'] = 12;
}
$mydata['appid'] = intval(get_param('appid'));//英雄ID
if($mydata['appid']<1 || is_empty($mydata['appid'])){//如果英雄ID为空，则出错
	echo('error! appid is empty!!');
	exit;
}else{//查找英雄信息
	$tmp_sql_data = "SELECT id,hi_name_cn,hi_name,hi_icon_get,hi_icon,hi_class,hi_intro,hi_icon_get,hi_icon,hi_searchtext ".
			" FROM `video_hero_info` WHERE  hi_isshow=1 AND id=".$mydata['appid'];
	$tmp_hero = $conn->find($tmp_sql_data);
	if($tmp_hero>0){
		$tmp_hero_info = $tmp_hero[0];
		
		if(!is_empty($tmp_hero_info['hi_icon_get'])){
			$tmp_img_url = LOCAL_URL_DOWN_IMG.$tmp_hero_info['hi_icon_get'];
		}else{
			$tmp_img_url = $tmp_hero_info['hi_icon'];
		}
		
		$tmp_hero_arr = array(
			'appid'=>$tmp_hero_info['id'],
				'name'=>$tmp_hero_info['hi_name_cn'],
				'name_en'=>$tmp_hero_info['hi_name'],
				'searchtext'=>str_replace($tmp_hero_info['hi_name_cn'],'',$tmp_hero_info['hi_searchtext']),
				'imgurl'=>$tmp_img_url,
				'intro'=>$tmp_hero_info['hi_intro']
		);
	}else{
		$tmp_hero_arr = array();
	}
}


$where = ' ';
$where .= ' AND vvl_hi_id='.$mydata['appid'];

$orderby = ' ORDER BY vvl_sort DESC,vvl_sort_sys DESC,id ASC ';

//查总数据行数
$sql = "SELECT count(1) as num FROM `video_video_list`  WHERE va_isshow=1 ".$where ;
$data_count = $conn->count($sql);

$PageMax=ceil($data_count/$mydata['pagesize']);//最大页数
$mydata['pagenum'] > $PageMax && $mydata['pagenum']=$PageMax;//当前页
$mydata['pagenum']==0 && $mydata['pagenum']=1;
$ParamPage=($mydata['pagenum']-1)*($mydata['pagesize']);//查询从第几行开始查

//初始化要返回的数据
$returnArr=array('total'=>$data_count,'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'hero_info'=>$tmp_hero_arr,'error'=>NULL);
//查数据
$sql_data = "SELECT id,vvl_title,vvl_imgurl,vvl_imgurl_get,vvl_category_id,vvl_type_id,in_date ".
			 " FROM `video_video_list` WHERE  va_isshow=1 ". $where.$orderby." LIMIT ".$ParamPage .",".$mydata['pagesize'];

$data = $conn->find($sql_data);
//列出视频数据
foreach ($data as $val){
	
	if(!is_empty($val['vvl_imgurl_get'])){
		$tmp_img_url = LOCAL_URL_DOWN_IMG.$val['vvl_imgurl_get'];
	}else{
		$tmp_img_url = $val['vvl_imgurl'];
	}
	$arr = array(
		'appid'=>$val['id'],//视频ID
		'typeid'=>$val['vvl_type_id'],//视频类型（1任务，2解说，3赛事战况，4集锦）
		'categoryid'=>$val['vvl_category_id'],//视频联赛ID(来自video_category_info表)'
		'title'=>$val['vvl_title'],//视频标题
		'imgurl'=>$tmp_img_url,//视频图片
		'time'=>$val['in_date']//采集时间
	);
	$returnArr['rows'][]=$arr;
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($sql_data);
	var_dump($returnArr);
	exit;
}
$str_encode = responseJson($returnArr,false);
exit($str_encode);

