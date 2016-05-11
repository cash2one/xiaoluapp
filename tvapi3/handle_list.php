<?php
/**
 * @copyright: @快游戏 2014
 * @description: 返回手柄列表
 * @file: handle_list.php
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
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['gpu']=get_param('gpu');//GPU型号，字符串（在用）
$mydata['order']=trim(get_param('order'));//order排序方法，字符串（在用）:use 按使人数排,game按游戏数量排
//设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount'));

//验证key是否正确
verify_key_kyx($mydata['key']);


if($mydata['pagenum']==0){
	$mydata['pagenum'] =1;
}
if($mydata['pagesize']==0){
	$mydata['pagesize'] = 12;
}

//===========begin 查游戏数量
$where_game_num = ' ';
//===========begin适配GPU
//查找适配的GPU
$tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
$tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');
$tmp_find_gpu_id = " ( FIND_IN_SET(0,mgd_gpu_id)>0 ";
foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
	$tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",mgd_gpu_id)>0 ";
}
$tmp_find_gpu_id .= " ) ";
//查文件大小及游戏是APK还是GPK
$tmp_sql_gpu_in = 'SELECT DISTINCT gv_id FROM mzw_game_downlist
			       WHERE mgd_client_type != 2 AND mgd_package_type!=2 AND '.$tmp_find_gpu_id;
//=============end 适配ＧＰＵ
//不显示大游戏（即不显示GPK的游戏）
if($mydata['insdcardunmount']==1){
	$where_game_num .= ' AND FIND_IN_SET(23,gv.g_tags_property)<1 ';
}

//==========end 查游戏数量

$orderby = '';
if($mydata['order']=='use'){//使用人数排
	$orderby = ' ORDER BY gh_order DESC,gh_use_num DESC,gh_game_num DESC,id DESC ';
}else{//游戏数
	$orderby = ' ORDER BY gh_order DESC,gh_game_num DESC,gh_use_num DESC,id DESC';
}

//查数据条数
$sql_count = 'SELECT count(1) as num FROM `mzw_game_handle` WHERE gh_state=1';
$data_count = $conn->count($sql_count);

$PageMax=ceil($data_count/$mydata['pagesize']);//最大页数
$mydata['pagenum'] > $PageMax && $mydata['pagenum']=$PageMax;//当前页
$mydata['pagenum']==0 && $mydata['pagenum']=1;
$ParamPage=($mydata['pagenum']-1)*($mydata['pagesize']);//查询从第几行开始查

//初始化要返回的数据
$returnArr=array('total'=>$data_count,'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'error'=>NULL);

//查数据
$sql_data = "SELECT id,gh_title,gh_factory,gh_pic,gh_brief,gh_brief_en,
		gh_use_num,gh_price,mb_id,gh_handle,gh_sys,gh_prop,gh_pid,gh_vid,gh_mid,
		gh_speed_score,gh_key_score,gh_adap_score,gh_game_num,gh_pattern FROM `mzw_game_handle` 
		WHERE gh_state=1 $orderby LIMIT $ParamPage,".$mydata['pagesize'];
$data = $conn->find($sql_data);

//查手柄所有品牌的名称
$tmp_sql = 'SELECT mb_id,mb_name as name FROM mzw_mobile_brand WHERE mb_type=1';
$tmp_type = $conn->find($tmp_sql,'mb_id');

foreach ($data as $key =>$v){
	
	//查找游戏的数量
	$tmp_handle = explode(',', $v['gh_pattern']);
	//如果有数据
	$where = ' WHERE gv.gv_status=1 ';
	$where_pattern = '';
	if(count($tmp_handle)>0){
		if(is_array($tmp_handle)){
			foreach ($tmp_handle as $tmp_handle_v){
				if( $tmp_handle_v > 0 ){
					$where_pattern .= " AND FIND_IN_SET(".$tmp_handle_v.",gv.gv_mb_pattern)>0 ";
				}
			}
		}else if($tmp_handle>0){
			$where_pattern .= " AND FIND_IN_SET(".$tmp_handle.",gv.gv_mb_pattern)>0 ";
		}
	}
	if(!is_empty($where_pattern)){
		$where .= $where_pattern .' OR ';
	}else{
		$where .= ' AND ';
	}
	//支持自定义按键
	$where .= ' (FIND_IN_SET(5,gv.gv_app_prop)>0 ';
	//去掉模拟器游戏
	$where .= " AND (gv.gv_nes_property='' OR gv.gv_nes_property is null OR FIND_IN_SET(1,gv.gv_nes_property)<1) ";
	$where .= ' AND gv.gv_status=1) ';
	
	$str_select2 = "SELECT count(DISTINCT gv_id) as num FROM `mzw_game_version` gv ".$where.$where_game_num." AND gv.gv_id IN($tmp_sql_gpu_in) AND (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0)";
	$rs_select2 = $conn->get_one($str_select2);
	if($rs_select2 && isset($rs_select2['num'])){
		$v['gh_game_num'] = intval($rs_select2['num']);
	}

	$arr = array(
		'id'			=>$v['id'],//手柄ID
		'gh_title'		=>$v['gh_title'],//手柄名称
		'gh_factory'	=>$v['gh_factory'],//厂家名称
		'gh_pic'		=>LOCAL_URL_DOWN_IMG.$v['gh_pic'],//手柄图片
		'gh_brief'		=>$v['gh_brief'],//手柄简介
		'gh_brief_en'	=>$v['gh_brief_en'],//手柄英文简介
		'gh_use_num'	=>$v['gh_use_num'],//使用人数
		'gh_price'		=>$v['gh_price'],//价格
		'mb_id'			=>$v['mb_id'],//品牌ID
		'mb_name'		=>isset($tmp_type[$v['mb_id']])?$tmp_type[$v['mb_id']]:"",//品牌名称,
		'gh_handle'		=>$v['gh_handle'],//手柄连接方式
		'gh_sys'		=>$v['gh_sys'],//支持平台(1、windows, 2、xbox, 3、ps3/4, 4、android, 5、iphone）
		'gh_prop'		=>$v['gh_prop'],//其他属性(1、震动,2、音频,3、六轴)
		'gh_speed_score'=>$v['gh_speed_score'],//连接速度评分
		'gh_key_score'	=>$v['gh_key_score'],//按键手感评分
		'gh_adap_score'	=>$v['gh_adap_score'],//'游戏适配评分
		'gh_game_num'	=>$v['gh_game_num']//游戏数量
				
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
