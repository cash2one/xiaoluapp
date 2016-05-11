<?php
/**
 * @copyright: @快游戏 2014
* @description: 获取型号信息,并加密JSON内容进行输出返回
* @file: game_model_brand.php
* @author: chengdongcai
* @charset: UTF-8
* @time: 2014-11-14  13:38
* @version 1.0
**/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

//model:  型号（字符串）
//brand：品牌（字符串）
$mydata = array();
$mydata['model']=get_param('model');//型号（字符串）
$mydata['brand']=get_param('brand');//品牌（字符串）
if(is_empty($mydata["model"]) || is_empty($mydata["brand"])){
	echo("参数不正确");
	exit;
}
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
//
$sql = "SELECT A.model_id,A.model_image_path FROM mzw_mobile_model A left join mzw_mobile_brand B ON A.mb_id=B.mb_id 
		WHERE INSTR('".$mydata['model']."',A.model_params)>0  AND INSTR('".$mydata['brand']."',B.mb_params)>0";

$data = $conn->find($sql);
$returnArr = array('rows'=>array());

if($data){
	foreach($data as $row){
		$sql_tmp = "SELECT ga_id,ga_image_key,ga_name FROM mzw_game_mobile_area WHERE mobile_model_id=".intval($row["model_id"]);
		$data_tmp = $conn->find($sql_tmp);
		if($data_tmp){//如果有找到。
			foreach ($data_tmp as $row_tmp){
				$tmp_sql = "SELECT img_path FROM mzw_img_path WHERE img_key = '".$row_tmp["ga_image_key"]."' AND status = 1 AND size_id=0 ORDER BY id DESC";
				$tmp_game_ico_arr = $conn->find($tmp_sql);
				if($tmp_game_ico_arr){
					$tmp_game_ico = LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr[0]["img_path"];
				}
				$json = array(
						'icon'=>$tmp_game_ico,//专区图片
						'cnname'=>$row_tmp['ga_name'],//专区中文名
						'id'=>$row_tmp['ga_id']//专区ID
				);
			}
		}else{//如果没有找到
			$json = array(
					'icon'=>LOCAL_URL_DOWN_IMG.DS."do_not_delete/non_mobile_area.jpg",//专区图片
					'cnname'=>"默认专区",//专区中文名
					'id'=>-1//手机型号的ID
			);
		}
		$returnArr['rows'][]=$json;
	}
}else{
	$json = array(
			'icon'=>LOCAL_URL_DOWN_IMG.DS."do_not_delete/non_mobile_area.jpg",//专区图片
			'cnname'=>"必备专区",//专区中文名
			'id'=>-1//手机型号的ID
	);
	$returnArr['rows'][]=$json;
}
if($is_bug_show==100){
	var_dump($returnArr);
	exit;
}
$str_encode = responseJson($returnArr,true);
exit($str_encode);
