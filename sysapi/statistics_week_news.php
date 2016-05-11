<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取获取统计周报数据
 * @file: statistics_week_news.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-20  15:25
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");

$key = get_param('key'); //验证key
if($key != md5(URL_KYX_KEY.'_'.SYS_URL_KYX_KEY)){
    exit(responseJson(array('操作错误')));
}

$begin_time_stamp = mktime(0,0,0,date('m'),date('d') - date('w') + 1 - 7,date('Y')); //上周一时间戳
$begin_time = date('Ymd',$begin_time_stamp); //上周一日期
$end_time_stamp = mktime(23,59,59,date('m'),date('d') - date('w') + 7 - 7,date('Y')); //上周日时间戳
$end_time = date('Ymd',$end_time_stamp); //上周日日期

//获取特定广告位的广告id
$sql = "SELECT `ad_id`,`adp_id`,`ad_game_id`,`ad_dis_order` FROM `mzw_ad` WHERE `adp_id` IN(58,47) AND `ad_status` = 1 ORDER BY `adp_id` ASC,`ad_game_id` ASC,`ad_dis_order` DESC ";
$ad_data = $conn->find($sql,'ad_id');

//获取视频关联所有游戏信息
$sql = "SELECT `id`,`gi_name` FROM `video_game_info` WHERE `gi_isshow` = 1";
$game_arr = $conn->find($sql,'id');

//上周阿里上传视频总数
$sql = "SELECT COUNT(1) AS num FROM `video_video_list` WHERE `vvl_sourcetype` = 14 AND `in_date` >= ".$begin_time_stamp." AND `in_date` <= ".$end_time_stamp;
$data = $conn->get_one($sql);
$ali_all_num = isset($data['num']) ? intval($data['num']) : 0;

//上周阿里上传视频可显示数量
$sql = "SELECT COUNT(1) AS num FROM `video_video_list` WHERE `vvl_sourcetype` = 14 AND `va_isshow` = 1 AND `in_date` >= ".$begin_time_stamp." AND `in_date` <= ".$end_time_stamp;
$data = $conn->get_one($sql);
$ali_show_num = isset($data['num']) ? intval($data['num']) : 0;

include_once("../db.save.config.inc.php");

//初始数组
$returnArr = array();

/**------- 获取视频播放统计 -------**/

//数据初始化
$returnArr[0] = array(
    'title' => '视频播放统计（一周）',
    'rows' => array()
);

//获取SDK爬取视频的一周播放数跟一周独立播放数
//$sql = "SELECT SUM(A.`play_num`) AS num,SUM(A.`play_mnum`) AS inum,A.`in_date` FROM `kyx_video_play_time` AS A
//        LEFT JOIN `mzw_video_video_list` AS B ON A.`video_id` = B.`id`
//        WHERE A.`source` =  2 AND B.`vvl_sourcetype` != 14 AND A.`in_date` >= ".$begin_time." AND A.`in_date` <= ".$end_time
//        ." GROUP BY A.`in_date` ORDER BY A.`in_date` ASC ";
$sql = "SELECT SUM(A.`play_num`) AS num,SUM(A.`mac_play_num`) AS inum,A.`in_date` FROM `kyx_sdk_video_click_play_time` AS A
        LEFT JOIN `mzw_video_video_list` AS B ON A.`v_id` = B.`id`
        WHERE B.`vvl_sourcetype` != 14 AND A.`in_date` >= ".$begin_time." AND A.`in_date` <= ".$end_time
        ." GROUP BY A.`in_date` ORDER BY A.`in_date` ASC";
$sdk_grab_data = $conn->find($sql,'in_date');
$sdk_grab_num = 0;
$sdk_grab_inum = 0;
if(!empty($sdk_grab_data)){
    //计算SDK爬取视频的一周播放数跟一周独立播放数总数
    foreach($sdk_grab_data as $val){
        $sdk_grab_num += intval($val['num']);
        $sdk_grab_inum += intval($val['inum']);
    }
}

