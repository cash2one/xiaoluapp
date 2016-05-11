<?php
/**
 * @copyright: @快游戏 2014
 * @description: 作者解说列表页，返回请求作者解说列表
 * @file: video_author_category_list.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-05-20  12:08
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
$mydata['typeid'] = intval(get_param('typeid'));//视频类型
if($mydata['typeid']==0 || is_empty($mydata['typeid'])){
	echo('error! typeid is empty!!');
	exit;
}

$mydata['gameid'] = intval(get_param('gameid'));//游戏ID
if($mydata['gameid']<1){
	$mydata['gameid'] = 1;
}

$mydata['categoryid'] = intval(get_param('categoryid'));//视频小类型

$where = ' ';
$where .= ' AND vc_type_id='.$mydata['typeid'];

if($mydata['typeid']==6){//如果是作者解说
	//查找解说人(即作者)的信息
	$tmp_sql = 'SELECT id,va_name FROM video_author_info WHERE va_isshow=1 AND va_game_id='.$mydata['gameid'].' ORDER BY va_order DESC,id DESC';
	$tmp_type = $conn->find($tmp_sql);
	foreach ($tmp_type as $val){
		$arr = array(
				'appid'=>$val['id'],//小分类ID
				'name'=>$val['va_name']//小分类名称
		);
		$tmp_categorys[]=$arr;
		if($mydata['categoryid']==1 && $mydata['gameid']!=1 && $val['id']!=1){
			$mydata['categoryid'] = $val['id'];
		}
	}
}
$where .= ' AND vc_author_id='.$mydata['categoryid'];
$orderby = ' ORDER BY vc_order DESC,id DESC ';

//查总数据行数
$sql = "SELECT count(1) as num FROM `video_category_info`  WHERE vc_isshow=1 AND vc_game_id=".$mydata['gameid']." ".$where ;
$data_count = $conn->count($sql);

$PageMax=ceil($data_count/$mydata['pagesize']);//最大页数
$mydata['pagenum'] > $PageMax && $mydata['pagenum']=$PageMax;//当前页
$mydata['pagenum']==0 && $mydata['pagenum']=1;
$ParamPage=($mydata['pagenum']-1)*($mydata['pagesize']);//查询从第几行开始查

//初始化要返回的数据
$returnArr=array('total'=>$data_count,'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'categorys'=>$tmp_categorys,'error'=>NULL);
//查数据
$sql_data = "SELECT id,vc_name,vc_icon,vc_icon_get,vc_type_id,vc_game_id,in_date ".
			 " FROM `video_category_info` WHERE  vc_isshow=1 AND vc_game_id=".$mydata['gameid']." ". $where.$orderby." LIMIT ".$ParamPage .",".$mydata['pagesize'];

$data = $conn->find($sql_data);
//列出视频数据
foreach ($data as $val){
	
	if(!is_empty($val['vc_icon_get'])){
		$tmp_img_url = LOCAL_URL_DOWN_IMG.$val['vc_icon_get'];
	}else{
		$tmp_img_url = $val['vc_icon'];
	}
	$tmp_category_id = $val['id'];

	$arr = array(
		'appid'=>$val['id'],//专辑ID
		'typeid'=>$val['vc_type_id'],//视频类型（1任务，2解说，3赛事战况，4集锦，5职业,6作者解说）
		'categoryid'=>$tmp_category_id,//视频联赛ID(来自video_category_info表)'
		'title'=>$val['vc_name'],//视频标题
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

