#! /usr/local/php/bin/php -q
<?php
/*=============================================================================
#     FileName: update_chl_count_redis.php
#         Desc: 每天统计 渠道 + MAC 的注册 及登陆 数量
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-04-02 14:22:48
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

//统计当天登录的数据(统计 1、总注册及每小时注册，2、总登录及每小时登陆，3、总登录次数，4、总游戏时间)
$sql = "SELECT `grml_chl`,count(DISTINCT if(`grml_in_date`=$mydata,`grml_mac`,0))-1 as num,
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=0,`grml_mac`,0))-1 as num0,
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=1,`grml_mac`,0))-1 as num1, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=2,`grml_mac`,0))-1 as num2, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=3,`grml_mac`,0))-1 as num3, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=4,`grml_mac`,0))-1 as num4, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=5,`grml_mac`,0))-1 as num5, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=6,`grml_mac`,0))-1 as num6, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=7,`grml_mac`,0))-1 as num7, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=8,`grml_mac`,0))-1 as num8, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=9,`grml_mac`,0))-1 as num9, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=10,`grml_mac`,0))-1 as num10, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=11,`grml_mac`,0))-1 as num11, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=12,`grml_mac`,0))-1 as num12, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=13,`grml_mac`,0))-1 as num13, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=14,`grml_mac`,0))-1 as num14, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=15,`grml_mac`,0))-1 as num15, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=16,`grml_mac`,0))-1 as num16, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=17,`grml_mac`,0))-1 as num17, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=18,`grml_mac`,0))-1 as num18, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=19,`grml_mac`,0))-1 as num19, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=20,`grml_mac`,0))-1 as num20, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=21,`grml_mac`,0))-1 as num21, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=22,`grml_mac`,0))-1 as num22, 
count(DISTINCT if(`grml_in_date`=$mydata and `grml_reg_time`=23,`grml_mac`,0))-1 as num23,

count(DISTINCT `grml_mac`) as lnum,
count(DISTINCT if(`grml_time`=0,`grml_mac`,0))-1 as lnum0,
count(DISTINCT if(`grml_time`=1,`grml_mac`,0))-1 as lnum1, 
count(DISTINCT if(`grml_time`=2,`grml_mac`,0))-1 as lnum2, 
count(DISTINCT if(`grml_time`=3,`grml_mac`,0))-1 as lnum3, 
count(DISTINCT if(`grml_time`=4,`grml_mac`,0))-1 as lnum4, 
count(DISTINCT if(`grml_time`=5,`grml_mac`,0))-1 as lnum5, 
count(DISTINCT if(`grml_time`=6,`grml_mac`,0))-1 as lnum6, 
count(DISTINCT if(`grml_time`=7,`grml_mac`,0))-1 as lnum7, 
count(DISTINCT if(`grml_time`=8,`grml_mac`,0))-1 as lnum8, 
count(DISTINCT if(`grml_time`=9,`grml_mac`,0))-1 as lnum9, 
count(DISTINCT if(`grml_time`=10,`grml_mac`,0))-1 as lnum10, 
count(DISTINCT if(`grml_time`=11,`grml_mac`,0))-1 as lnum11, 
count(DISTINCT if(`grml_time`=12,`grml_mac`,0))-1 as lnum12, 
count(DISTINCT if(`grml_time`=13,`grml_mac`,0))-1 as lnum13, 
count(DISTINCT if(`grml_time`=14,`grml_mac`,0))-1 as lnum14, 
count(DISTINCT if(`grml_time`=15,`grml_mac`,0))-1 as lnum15, 
count(DISTINCT if(`grml_time`=16,`grml_mac`,0))-1 as lnum16, 
count(DISTINCT if(`grml_time`=17,`grml_mac`,0))-1 as lnum17, 
count(DISTINCT if(`grml_time`=18,`grml_mac`,0))-1 as lnum18, 
count(DISTINCT if(`grml_time`=19,`grml_mac`,0))-1 as lnum19, 
count(DISTINCT if(`grml_time`=20,`grml_mac`,0))-1 as lnum20, 
count(DISTINCT if(`grml_time`=21,`grml_mac`,0))-1 as lnum21, 
count(DISTINCT if(`grml_time`=22,`grml_mac`,0))-1 as lnum22, 
count(DISTINCT if(`grml_time`=23,`grml_mac`,0))-1 as lnum23,
sum(grml_num) as grml_num_all,sum(grml_ut) as grml_ut_all
FROM `kyx_game_reg_mac_login` 
WHERE `grml_login_date` = $mydata 
group by `grml_chl`";

$row = $conn->find($sql);
if($row){
	$redis->select(2);//选择redis的第三个数据库来存放
	foreach ($row as $val){

		//检查是否有记录这个渠道
		$redis_key = md5('kyxchl|'.$val['grml_chl']);
		$redis_ok = $redis->get($redis_key);
		//如果没有找到，则插入
		if(!$redis_ok){
			//把数据插入渠道信息表
			$arr = array(
					'c_in_date'=>$mydata,//'记录日期',
					'c_chl'=>$val['grml_chl'],//'渠道ID',
					'c_name'=>$val['grml_chl'],//'渠道名称(需要在后台填写)',
					'c_order'=>0//'排序号',
			);
			$chl_id = $conn->save('kyx_channel_info', $arr);
			//插入redis
			$redis->set($redis_key,$chl_id);
		}else{//如果有找到，则读取ID
			$chl_id = $redis_ok;
		}
		//插入注册数据
		$arr = array(
			'in_date'=>$mydata,//'统计日期',
			'chl_id'=>intval($chl_id),//'渠道ID(表kyx_channel_info里的c_id)',
			'crt_num0'=>intval($val['num0']),//'0点新增人数',
			'crt_num1'=>intval($val['num1']),//'1点新增人数',
			'crt_num2'=>intval($val['num2']),//'2点新增人数',
			'crt_num3'=>intval($val['num3']),//'3点新增人数',
			'crt_num4'=>intval($val['num4']),//'3点新增人数',
			'crt_num5'=>intval($val['num5']),//'5点新增人数',
			'crt_num6'=>intval($val['num6']),//'6点新增人数',
			'crt_num7'=>intval($val['num7']),//'7点新增人数',
			'crt_num8'=>intval($val['num8']),//'8点新增人数',
			'crt_num9'=>intval($val['num9']),//'9点新增人数',
			'crt_num10'=>intval($val['num10']),//'10点新增人数',
			'crt_num11'=>intval($val['num11']),//'11点新增人数',
			'crt_num12'=>intval($val['num12']),//'12点新增人数',
			'crt_num13'=>intval($val['num13']),//'13点新增人数',
			'crt_num14'=>intval($val['num14']),//'14点新增人数',
			'crt_num15'=>intval($val['num15']),//'15点新增人数',
			'crt_num16'=>intval($val['num16']),//'16点新增人数',
			'crt_num17'=>intval($val['num17']),//'17点新增人数',
			'crt_num18'=>intval($val['num18']),//'18点新增人数',
			'crt_num19'=>intval($val['num19']),//'19点新增人数',
			'crt_num20'=>intval($val['num20']),//'20点新增人数',
			'crt_num21'=>intval($val['num21']),//'21点新增人数',
			'crt_num22'=>intval($val['num22']),//'22点新增人数',
			'crt_num23'=>intval($val['num23']),//'23点新增人数'
			'crt_reg_num'=>intval($val['num']),//'新增人数(当天注册人数)',
			'crt_login_num'=>intval($val['lnum']),//'登陆人数(活跃数)',
			'crt_ut'=>intval($val['grml_ut_all']),//'游戏总时间(当天总游戏时间)',
			'crt_login_time'=>intval($val['grml_num_all'])//'启动次数'
		);
		$conn->save('kyx_chl_reg_time', $arr);
		//插入登陆数据
		$arr = array(
				'in_date'=>$mydata,//'统计日期',
				'chl_id'=>intval($chl_id),//'渠道ID(表kyx_channel_info里的c_id)',
				'clt_num0'=>intval($val['lnum0']),//'0点新增人数',
				'clt_num1'=>intval($val['lnum1']),//'1点新增人数',
				'clt_num2'=>intval($val['lnum2']),//'2点新增人数',
				'clt_num3'=>intval($val['lnum3']),//'3点新增人数',
				'clt_num4'=>intval($val['lnum4']),//'3点新增人数',
				'clt_num5'=>intval($val['lnum5']),//'5点新增人数',
				'clt_num6'=>intval($val['lnum6']),//'6点新增人数',
				'clt_num7'=>intval($val['lnum7']),//'7点新增人数',
				'clt_num8'=>intval($val['lnum8']),//'8点新增人数',
				'clt_num9'=>intval($val['lnum9']),//'9点新增人数',
				'clt_num10'=>intval($val['lnum10']),//'10点新增人数',
				'clt_num11'=>intval($val['lnum11']),//'11点新增人数',
				'clt_num12'=>intval($val['lnum12']),//'12点新增人数',
				'clt_num13'=>intval($val['lnum13']),//'13点新增人数',
				'clt_num14'=>intval($val['lnum14']),//'14点新增人数',
				'clt_num15'=>intval($val['lnum15']),//'15点新增人数',
				'clt_num16'=>intval($val['lnum16']),//'16点新增人数',
				'clt_num17'=>intval($val['lnum17']),//'17点新增人数',
				'clt_num18'=>intval($val['lnum18']),//'18点新增人数',
				'clt_num19'=>intval($val['lnum19']),//'19点新增人数',
				'clt_num20'=>intval($val['lnum20']),//'20点新增人数',
				'clt_num21'=>intval($val['lnum21']),//'21点新增人数',
				'clt_num22'=>intval($val['lnum22']),//'22点新增人数',
				'clt_num23'=>intval($val['lnum23']),//'23点新增人数'
				'clt_reg_num'=>intval($val['num']),//'新增人数(当天注册人数)',
				'clt_login_num'=>intval($val['lnum']),//'登陆人数(活跃数)',
				'clt_ut'=>intval($val['grml_ut_all']),//'游戏总时间(当天总游戏时间)',
				'clt_login_time'=>intval($val['grml_num_all'])//'启动次数'
		);
		$conn->save('kyx_chl_login_time', $arr);
	}
	echo($mydata.'统计数据成功'.chr(10));
}else{
	echo($mydata.'没有查到统计数据！'.chr(10));
}