//获取SDK用户上传视频的一周播放数跟一周独立播放数
$sql = "SELECT SUM(A.`play_num`) AS num,SUM(A.`mac_play_num`) AS inum,A.`in_date` FROM `kyx_sdk_video_click_play_time` AS A
        LEFT JOIN `mzw_video_video_list` AS B ON A.`v_id` = B.`id`
        WHERE B.`vvl_sourcetype` = 14 AND A.`in_date` >= ".$begin_time." AND A.`in_date` <= ".$end_time
        ." GROUP BY A.`in_date` ORDER BY A.`in_date` ASC";
//$sql = "SELECT SUM(A.`play_num`) AS num,SUM(A.`play_mnum`) AS inum,A.`in_date` FROM `kyx_video_play_time` AS A
//        LEFT JOIN `mzw_video_video_list` AS B ON A.`video_id` = B.`id`
//        WHERE A.`source` =  2 AND B.`vvl_sourcetype` = 14 AND A.`in_date` >= ".$begin_time." AND A.`in_date` <= ".$end_time
//        ." GROUP BY A.`in_date` ORDER BY A.`in_date` ASC ";
$sdk_user_data = $conn->find($sql,'in_date');
$sdk_user_num = 0;
$sdk_user_inum = 0;
if(!empty($sdk_user_data)){
    //计算SDK用户上传视频的一周播放数跟一周独立播放数总数
    foreach($sdk_user_data as $val){
        $sdk_user_num += intval($val['num']);
        $sdk_user_inum += intval($val['inum']);
    }
}

//获取客户端爬取视频的一周播放数跟一周独立播放数
$sql = "SELECT SUM(A.`play_num`) AS num,SUM(A.`play_mnum`) AS inum,A.`in_date` FROM `kyx_video_play_time` AS A
        LEFT JOIN `mzw_video_video_list` AS B ON A.`video_id` = B.`id`
        WHERE A.`source` =  1 AND B.`vvl_sourcetype` != 14 AND A.`in_date` >= ".$begin_time." AND A.`in_date` <= ".$end_time
        ." GROUP BY A.`in_date` ORDER BY A.`in_date` ASC ";
$app_grab_data = $conn->find($sql,'in_date');
$app_grab_num = 0;
$app_grab_inum = 0;
if(!empty($app_grab_data)){
    //计算客户端爬取视频的一周播放数跟一周独立播放数总数
    foreach($app_grab_data as $val){
        $app_grab_num += intval($val['num']);
        $app_grab_inum += intval($val['inum']);
    }
}

//获取客户端用户上传视频的一周播放数跟一周独立播放数
$sql = "SELECT SUM(A.`play_num`) AS num,SUM(A.`play_mnum`) AS inum,A.`in_date` FROM `kyx_video_play_time` AS A
        LEFT JOIN `mzw_video_video_list` AS B ON A.`video_id` = B.`id`
        WHERE A.`source` =  1 AND B.`vvl_sourcetype` = 14 AND A.`in_date` >= ".$begin_time." AND A.`in_date` <= ".$end_time
        ." GROUP BY A.`in_date` ORDER BY A.`in_date` ASC ";
$app_user_data = $conn->find($sql,'in_date');
$app_user_num = 0;
$app_user_inum = 0;
if(!empty($app_user_data)){
    //计算客户端用户上传视频的一周播放数跟一周独立播放数总数
    foreach($app_user_data as $val){
        $app_user_num += intval($val['num']);
        $app_user_inum += intval($val['inum']);
    }
}

//计算周一到周日每天的播放总数
$sdk_week_num = 0;
$app_week_num = 0;
for($i = 0;$i<7;$i++){
    $temp_time = date('Ymd',$begin_time_stamp + $i * 86400);
    $sdk_week_data[$temp_time] = 0;
    $app_week_data[$temp_time] = 0;

    //sdk爬取视频播放数
    if(isset($sdk_grab_data[$temp_time]['num'])){
        $sdk_week_data[$temp_time] += $sdk_grab_data[$temp_time]['num'];
        $sdk_week_num += $sdk_grab_data[$temp_time]['num'];
    }
    //sdk用户上传播放数
    if(isset($sdk_user_data[$temp_time]['num'])){
        $sdk_week_data[$temp_time] += $sdk_user_data[$temp_time]['num'];
        $sdk_week_num += $sdk_user_data[$temp_time]['num'];
    }
    //app爬取视频播放数
    if(isset($app_grab_data[$temp_time]['num'])){
        $app_week_data[$temp_time] += $app_grab_data[$temp_time]['num'];
        $app_week_num += $app_grab_data[$temp_time]['num'];
    }
    //app用户上传播放数
    if(isset($app_user_data[$temp_time]['num'])){
        $app_week_data[$temp_time] += $app_user_data[$temp_time]['num'];
        $app_week_num += $app_user_data[$temp_time]['num'];
    }
}

