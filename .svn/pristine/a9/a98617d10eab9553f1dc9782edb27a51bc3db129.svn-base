<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频项目 首页专辑视频列表,并JSON内容进行输出返回
 * @file: video_index_album.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-05-04  10:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['pagenum'] = get_param('pagenum');//当前页
$mydata['pagenum'] = empty($mydata['pagenum'])?1:$mydata['pagenum'];
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize'])?32:$mydata['pagesize'];

$mydata['appid'] = intval(get_param('appid'));//广告类型ID(视频项目的广告类型ID为：31)


$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

$offset = ($mydata['pagenum']-1)*$mydata['pagesize'];

$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";
if($mydata['appid']<1){//如果专辑ID小于1则出错
	echo("appid error!!");
	exit;
}

//查数据条数
$sql_count = "SELECT count(*) as num "
		. "FROM mzw_ad  WHERE ad_status=1 AND adp_id =".$mydata['appid'];
$data_count = $conn->find($sql_count);

//定义回转的默认参数
$returnArr = array('total'=>$data_count[0]['num'],'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'error'=>NULL,'update'=>time());

$orderby = ' ORDER BY A.ad_dis_order DESC,A.ad_id DESC ';

//查数据列表
$sql_data = "SELECT A.ad_id,A.ad_title,A.ad_img_key,A.ad_a_href,B.img_path FROM `mzw_ad` A 
		LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key 
		WHERE A.ad_status=1 AND B.size_id=0 AND A.adp_id =".$mydata['appid'].$orderby;

$data = $conn->find($sql_data);
if($data && count($data)>0){
	foreach ($data as $val){
		
		$arr = array(
				'appid'=>$val['ad_id'],//广告ID
				'typeid'=>$mydata['appid'],//广告类型ID
				'title'=>$val['ad_title'],//广告名称
				'appurl'=>$val['ad_a_href'],//广告跳转地址
				'imgurl'=>LOCAL_URL_DOWN_IMG.$val['img_path']//广告图片图片
		);
		$returnArr['rows'][]=$arr;
	}
}
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($sql_data);
	var_dump($returnArr);
	exit;
}
$str_encode = responseJson($returnArr,false);
exit($str_encode);





