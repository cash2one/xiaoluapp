<?php
/**
 * @copyright: @快游戏 2014
 * @description: 更新添加游戏关联信息（游戏包名）
 * @file: update_game_rele_info.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-02-18 11:25
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

$type_id = intval(get_param('category_id')); //游戏关联分类id
$firm_name = get_param('company'); //厂商名称
$game_name = get_param('name'); //游戏名称
$game_en_name = get_param('en_name'); //游戏英文名称
$game_package = get_param('package_name'); //游戏包名
$game_ico = get_param('icon_url'); //游戏图标
$game_desc = get_param('intro'); //游戏描述
$game_down = get_param('download_url'); //游戏下载地址
$game_version = get_param('version'); //游戏版本号
$game_size = intval(get_param('size')); //游戏大小
$update_time = intval(get_param('publish_time')); //更新时间（时间戳）
$game_screen = get_param('screenshot'); //游戏截图（json字符串，多张）
$game_tag = get_param('tag'); //游戏标签
$game_screen = stripslashes($game_screen);


//常量地址定义
$local_url = BACK_URL;
$img_path = $local_url.'/uploads/img';

if(empty($game_name) || empty($game_package)){
    $str_encode = responseJson(array('code' => 0));
    exit($str_encode);
}

//获取分类名称
$sql = "SELECT `t_name_cn` FROM `video_game_type` WHERE `t_id` = ".$type_id;
$type_data = $conn->find($sql);
$type_name = isset($type_data[0]['t_name_cn']) ? $type_data[0]['t_name_cn'] : '';

//需要过滤掉包名后缀
$filter_arr = array('mi','game','xiaomi','games','tmgp','free','google','mm','freefull','full','hd','fulla');

//包名过滤
$extend = pathinfo($game_package);
$extend = strtolower($extend["extension"]);
if(!empty($extend) && in_array($extend,$filter_arr)){
    $extend = '.'.$extend;
    $game_package = str_replace($extend,'',$game_package);
}

//初始常量定义
$get_img_url = $local_url.'/cli_php/auto_load/upload_grab_img.php';
$game_ico_get = ''; //游戏本地ico图标
$firm_id = 0; //厂商id
$game_screen_get = ''; //游戏本地截图json串
$game_id = 0;

//检查厂商是否已存在
if(!empty($firm_name)){
    $sql = "SELECT `f_id` FROM `video_game_firm` WHERE `f_is_hide` = 1 AND `f_name_cn` = '".$firm_name."' LIMIT 1";
    $check = $conn->find($sql);
    if(isset($check[0]['f_id']) && !empty($check[0]['f_id'])){
        $firm_id = intval($check[0]['f_id']);
    }else{
        $save_arr = array(
            'f_name_cn' => $firm_name,
            'f_name_en' => '',
            'f_is_hide' => 1
        );
        $firm_id = $conn->save('video_game_firm',$save_arr);
    }
}

//检查是否存在该游戏（包名相同）
$sql = "SELECT `id`,`gi_firm_id`,`gi_type_id`,`gi_game_ico_get`,`gi_game_screen_get`
        FROM `mzw_game_package_info` WHERE `gi_packname` = '".$game_package."' LIMIT 1";
$check = $conn->find($sql);

//有已存在的包名游戏（更新对应缺失数据）
if(isset($check[0]['id']) && !empty($check[0]['id'])){

    //游戏id
    $game_id = intval($check[0]['id']);

    //固定更新参数添加
    $update_arr = array(
        'id' => intval($check[0]['id']),
        'gi_desc' => $game_desc,
        'gi_down_path' => $game_down,
        'gi_version' => $game_version,
        'gi_size' => $game_size,
        'gi_update_time' => $update_time,
        'gi_source' => 2
    );

    //检查厂商是否需要更新
    if(isset($check[0]['gi_firm_id']) && empty($check[0]['gi_firm_id'])){
        $update_arr['gi_firm'] = $firm_name;
        $update_arr['gi_firm_id'] = $firm_id;
    }

    //检查分类是否需要更新
    if(isset($check[0]['gi_type_id']) && empty($check[0]['gi_type_id'])){
        $update_arr['gi_type'] = $type_name;
        $update_arr['gi_type_id'] = $type_id;
    }

    //检查游戏ico图片是否需要更新
    if(isset($check[0]['gi_game_ico_get']) && empty($check[0]['gi_game_ico_get'])){
        //游戏ico上传
        if(!empty($game_ico)){
            $update_arr['gi_game_ico'] = $game_ico;
            $arr_img = array('img_url'=>$game_ico,'type' => 4);

            //调用后台上传阿里百川视频图片处理接口
            $json = curl_post($get_img_url,$arr_img);
            $arr = json_decode($json,TRUE);

            if($arr['code']==200){
                $update_arr['gi_game_ico_get'] = $arr['msg'];
            }
        }
    }

    //检查游戏截图是否需要更新
    if(isset($check[0]['gi_game_screen_get']) && empty($check[0]['gi_game_screen_get'])){
        //游戏截图上传
        if(!empty($game_screen)){
            $update_arr['gi_game_screen'] = $game_screen;
            $arr_img = array('img_url'=>$game_screen,'type' => 5,'batch' => 1);

            //调用后台上传阿里百川视频图片处理接口
            $json = curl_post($get_img_url,$arr_img);
            $arr = json_decode($json,TRUE);

            if($arr['code']==200){
                $update_arr['gi_game_screen_get'] = $arr['msg'];
            }
        }
    }

    $conn->update('mzw_game_package_info',$update_arr);

}else{ //不存在相应的包名游戏，插入数据

    //游戏ico上传
    if(!empty($game_ico)){
        $arr_img = array('img_url'=>$game_ico,'type' => 4);

        //调用后台上传阿里百川视频图片处理接口
        $json = curl_post($get_img_url,$arr_img);
        $arr = json_decode($json,TRUE);

        if($arr['code']==200){
            $game_ico_get = $arr['msg'];
        }
    }

    //游戏截图上传
    if(!empty($game_screen)){
        $arr_img = array('img_url'=>$game_screen,'type' => 5,'batch' => 1);

        //调用后台上传阿里百川视频图片处理接口
        $json = curl_post($get_img_url,$arr_img);
        $arr = json_decode($json,TRUE);

        if($arr['code']==200){
            $game_screen_get = $arr['msg'];
        }
    }

    $save_arr = array(
        'gi_name' => $game_name,
        'gi_name_en' => $game_en_name,
        'gi_packname' => $game_package,
        'gi_isshow' => 1,
        'gi_firm' => $firm_name,
        'gi_firm_id' => $firm_id,
        'gi_type' => $type_name,
        'gi_type_id' => $type_id,
        'gi_game_id' => 0,
        'gi_game_ico' => $game_ico,
        'gi_game_ico_get' => $game_ico_get,
        'gi_desc' => $game_desc,
        'gi_down_path' => $game_down,
        'gi_version' => $game_version,
        'gi_size' => $game_size * 1024,
        'gi_update_time' => $update_time,
        'gi_game_screen' => $game_screen,
        'gi_game_screen_get' => $game_screen_get,
        'gi_source' => 2
    );

    $game_id = $conn->save('mzw_game_package_info',$save_arr);
}

//打标签
if(!empty($game_tag)){
    //视频游戏注册
    $sql = "SELECT `id` FROM `video_game_info` WHERE `gi_name` = '".$game_name."' LIMIT 1";
    $check = $conn->find($sql);
    if(isset($check[0]['id']) && !empty($check[0]['id'])){
        $game_id = intval($check[0]['id']);
    }else{

        //获取游戏拼音
        $pinyin = '';
        if(!empty($game_name)){
            $pinyin_temp = '';
            $temp_game_name = $game_name;
            if(preg_match('/[0-9A-Za-z]{1,}/', $game_name,$match)){
                $pinyin_temp = $match[0];
                $temp_game_name = str_replace($pinyin_temp,'org',$game_name);
                $temp_game_name = strtolower($temp_game_name);
            }

            $pinyin = pinyin($temp_game_name);
            $pinyin = str_replace('org',$pinyin_temp,$pinyin);
        }

        $save_arr = array(
            'gi_name' => $game_name,
            'gi_packname' => $game_package,
            'gi_logo' => $game_ico_get,
            'gi_bg_img' => '',
            'gi_intro' => $game_desc,
            'gi_in_uid' => 1,
            'gi_in_name' => 'admin',
            'gi_order' => 0,
            'gi_simple_txt' => '',
            'gi_download_url' => '',
            'gi_isshow' => 1,
            'gi_created' => time(),
            'gi_firm_id' => $firm_id,
            'gi_type_id' => $type_id,
            'gi_pingyin' => $pinyin
        );

        $game_id = $conn->save('video_game_info',$save_arr);
    }

    //标签
    $tag_arr = explode(',',$game_tag);
    foreach($tag_arr as $tval){
        if($tval <> $game_name){
            //查询标签是否存在
            $sql = "SELECT `vtc_id` FROM `video_game_tags` WHERE `vtc_type` = 1 AND `vtc_status` = 1 AND `vtc_name` = '".$tval."'";
            $check = $conn->find($sql);
            if(isset($check[0]['vtc_id']) && !empty($check[0]['vtc_id'])){
                $tag_id = intval($check[0]['vtc_id']);
            }else {
                $save_arr = array(
                    'vtc_name' => $tval,
                    'vtc_user_name' => 'admin',
                    'vtc_user_id' => 1,
                    'vtc_type' => 1,
                    'vtc_create_time' => time(),
                    'vtc_update_time' => '',
                    'vtc_status' => 1,
                    'vtc_start_recom' => 2
                );
                $tag_id = $conn->save('video_game_tags', $save_arr);
            }

            //更新游戏标签关系
            $sql = "SELECT `game_id` FROM `video_game_tag_mapping` WHERE `game_id` = ".$game_id." AND `vtc_tag_id` = ".$tag_id." LIMIT 1";
            $check = $conn->find($sql);
            if(!isset($check[0]['game_id']) || empty($check[0]['game_id'])){
                $save_arr = array(
                    'game_id' => $game_id,
                    'vtc_tag_id' => $tag_id
                );

                $conn->save('video_game_tag_mapping',$save_arr);
            }
        }
    }
}

$str_encode = responseJson(array('code' => 1,'game_id' => intval($game_id)));
exit($str_encode);



