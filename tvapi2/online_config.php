<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 检查接口的数据是否有更新,并加密JSON内容进行输出返回
 * @file: category.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
$mydata['kyxversion'] = get_param('kyxversion');//客户端版本
$mydata['model'] = get_param('model');//型号
$mydata['brand'] = get_param('brand');//品牌
$mydata['gpu'] = get_param('gpu');//GPU
$mydata['cpu'] = get_param('cpu');//CPU
$mydata['mac'] = get_param('mac');//MAC

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

$returnArr=array(
	'apidomain' =>array(
			  	array('id'=>1,'domain'=>'http://api.kuaiyouxi.com'),//数据接口域名
				array('id'=>2,'domain'=>'http://api2.kuaiyouxi.com'),//SDK接口域名
				array('id'=>3,'domain'=>'http://api3.kuaiyouxi.com')//数据统计域名
			),//API的域名地址
	'sdcardpath'=>array(
			'/storage/sda1',
			'/mnt/usb/sda1',
			'/storage/external_storage/sda1'
			),//SD卡的存放路径
	'cachetime'=>600,//缓存时间（秒）
	'isactivity'=>1,//是否活动期(1表示是活动期，0表示非活动期)
	'apiupdate'=>array()//接口更新信息
);
//首页的更新
$returnArr['apiupdate'][]=array(
	'name'=>'home_hotgame',
	'path'=>'/tvapi2/home_hotgame.php',
	'typeid'=>-100,//分类ID
	'typename'=>'null',//类型键值（每一类的typeid会有不同）
	'update'=>time()
);
//游戏分类的更新
$sql = "SELECT t_id as tid FROM mzw_game_type WHERE t_status = 1 ORDER BY t_order_num DESC";
$data = $conn->find($sql);
if($data){
	foreach ($data as $val){
		$returnArr['apiupdate'][]=array(
			'name'=>'game_list',
			'path'=>'/tvapi2/game_list.php',
			'typeid'=>$val['tid'],//分类ID
			'typename'=>'category',//类型键值（每一类的typeid会有不同）
			'update'=>time()
		);
	}
}
if($is_bug_show==100){
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);
