<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取全部游戏的分类,并加密JSON内容进行输出返回
 * @file: game_category.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['key'] = get_param('key');
$mydata['pagenum']=intval(get_param('pagenum'));
$mydata['pagesize']=intval(get_param('pagesize'));

$sql = "SELECT t_id as tid,t_name_cn as title,t_img_key as icom,t_p_id AS type FROM mzw_game_type WHERE t_status = 1 AND (t_type = 1 OR t_type=2) ORDER BY t_order_num";
$data = $conn->find($sql);
$Appcount=count($data);

$returnArr = array('total'=>$Appcount,'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array());
if($data){
	foreach($data as $row){
		$json = array(
				'cateId'=>intval($row['tid']),
				'title'=>$row['title'],
				'icon'=>LOCAL_URL_DOWN_IMG.$row['icom'],
				'type'=>0 - intval($row['type'])
		);
		$returnArr['rows'][]=$json;
	}
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);
