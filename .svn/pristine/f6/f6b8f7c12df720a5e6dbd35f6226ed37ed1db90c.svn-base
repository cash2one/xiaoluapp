<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取广告列表,并加密JSON内容进行输出返回
 * @file: game_ad.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-08-10  17:36
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['channel'] = get_param('channel');//渠道名称，字符串类型
$mydata['adpid'] = intval(get_param('adpid'));//广告位ID（不传默认快游戏手机端首页广告位 43）
$mydata['adpid'] = empty($mydata['adpid']) ? 43 : $mydata['adpid'];
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式

//key校验
$key_auth = kyx_authorize_key($mydata['key'],$request);
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//查询条件
$tmp_where = '';

//广告位ID
if( !empty($mydata['adpid']) && $mydata['adpid']!=0){
	$tmp_where = ' AND A.adp_id='.$mydata['adpid'];
}

//如果有传渠道过来，则调渠道对应的
if( !is_empty($mydata['channel'])){
	$tmp_where .= " AND A.ad_qudao='".$mydata['channel']."' ";
}else{//如果没有传渠道过来，则调不限渠道的
	$tmp_where .= " AND (A.ad_qudao is Null OR A.ad_qudao='0' OR A.ad_qudao='') ";
}

//查询广告位对应广告列表
$sql = "SELECT A.ad_id,A.ad_title,A.ad_img_key,A.ad_a_href,A.ad_des,B.img_path FROM `mzw_ad` A 
		LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key 
		WHERE A.ad_status=1 ".$tmp_where."  AND (B.size_id=0 OR B.size_id is NULL) ORDER BY ad_dis_order DESC";
$data = $conn->find($sql);

//返回信息初始化
$returnArr = array('rows'=>array());

//返回数组拼装
if(!empty($data)){
	foreach($data as $row){
		$json = array(
            'title'=>$row['ad_title'],//广告名称
            'img'=>empty($row['img_path']) ? '' : LOCAL_URL_DOWN_IMG.$row['img_path'], //广告图片
            'desc'=>$row['ad_des'], //广告描述
            'href'=>$row['ad_a_href'] //广告链接
		);
		$returnArr['rows'][]=$json;
	}
}

//是否显示数据调试
$is_bug_show = intval(get_param('bug_show'));
if($is_bug_show==100){
    exit(responseJson($returnArr,false));
}
exit(responseJson($returnArr,true));
