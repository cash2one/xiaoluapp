<?php

/**
 * @copyright: @快游戏 2015
 * @description: 上传阿里百川视频
 * @file:save_ali_video_info.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-12-23  10:28
 * @version 1.0
 **/

include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['title'] = get_param('title');//视频标题
$mydata['url'] = get_param('url');//阿里播放地址
$mydata['packagename'] = get_param('packagename'); //游戏包名
$mydata['time'] = microtime_float(); //上传时间，毫秒级别
$mydata['mac'] = get_param('mac'); //mac地址
$mydata['size'] = intval(get_param('size')); //视频大小
$mydata['fmd5'] = get_param('fmd5'); //文件md5地址
$mydata['tags'] = get_param('tags'); //tag数组，多个用，号隔开
$mydata['uid'] = intval(get_param('uid')); //用户id
$mydata['vtime'] = get_param('vtime'); //视频时长
$mydata['mapurl'] = get_param('mapurl'); //视频地图地址
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$is_null = strstr($mydata['url'],'null.mp4');
$video_ext = pathinfo($mydata['url'],PATHINFO_EXTENSION);
if(!empty($is_null) || $video_ext != 'mp4'){
    exit(ResponseJson(array('code'=>20001,'msg'=>'上传地址错误')));
}

$sql = "SELECT `id` FROM `video_video_list` WHERE `vvl_server_url` = '".$mydata['url']."' ";
$check_title = $conn->find($sql);
if(isset($check_title[0]['id']) && !empty($check_title[0]['id'])){
    exit(ResponseJson(array('code'=>20002,'msg'=>'该视频已上传，请勿重复上传!')));
}

//包名替换（去除后缀.kyx）
$mydata['packagename'] = str_replace('.kyx','',$mydata['packagename']);

$mem_obj = new kyx_memcache();

//获取包名对应游戏id
$game_id_key = 'game_id_package_key_'.$mydata['packagename'];
$game_id = $mem_obj->get($game_id_key);
if($game_id === false){
    $game_sql = "SELECT `id` FROM `video_game_info` WHERE `gi_packname` = '".$mydata['packagename']."'";
    $game_data = $conn->get_one($game_sql);
    $game_id = isset($game_data['id']) ? intval($game_data['id']) : 0;
    $mem_obj->set($game_id_key,$game_id,3600);
}

$tag_arr_key = 'all_tag_arr_key';
$tag_arr = $mem_obj->get($tag_arr_key);
if($tag_arr === false){
    $sql = "SELECT `cha_id`,`category_id`,`tag_id`,`game_id` FROM `mzw_video_tag_relation`";
    $all_rela = $conn->find($sql);

    //获取 游戏=>频道 频道=>分类 关系
    $tag_arr = array();
    $channel_category = array();
    if(!empty($all_rela)){
        foreach($all_rela as $key => $val){
            $tag_arr[$val['game_id']][$val['cha_id']] = $val['cha_id'];
            if(!empty($val['category_id']) && empty($val['tag_id'])){
                $channel_category[$val['cha_id']][] = $val['category_id'];
            }
        }
    }

    //组装频道分类关系数组
    foreach($tag_arr as $key => $val){
        //频道下的分类
        foreach($val as $ckey => $cval){
            $temp_category = isset($channel_category[$ckey]) ? $channel_category[$cval] : array();
            $tag_arr[$key][$ckey] = $temp_category;
        }
    }

    $mem_obj->set($tag_arr_key,$tag_arr,14400);
}

//获取视频图像
$filename = isset($_FILES['img']['name']) ? basename( $_FILES['img']['name']) : '';
if(empty($filename)){
    exit(ResponseJson(array('code'=>20003,'msg'=>'上传视频图片不存在')));
}
$target_path = LOCAL_AVATAR_PATH."/ali/".date('Y/m/d/');//用户头像目录
if(!is_dir($target_path)){
    create_my_file_path($target_path);
}

$ext = pathinfo($filename,PATHINFO_EXTENSION);
$basename= md5($mydata['url']);
$pic_full_path = $target_path . $basename. '.'.$ext;

if(!in_array($ext,array('jpg','png'))){
    exit(json_encode(array('code'=>20004,'msg'=>'文件必须为JPG,PNG格式')));
}