//数据组装
$rows_title = array(
    '播放总数(爬取）',
    '播放总数（用户上传）',
    '独立播放数(爬取）',
    '独立播放数（用户上传）',
    '播放总数'
);
$returnArr[0]['rows'][0] = array('播放/万次','周一（SDK/客户端）','周二（SDK/客户端）','周三（SDK/客户端）','周四（SDK/客户端）','周五（SDK/客户端）','周六（SDK/客户端）','周日（SDK/客户端）','总计（SDK/客户端）');
foreach($rows_title as $key => $val){
    $returnArr[0]['rows'][$key+1][0] = $val;
    for($i = 0;$i<8;$i++){
        $temp_time = date('Ymd',$begin_time_stamp + $i * 86400);
        if($key == 0 || $key == 2){
            if($i < 7){
                $key_name = ($key == 0) ? 'num' : 'inum';
                $sdk_temp = isset($sdk_grab_data[$temp_time][$key_name]) ? intval($sdk_grab_data[$temp_time][$key_name]) : 0;
                $app_temp = isset($app_grab_data[$temp_time][$key_name]) ? intval($app_grab_data[$temp_time][$key_name]) : 0;
            }else{
                $sdk_temp = ($key == 0) ? $sdk_grab_num : $sdk_grab_inum;
                $app_temp = ($key == 0) ? $app_grab_num : $app_grab_inum;
            }
        }elseif($key == 1 || $key == 3){
            if($i < 7){
                $key_name = ($key == 1) ? 'num' : 'inum';
                $sdk_temp = isset($sdk_user_data[$temp_time][$key_name]) ? intval($sdk_user_data[$temp_time][$key_name]) : 0;
                $app_temp = isset($app_user_data[$temp_time][$key_name]) ? intval($app_user_data[$temp_time][$key_name]) : 0;
            }else{
                $sdk_temp = ($key == 1) ? $sdk_user_num : $sdk_user_inum;
                $app_temp = ($key == 1) ? $app_user_num : $app_user_inum;
            }
        }else{
            if($i < 7){
                $sdk_temp = isset($sdk_week_data[$temp_time]) ? intval($sdk_week_data[$temp_time]) : 0;
                $app_temp = isset($app_week_data[$temp_time]) ? intval($app_week_data[$temp_time]) : 0;
            }else{
                $sdk_temp = $sdk_week_num;
                $app_temp = $app_week_num;
            }
        }
        $returnArr[0]['rows'][$key+1][] = ($sdk_temp/10000).'/'.($app_temp/10000);
    }
}

//专辑点击统计
$returnArr[1] = array(
    'title' => '专辑点击统计（一周,APP跟SDK前30）',
    'rows' => array()
);
$returnArr[1]['rows'][0] = array('专辑名称','点击次数（SDK/客户端）','独立点击（SDK）');

/**----- SDK专辑统计数据 -----**/
$sql = "SELECT SUM(`click_num`) AS sdknum,SUM(`mac_click_num`) AS sdkinum,`t_name`,`t_id`
        FROM `kyx_sdk_video_topic_click_time`
        WHERE `in_date` >= ".$begin_time." AND `in_date` <= ".$end_time." GROUP BY `t_id` ORDER BY `sdknum` DESC,sdkinum DESC";
$sdk_topic_data = $conn->find($sql,'t_id');

//APP专辑统计数据
$sql = "SELECT SUM(`vaa_num`) AS appnum,`vaa_topicid`,`vaa_topictitle`
        FROM `kyx_video_app_album`
        WHERE `vaa_in_date` >= ".$begin_time." AND `vaa_in_date` <= ".$end_time
        ."  GROUP BY `vaa_topicid` ORDER BY appnum DESC";
$app_topic_data = $conn->find($sql,'vaa_topicid');

$tid_arr = array();
if(!empty($sdk_topic_data)){
    $i = 0;
    foreach($sdk_topic_data as $key => $val){
        if($i >= 30){
            continue;
        }
        $tid_arr[] = intval($key);
        $i++;
    }
}

