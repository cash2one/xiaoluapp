<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取 51快装软件 专辑游戏列表,并加密JSON内容进行输出返回
 * @file: 51kuaiapp_list.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-04-14  16:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['key'] = get_param('key');//验证KEY
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum'])?1:intval($mydata['pagenum']);
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize'])?64:intval($mydata['pagesize']);

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

$mydata['sysversion'] = intval(get_param('sysversion'));//安卓系统版本下限

$offset = ($mydata['pagenum']-1)*$mydata['pagesize'];

//验证key是否正确
verify_key_kyx($mydata['key']);

$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

$tmp_ga_id = intval(get_param('albumid'));//专区ID先，后面再按其它条件来查数据
if($tmp_ga_id<1){
	$tmp_ga_id = 16;//定死某个（51快装软件）专区ID先，后面再按其它条件来查数据
}


$tmp_where = ' AND gv_android <= '.$mydata['sysversion'].' ';

$tmp_order_by = ' A.gv_must_soft_order DESC,A.gv_id DESC ';

//查数据条数
$sql_count = "SELECT count(*) as num "
		. "FROM mzw_game_version A LEFT JOIN mzw_game_m_a_relation B ON A.g_id = B.g_id AND (A.`gv_id` = B.`gv_id` OR B.`gv_id` =0) WHERE B.ga_id =".$tmp_ga_id.$tmp_where;
$data_count = $conn->find($sql_count);

//定义回转的默认参数
$returnArr = array('total'=>$data_count[0]['num'],'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'error'=>NULL,'update'=>time());
//查专区的信息

$sql = "SELECT A.g_id as gid,A.gv_id as appid,A.gv_type_id as tid,A.gv_title as title,A.gv_version_name as version,A.gv_update_time as updatetime,
		A.gv_publish_time as published,A.gv_package_name as packagename,A.gv_ico_key as icon,A.gv_down_nums as downloadscount,A.gv_version_no as versioncode "
		. "FROM mzw_game_version A LEFT JOIN mzw_game_m_a_relation B ON A.g_id = B.g_id AND (A.`gv_id` = B.`gv_id` OR B.`gv_id` =0) WHERE B.ga_id =".$tmp_ga_id.$tmp_where." ORDER BY $tmp_order_by $limit";
$data = $conn->find($sql);
if($data && count($data)>0){
foreach ($data as $val){
	//查文件大小
	$tmp_sql = 'SELECT mgd_id,mgd_mzw_server_url,mgd_package_type as type,mgd_game_size FROM mzw_game_downlist
			WHERE gv_id='.$val["appid"]." AND mgd_package_type!=2 ORDER BY mgd_package_type DESC,mgd_id DESC";
	$tmp_downlist = $conn->find($tmp_sql);
	$downloadPaths = array();
	if($tmp_downlist){

		$size = $tmp_downlist[0]['mgd_game_size'];
		$down_apk_gpk = CDN_LESHI_URL_DOWN.$tmp_downlist[0]['mgd_mzw_server_url'];
		//组合乐视CDN 相关下载地址
		$downloadPaths[] = array(
				'id' => -3,
				'name' => '普通下载',
				'icon' => CDN_LESHI_URL_DOWN.'/app420/cdn.png',
				'url' => $down_apk_gpk,
				'backup' =>'',
				'visible' =>1 ,
				'parse' =>false,
				'files' =>array()
		);
	}else{
		$down_apk_gpk = '';//下载地址
		$size = 0;//文件大小
	}
	
	
	$gv_game_ico = '';//ICO地址
	$tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$val["icon"]."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
	$tmp_game_ico_arr = $conn->find($tmp_sql);
	if($tmp_game_ico_arr){
		$tmp_game_ico = LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr[0]["img_path"];
	}
	
	$online = true;
	$json = array(
			'appid'=>intval($val["appid"]),//游戏版本的ID
			'title'=>$val["title"],//游戏中文标题
			'size'=>intval($size),//文件大小
			'packagename'=>$val["packagename"],//游戏包名
			'version'=>$val["version"],//游戏版本名
			'versioncode'=>intval($val["versioncode"]),//游戏版本号
			'downloadscount'=>intval($val["downloadscount"]),//游戏下载次数
			'iconpath'=>$tmp_game_ico,//游戏的ICO文件
			'downloadPaths'=>$downloadPaths//下载地址
	);
	$returnArr['rows'][]=$json;
}
}
if($is_bug_show==100){
	echo($sql);
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);

?>