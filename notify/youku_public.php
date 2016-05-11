<?php
include_once("../config.inc.php");
include_once("../db.config.inc.php");
$vid = intval(get_param('vid'));//视频id
$youku_id = trim(get_param('youku_id'));//优酷id

if(empty($vid) || empty($youku_id)){
	exit("502");
}

$sql = "SELECT id from video_video_list WHERE `id`={$vid}";
$data = $conn->get_one($sql);
if(empty($data)){
	exit("501");
}

$sql= "UPDATE video_video_list SET va_isshow=1,vvl_video_id='{$youku_id}' WHERE id={$vid}";
if($conn->query($sql)){
	exit("200");
}
