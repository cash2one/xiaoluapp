#! /usr/local/php/bin/php -q
<?php
/*=============================================================================
#     FileName: update_game_model_count_redis.php
#         Desc: 每天统计 游戏 +盒子 + MAC 的注册 及登陆 数量
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-04-01 16:22:48
#      History:
=============================================================================*/
//非命令行下 404
if(php_sapi_name() != 'cli') {
	header('HTTP/1.1 404 Not Found');
	header('status: 404 Not Found');
	exit;
}
include_once(str_replace("count_data","",dirname(__FILE__))."config.inc.php");
include_once(WEBPATH_DIR."include".DS."redis.class.php");//redis处理类
include_once(WEBPATH_DIR."redis.config.inc.php"); //redis连接
//redis连接
//$b = array('host'=>'127.0.0.1','port'=>6379);
//$redis = new myredis($b);

include_once(WEBPATH_DIR."db.save.config.inc.php");
//如果有参数，则用参数的时间
$mydata = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';
$tmp_len = strlen($mydata);
if( !is_empty($mydata) && $tmp_len!=8 ){
	echo('输入的日期不对:'.$mydata);
	exit;
}else if(is_empty($mydata)){//如果没有参数，则获取上一天
	$mydata = date("Ymd",THIS_DATETIME - 86400);//20150317;
}
echo('开始进行数据统计'.$mydata.chr(10));


//游戏.盒子型号统计(统计 1、总注册，2、总登录，3、总游戏时间)
$sql = "SELECT `grml_title`,`grml_pn`, `grml_md`,count(DISTINCT if(`grml_in_date`=$mydata,`grml_mac`,0))-1 as num,
count(DISTINCT `grml_mac`) as lnum,sum(grml_ut) as grml_ut_all
FROM `kyx_game_reg_mac_login` 
WHERE `grml_login_date` = $mydata 
group by `grml_pn`,`grml_md`";

$row = $conn->find($sql);
if($row){
	$redis->select(2);//选择redis的第三个数据库来存放
	foreach ($row as $val){
		//转义字符
		$val['grml_title'] = mysql_real_escape_string($val['grml_title']);
		
		//检查是否有记录这个游戏
		$redis_key = md5('kyxgame|'.$val['grml_pn']);
		$redis_ok = $redis->get($redis_key);
		//如果没有找到，则插入
		if(!$redis_ok){
			//把数据插入游戏信息表
			$arr = array(
					'g_in_date'=>$mydata,//'记录日期',
					'g_pn'=>$val['grml_pn'],//'游戏包名',
					'g_name'=>$val['grml_title'],//'游戏名称',
					'g_order'=>0//'排序号',
			);
			$game_id = $conn->save('kyx_game_info', $arr);
			//插入redis
			$redis->set($redis_key,$game_id);
		}else{//如果有找到，则读取ID
			$game_id = $redis_ok;
		}
		//检查是否有记录这个盒子型号
		$redis_key = md5('kyxmodel|'.$val['grml_md']);
		$redis_ok = $redis->get($redis_key);
		//如果没有找到，则插入
		if(!$redis_ok){
			//把数据插入盒子型号信息表
			$arr = array(
					'm_in_date'=>$mydata,//'记录日期',
					'm_name'=>$val['grml_md'],//'盒子型号名称',
					'm_name_cn'=>$val['grml_md'],//'盒子型号名称',
					'm_order'=>0//'排序号',
			);
			$model_id = $conn->save('kyx_model_info', $arr);
			//插入redis
			$redis->set($redis_key,$model_id);
		}else{//如果有找到，则读取ID
			$model_id = $redis_ok;
		}
		
		//插入 游戏.盒子 统计数据
		$arr = array(
			'in_date'=>$mydata,//'统计日期',
			'g_id'=>$game_id,//'游戏ID(表kyx_game_info里的g_id)',
			'm_id'=>$model_id,//'盒子型号ID(表kyx_model_info里的m_id)',
			'gc_reg_num'=>intval($val['num']),//'新增人数(当天注册人数)',
			'gc_login_num'=>intval($val['lnum']),//'登陆人数(活跃数)',
			'gc_ut'=>intval($val['grml_ut_all'])//'游戏平均时间(当天总游戏时间/活跃数)'
		);
		$conn->save('kyx_game_model_count', $arr);
	
	}
	
	echo($mydata.'统计数据成功'.chr(10));
}else{
	echo($mydata.'没有查到统计数据！'.chr(10));
}