if(!empty($app_topic_data)){
    $i = 0;
    foreach($app_topic_data as $key => $val){
        if($i >= 30){
            continue;
        }
        $tid_arr[] = intval($key);
        $i++;
    }
}

//专辑id去重,数据整合
$tid_arr = array_unique($tid_arr);
if(!empty($tid_arr)){
    foreach($tid_arr as $tval){
        $name = isset($sdk_topic_data[$tval]['t_name']) ? $sdk_topic_data[$tval]['t_name'] : $app_topic_data[$tval]['vaa_topictitle'];
        $cnum = (isset($sdk_topic_data[$tval]['sdknum']) ? intval($sdk_topic_data[$tval]['sdknum']) : 0).'/'.(isset($app_topic_data[$tval]['appnum']) ? intval($app_topic_data[$tval]['appnum']) : 0);
        $icnum = (isset($sdk_topic_data[$tval]['sdkinum']) ? intval($sdk_topic_data[$tval]['sdkinum']) : 0);

        $returnArr[1]['rows'][] = array($name,$cnum,$icnum);
    }
}


/**------- 首页banner点击统计 -------**/
$returnArr[2] = array(
    'title' => '客户端Banner广告位点击统计（一周）',
    'rows' => array()
);
$returnArr[2]['rows'][0] = array('广告位置排序','广告id','所属游戏','广告名称','展示次数','点击次数');
if(!empty($ad_data)){
    //获取需要获取统计数据的广告id
    $ad_id = array();
    foreach($ad_data as $akey => $aval){
        $ad_id[] = intval($akey);
    }
    $ad_id_str = implode(',',$ad_id);

    //获取统计数据
    $sql = "SELECT SUM(`show_num`) AS snum,SUM(`click_num`) AS cnum,`aid`,`ad_title`
            FROM `kyx_video_vert_oper_time` WHERE `aid` IN (".$ad_id_str.") AND `in_date` >= ".$begin_time." AND `in_date` <= ".$end_time." GROUP BY `aid`";
    $ad_arr = $conn->find($sql,'aid');

    foreach($ad_data as $akey => $aval){

        $order = intval($aval['ad_dis_order']);
        $adid = intval($akey);
        $adname = isset($ad_arr[$akey]['ad_title']) ? $ad_arr[$akey]['ad_title'] : '';
        $shownum = isset($ad_arr[$akey]['snum']) ? intval($ad_arr[$akey]['snum']) : 0;
        $clicknum = isset($ad_arr[$akey]['cnum']) ? intval($ad_arr[$akey]['cnum']) : 0;
        $gamename = isset($game_arr[$aval['ad_game_id']]['gi_name']) ? $game_arr[$aval['ad_game_id']]['gi_name'] : '我的世界';

        if(empty($adname) && empty($shownum) && empty($clicknum)){
            continue;
        }
        $returnArr[2]['rows'][] = array($order,$adid,$gamename,$adname,$shownum,$clicknum);
    }
}


/**------- SDK录制视频统计 -------**/
$returnArr[3] = array(
    'title' => 'SDK录制视频统计（一周）',
    'rows' => array()
);
$returnArr[3]['rows'][0] = array('游戏名','点录制数（独立）','录制后点播放数（独立）','移除悬浮框数（独立）','有录制数（独立）','录制最大时长','录制平均时长');

//$sdk_game_arr = array(
//    439 => '我的世界'
//);

$sql = "SELECT `game_id`,SUM(`ct_num`) AS `ctnum`,SUM(`mac_ct_num`) AS `ctinum`,SUM(`cp_num`) AS `cpnum`,SUM(`mac_cp_num`) AS `cpinum`,
        SUM(`remove_num`) AS `renum`,SUM(`mac_remove_num`) AS `reinum`,SUM(`has_user_num`) AS `hsnum`,SUM(`has_mac_user_num`) AS `hsinum`,
        MAX(if(`max_time` > 14400000,0,`max_time`)) AS `max_time`,SUM(`avg_time` * `has_user_num`)/SUM(`has_user_num`) as avg_time,B.`g_name`
        FROM `kyx_sdk_video_transcribe_time` AS A LEFT JOIN `kyx_game_info` AS B ON A.`game_id` = B.`g_id`
        WHERE `in_date` >= ".$begin_time." AND `in_date` <= ".$end_time." GROUP BY `game_id` ORDER BY `ctnum` DESC";
