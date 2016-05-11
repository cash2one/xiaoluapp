<?php
/**
 * @copyright: @快游戏 2015
 * @description: 获取不同CPU平台的PSP模拟器插件
 * @file: getemuplugin.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-03-11  21:28
 * @version 1.0
 **/
include_once("../config.inc.php");
/*参数*/
$mydata = array();
$mydata['cpu']=get_param('cpu');//CPU参数

if(is_empty($mydata['cpu'])){
	exit('参数出错');
}
$go_to_url = '';
switch ($mydata['cpu']){
	case 'armeabi':
		$go_to_url = 'http://letv.cdn.gugeanzhuangqi.com/game/2015/03/13/psp_a_313.dat';
		break;
	case 'armeabi-v7a':
		$go_to_url = 'http://letv.cdn.gugeanzhuangqi.com/game/2015/03/13/psp_a_313.dat';
		break;
	case 'x86':
		$go_to_url = 'http://letv.cdn.gugeanzhuangqi.com/game/2015/03/13/psp_x_313.dat';
		break;
	default:
		exit('参数出错');
		break;
}
header("Location: $go_to_url");
exit;

