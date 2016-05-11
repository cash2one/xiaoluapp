<?php
/**
 * @copyright: @快游戏 2014
 * @description: 更新添加视频
 * @file: update_video_rele_info.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-02-18 11:25
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');
include_once("../uc_client/client.php");

$vid = get_param('vid'); //关联视频id
$title = get_param('title'); //关联视频id
$game_name = get_param('game_name'); //游戏名称
$status = intval(get_param('status')); //是否能播(status = 3)
//$game_ico = get_param('icon_url'); //游戏图标
$category_name = get_param('album_title'); //专辑名称
$author_name = get_param('user_name'); //用户名
$author_ico = get_param('user_avatar'); //用户头像
$author_sex = intval(get_param('user_gender')); //用户性别（1：男 2：女 3：未知）
$author_desc = get_param('user_desc'); //用户描述
$img = get_param('thumbnail'); //视频图片
$duration = intval(get_param('duration')); //视频时长
$play_num = intval(get_param('vv')); //视频播放数
$source_type = intval(get_param('source_type')); //视频来源
$source_type = empty($source_type) ? 1 : $source_type;
$source_url = get_param('source_url'); //采集地址
$video_tag = get_param('tag'); //视频标签
$swf_url = get_param('swf_url'); //swf地址
$mp4_url = get_param('mp4_url'); //mp4地址
$public_time = intval(get_param('publish_time')); //视频发布时间

$local_url = BACK_URL;
$img_path = $local_url.'/uploads/img';

//$arr[] = array(
//    'time' => '1447776000', //采集时间
//    'title' => '测试视频标题1', //视频标题
//    'game_name' => '我的世界', //游戏名称（计算关联游戏）
//    'category_name' => '创意建筑1', //专辑名称（计算关联专辑）
//    'author_name' => '苏丶小丶妍1', //作者名称（计算关联作者）
//    'author_ico' => 'http://g3.ykimg.com/0130391F4855387FC6F0FA05A1121EDD957C1C-DDEB-333B-D3D4-CFCBC5D0A40B', //作者头像地址
//    'author_desc' => '作者描述', //作者描述
//    'author_sex' => 1, //作者性别（1：男 2：女 3：未知）
//    'source_type' => 1, //来源
//    'img' => 'http://g2.ykimg.com/0100641F4653F361396028095CD25C5D55520A-D8E1-7CF3-5201-BD610E242840', //视频图片
//    'duration' => 1000, //视频时长，秒级
//    'grab_url' => 'http://lol.duowan.com/1310/245112043649.html', //采集地址
//    'url' => '', //优酷播放swf地址
//    'play_count' => 114654, //采集播放次数
//    'video_id' => 'XODg5MzgzNDIo', //关联id（优酷关联ID）
//    'server_url' => 'http://w5.dwstatic.com/8/7/1603/762933-102-1453641245.mp4', //本地播放地址（MP4直接播放地址等）
//);
//


if(empty($title)){
    $str_encode = responseJson(array('code' => 0));
    exit($str_encode);
}

$area_arr = array();
$sql = "SELECT `title`,`rela_id` FROM `video_default_nav_info` WHERE `nav_type` = 3";
$temp_arr = $conn->find($sql);
if(!empty($temp_arr)){
    foreach($temp_arr as $val){
        $area_arr[] = $val['title'];
        $area_id_arr[$val['title']] = intval($val['rela_id']);
    }
}

//根据游戏名称获取关联游戏id
$game_id = 0;
$game_package = '';
$game_type = 0;
if(!in_array($game_name,$area_arr)){
    if(!empty($game_name)){

        $sql = "SELECT `id`,`gi_packname`,`gi_type_id`,`gi_logo` FROM `video_game_info` WHERE `gi_isshow` = 1 AND `gi_name` = '".$game_name."' ORDER BY id ASC LIMIT 1";
        $game_data = $conn->find($sql);

        if(!empty($game_data)){
            $game_id = intval($game_data[0]['id']);
            $game_package = $game_data[0]['gi_packname'];
            $game_type = intval($game_data[0]['gi_type_id']);
//        $temp_game_ico = $game_data[0]['gi_logo'];

            //游戏图标没有的话，更新游戏图标
//        if(empty($temp_game_ico)){
//            $get_img_url = $local_url.'/cli_php/auto_load/upload_grab_img.php';
//            $arr_img = array('img_url'=>$game_ico,'type' => 4);
//
//            //调用后台上传阿里百川视频图片处理接口
//            $json = curl_post($get_img_url,$arr_img);
//            $arr = json_decode($json,TRUE);
//
//            $temp_img = '';
//            if($arr['code']==200){
//                $temp_img = $arr['msg'];
//                $pic_url = $img_path.$temp_img;
//            }
//
//            //更新游戏图片信息
//            $update_arr = array(
//                'id' => $game_id,
//                'gi_logo' => $pic_url
//            );
//            $conn->update('video_game_info',$update_arr);
//        }

        }else{
            //查看是否游戏包名表是否有相关标题游戏信息
            $sql = "SELECT `gi_packname`,`gi_firm_id`,`gi_type_id` FROM `mzw_game_package_info` WHERE `gi_isshow` = 1 AND `gi_name` = '".$game_name."' ORDER BY `gi_game_id` DESC,`id` DESC LIMIT 1";
            $temp_game_data = $conn->find($sql);

            //关联游戏分类
            $game_type = isset($temp_game_data[0]['gi_type_id']) ? intval($temp_game_data[0]['gi_type_id']) : 0;

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
                'gi_packname' => isset($temp_game_data[0]['gi_packname']) ? $temp_game_data[0]['gi_packname'] : '',
                'gi_logo' => '',
                'gi_bg_img' => '',
                'gi_intro' => '',
                'gi_in_uid' => 1,
                'gi_in_name' => 'admin',
                'gi_order' => 0,
                'gi_simple_txt' => '',
                'gi_download_url' => '',
                'gi_isshow' => 1,
                'gi_created' => time(),
                'gi_firm_id' => isset($temp_game_data[0]['gi_firm_id']) ? intval($temp_game_data[0]['gi_firm_id']) : 0,
                'gi_type_id' => $game_type,
                'gi_pingyin' => $pinyin
            );

            $game_id = $conn->save('video_game_info',$save_arr);
        }
    }else{
        $game_id = 0;
    }
}

//获取关联用户信息
$pic_url = '';
if(!empty($author_name)){
    //查询解说表是否存在该用户（名字相同并且有注册）
    $sql = "SELECT `id`,`va_uid`,`va_icon_get`,`va_icon` FROM `video_author_info` WHERE `va_name` = '".$author_name."' AND `va_uid` > 0 AND `va_isshow` = 1";
    $author_data = $conn->find($sql);

    //没找到名字相同并且有注册的解说，则查询名字相同没有注册的
    if(empty($author_data)){
        $sql = "SELECT `id`,`va_uid`,`va_icon_get`,`va_icon` FROM `video_author_info` WHERE `va_name` = '".$author_name."' AND `va_isshow` = 1";
        $author_data = $conn->find($sql);

        //有找到,查看是否存在用户信息，存在则更新，不存在则注册
        if(!empty($author_data)){

            //解说id
            $author_id = intval($author_data[0]['id']);

            //查看解说头像是否存在
            if(isset($author_data[0]['va_icon_get']) && !empty($author_data[0]['va_icon_get'])){
                $pic_url = $img_path.$author_data[0]['va_icon_get'];
            }elseif(!empty($author_ico)){

                $get_img_url = $local_url.'/cli_php/auto_load/upload_grab_img.php';
                $arr_img = array('img_url'=>$author_ico);

                //调用后台上传阿里百川视频图片处理接口
                $json = curl_post($get_img_url,$arr_img);
                $arr = json_decode($json,TRUE);

                $temp_img = '';
                if($arr['code']==200){
                    $temp_img = $arr['msg'];
                    $pic_url = $img_path.$temp_img;
                }
            }

            //检查解说是否已注册
            $sql = "SELECT `uid` FROM `uc_members` WHERE `nickname` = '".$author_name."' AND `is_show` = 1 AND `source` = 3";
            $user_data = $uconn->find($sql);

            //有找到
            if(isset($user_data[0]['uid']) && !empty($user_data[0]['uid'])){
                $uid = intval($user_data[0]['uid']); //用户id

                //检查是否要更新关联游戏
                $sql = "SELECT `video_game`,`video_game_type` FROM `uc_members` WHERE `uid` = ".$uid;
                $video_game_arr = $uconn->find($sql);
                if(isset($video_game_arr[0]['video_game']) && !empty($video_game_arr[0]['video_game'])){
                    $temp_arr = explode(',',$video_game_arr[0]['video_game']);
                    $temp_arr[] = $game_id;
                    $temp_arr = array_filter(array_unique($temp_arr));
                    $temp_str = implode(',',$temp_arr);
                }else{
                    $temp_str = $game_id;
                }

                //关联游戏分类
                if(isset($video_game_arr[0]['video_game_type']) && !empty($video_game_arr[0]['video_game_type'])){
                    $temp_type_arr = explode(',',$video_game_arr[0]['video_game_type']);
                    $temp_type_arr[] = $game_type;
                    $temp_type_arr = array_filter(array_unique($temp_type_arr));
                    $temp_type_str = implode(',',$temp_type_arr);
                }else{
                    $temp_type_str = $game_type;
                }

                //更新用户关联游戏
                $update_arr = array();
                $update_arr['desc'] = $author_desc;
                if($temp_str <> $video_game_arr[0]['video_game']){
                    $update_arr['video_game'] = $temp_str;
                }

                if($temp_type_str <> $video_game_arr[0]['video_game_type']){
                    $update_arr['video_game_type'] = $temp_type_str;
                }

                if(!empty($update_arr)){
                    $update_arr['uid'] = $uid;
                    $uconn->update('uc_members',$update_arr,'uid');
                }

                $temp_img = UC_API.'/avatar.php?uid='.$uid.'&type=real&size=small';

                //检查用户头像是否是默认头像
                $red_img = get_redirect_url($temp_img);

                //如果是默认头像
                if(strpos($red_img,'noavatar_small')){
                    $target_path = LOCAL_AVATAR_PATH."/author_ico/".date('Y/m/d/');//用户头像目录
                    if(!is_dir($target_path)){
                        create_my_file_path($target_path);
                    }

                    $ext = pathinfo($pic_url,PATHINFO_EXTENSION);
                    $basename= md5(pathinfo($pic_url,PATHINFO_FILENAME));

                    //将非JPG图像转换为JPG
                    $pic_full_path = '';
                    if(in_array($ext, array('png','gif'))){
                        $new_pic_full_path = $target_path . $basename.'.jpg';
                        image_to_jpg($pic_url,$new_pic_full_path ,180,180);
                        $pic_full_path = $new_pic_full_path;
                        unset($new_pic_full_path);
                    }

                    if(!empty($pic_full_path)){
                        $pic_url = 'http://' .$_SERVER['HTTP_HOST'] . '/uc_client/data'. str_replace(LOCAL_AVATAR_PATH, '', $pic_full_path);
                    }

                    if(!empty($pic_url)){
                        $data = file_get_contents($pic_url);
                        $im = imagecreatefromstring($data);
                        unset($data);
                        if($im <> false){
                            //生产环境抓取图片的接口
                            $get_img_url = UC_API . '/api/get_avatar_img.php';
                            $arr_img = array('local_img'=>$pic_url,'uid'=>$uid);

                            //调用ucenter的头像处理接口
                            $json = curl_post($get_img_url,$arr_img);
                            $arr = json_decode($json,TRUE);
                        }
                    }
                }

            }else{

                //注册用户
                $username = 'k_'.time(); //自动生成用户名
                $password = 'kyx66666666';   //统一生成密码
                $salt = substr(uniqid(rand()), -6);
                $param = array(
                    'username' => $username,
                    'password' => md5(md5($password).$salt),
                    'regip' => '127.0.0.1',
                    'regdate' => time(),
                    'salt' => $salt,
                    'nickname' => $author_name,
                    'gender' => $author_sex,
                    'desc' => $author_desc,
                    'source' => 3,
                    'is_recommed' => 1,
                    'is_show' => 1,
                    'video_game' => intval($game_id),
                    'video_game_type' => $game_type
                );
                $uconn->save('uc_members',$param);

                //获取刚注册用户id
                $sql = "SELECT `uid` FROM `uc_members` WHERE `nickname` = '".$author_name."' AND `source` = 3 LIMIT 1";
                $uid_data = $uconn->find($sql);
                $uid = isset($uid_data[0]['uid']) ? intval($uid_data[0]['uid']) : 0;

                if($uid > 0){

                    //更新作者头像
                    if(!empty($pic_url)){
                        $target_path = LOCAL_AVATAR_PATH."/author_ico/".date('Y/m/d/');//用户头像目录
                        if(!is_dir($target_path)){
                            create_my_file_path($target_path);
                        }

                        $ext = pathinfo($pic_url,PATHINFO_EXTENSION);
                        $basename= md5(pathinfo($pic_url,PATHINFO_FILENAME));

                        //将非JPG图像转换为JPG
                        $pic_full_path = '';
                        if(in_array($ext, array('png','gif'))){
                            $new_pic_full_path = $target_path . $basename.'.jpg';
                            image_to_jpg($pic_url,$new_pic_full_path ,180,180);
                            $pic_full_path = $new_pic_full_path;
                            unset($new_pic_full_path);
                        }

                        if(!empty($pic_full_path)){
                            $pic_url = 'http://' .$_SERVER['HTTP_HOST'] . '/uc_client/data'. str_replace(LOCAL_AVATAR_PATH, '', $pic_full_path);
                        }

                        if(!empty($pic_url)){
                            $data = file_get_contents($pic_url);
                            $im = imagecreatefromstring($data);
                            unset($data);
                            if($im <> false){
                                //生产环境抓取图片的接口
                                $get_img_url = UC_API . '/api/get_avatar_img.php';
                                $arr_img = array('local_img'=>$pic_url,'uid'=>$uid);

                                //调用ucenter的头像处理接口
                                $json = curl_post($get_img_url,$arr_img);
                                $arr = json_decode($json,TRUE);
                            }
                        }
                    }
                }
            }

            //更新解说表
            $update_arr = array(
                'id' => $author_id,
                'va_uid' => $uid
            );
            $conn->update('video_author_info',$update_arr);
        }else{ //没找到，注册解说信息，注册用户信息

            //上传解说头像
            if(!empty($author_ico)){
                $get_img_url = $local_url.'/cli_php/auto_load/upload_grab_img.php';
                $arr_img = array('img_url'=>$author_ico);

                //调用后台上传阿里百川视频图片处理接口
                $json = curl_post($get_img_url,$arr_img);
                $arr = json_decode($json,TRUE);

                $temp_img = '';
                if($arr['code']==200){
                    $temp_img = $arr['msg'];
                    $pic_url = $img_path.$temp_img;
                }
            }

            //检测昵称是否存在
            $sql = "SELECT `uid` FROM `uc_members` WHERE `nickname` = '".$author_name."' AND `source` = 3";
            $check = $uconn->find($sql);

            if(isset($check[0]['uid']) && !empty($check[0]['uid'])){
                $uid = intval($check[0]['uid']);
            }else{
                //注册用户
                $username = 'k_'.time(); //自动生成用户名
                $password = 'kyx66666666';   //统一生成密码
                $salt = substr(uniqid(rand()), -6);
                $param = array(
                    'username' => $username,
                    'password' => md5(md5($password).$salt),
                    'regip' => '127.0.0.1',
                    'regdate' => time(),
                    'salt' => $salt,
                    'nickname' => $author_name,
                    'gender' => $author_sex,
                    'desc' => $author_desc,
                    'source' => 3,
                    'is_recommed' => 1,
                    'is_show' => 1,
                    'video_game' => intval($game_id),
                    'video_game_type' => $game_type
                );
                $uconn->save('uc_members',$param);

                //获取刚注册用户id
                $sql = "SELECT `uid` FROM `uc_members` WHERE `nickname` = '".$author_name."' AND `source` = 3 LIMIT 1";
                $uid_data = $uconn->find($sql);
                $uid = isset($uid_data[0]['uid']) ? intval($uid_data[0]['uid']) : 0;
            }

            if($uid > 0){
                //更新作者头像
                if(!empty($pic_url)){

                    $temp_img = UC_API.'/avatar.php?uid='.$uid.'&type=real&size=small';

                    //检查用户头像是否是默认头像
                    $red_img = get_redirect_url($temp_img);

                    //如果是默认头像
                    if(strpos($red_img,'noavatar_small')){
                        $target_path = LOCAL_AVATAR_PATH."/author_ico/".date('Y/m/d/');//用户头像目录
                        if(!is_dir($target_path)){
                            create_my_file_path($target_path);
                        }

                        $ext = pathinfo($pic_url,PATHINFO_EXTENSION);
                        $basename= md5(pathinfo($pic_url,PATHINFO_FILENAME));

                        //将非JPG图像转换为JPG
                        $pic_full_path = '';
                        if(in_array($ext, array('png','gif'))){
                            $new_pic_full_path = $target_path . $basename.'.jpg';
                            image_to_jpg($pic_url,$new_pic_full_path ,180,180);
                            $pic_full_path = $new_pic_full_path;
                            unset($new_pic_full_path);
                        }

                        if(!empty($pic_full_path)){
                            $pic_url = 'http://' .$_SERVER['HTTP_HOST'] . '/uc_client/data'. str_replace(LOCAL_AVATAR_PATH, '', $pic_full_path);
                        }

                        if(!empty($pic_url)){
                            $data = file_get_contents($pic_url);
                            $im = imagecreatefromstring($data);
                            unset($data);
                            if($im <> false){
                                //生产环境抓取图片的接口
                                $get_img_url = UC_API . '/api/get_avatar_img.php';
                                $arr_img = array('local_img'=>$pic_url,'uid'=>$uid);

                                //调用ucenter的头像处理接口
                                $json = curl_post($get_img_url,$arr_img);
                                $arr = json_decode($json,TRUE);
                            }
                        }
                    }
                }
            }

            //注册解说信息
            $save_arr = array(
                'in_date' => time(),
                'va_game_id' => $game_id,
                'va_name' => $author_name,
                'va_icon' => $author_ico,
                'va_icon_get' => $temp_img,
                'va_isshow' => 1,
                'va_intro' => $author_desc,
                'va_email' => '',
                'va_order' => 0,
                'va_recom' => 0,
                'va_uid' => intval($uid),
                'va_rel_game' => $game_id
            );

            $author_id = $conn->save('video_author_info',$save_arr);
        }
    }else{

        $author_id = intval($author_data[0]['id']);
        $uid = intval($author_data[0]['va_uid']);

        if(isset($author_data[0]['va_icon_get']) && !empty($author_data[0]['va_icon_get'])){
            $pic_url = $img_path.$author_data[0]['va_icon_get'];
        }elseif(!empty($author_ico)){
            //上传解说头像
            $get_img_url = $local_url.'/cli_php/auto_load/upload_grab_img.php';
            $arr_img = array('img_url'=>$author_ico);

            //调用后台上传阿里百川视频图片处理接口
            $json = curl_post($get_img_url,$arr_img);
            $arr = json_decode($json,TRUE);

            $temp_img = '';
            if($arr['code']==200){
                $temp_img = $arr['msg'];
                $pic_url = $img_path.$temp_img;

                //更新作者头像信息
                $update_arr = array(
                    'id' => $author_id,
                    'va_icon' => $author_ico,
                    'va_icon_get' => $temp_img,
                    'va_intro' => $author_desc
                );
                $conn->update('video_author_info',$update_arr);
            }
        }

        //检查是否要更新关联游戏
        $sql = "SELECT `video_game`,`video_game_type` FROM `uc_members` WHERE `uid` = ".$uid;
        $video_game_arr = $uconn->find($sql);
        if(isset($video_game_arr[0]['video_game']) && !empty($video_game_arr[0]['video_game'])){
            $temp_arr = explode(',',$video_game_arr[0]['video_game']);
            $temp_arr[] = $game_id;
            $temp_arr = array_filter(array_unique($temp_arr));
            $temp_str = implode(',',$temp_arr);
        }else{
            $temp_str = $game_id;
        }

        //关联游戏分类
        if(isset($video_game_arr[0]['video_game_type']) && !empty($video_game_arr[0]['video_game_type'])){
            $temp_type_arr = explode(',',$video_game_arr[0]['video_game_type']);
            $temp_type_arr[] = $game_type;
            $temp_type_arr = array_filter(array_unique($temp_type_arr));
            $temp_type_str = implode(',',$temp_type_arr);
        }else{
            $temp_type_str = $game_type;
        }

        //更新用户关联游戏
        $update_arr = array();
        $update_arr['desc'] = $author_desc;
        if($temp_str <> $video_game_arr[0]['video_game']){
            $update_arr['video_game'] = $temp_str;
        }

        if($temp_type_str <> $video_game_arr[0]['video_game_type']){
            $update_arr['video_game_type'] = $temp_type_str;
        }

        if(!empty($update_arr)){
            $update_arr['uid'] = $uid;
            $uconn->update('uc_members',$update_arr,'uid');
        }

        //更新作者头像
        if(!empty($pic_url)){

            $temp_img = UC_API.'/avatar.php?uid='.$uid.'&type=real&size=small';

            //检查用户头像是否是默认头像
            $red_img = get_redirect_url($temp_img);

            //如果是默认头像
            if(strpos($red_img,'noavatar_small')){
                $target_path = LOCAL_AVATAR_PATH."/author_ico/".date('Y/m/d/');//用户头像目录
                if(!is_dir($target_path)){
                    create_my_file_path($target_path);
                }

                $ext = pathinfo($pic_url,PATHINFO_EXTENSION);
                $basename= md5(pathinfo($pic_url,PATHINFO_FILENAME));

                //将非JPG图像转换为JPG
                $pic_full_path = '';
                if(in_array($ext, array('png','gif'))){
                    $new_pic_full_path = $target_path . $basename.'.jpg';
                    image_to_jpg($pic_url,$new_pic_full_path ,180,180);
                    $pic_full_path = $new_pic_full_path;
                    unset($new_pic_full_path);
                }

                if(!empty($pic_full_path)){
                    $pic_url = 'http://' .$_SERVER['HTTP_HOST'] . '/uc_client/data'. str_replace(LOCAL_AVATAR_PATH, '', $pic_full_path);
                }

                if(!empty($pic_url)){
                    $data = file_get_contents($pic_url);
                    $im = imagecreatefromstring($data);
                    unset($data);
                    if($im <> false){
                        //生产环境抓取图片的接口
                        $get_img_url = UC_API . '/api/get_avatar_img.php';
                        $arr_img = array('local_img'=>$pic_url,'uid'=>$uid);

                        //调用ucenter的头像处理接口
                        $json = curl_post($get_img_url,$arr_img);
                        $arr = json_decode($json,TRUE);
                    }
                }
            }
        }
    }
}else{
    $author_id = 0;
    $uid = 0;
}

//获取专辑信息
$category_url = '';
if(!empty($category_name)){

    $sql = "SELECT `id` FROM `video_category_info` WHERE `vc_game_id` = ".$game_id." AND `vc_type_id` = 6 AND `vc_p_id` = 0 AND `vc_name` = '".$category_name."'";
    $category_data = $conn->find($sql);

    if(!empty($category_data)){
        $category_id = intval($category_data[0]['id']);
    }else{
        //把当前视频大图当成专辑图片
        if(!empty($img)){
            //上传专辑图片
            $get_img_url = $local_url.'/cli_php/auto_load/upload_grab_img.php';
            $arr_img = array('img_url'=>$img,'type'=>2);

            //调用后台上传阿里百川视频图片处理接口
            $json = curl_post($get_img_url,$arr_img);
            $arr = json_decode($json,TRUE);

            if($arr['code']==200){
                $category_url = $arr['msg'];
            }
        }

        $save_arr = array(
            'in_date' => time(),
            'vc_game_id' => $game_id,
            'vc_type_id' => 6,
            'vc_p_id' => 0,
            'vc_name' => $category_name,
            'vc_intro' => '',
            'vc_isshow' => 1,
            'vc_order' => 0,
            'vc_icon' => $img,
            'vc_icon_get' => $category_url,
            'vc_bicon' => $category_url,
            'vc_scount' => 0,
            'vc_splaycount' => 0,
            'vc_author_id' => intval($author_id),
            'vc_update_time' => 0,
            'vc_recommend' => 0,
            'vc_index_icon' => '',
            'vc_index_recom' => 0,
            'vc_uid' => intval($uid),
            'vc_xiaolu_recom' => 0

        );

        $category_id = $conn->save('video_category_info',$save_arr);
    }

}else {
    $category_id = 0;
}

//检查视频是否已经存在
$check_where = '';
if(!empty($vid)){
    $check_where .= " `vvl_video_id` = '".$vid."' OR";
}

//if(!empty($source_url)){
//    $check_where .= " `vvl_playurl` = '".$source_url."' OR";
//}

if(!empty($title )){
    $check_where .= " `vvl_title` = '".$title."' OR";
}

$video_arr = array();
if(!empty($check_where)){
    $check_where = rtrim($check_where,'OR');
    $sql = "SELECT `id` FROM `video_video_list` WHERE `va_isshow` = 1 AND (".$check_where.")";
    $video_arr = $conn->find($sql);
}

//视频图片上传
$video_img = '';
//if(!empty($img)){
//    //上传专辑图片
//    $get_img_url = $local_url.'/cli_php/auto_load/upload_grab_img.php';
//    $arr_img = array('img_url'=>$img,'type'=>3);
//
//    //调用后台上传阿里百川视频图片处理接口
//    $json = curl_post($get_img_url,$arr_img);
//    $arr = json_decode($json,TRUE);
//
//    if($arr['code']==200){
//        $video_img = $arr['msg'];
//    }
//}

//视频存在，则更新
if(isset($video_arr[0]['id']) && !empty($video_arr[0]['id'])){

    //视频id
    $video_id = intval($video_arr[0]['id']);

    $update_arr = array();

    //专辑id
    if(!empty($category_id)){
        $update_arr['vvl_category_id'] = intval($category_id);
        $update_arr['vvl_type_id'] = 6;
    }

    //解说id
    if(!empty($author_id)){
        $update_arr['vvl_author_id'] = intval($author_id);
    }

    //用户id
    if(!empty($uid)){
        $update_arr['vvl_uid'] = intval($uid);
    }

    //视频不能播
    if($status == 3){
        $update_arr['va_isshow'] = 2;
    }else{
        $update_arr['va_isshow'] = 1;
    }

    //视频图片
    $update_arr['vvl_imgurl'] = $img;
    $update_arr['vvl_imgurl_get'] = $video_img;
//    if(!empty($video_img)){
//        $update_arr['vvl_imgurl'] = $img;
//        $update_arr['vvl_imgurl_get'] = $video_img;
//    }

    if(!empty($update_arr)){
        $update_arr['id'] = intval($video_arr[0]['id']);
        $conn->update('video_video_list',$update_arr);
    }

    //检测该视频关联专辑是否还有视频，没有则隐藏
    if($status == 3){
        $sql = "SELECT `id` FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_type_id` = 6 AND `vvl_category_id` = ".intval($category_id)." LIMIT 1";
        $check = $conn->find($sql);
        if(!isset($check[0]['id']) || empty($check[0]['id'])){
            $conn->update('video_category_info',array('id' => intval($category_id),'vc_isshow' => 2));
        }
    }

}else{ //视频不存在，则插入

    $now_date = date('Ymd',time());

    //视频时长计算（毫秒转字符串）
    $time = intval($duration/1000);
    if($time >= 3600){
        $hour = intval($time/3600);
        $hour_str = ($hour < 10) ? ('0'.$hour) : $hour;
        $min = intval($time%3600);
        $time_min = $hour_str.':'.date('i:s',$min);
    }else{
        $time_min = date('i:s',$time);
    }

    //计算本地播放数加采集播放数分值
    if($play_num > 10000){
        $play_base_num = 1;
    }else{
        $play_base_num = $play_num/10000;
    }
    $count = $play_base_num * 40 + 60;

    //视频信息保存
    $update_arr = array(
        'in_date' => time(),
        'vvl_game_id' => $game_id,
        'vvl_category_id' => $category_id,
        'vvl_type_id' => 6,
        'vvl_sourcetype' => $source_type,
        'vvl_imgurl' => $img,
        'vvl_imgurl_get' => $video_img,
        'vvl_big_imgurl_get' => '',
        'vvl_time' => $time_min,
        'vvl_playurl' => $source_url,
        'vvl_playurl_get' => '',
        'vvl_author_id' => $author_id,
        'vvl_title' => $title,
        'vvl_playcount' => $play_num,
        'vvl_count' => $play_num,
        'vvl_package_name' => $game_package,
        'va_isshow' => 1,
        'vvl_video_id' => $vid,
        'vvl_server_url' => $mp4_url,
        'vvl_uid' => $uid,
        'vvl_upload_time' => $public_time,
        'vvl_video_score' => $count
    );

    $video_id = $conn->save('video_video_list',$update_arr);
}

//更新新游预告视频
if(in_array($game_name,$area_arr)){

    $area_id = isset($area_id_arr[$game_name]) ? intval($area_id_arr[$game_name]) : 0;
    if(!empty($area_id)){
        $sql = "SELECT `id` FROM `video_area_video_info` WHERE `va_id` = ".$area_id." AND `vvl_id` = ".intval($video_id)." LIMIT 1";
        $res = $conn->find($sql);
        if(!isset($res[0]['id']) || empty($res[0]['id'])){
            $save_arr = array(
                'va_id' => $area_id,
                'vvl_id' => intval($video_id),
                'created' => time()
            );
            $conn->save('video_area_video_info',$save_arr);
        }
    }
}

//更新视频标签
if(!empty($video_tag)){
    $tag_arr = explode(',',$video_tag);
    foreach($tag_arr as $tval){
        if($tval <> $game_name && $tval <> $author_name){
            //查询标签是否存在
            $sql = "SELECT `vtc_id` FROM `video_game_tags` WHERE `vtc_type` = 2 AND `vtc_status` = 1 AND `vtc_name` = '".$tval."'";
            $check = $conn->find($sql);
            if(isset($check[0]['vtc_id']) && !empty($check[0]['vtc_id'])){
                $tag_id = intval($check[0]['vtc_id']);
            }else {
                $save_arr = array(
                    'vtc_name' => $tval,
                    'vtc_user_name' => 'admin',
                    'vtc_user_id' => 1,
                    'vtc_type' => 2,
                    'vtc_create_time' => time(),
                    'vtc_update_time' => '',
                    'vtc_status' => 1,
                    'vtc_start_recom' => 2
                );
                $tag_id = $conn->save('video_game_tags', $save_arr);
            }

            //更新视频标签关系
            $sql = "SELECT `v_id` FROM `video_tag_mapping` WHERE `v_id` = ".$video_id." AND `vtc_tag_id` = ".$tag_id." LIMIT 1";
            $check = $conn->find($sql);
            if(!isset($check[0]['v_id']) || empty($check[0]['v_id'])){
                $save_arr = array(
                    'v_id' => $video_id,
                    'vtc_tag_id' => $tag_id
                );
                $res = $conn->save('video_tag_mapping',$save_arr);
            }
        }
    }
}


$str_encode = responseJson(array('code' => 1,'vid' => intval($video_id)));
exit($str_encode);