if(move_uploaded_file($_FILES['img']['tmp_name'], $pic_full_path)) {

    if(is_file($pic_full_path)){
        $pic_url = 'http://' .$_SERVER['HTTP_HOST'] . '/uc_client/data'. str_replace(LOCAL_AVATAR_PATH, '', $pic_full_path);
        $data = file_get_contents($pic_url);
        $im = imagecreatefromstring($data);
        if($im == false){
            exit(json_encode(array('code'=>20005,'msg'=>'不是正常的图片文件')));
        }
        unset($data);

        //生产环境抓取图片的接口
//        $get_img_url = 'http://test.admin.kuaiyouxi.com/cli_php/auto_load/upload_ali_img.php';
        $get_img_url = 'http://ksadmin.youxilaile.com/cli_php/auto_load/upload_ali_img.php';
        $arr_img = array('img_url'=>$pic_url);

        //调用后台上传阿里百川视频图片处理接口
        $json = curl_post($get_img_url,$arr_img);
        $arr = json_decode($json,TRUE);

        $img = '';
        if($arr['code']==200){
            $img = $arr['msg'];
        }

        unlink($pic_full_path);

        $rows = array(
            'in_date' => intval($mydata['time']),
            'vvl_game_id' => $game_id,
            'vvl_hi_id' => 0,
            'vvl_category_id' => 0,
            'vvl_type_id' => 4,
            'vvl_sourcetype' => 14,
            'vvl_imgurl' => '',
            'vvl_imgurl_get' => $img,
            'vvl_time' => $mydata['vtime'],
            'vvl_playurl' => '',
            'vvl_playurl_get' => '',
            'vvl_author_id' => 0,
            'vvl_title' => $mydata['title'],
            'vvl_playurlback' => '',
            'vvl_playurlback_get' => '',
            'vvl_playcount' => 0,
            'vvl_count' => 0,
            'vvl_sort' => 0,
            'va_isshow' => 2,
            'vvl_sort_sys' => 0,
            'vvl_comment_num' => 0,
            'vvl_video_id' => md5($mydata['url']),
            'vvl_gv_id' => 0,
            'vvl_server_url' => $mydata['url'],
            'vvl_medium_server_url' => '',
            'vvl_low_server_url' => '',
            'vvl_package_name' => $mydata['packagename'],
            'vvl_easy' => 0,
            'vvl_title_en' => '',
            'vvl_last_day_plays' => 0,
            'vvl_last_week_plays' => 0,
            'vvl_last_month_plays' => 0,
            'vvl_tags' => 0,
            'vvl_update_time' => time(),
            'vvl_recommend' => 0,
            'vvl_uid' => intval($mydata['uid']),
            'vvl_mac' => $mydata['mac'],
            'vvl_md5file' => $mydata['fmd5'],
            'vvl_size' => $mydata['size'],
            'vvl_upload_time' => time(),
            'vvl_map_url' => $mydata['mapurl']
        );

        $res = $conn->save('video_video_list', $rows);
        if($res){
            if($res > 0 || !empty($mydata['tags'])){
                //保存关联标签
                $rela_tags_arr = json_decode($mydata['tags']);
                if(!empty($rela_tags_arr)){
                    //对应游戏关联标签关系数组
                    $game_tags_arr = isset($tag_arr[$game_id]) ? $tag_arr[$game_id] : '';
                    if(!empty($game_tags_arr)){
                        foreach($rela_tags_arr as $rtval){
                            foreach($game_tags_arr as $gtkey => $gtval){
                                if(in_array($rtval,$gtval)){
                                    $tag_data = array(
                                        'v_id' => intval($res),
                                        'game_id' => intval($game_id),
                                        'vtc_cha_id' => intval($gtkey),
                                        'vtc_category_id' => intval($rtval),
                                        'vtc_tag_id' => 0
                                    );

                                    $map_res = $conn->save('mzw_video_tag_mapping', $tag_data);
                                }
                            }
                        }
                    }
                }
            }

            exit(json_encode(array('code'=>200,'msg'=>'上传成功')));
        }else{
            exit(json_encode(array('code'=>20006,'msg'=>'上传失败')));
        }
    }
}