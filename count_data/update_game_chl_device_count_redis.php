#! /usr/local/php/bin/php -q
<?php
/*=============================================================================
#     FileName: update_game_chl_device_count_redis.php
#         Desc: 每天更新统计 游戏 + 渠道 +手柄 + MAC 的注册  数量(即使用人数)
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-04-02 12:00:48
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


//游戏.手柄使用统计(统计 1、游戏每个手柄的使用总数)
$sql = "SELECT `grml_title`,`grml_chl`,`grml_pn`,`grml_dc`,count(DISTINCT if(`grml_in_date`=$mydata,`grml_mac`,0)) as num,
count(DISTINCT `grml_mac`) as lnum
FROM `kyx_game_reg_mac_login` 
WHERE `grml_login_date` = $mydata 
group by `grml_pn`,`grml_chl`,`grml_dc`";

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

		//检查是否有记录这个手柄
		$redis_key = md5('kyxdevice|'.$val['grml_dc']);
		$redis_ok = $redis->get($redis_key);
		//如果没有找到，则插入手柄信息
		if(!$redis_ok){
			//把数据插入手柄信息表
			$arr = array(
					'd_in_date'=>$mydata,//'记录日期',
					'd_name'=>$val['grml_dc'],//'手柄字符串名',
					'd_name_cn'=>$val['grml_dc'],//'手柄名称(需要后台填写)',
					'd_order'=>0//'排序号',
			);
			$device_id = $conn->save('kyx_device_info', $arr);
			//插入redis
			$redis->set($redis_key,$device_id);
		}else{
			$device_id = $redis_ok;
		}
		
		//插入 游戏.手柄 统计数据
		$arr = array(
		  'in_date'=>$mydata,//'统计日期',
		  'g_id'=>$game_id,//'游戏ID(表kyx_game_info里的g_id)',
          'chl_id'=>$chl_id,//'渠道ID(表kyx_channel_info里的c_id)',
		  'd_id'=>$device_id,//'盒子型号ID(表kyx_device_info里的d_id)',
		  'gd_reg_num'=>intval($val['num']),//注册人数(新使用该手柄的用户数)
		  'gd_login_num'=>intval($val['lnum'])//使用人数(当天使用该手柄的用户数)
		);
		$conn->save('kyx_game_chl_device_count', $arr);
			
	}
	
	echo($mydata.'统计数据成功'.chr(10));
}else{
	echo($mydata.'没有查到统计数据！'.chr(10));
}
