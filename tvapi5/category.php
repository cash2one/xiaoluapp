<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取全部游戏的分类及该分类下的游戏个数,并加密JSON内容进行输出返回（废弃）
 * @file: category.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['kyxversion'] = intval(get_param('kyxversion'));//客户端版本
//设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount'));

//查分类的信息
$sql = "SELECT t_id as tid,t_name_cn as title,t_img_key as icom,t_p_id AS type 
		FROM mzw_game_type WHERE t_status = 1 ORDER BY t_order_num DESC";
$data = $conn->find($sql);
foreach ($data as $data_val){
	$tmp_type_arr[$data_val["tid"]] = 0;
}

//查最新游戏数据(-1)
$json_a = array(
		'id'=>-1,//分类ID
		'title'=>'最新游戏',//分类名字
		'icon'=>'', //分类手机图标
		'counts'=>0 //游戏个数
);
//查大型游戏标签数据(-2)
$json_b = array(
		'id'=>-2,//分类ID
		'title'=>'大型游戏',//分类名字
		'icon'=>'', //分类手机图标
		'counts'=>0 //游戏个数
);
//查模拟器游戏标签数据(-3)
$json_c = array(
		'id'=>-3,//分类ID
		'title'=>'模拟器',//分类名字
		'icon'=>'', //分类手机图标
		'counts'=>0 //游戏个数
);
//查多人游戏游戏标签数据(-5)
$json_d = array(
		'id'=>-5,//分类ID
		'title'=>'多人游戏',//分类名字
		'icon'=>'', //分类手机图标
		'counts'=>0 //游戏个数
);

$returnArr['rows'][]=$json_a; //最新游戏数据(-1)
//不显示大游戏（即不显示GPK的游戏）
if($mydata['insdcardunmount']==0){
	$returnArr['rows'][]=$json_b; //大型游戏标签数据(-2)
}
if($mydata['kyxversion']>100){
	$returnArr['rows'][]=$json_c; //模拟器游戏 标签数据(-3)
	$returnArr['rows'][]=$json_d; //多人游戏 标签数据(-5)
}
if($data){
	foreach($data as $row){
		$json = array(
				'id'=>intval($row['tid']),//分类ID
				'title'=>$row['title'],//分类名字
				'icon'=>LOCAL_URL_DOWN_IMG.$row['icom'], //分类手机图标
				'counts'=>intval($tmp_type_arr[$row['tid']]) //游戏个数
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



?>