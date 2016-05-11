<?php
/**
 * @copyright: @快游戏 2014
 * @description: 视频播放（解析视频播放地址）
 * @file: video_play.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-08-04  16:32
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once(WEBPATH_DIR."include".DS."video_parser.php");//视频解析处理
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['appid'] = intval(get_param('appid'));//视频ID
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

if($mydata['appid'] < 1){//如果视频ID为空，则出错
    exit('error! appid is empty!!');
}

$mydata['cpu'] = get_param('cpu');//CPU参数
$mydata['gpu'] = get_param('gpu');//GPU参数
$mydata['source'] = intval(get_param('source'));//访问来源（1：app 2:SDK）
$mydata['locale'] = get_param('locale');//言语版本
$mydata['density'] = get_param('density');//分辨率
$mydata['brand'] = get_param('brand');//品牌
$mydata['model'] = get_param('model');//型号
$mydata['mac'] = get_param('mac');//MAC地址

//记录播放日志
$tmp_str = json_encode($mydata).chr(13).chr(10);
write_file_random($tmp_str,"video_play",true,date('Ymd',time()));
unset($tmp_str);

//判断用户是否订阅主播参数
$mydata['uid'] = intval(get_param('uid'));//用户ID
$mydata['imei'] = get_param('imei');//用户imei地址

$mem_obj = new kyx_memcache();

$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//查找用户订阅内容
$user_sub_info_key = 'user_sub_info_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei']);
$sub_info = $mem_obj->get($user_sub_info_key);
if($sub_info === false){
    $sub_info = array();
    $sql = "SELECT `subid`,`subtype` FROM `video_user_sub_info` WHERE `status` = 1 ".$check_where;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $sub_info[] = array(
                'subid' => intval($val['subid']),
                'subtype' => intval($val['subtype'])
            );
        }
    }
    $mem_obj->set($user_sub_info_key,$sub_info,600);
}

//游戏分类数组
$type_key = "all_xl_game_type_arr";
$all_game_type = $mem_obj->get($type_key);
if($all_game_type === false){
    $sql = "SELECT `t_id`,`t_name_cn` FROM `video_game_type` WHERE `t_status` = 1";
    $type_data = $conn->find($sql);
    $all_game_type = array();
    if(!empty($type_data)){
        foreach($type_data as $val){
            $all_game_type[$val['t_id']] = $val['t_name_cn'];
        }
    }
    $mem_obj->set($type_key,$all_game_type,14400);
}

//初始化数组
$returnArr = array();

//查数据
$data_key = "xl_video_info_".$mydata['appid'];
$video_info = $mem_obj->get($data_key);
if($video_info ===  false){
    $sql_data = "SELECT `id`,`vvl_title`,`vvl_playurl`,`vvl_upload_time`,`vvl_playurl_get`,`vvl_time`,`vvl_count`,`vvl_server_url`,
                 `vvl_sourcetype`,`vvl_imgurl_get`,`vvl_imgurl`,`vvl_type_id`,`vvl_author_id`,`vvl_category_id`,`vvl_sourcetype`,
		         `vvl_video_id`,`vvl_game_id`,`vvl_uid`,`vvl_up_num`,`vvl_down_num`,`in_date` FROM `video_video_list`
		         WHERE `id` = ".$mydata['appid'];
    $video_info = $conn->get_one($sql_data);
    $mem_obj->set($data_key,$video_info,600);
}
if(count($video_info)>0){

    //视频截图
    $tmp_img_url = empty($video_info['vvl_imgurl_get']) ? $video_info['vvl_imgurl'] : (LOCAL_URL_DOWN_IMG.$video_info['vvl_imgurl_get']);

    $tmp_play_url_arr = array();

    //如果本地视频地址不为空
    if(!empty($video_info['vvl_server_url'])){
        if($video_info['vvl_sourcetype'] == 14 || $video_info['vvl_sourcetype'] == 17){
            $temp_video_id = intval($video_info['vvl_video_id']);
            $temp_video_len = strlen($video_info['vvl_video_id']);
            if(empty($temp_video_id) && $temp_video_len <> 32){
                $video_info['vvl_sourcetype'] = 1; //源转换为优酷源
            }
            $tmp_play_url_arr['a'] = $video_info['vvl_server_url'];
        }elseif($video_info['vvl_sourcetype'] == 7 || $video_info['vvl_sourcetype'] == 13 || $video_info['vvl_sourcetype'] == 11){
            $arr = explode('.', $video_info['vvl_server_url']);
            $tmp_play_url_arr['a'] = 'http://kyxservervideo.file.alimmdn.com'.reset($arr);
        }else{
            $tmp_play_url_arr['a'] = CDN_LESHI_URL_DOWN.$video_info['vvl_server_url'];
        }
    }else{
        if($video_info['vvl_sourcetype']==1  && $video_info['vvl_playurl_get']!=''){//如果是youku的，则直接返回m3u8的下载地址
            //type=mp4（高清）可替换为type=flv（标清）、type=hd2（超清）
            $tmp_play_url_arr['a'] = str_replace('type=mp4','type=flv',$video_info['vvl_playurl_get']);//标清
            $tmp_play_url_arr['b'] = $video_info['vvl_playurl_get'];//高清
            $tmp_play_url_arr['c'] = str_replace('type=mp4','type=hd2',$video_info['vvl_playurl_get']);//超清
        }elseif($video_info['vvl_sourcetype']==2 && $video_info['vvl_playurl_get']!=''){//如果是letv的
            $a = file_get_contents($video_info['vvl_playurl_get']);
            $b =json_decode($a,true);
            if(count($b)>1 && isset($b['result']) && isset($b['result']['items'])){
                if(count($b['result']['items']) > 1){
                    foreach ($b['result']['items'] as $value) {
                        if(!empty($value['task_name']) && in_array($value['task_name'],array('流畅','超清','高清'))){
                            switch ($value['task_name']) {
                                case '流畅':
                                    $tmp_play_url_arr['a'] = count($value['transcode']['urls'])?$value['transcode']['urls'][array_rand($value['transcode']['urls'])]:'';
                                    break;
                                case '高清':
                                    $tmp_play_url_arr['b'] = count($value['transcode']['urls'])?$value['transcode']['urls'][array_rand($value['transcode']['urls'])]:'';
                                    break;
                                case '超清':
                                    $tmp_play_url_arr['c'] = count($value['transcode']['urls'])?$value['transcode']['urls'][array_rand($value['transcode']['urls'])]:'';
                                    break;
                            }
                        }else{
                            if(count($b)>1 && isset($b['result']) && isset($b['result']['items'])
                                && isset($b['result']['items'][0]) && isset($b['result']['items'][0]['transcode'])
                                && isset($b['result']['items'][0]['transcode']['urls'])){

                                $tmp_play_url_arr['a'] = $b['result']['items'][0]['transcode']['urls'][array_rand($b['result']['items'][0]['transcode']['urls'])];
                            }else{
                                $tmp_play_url_arr['a'] = $video_info['vvl_playurl_get'];
                            }
                            if(count($b)>1 && isset($b['result']) && isset($b['result']['items'])
                                && isset($b['result']['items'][1]) && isset($b['result']['items'][1]['transcode'])
                                && isset($b['result']['items'][1]['transcode']['urls'])){

                                $tmp_play_url_arr['b'] = $b['result']['items'][1]['transcode']['urls'][array_rand($b['result']['items'][1]['transcode']['urls'])];
                            }
                            if(count($b)>1 && isset($b['result']) && isset($b['result']['items'])
                                && isset($b['result']['items'][2]) && isset($b['result']['items'][2]['transcode'])
                                && isset($b['result']['items'][2]['transcode']['urls'])){

                                $tmp_play_url_arr['c'] = $b['result']['items'][2]['transcode']['urls'][array_rand($b['result']['items'][2]['transcode']['urls'])];
                            }
                        }
                    }
                }else{
                    if(count($b)>1 && isset($b['result']) && isset($b['result']['items'])
                        && isset($b['result']['items'][0]) && isset($b['result']['items'][0]['transcode'])
                        && isset($b['result']['items'][0]['transcode']['urls'])){

                        $tmp_play_url_arr['a'] = $tmp_play_url_arr['b'] = $tmp_play_url_arr['c'] = $b['result']['items'][0]['transcode']['urls'][array_rand($b['result']['items'][0]['transcode']['urls'])];
                    }else{
                        $tmp_play_url_arr['a'] = $tmp_play_url_arr['b'] = $tmp_play_url_arr['c'] = $video_info['vvl_playurl_get'];
                    }
                }
            }
        }elseif($video_info['vvl_sourcetype']==3  && $video_info['vvl_playurl_get']!=''){//如果是sohu的
            $video = new video_parser();
            preg_match('/vid=(.*?)$/', $video_info['vvl_playurl_get'],$match);

            $tmp_vid = $match[1];
            $tmp_arr_video = $video->handle_sohu_video($tmp_vid);
            //随机拿一个视频地址
            if(isset($tmp_arr_video['real_mp4_url'])){
                $tmp_play_url_arr['a'] =  $tmp_arr_video['real_mp4_url'][array_rand($tmp_arr_video['real_mp4_url'],1)];//高清
            }else{
                $tmp_play_url_arr['a'] =  $video_info['vvl_playurl_get'];//高清
            }
        }elseif($video_info['vvl_sourcetype']==4  && $video_info['vvl_playurl_get']!=''){//如果是qq的

            $tmp_play_url_arr['a'] = $video_info['vvl_playurl_get'];//高清

        }elseif($video_info['vvl_sourcetype']==5  && $video_info['vvl_playurl_get']!=''){//如果是tudou的
            $video = new video_parser();
            preg_match('/getItemSegs\.action\?iid=(.*?)$/', $video_info['vvl_playurl_get'],$match);
            $tmp_vid = $match[1];
            $tmp_arr_video = $video->handle_tudou_video($tmp_vid);
            //$key = 3,2,5分别表示高清，标清，超清
            if(isset($tmp_arr_video[2]) && isset($tmp_arr_video[2][0]['real_flv_url'])){
                $tmp_play_url_arr['a'] = str_replace('&amp;', '&',$tmp_arr_video[2][0]['real_flv_url']);//标清
            }
            if(isset($tmp_arr_video[3]) && isset($tmp_arr_video[3][0]['real_flv_url'])){
                $tmp_play_url_arr['b'] = str_replace('&amp;', '&',$tmp_arr_video[3][0]['real_flv_url']);//高清
            }
            if(isset($tmp_arr_video[5]) && isset($tmp_arr_video[5][0]['real_flv_url'])){
                $tmp_play_url_arr['c'] = str_replace('&amp;', '&',$tmp_arr_video[5][0]['real_flv_url']);//超清
            }

        }elseif($video_info['vvl_sourcetype']==6  && $video_info['vvl_playurl_get']!=''){ //如果是ku6的
            if(strpos($video_info['vvl_playurl_get'],'.youku.com')){ //如果是youku源视频
                preg_match('/&vid=(.*?)&/', $video_info['vvl_playurl_get'],$yk_id);
                $tmp_play_url_arr['a'] = 'http://player.youku.com/player.php/sid/'.$yk_id[1].'/v.swf';
            }elseif(strpos($video_info['vvl_playurl_get'],'.ku6.com')){ //如果是ku6源
                $a = file_get_contents($video_info['vvl_playurl_get']);
                $b = json_decode($a,true);
                $tmp_play_url_arr['a'] = isset($b['data']['f']) ? $b['data']['f'] : '';
            }else{
                $a = file_get_contents($video_info['vvl_playurl_get']);
                $b = json_decode($a,true);
                if(count($b)>1 && isset($b['result']) && isset($b['result']['items'])){
                    //视频地址数量
                    $video_count = count($b['result']['items']);
                    if($video_count > 0){
                        foreach ($b['result']['items'] as $value) {
                            if(!empty($value['task_name']) && in_array($value['task_name'],array('流畅','超清','高清'))){
                                switch ($value['task_name']) {
                                    case 'liuchang':
                                        $tmp_play_url_arr['a'] = count($value['transcode']['urls'])?$value['transcode']['urls'][array_rand($value['transcode']['urls'])]:'';
                                        break;
                                    case 'gaoqing':
                                        $tmp_play_url_arr['b'] = count($value['transcode']['urls'])?$value['transcode']['urls'][array_rand($value['transcode']['urls'])]:'';
                                        break;
                                    case 'chaoqing':
                                        $tmp_play_url_arr['c'] = count($value['transcode']['urls'])?$value['transcode']['urls'][array_rand($value['transcode']['urls'])]:'';
                                        break;
                                }
                            }else{
                                switch($video_count){
                                    case 1:
                                        $tmp_play_url_arr['a'] = isset($b['result']['items'][0]['transcode']['urls']) ? $b['result']['items'][0]['transcode']['urls'][array_rand($b['result']['items'][0]['transcode']['urls'])] : ''; //流畅
                                        break;
                                    case 2:
                                        $tmp_play_url_arr['a'] = isset($b['result']['items'][0]['transcode']['urls']) ? $b['result']['items'][0]['transcode']['urls'][array_rand($b['result']['items'][0]['transcode']['urls'])] : ''; //流畅
                                        $tmp_play_url_arr['b'] = isset($b['result']['items'][1]['transcode']['urls']) ? $b['result']['items'][1]['transcode']['urls'][array_rand($b['result']['items'][1]['transcode']['urls'])] : ''; //高清
                                        break;
                                    case 3:
                                        $tmp_play_url_arr['a'] = isset($b['result']['items'][0]['transcode']['urls']) ? $b['result']['items'][0]['transcode']['urls'][array_rand($b['result']['items'][0]['transcode']['urls'])] : ''; //流畅
                                        $tmp_play_url_arr['b'] = isset($b['result']['items'][1]['transcode']['urls']) ? $b['result']['items'][1]['transcode']['urls'][array_rand($b['result']['items'][1]['transcode']['urls'])] : ''; //高清
                                        $tmp_play_url_arr['c'] = isset($b['result']['items'][2]['transcode']['urls']) ? $b['result']['items'][2]['transcode']['urls'][array_rand($b['result']['items'][2]['transcode']['urls'])] : ''; //超清
                                        break;
                                    case 4:
                                        $tmp_play_url_arr['a'] = isset($b['result']['items'][1]['transcode']['urls']) ? $b['result']['items'][1]['transcode']['urls'][array_rand($b['result']['items'][1]['transcode']['urls'])] : ''; //流畅
                                        $tmp_play_url_arr['b'] = isset($b['result']['items'][2]['transcode']['urls']) ? $b['result']['items'][2]['transcode']['urls'][array_rand($b['result']['items'][2]['transcode']['urls'])] : ''; //高清
                                        $tmp_play_url_arr['c'] = isset($b['result']['items'][3]['transcode']['urls']) ? $b['result']['items'][3]['transcode']['urls'][array_rand($b['result']['items'][3]['transcode']['urls'])] : ''; //超清
                                        break;
                                    default:
                                        $tmp_play_url_arr['a'] = isset($b['result']['items'][0]['transcode']['urls']) ? $b['result']['items'][0]['transcode']['urls'][array_rand($b['result']['items'][0]['transcode']['urls'])] : ''; //流畅
                                        $tmp_play_url_arr['b'] = isset($b['result']['items'][1]['transcode']['urls']) ? $b['result']['items'][1]['transcode']['urls'][array_rand($b['result']['items'][1]['transcode']['urls'])] : ''; //高清
                                        $tmp_play_url_arr['c'] = isset($b['result']['items'][2]['transcode']['urls']) ? $b['result']['items'][2]['transcode']['urls'][array_rand($b['result']['items'][2]['transcode']['urls'])] : ''; //超清
                                        break;

                                }
                            }
                        }
                    }
                }
            }
        }elseif($video_info['vvl_sourcetype']==7  && $video_info['vvl_playurl_get']!=''){//如果是aipai的
            $tmp_play_url_arr['a'] = $video_info['vvl_playurl_get'];
        }elseif($video_info['vvl_sourcetype']==9  && $video_info['vvl_playurl_get']!=''){
            $a = file_get_contents($video_info['vvl_playurl_get']);
            $b = json_decode($a,true);
            if(count($b)>1 && isset($b['data']) && isset($b['data']['splitInfo'])){
                $count = count($b['data']['splitInfo']);
                if($count == 1){
                    $tmp_play_url_arr['a'] = isset($b['data']['splitInfo'][0]['info'][0]['url'][array_rand($b['data']['splitInfo'][0]['info'][0]['url'])]) ? $b['data']['splitInfo'][0]['info'][0]['url'][array_rand($b['data']['splitInfo'][0]['info'][0]['url'])] : '';
                }elseif($count == 2){
                    $tmp_play_url_arr['a'] = isset($b['data']['splitInfo'][0]['info'][0]['url'][array_rand($b['data']['splitInfo'][0]['info'][0]['url'])]) ? $b['data']['splitInfo'][0]['info'][0]['url'][array_rand($b['data']['splitInfo'][0]['info'][0]['url'])] : '';
                    $tmp_play_url_arr['b'] = isset($b['data']['splitInfo'][1]['info'][0]['url'][array_rand($b['data']['splitInfo'][1]['info'][0]['url'])]) ? $b['data']['splitInfo'][1]['info'][0]['url'][array_rand($b['data']['splitInfo'][1]['info'][0]['url'])] : '';
                }elseif($count == 3){
                    $tmp_play_url_arr['a'] = isset($b['data']['splitInfo'][0]['info'][0]['url'][array_rand($b['data']['splitInfo'][0]['info'][0]['url'])]) ? $b['data']['splitInfo'][0]['info'][0]['url'][array_rand($b['data']['splitInfo'][0]['info'][0]['url'])] : '';
                    $tmp_play_url_arr['b'] = isset($b['data']['splitInfo'][1]['info'][0]['url'][array_rand($b['data']['splitInfo'][1]['info'][0]['url'])]) ? $b['data']['splitInfo'][1]['info'][0]['url'][array_rand($b['data']['splitInfo'][1]['info'][0]['url'])] : '';
                    $tmp_play_url_arr['c'] = isset($b['data']['splitInfo'][2]['info'][0]['url'][array_rand($b['data']['splitInfo'][2]['info'][0]['url'])]) ? $b['data']['splitInfo'][2]['info'][0]['url'][array_rand($b['data']['splitInfo'][2]['info'][0]['url'])] : '';
                }
            }
        }else{
            $tmp_play_url_arr['a'] = $video_info['vvl_playurl_get'];//高清
        }
    }

    $tmp_play_url_arr['a'] = (isset($tmp_play_url_arr['a']) && strlen($tmp_play_url_arr['a'])>10)?$tmp_play_url_arr['a']:'';
    $tmp_play_url_arr['b'] = (isset($tmp_play_url_arr['b']) && strlen($tmp_play_url_arr['b'])>10)?$tmp_play_url_arr['b']:'';
    $tmp_play_url_arr['c'] = (isset($tmp_play_url_arr['c']) && strlen($tmp_play_url_arr['c'])>10)?$tmp_play_url_arr['c']:'';

    //更新对应视频的播放次数（设置延迟更新，当播放数到达100更新一次）
    $play_key = 'video_play_num_'.intval($video_info['id']); //视频播放key
    $old_play_val = $mem_obj->get($play_key); //获取视频原始播放数
    $new_play_val = $old_play_val + 1;
    if(intval($new_play_val) >= 100){ //播放数缓存到达100的时候更新视频播放数
        $sql_update = 'UPDATE video_video_list SET vvl_count = vvl_count+100 WHERE id='.$video_info['id'];
        $conn->query($sql_update);
        $mem_obj->set($play_key,0,0);
    }else{
        $mem_obj->set($play_key,$new_play_val,0);
    }

    //获取视标签数组
    $tag_arr_key = 'tag_arr_'.$mydata['appid']; //视频标签缓存key 'tag_arr_' + 视频id
    $tag_arr = $mem_obj->get($tag_arr_key);
    if($tag_arr === false){
        $tag_arr = array();
        $sql = "SELECT `vtc_tag_id` FROM `video_tag_mapping` WHERE `v_id` = ".$mydata['appid'];
        $all_tag = $conn->find($sql);
        if(!empty($all_tag)){
            $temp_tag = array();
            foreach($all_tag as $atval){
                $temp_tag[] = $atval['vtc_tag_id'];
            }
            $temp_tag = array_filter(array_unique($temp_tag));

            //获取频道分类标签信息
            $temp_tag_str = implode(',',$temp_tag);
            $sql = "SELECT `vtc_id`,`vtc_name` FROM `video_game_tags` WHERE `vtc_type` = 2 AND `vtc_id` IN (".$temp_tag_str.")";
            $tag_info = $conn->find($sql);
            if(!empty($tag_info)){
                foreach($tag_info as $tival){
                    $tag_arr[] = array(
                        'id' => intval($tival['vtc_id']),
                        'title' => $tival['vtc_name'],
                        'type' => 1 //id类型（1：标签 2：主播 3：游戏 4：游戏分类）
                    );
                }
            }
        }
        $mem_obj->set($tag_arr_key,$tag_arr,14400);
    }

    //获取作者名称、头像
    $vp_author_data_key = 'vp_author_data_'.$video_info['vvl_uid'];
    $author_data = $mem_obj->get($vp_author_data_key);
    if($author_data === false){
        $sql = "SELECT `nickname`,`source`,`gender` FROM `uc_members` WHERE `uid` = ".intval($video_info['vvl_uid']);
        $author_data = $uconn->get_one($sql);
        $mem_obj->set($vp_author_data_key,$author_data,14400);
    }
    $author_gender = isset($author_data['gender']) ? intval($author_data['gender']) : 1;
    $author_name = isset($author_data['nickname']) ? $author_data['nickname'] : '网友';
    $author_img = UC_API.'/avatar.php?uid='.intval($video_info['vvl_uid']).'&type=real&size=big';

    //主播信息不为空
    if(!empty($author_data)){
        $tag_arr[] = array(
            'id' => intval($video_info['vvl_uid']),
            'title' => '主播：'.$author_name,
            'type' => 2 //id类型（1：标签 2：主播 3：游戏 4：游戏分类）
        );
    }

    //游戏id不为空
    $vp_game_data_key = 'vp_game_data_'.$video_info['vvl_game_id'];
    $game_data = $mem_obj->get($vp_game_data_key);
    if($game_data === false){
        $sql = "SELECT `id`,`gi_name`,`gi_type_id` FROM `video_game_info` WHERE `gi_isshow` = 1 AND `id` = ".intval($video_info['vvl_game_id']);
        $game_data = $conn->get_one($sql);
        $mem_obj->set($vp_game_data_key,$game_data,14400);
    }

    //游戏标签
    if(isset($game_data['id']) && !empty($game_data['id'])){
        $tag_arr[] = array(
            'id' => intval($game_data['id']),
            'title' => '游戏：'.$game_data['gi_name'],
            'type' => 3 //id类型（1：标签 2：主播 3：游戏 4：游戏分类）
        );
    }

    //游戏类型不为空
    if(isset($game_data['gi_type_id']) && !empty($game_data['gi_type_id'])){
        $tag_arr[] = array(
            'id' => intval($game_data['gi_type_id']),
            'title' => isset($all_game_type[$game_data['gi_type_id']]) ? ('分类：'.$all_game_type[$game_data['gi_type_id']]) : '',
            'type' => 4 //id类型（1：标签 2：主播 3：游戏 4：游戏分类）
        );
    }

    //获取主播视频数
    $vp_user_video_count_key = 'vp_user_video_count_'.$video_info['vvl_uid'];
    $user_video_num = $mem_obj->get($vp_user_video_count_key);
    if($user_video_num === false){
        $sql = "SELECT COUNT(1) AS num FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_uid` = ".intval($video_info['vvl_uid']);
        $user_video_num = $conn->count($sql);
        $mem_obj->set($vp_user_video_count_key,$user_video_num,14400);
    }

    //获取用户点赞点踩关联视频数组
    $user_up_arr_key = "xl_user_up_arr_".md5($check_where);
    $user_down_arr_key = "xl_user_down_arr_".md5($check_where);
    $user_up_arr = $mem_obj->get($user_up_arr_key); //点赞数组
    $user_down_arr = $mem_obj->get($user_down_arr_key); //点踩数组
    if($user_up_arr ===  false || $user_down_arr === false){
        $user_up_arr = array(); //点赞数组
        $user_down_arr = array(); //点踩数组

        $sql = "SELECT `v_id`,`oper_type` FROM `video_up_down` WHERE 1 ".$check_where;
        $user_ud_data = $conn->find($sql);

        if(!empty($user_ud_data)){
            foreach($user_ud_data as $ud_val){
                if($ud_val['oper_type'] == 1){
                    $user_up_arr[] = intval($ud_val['v_id']);
                }elseif($ud_val['oper_type'] == 2){
                    $user_down_arr[] = intval($ud_val['v_id']);
                }
            }
        }

        //设置缓存
        $mem_obj->set($user_up_arr_key,$user_up_arr,3600);
        $mem_obj->set($user_down_arr_key,$user_down_arr,3600);
    }

    //判断用户是否点赞点踩
    $has_up = 0;
    $has_down = 0;
    if(in_array($video_info['id'],$user_up_arr)){
        $has_up = 1;
    }elseif(in_array($video_info['id'],$user_down_arr)){
        $has_down = 1;
    }

    //获取视频点赞点踩数
    $video_up_down_key = 'video_up_num_data_key_'.$video_info['id'];
    $video_up_down = $mem_obj->get($video_up_down_key);
    if($video_up_down === false){
        $sql = "SELECT vvl_up_num,vvl_down_num FROM `video_video_list` WHERE `id` = ".$video_info['id'];
        $video_up_down = $conn->find($sql);
        $mem_obj->set($video_up_down_key,$video_up_down,1800);
    }
    $up_num = intval($video_up_down[0]['vvl_up_num']);
    $down_num = intval($video_up_down[0]['vvl_down_num']);

    //检查用户是否订阅该主播
    $subscribe = 0;
    if(!empty($sub_info)){
        foreach($sub_info as $sub){
            if($sub['subid'] == $video_info['vvl_uid'] && $sub['subtype'] == 2){
                $subscribe = 1;
            }
        }
    }

    //更新用户行为信息
    if(!empty($tag_arr)){
        foreach($tag_arr as $bval){
            //如果有改属性，则插入
            $sql = "SELECT `weight` FROM `video_user_behavior` WHERE `id_type` = ".intval($bval['type'])." AND `rele_id` = ".intval($bval['id']).$check_where;
            $check = $conn->find($sql);
            if(isset($check[0]['weight']) && !empty($check[0]['weight'])){
                $sql = "UPDATE video_user_behavior SET weight = weight + 1 WHERE `id_type` = ".intval($bval['type'])." AND `rele_id` = ".intval($bval['id']).$check_where;
                $conn->query($sql);
            }else{
                $save_arr = array(
                    'uid' => $mydata['uid'],
                    'mac' => $mydata['mac'],
                    'imei' => $mydata['imei'],
                    'id_type' => intval($bval['type']),
                    'rele_id' => intval($bval['id']),
                    'weight' => 1
                );

                $conn->save('video_user_behavior',$save_arr);
            }
        }
    }

    $returnArr['rows'][0] = array(
        'appid' => intval($video_info['id']),//视频ID，自增
        'title' => filter_search(delete_html($video_info['vvl_title'])),//视频标题
        'source' => isset($video_info['vvl_sourcetype']) ? (isset($GLOBALS['SOURCE_ARR'][$video_info['vvl_sourcetype']]) ? $GLOBALS['SOURCE_ARR'][$video_info['vvl_sourcetype']] : '') : '',
        'anchorid' => intval($video_info['vvl_uid']), //主播id
        'gameid' => intval($video_info['vvl_game_id']), //游戏id
        'imgurl' => $tmp_img_url,//视频图片
        'sourcetype' => intval($video_info['vvl_sourcetype']),//视频来源类型
        'videoid' => $video_info['vvl_video_id'],//抓取视频的ID
        'playnum' => intval($video_info['vvl_count']) + intval($new_play_val),//视频本地播放数 + 缓存数
        'playurl' => $tmp_play_url_arr, //播放地址
        'videonum' => intval($user_video_num), //主播视频数
        'authorname' => $author_name, //作者名称
        'authorimg' => $author_img, //作者头像
        'likenum' => $up_num, //视频点赞数
        'unlikenum' => $down_num, //视频点踩数
        'haslike' => $has_up, //是否点赞过该视频 1：是 0：否
        'hasunlike' => $has_down, //是否点踩过该视频 1：是 0：否
        'authorgender' => isset($author_gender) ? $author_gender : 1, //主播性别（1：男 2：女 3：未知）
        'subid' => intval($video_info['vvl_uid']), //订阅id
        'subtype' => 2, //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
        'subscribe' => $subscribe, //是否订阅（1：已订阅 0：未订阅）
        'tag_arr' => $tag_arr, //相关标签
        'timestamp' => isset($video_info['vvl_upload_time']) ? $video_info['vvl_upload_time'] : 0, //时间戳
        'time' => isset($video_info['vvl_upload_time']) ? (empty($video_info['vvl_upload_time']) ? '' : date('Y-m-d',$video_info['vvl_upload_time'])) : date('Y-m-d',$video_info['in_date']) //采集时间
    );

}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);