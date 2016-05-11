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
include_once("../../config.inc.php");
include_once("../../db.config.inc.php");
/*参数*/

$sql = "SELECT gv_package_name FROM `mzw_game_version` GROUP BY g_id";
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

$str_encode = responseJson($returnArr,true);
exit($str_encode);
?>