$trans_data = $conn->find($sql,'game_id');
if(!empty($trans_data)){
    foreach($trans_data as $key => $val){
        $gamename = $val['g_name'];
        $ctnum =  empty($val['ctinum']) ? $val['ctnum'] : ($val['ctnum']."（".$val['ctinum']."）");
        $cpnum =  empty($val['cpinum']) ? $val['cpnum'] : ($val['cpnum']."（".$val['cpinum']."）");
        $renum =  empty($val['reinum']) ? $val['renum'] : ($val['renum']."（".$val['reinum']."）");
        $hsnum =  empty($val['hsinum']) ? $val['hsnum'] : ($val['hsnum']."（".$val['hsinum']."）");
        $max_time =  round($val['max_time']/1000,2)."s";
        $avg_time =  round($val['avg_time']/1000,2)."s";

        $returnArr[3]['rows'][] = array($gamename,$ctnum,$cpnum,$renum,$hsnum,$max_time,$avg_time);
    }
}


/**------- 分享次数统计 -------**/
$returnArr[4] = array(
    'title' => 'SDK录制视频统计（一周）',
    'rows' => array()
);
$returnArr[4]['rows'][0] = array('分享类型','分享次数（独立）','分享成功数（独立）','取消分享数（独立）','成功率','取消率');

$share_arr = array(
    0 => '微博',
    1 => 'QQ',
    2 => 'QZONE',
    3 => '优酷',
    4 => '微信',
    5 => '更多'
);

$sql = "SELECT `s_type`,SUM(`bshare_num`) AS bnum,SUM(`mac_bshare_num`) AS binum,SUM(`sshare_num`) AS snum,SUM(`mac_sshare_num`) AS sinum,
        SUM(`qshare_num`) AS qnum,SUM(`mac_qshare_num`) AS qinum,SUM(`sshare_num`)/SUM(`bshare_num`) AS srate,SUM(`qshare_num`)/SUM(`bshare_num`) AS qrate
        FROM `kyx_sdk_video_share_time` WHERE `in_date` >= ".$begin_time." AND `in_date` <= ".$end_time." GROUP BY `s_type";
$share_data = $conn->find($sql);
if(!empty($share_data)){
    foreach($share_data as $val){
        $sharename = isset($share_arr[$val['s_type']]) ? $share_arr[$val['s_type']] : '未知平台';
        $bnum =  empty($val['binum']) ? $val['bnum'] : ($val['bnum']."（".$val['binum']."）"); //分享数
        $snum =  empty($val['sinum']) ? $val['snum'] : ($val['snum']."（".$val['sinum']."）"); //成功分享数
        $qnum =  empty($val['qinum']) ? $val['qnum'] : ($val['qnum']."（".$val['qinum']."）"); //取消分享数
        $srate = round($val['srate'] * 100,2).'%'; //成功率
        $qrate = round($val['qrate'] * 100,2).'%'; //取消率

        $returnArr[4]['rows'][] = array($sharename,$bnum,$snum,$qnum,$srate,$qrate);
    }
}

/**------- 阿里百川上传视频数量 -------**/
$returnArr[5] = array(
    'title' => '阿里百川上传视频统计（一周）',
    'rows' => array()
);
$returnArr[5]['rows'][0] = array('上周视频上传数量','上周不可显示视频数量','上周可显示视频数量');
$returnArr[5]['rows'][1] = array($ali_all_num,($ali_all_num - $ali_show_num),$ali_show_num);

/**------- 获取一周用户注册数 -------**/
$returnArr[6] = array(
    'title' => '一周用户注册数',
    'rows' => array()
);

$sql = "SELECT COUNT(1) AS num FROM `uc_members` WHERE `source` = 2 AND `regdate` >= ".$begin_time_stamp." AND `regdate` <= ".$end_time_stamp;
$data = $uconn->find($sql);
$reg_num = isset($data[0]['num']) ? intval($data[0]['num']) : 0;

$returnArr[6]['rows'][0] = array('一周用户注册数',$reg_num);

exit(responseJson($returnArr));