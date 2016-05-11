<?php
/*=============================================================================
#     FileName: update_video_play_date.php
#         Desc: 定期统计视频播放信息（统计表kyx_video_play_log里的数据,存到kyx_video_play_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-05-21 14:55:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}
include_once(WEBPATH_DIR."db.save.config.inc.php");

//获取上一天的日期
$this_date = date('Ymd',THIS_DATETIME - 86400);

$sql = 'SELECT vp_in_date,vp_id,vp_source,COUNT(vp_mac) AS play_num,COUNT(DISTINCT vp_mac) as play_mnum FROM kyx_video_play_log WHERE vp_source > 0 AND vp_in_date='.$this_date.' GROUP BY vp_id,vp_source';

$data = $conn->find($sql);
foreach ($data as $val){
	if(isset($val['vp_id']) && !is_empty($val['vp_id'])){

		$row = array(
		  "in_date"=>$this_date,//'播放日期',
		  "video_id"=>intval($val['vp_id']),//'视频ID',
		  "play_num"=>intval($val['play_num']),//'视频播放次数',
		  "play_mnum"=>intval($val['play_mnum']),//'视频独立播放次数',
          "source" => intval($val['vp_source']) //视频访问来源（1：APP 2：SDK）
		);
		$conn->save('kyx_video_play_time', $row);

	}else{
		echo("视频播放数据为空");
	}
}
echo("成功更新视频播放数据");
