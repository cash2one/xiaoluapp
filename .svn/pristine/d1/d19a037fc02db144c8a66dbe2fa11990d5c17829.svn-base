<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 接收后台传过来的缓存更新请求，生成最新的缓存
 * @file: search.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-12-23  11:25
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once(WEBPATH_DIR."include/search.class.php");//搜索操作
include_once(WEBPATH_DIR."include/search.model.php");//搜索数据操作

//本页可操作的内容
$my_act = array('retrieve');

$my_a = get_param('a');//要执行的内容

$act_search = new search();
//如果存在这个执行类型，则执行
if(in_array($my_a, $my_act)){
	$act_search->$my_a();
}else{
	echo('操作错误！');
}
