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

/*参数*/
$mydata = array();
$mydata['appid'] = intval(get_param('appid'));//视频ID
$mydata['gamepackagename'] = get_param('gamepackagename'); //游戏包名
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

if($mydata['appid'] < 1){//如果视频ID为空，则出错
    echo('error! appid is empty!!');
    exit;
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

//初始化数组
$returnArr = array();

//查数据
$sql_data = "SELECT `id`,`vvl_title`,`vvl_playurl`,`vvl_playurl_get`,`vvl_time`,`vvl_count`,`vvl_server_url`,
		     `vvl_sourcetype`,`vvl_imgurl_get`,`vvl_imgurl`,`vvl_type_id`,`vvl_author_id`,`vvl_category_id`,
		     `vvl_video_id`,`vvl_game_id` FROM `video_video_list` WHERE `id` = ".$mydata['appid'] ;
$video_info = $conn->get_one($sql_data);
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
    $mem_obj = new kyx_memcache();
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

    //广告位id
    $adp_id = 0;
    if($video_info['vvl_game_id'] == 12){ //我的世界故事模式广告
        $adp_id = 53;
    }elseif($video_info['vvl_game_id'] == 2){ //我的世界广告
        $adp_id = 52;
    }

    $returnArr['rows'][0] = array(
        'appid' => intval($video_info['id']),//视频ID，自增
        'typeid' => intval($video_info['vvl_type_id']),//视频类型（1任务，2解说，3赛事战况，4集锦）
        'title' => filter_search(delete_html($video_info['vvl_title'])),//视频标题
        'categoryid' => intval($video_info['vvl_category_id']), //视频联赛ID(来自video_category_info表)
        'imgurl' => $tmp_img_url,//视频图片
        'adpid' => $adp_id, //广告位id
        'playnum' => intval($video_info['vvl_count']) + intval($new_play_val),//视频本地播放数 + 缓存数
        'sourcetype' => intval($video_info['vvl_sourcetype']),//视频来源类型
        'videoid' => $video_info['vvl_video_id'],//抓取视频的ID
        'playurl' => $tmp_play_url_arr//播放地址
    );

    if(!empty($mydata['gamepackagename'])){

        $video_play_game_info_key = 'video_play_game_info_'.$mydata['gamepackagename']; //视频详情播放游戏数据缓存key  'video_play_game_info_' + 游戏包名
        $game_data_temp = $mem_obj->get($video_play_game_info_key); //视频详情播放游戏数据
        if($game_data_temp === false){
            //根据包名获取游戏信息
            $sql = "SELECT gv_id,gv_title,gv_type_id,gv_ico_key FROM mzw_game_version WHERE gv_status=1 AND gv_package_name = '".$mydata['gamepackagename']."' ORDER BY gv_id DESC LIMIT 1";
            $game_data = $conn->get_one($sql);

            //没有找到上架的游戏信息，取下架的最大游戏信息
            if(empty($game_data)){
                $sql = "SELECT gv_id,gv_title,gv_type_id,gv_ico_key FROM mzw_game_version WHERE gv_package_name = '".$mydata['gamepackagename']."' ORDER BY gv_id DESC LIMIT 1";
                $game_data = $conn->get_one($sql);
            }

            if(!empty($game_data)){

                //获取游戏ICO地址（175 * 175）
                $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
                    LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                    WHERE A.gv_id = '.$game_data['gv_id'].' AND A.type = 7 AND B.size_id = 22 ORDER BY B.id DESC';
                $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                $iconpath = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

                //如果没找到175*175的ICO图标，则去100*100的ICO图标
                if(empty($iconpath)){
                    $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
                        LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$game_data["gv_ico_key"]
                        ."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
                    $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                    if($tmp_game_ico_arr){
                        $iconpath = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr["img_path"]);
                    }
                }

                //获取游戏分类属性
                $tmp_sql = 'SELECT t_name_cn as name FROM mzw_game_type WHERE t_id='.$game_data["gv_type_id"];
                $tmp_type = $conn->get_one($tmp_sql);
                $category = isset($tmp_type['name']) ? $tmp_type['name'] : '';

                //游戏下载地址
                $tmp_sql = "SELECT gv_id,mgd_game_size,mgd_mzw_server_url
                            FROM mzw_game_downlist
                            WHERE mgd_package_type!=2 AND gv_id = ".$game_data['gv_id']." ORDER BY mgd_id DESC,mgd_game_size DESC";
                $tmp_down   = $conn->get_one($tmp_sql);
                $size = isset($tmp_down['mgd_game_size']) ? intval($tmp_down['mgd_game_size']) : 0;
                $url = isset($tmp_down['mgd_mzw_server_url']) ? (CDN_LESHI_URL_DOWN.$tmp_down['mgd_mzw_server_url']) : '';

                $game_data_temp = array(
                    'title' => $game_data['gv_title'],
                    'iconpath' => $iconpath,
                    'category' => $category,
                    'size' => $size,
                    'url' => $url
                );
                $mem_obj->set($video_play_game_info_key,$game_data_temp,3600);
            }
        }

        if(!empty($game_data_temp)){
            //属性重新拼装
            $returnArr['rows'][0]['gametitle'] = $game_data_temp['title'];
            $returnArr['rows'][0]['iconpath'] = $game_data_temp['iconpath'];
            $returnArr['rows'][0]['category'] = $game_data_temp['category'];
            $returnArr['rows'][0]['size'] = $game_data_temp['size'];
            $returnArr['rows'][0]['url']  = $game_data_temp['url'];
        }

    }
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);