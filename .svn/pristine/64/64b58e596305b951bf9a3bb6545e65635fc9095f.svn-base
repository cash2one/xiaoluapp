<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 接收后台传过来的缓存更新请求，生成最新的缓存
 * @file: game_update.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-12-05  11:25
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['gv_id']=get_param('gv_id');//游戏版本表的ID
$mydata['type_id']=get_param('type_id');//游戏分类ID
$mydata['sys_key']=get_param('key');//后台接口合法验证KEY
