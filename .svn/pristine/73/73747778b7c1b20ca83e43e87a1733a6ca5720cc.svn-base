<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频项目 专辑视频列表,并JSON内容进行输出返回
 * @file: video_album_videolist.php
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

$mydata['appid'] = intval(get_param('appid'));//专辑ID


$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

$offset = ($mydata['pagenum']-1)*$mydata['pagesize'];

$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";
if($mydata['appid']<1){//如果专辑ID小于1则出错
	echo("appid error!!");
	exit;
}

$tmp_where = ' ';
$tmp_order_by = 'A.vvl_sort DESC,A.id DESC ';

//查数据条数
$sql_count = "SELECT count(*) as num "
		. "FROM video_video_list A LEFT JOIN video_area_video_info B ON A.id = B.vvl_id WHERE A.va_isshow=1 AND B.va_id =".$mydata['appid'].$tmp_where;
$data_count = $conn->find($sql_count);

//定义回转的默认参数
$returnArr = array('total'=>$data_count[0]['num'],'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'error'=>NULL,'update'=>time());

//查专区的信息
$sql = "SELECT A.vvl_title,A.id,A.vvl_type_id,A.vvl_category_id,A.vvl_imgurl_get,A.vvl_imgurl,A.in_date "
		. "FROM video_video_list A LEFT JOIN video_area_video_info B ON A.id = B.vvl_id WHERE A.va_isshow=1 AND B.va_id =".$mydata['appid'].$tmp_where." ORDER BY $tmp_order_by $limit";
$data = $conn->find($sql);
if($data && count($data)>0){
foreach ($data as $val){
	
	//查找这个游戏对应的相关图片
	if(!is_empty($val['vvl_imgurl_get'])){
		$tmp_img_url = LOCAL_URL_DOWN_IMG.$val['vvl_imgurl_get'];
	}else{
		$tmp_img_url = $val['vvl_imgurl'];
	}
	
	//按视频类型判断视频小分类的ID
	//视频类型（1任务，2解说，3赛事战况，4集锦）
	if($val['vvl_type_id']==1){//1任务
		//$tmp_category_id = $val['hi_class'];
		$tmp_category_id = $val['vvl_category_id'];
	}elseif($val['vvl_type_id']==2){//2解说
		$tmp_category_id = $val['vvl_author_id'];
	}else{
		$tmp_category_id = $val['vvl_category_id'];
	}
	
	$arr = array(
		'appid'=>$val['id'],//视频ID
		'typeid'=>$val['vvl_type_id'],//视频类型（1任务，2解说，3赛事战况，4集锦）
		'categoryid'=>$tmp_category_id,//视频联赛ID(来自video_category_info表)'
		'title'=>$val['vvl_title'],//视频标题
		'imgurl'=>$tmp_img_url,//视频图片
		'time'=>$val['in_date']//采集时间
	);
	$returnArr['rows'][]=$arr;
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