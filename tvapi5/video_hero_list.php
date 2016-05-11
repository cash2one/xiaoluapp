<?php
/**
 * @copyright: @快游戏 2014
 * @description: 英雄列表页，返回请求英雄列表
 * @file: video_hero_list.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-04-30  13:40
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
if($mydata['typeid']!=1){//如果不是人物，则出错
	echo('error! typeid is error!!');
	exit;
}

$mydata['gameid'] = intval(get_param('gameid'));//游戏ID
if($mydata['gameid']<1){//如果没有传游戏ID过来，则默认为1
	$mydata['gameid'] = 1;
}

$mydata['categoryid'] = intval(get_param('categoryid'));//视频小类型
if($mydata['categoryid']<1 || $mydata['categoryid']>10){
	$mydata['categoryid'] = 1;
}

$where = ' ';//查询条件
//限限只显示当前游戏的英雄
$where .=' AND hi_game_id='.$mydata['gameid'];

$tmp_type = array(
		1 => '近战',2 => '远程',3 => '物理',
		4 => '法术',5 => '坦克',6 => '辅助',
		7 => '打野',8 => '突进',9 => '男性',10 => '女性'
);
foreach ($tmp_type as $key=>$val){
	$tmp_type_return = false;
	if($mydata['categoryid']==$key){
		$tmp_type_return = true;
		//$where .=' AND hi_class='.$mydata['categoryid'];
		$where .=' AND FIND_IN_SET('.$mydata['categoryid'].',hi_class)>0 ';
	}
	$arr = array(
			'appid'=>$key,//小分类ID
			'name'=>$val,//小分类名称
			'type'=>$tmp_type_return//是否选择
	);
	$tmp_categorys[]=$arr;
}

$orderby = ' ORDER BY hi_order DESC,id DESC ';

//查总数据行数
$sql = "SELECT count(1) as num FROM `video_hero_info`  WHERE hi_isshow=1 ".$where ;
$data_count = $conn->count($sql);

$PageMax=ceil($data_count/$mydata['pagesize']);//最大页数
$mydata['pagenum'] > $PageMax && $mydata['pagenum']=$PageMax;//当前页
$mydata['pagenum']==0 && $mydata['pagenum']=1;
$ParamPage=($mydata['pagenum']-1)*($mydata['pagesize']);//查询从第几行开始查

//初始化要返回的数据
$returnArr=array('total'=>$data_count,'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'categorys'=>$tmp_categorys,'error'=>NULL);
//查数据
$sql_data = "SELECT id,hi_name_cn,hi_name,hi_icon_get,hi_icon,hi_class,hi_searchtext ".
			 " FROM `video_hero_info` WHERE  hi_isshow=1 ". $where.$orderby." LIMIT ".$ParamPage .",".$mydata['pagesize'];

$data = $conn->find($sql_data);
//列出视频数据
foreach ($data as $val){
	
	if(!is_empty($val['hi_icon_get'])){
		$tmp_img_url = LOCAL_URL_DOWN_IMG.$val['hi_icon_get'];
	}else{
		$tmp_img_url = $val['hi_icon'];
	}
	$arr = array(
		'appid'=>$val['id'],//英雄ID
		'typeid'=>1,//视频类型（1任务，2解说，3赛事战况，4集锦）
		'categoryid'=>$val['hi_class'],//英雄职责ID(来自video_category_info表)'
		'title'=>$val['hi_name_cn'],//英雄名称
		'title_en'=>$val['hi_name'],//英雄英文名称
		'searchtext'=>str_replace($val['hi_name_cn'],'',$val['hi_searchtext']),
		'imgurl'=>$tmp_img_url//视频图片
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

