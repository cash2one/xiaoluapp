<?php
/**
 * @copyright: @快游戏 2014
 * @description: 视频播放（解析视频播放地址）
 * @file: video_play.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-05-04  12:20
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once(WEBPATH_DIR."include".DS."video_parser.php");//视频解析处理
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['appid'] = intval(get_param('appid'));//视频ID

if($mydata['appid']<1){//如果视频ID为空，则出错
    echo('error! appid is empty!!');
    exit;
}

$mydata['cpu']=get_param('cpu');//CPU参数
$mydata['gpu']=get_param('gpu');//GPU参数
$mydata['source']=get_param('source');//访问来源
$mydata['locale'] = get_param('locale');//言语版本
$mydata['density']=get_param('density');//分辨率
$mydata['brand']=get_param('brand');//品牌
$mydata['model']=get_param('model');//型号
$mydata['mac']=get_param('mac');//MAC地址

//记录播放日志
$tmp_str = json_encode($mydata).chr(13).chr(10);
write_file_random($tmp_str,"video_play",true,date('Ymd',time()));
unset($tmp_str);



//查数据
$sql_data = "SELECT id,vvl_title,vvl_playurl,vvl_playurl_get,vvl_time,vvl_count,
		vvl_sourcetype,vvl_imgurl_get,vvl_imgurl,vvl_type_id,vvl_author_id,vvl_category_id,vvl_video_id ".
    " FROM `video_video_list` WHERE `id`=".$mydata['appid'] ;
//die($sql_data);
$data = $conn->find($sql_data);
if(count($data)>0){
    $video_info = $data[0];

    if(!is_empty($video_info['vvl_imgurl_get'])){
        $tmp_img_url = LOCAL_URL_DOWN_IMG.$video_info['vvl_imgurl_get'];
    }else{
        $tmp_img_url = $video_info['vvl_imgurl'];
    }
    //按视频类型判断视频小分类的ID
    //视频类型（1任务，2解说，3赛事战况，4集锦）
    if($video_info['vvl_type_id']==1){//1任务
        $tmp_category_id = $video_info['vvl_category_id'];
    }elseif($video_info['vvl_type_id']==2){//2解说
        $tmp_category_id = $video_info['vvl_author_id'];
    }else{
        $tmp_category_id = $video_info['vvl_category_id'];
    }

    /*视频的来源
     *     	$source_arr = array(
                'youku' => 1,
                'letv' => 2,
                'sohu' => 3,
                'qq' => 4,
                'tudou' => 5,
            );
     */
    $tmp_play_url_arr = array();

    //如果本地视频地址不为空
    if(!empty($video_info['vvl_server_url'])){
        if($video_info['vvl_sourcetype'] == 14){
            $tmp_play_url_arr['a'] = $video_info['vvl_server_url'];
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

            $tmp_play_url_arr['a'] = $video_info['vvl_playurl_get'];
        }
    }

    $tmp_play_url_arr['a'] = (isset($tmp_play_url_arr['a']) && strlen($tmp_play_url_arr['a'])>10)?$tmp_play_url_arr['a']:'';
    $tmp_play_url_arr['b'] = (isset($tmp_play_url_arr['b']) && strlen($tmp_play_url_arr['b'])>10)?$tmp_play_url_arr['b']:'';
    $tmp_play_url_arr['c'] = (isset($tmp_play_url_arr['c']) && strlen($tmp_play_url_arr['c'])>10)?$tmp_play_url_arr['c']:'';

    //更新对应视频的播放次数（设置延迟更新，当播放数到达1000更新一次）
    $mem_obj = new kyx_memcache();
    $play_key = 'video_play_num_'.intval($video_info['id']); //视频播放key
    $old_play_val = $mem_obj->get($play_key); //获取视频原始播放数
    $new_play_val = $old_play_val + 1;
    if(intval($new_play_val) >= 100){ //播放数缓存到达1000的时候更新视频播放数
        $sql_update = 'UPDATE video_video_list SET vvl_count = vvl_count+100 WHERE id='.$video_info['id'];
        $conn->query($sql_update);
        $mem_obj->set($play_key,0,0);
    }else{
        $mem_obj->set($play_key,$new_play_val,0);
    }

    $arr = array(
        'appid'=>$video_info['id'],//视频ID，自增
        'typeid'=>$video_info['vvl_type_id'],//视频类型（1任务，2解说，3赛事战况，4集锦）
        'categoryid'=>$tmp_category_id,//视频联赛ID(来自video_category_info表)'
        'title'=>$video_info['vvl_title'],//视频标题
        'imgurl'=>$tmp_img_url,//视频图片
        'playurl'=>$tmp_play_url_arr,//播放地址
        'sourcetype' => $video_info['vvl_sourcetype'],//视频来源类型
        'videoid'=>$video_info['vvl_video_id'],//抓取视频的ID
    );

    $returnArr['rows'][]=$arr;
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
    echo($sql_data);
    var_dump($returnArr);
    exit;
}
$str_encode = responseJson($returnArr,false);
exit($str_encode);