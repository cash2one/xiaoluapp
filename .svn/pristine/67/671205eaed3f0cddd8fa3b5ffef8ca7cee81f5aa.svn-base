<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取全部游戏的包名,并加密JSON内容进行输出返回
 * @file: games.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

$mydata['tid'] = intval(get_param('tid'));//筛选条件（1:只获取上架的手机端游戏 其他：获取所有游戏包，包括软件，TV端游戏，手机端游戏）
$mydata['key']=get_param('key'); //验证KEY
$request = $_SERVER['REQUEST_METHOD']; //请求方式

//key校验
$key_auth = kyx_authorize_key($mydata['key'],$request);
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

switch ($mydata['tid']) {
	case 1:
        $sql = 'SELECT `gv_package_name` FROM `mzw_game_version` WHERE `gv_m_status`=1 GROUP BY g_id';
	break;
	default:
        $sql = "SELECT gv_package_name FROM `mzw_game_version` GROUP BY g_id";
	break;
}
$data = $conn->find($sql);
$returnArr = array('rows'=>array());
if($data){
    foreach($data as $row){
        $json = array(
            'packagename'=>$row['gv_package_name']//包名
        );
        $returnArr['rows'][]=$json;
    }
}

exit(responseJson($returnArr,true));