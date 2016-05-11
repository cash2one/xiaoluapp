<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取全部游戏的分类及该分类下的游戏个数,并加密JSON内容进行输出返回
 * @file: category.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
//packagename：包名，字符串类型
//sdkversion：SDK版本，整型
//channel：渠道名称，字符串类型
$mydata = array();

$mydata['packagename'] = get_param('packagename');//包名，字符串类型
$mydata['sdkversion'] = get_param('sdkversion');//SDK版本，整型
$mydata['channel'] = get_param('channel');//渠道名称，字符串类型
$mydata['adpid'] = intval(get_param('adpid'));//广告位ID
$mydata['adid'] = intval(get_param('adid'));//广告ID

$tmp_where = '';
if( !is_empty($mydata['adpid']) && $mydata['adpid']!=0){
	$tmp_where = ' AND A.adp_id='.$mydata['adpid'];
}else{
	$tmp_where = ' AND A.adp_id=4 ';//默认广告位
}
if( !is_empty($mydata['adid']) && $mydata['adid']!=0){
	$tmp_where .= ' AND A.ad_id='.$mydata['adid'];
}
//如果有传渠道过来，则调渠道对应的
if( !is_empty($mydata['channel'])){
	$tmp_where .= " AND A.ad_qudao='".$mydata['channel']."' ";
}else{//如果没有传渠道过来，则调不限渠道的
	$tmp_where .= " AND (A.ad_qudao is Null OR A.ad_qudao='0' OR A.ad_qudao='') ";
}

//adp_id=1 表示客户端首页广告
$sql = "SELECT A.ad_id,A.ad_title,A.ad_img_key,A.ad_a_href,A.ad_des,B.img_path FROM `mzw_ad` A 
		LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key 
		WHERE A.ad_status=1 ".$tmp_where."  AND (B.size_id=0 OR B.size_id is NULL) ";
$data = $conn->find($sql);
$returnArr = array('rows'=>array());
if($data){
	foreach($data as $row){
		$json = array(
				'title'=>$row['ad_title'],//广告名称
				'img'=>LOCAL_URL_DOWN_IMG.$row['img_path'],
				'desc'=>$row['ad_des'],
				'action'=>$row['ad_a_href']
				
		);
		$returnArr['rows'][]=$json;
	}
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($sql);
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);
