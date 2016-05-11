<?PHP
/**
 * @copyright: @快游戏 2015
 * @description: 获取首页游戏广告的两个内容,并JSON内容进行输出返回
 * @file: home_page_game.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-01-13  12:08
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../config.open.api.php");
include_once("../../db.config.inc.php");
/*参数*/

$mydata = array();
$mydata['time'] = get_param('time');
$p_key = get_param('key');
$tmp_key = open_key_kyx($mydata,$GLOBALS['SYS_OPEN_API_KEY']['viaplay']);

if($p_key!=$tmp_key){
	exit('KEY ERROR');
}


$sql = "SELECT A.ad_title,A.ad_a_href,B.img_path FROM mzw_ad A LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key WHERE A.adp_id = 26 AND B.size_id=0 ORDER BY A.ad_dis_order DESC,A.ad_id DESC LIMIT 2";
$data = $conn->find($sql);
$returnArr = array();
if($data){
	foreach($data as $row){
		$json = array(
				'name'=>$row['ad_title'],//游戏名称
				'image'=>LOCAL_URL_DOWN_IMG.$row['img_path'],//图片地址
				'address'=>$row['ad_a_href'] //链接地址
		);
		$returnArr[]=$json;
	}
}
$str_encode = responseJson($returnArr,false);
exit($str_encode);