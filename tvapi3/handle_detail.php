<?php
/**
 * @copyright: @快游戏 2014
 * @description: 返回手柄详细信息
 * @file: handle_detail.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-01-27  20:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();

$mydata['key'] = get_param('key');
$mydata['pid'] = trim(get_param('pid'));//产品ID
$mydata['vid'] = trim(get_param('vid'));//厂商ID
$mydata['mid'] = trim(get_param('mid'));//设备描述
$mydata['cname'] = trim(get_param('cname'));//设备名称
//验证key是否正确
verify_key_kyx($mydata['key']);

//查数据
$sql_data = "SELECT id,gh_title,gh_factory,gh_pic,gh_brief,gh_brief_en,
gh_use_num,gh_price,mb_id,gh_handle,gh_sys,gh_prop,gh_pid,gh_vid,gh_mid,
gh_speed_score,gh_key_score,gh_adap_score FROM `mzw_game_handle`
WHERE gh_state=1 AND INSTR(gh_pid,'".$mydata['pid']."')>0 AND INSTR(gh_vid,'".$mydata['vid']
."')>0 AND INSTR(gh_mid,'".$mydata['mid']."')>0 AND INSTR(gh_device_name,'".$mydata['cname']."')>0";

$data = $conn->get_one($sql_data);
//初始化要返回的数据
$returnArr=array('total'=>1,'pagecount'=>1,'pagenum'=>1,'rows'=>array(),'error'=>NULL);
//如果有查到手柄数据，则
if($data){
	if(isset($data['mb_id']) && $data['mb_id']>0){
		//查手柄所有品牌的名称
		$tmp_sql = 'SELECT mb_id,mb_name as name FROM mzw_mobile_brand WHERE mb_type=1 AND mb_id='.intval($data['mb_id']);
		$tmp_type = $conn->find($tmp_sql,'mb_id');
		if(!isset($tmp_type)|| !isset($tmp_type['mb_name']) || $tmp_type['mb_name']==''){
			$tmp_type['mb_name']='';
		}
	}else{
		$tmp_type['mb_name']='';
	}
	$arr = array(
			'id'			=>$data['id'],//手柄ID
			'gh_title'		=>$mydata['cname']=='Microsoft X-Box 360 pad'?"XBOX手柄":$data['gh_title'],//手柄名称(Microsoft X-Box 360 pad手柄特别处理)
			'gh_factory'	=>$data['gh_factory'],//厂家名称
			'gh_pic'		=>$mydata['cname']=='Microsoft X-Box 360 pad'?"":LOCAL_URL_DOWN_IMG.$data['gh_pic'],//手柄图片
			'gh_brief'		=>$data['gh_brief'],//手柄简介
			'gh_brief_en'	=>$data['gh_brief_en'],//手柄英文简介
			'gh_use_num'	=>$data['gh_use_num'],//使用人数
			'gh_price'		=>$data['gh_price'],//价格
			'mb_id'			=>$data['mb_id'],//品牌ID
			'mb_name'		=>$tmp_type['mb_name'],//品牌名称
			'gh_handle'		=>$data['gh_handle'],//手柄连接方式
			'gh_sys'		=>$data['gh_sys'],//支持平台(1、windows,2、xbox,3、ps3/4,4、android,5、iphone）
			'gh_prop'		=>$data['gh_prop'],//其他属性(1、震动,2、音频,3、六轴)
			'gh_speed_score'=>$data['gh_speed_score'],//连接速度评分
			'gh_key_score'	=>$data['gh_key_score'],//按键手感评分
			'gh_adap_score'	=>$data['gh_adap_score'],//'游戏适配评分
			'pid'			=>$mydata['pid'],
			'vid'			=>$mydata['vid'],
			'mid'			=>$mydata['mid'],
			'cname'			=>$mydata['cname']
			
	);
	$returnArr['rows'][]=$arr;
}
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($sql_data);
	var_dump($returnArr);
	exit;
}
$str_encode = responseJson($returnArr,true);
exit($str_encode);
