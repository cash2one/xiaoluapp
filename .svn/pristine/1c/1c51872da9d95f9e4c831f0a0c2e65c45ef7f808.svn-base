<?PHP
    /**
     * @copyright: @快游戏 2014
     * @description: 用户缺省订阅推荐列表
     * @file: video_user_default_sub_list.php
     * @author: Chen Zhong
     * @charset: UTF-8
     * @time: 2016-01-19  14:43
     * @version 1.0
     **/
    include_once("../config.inc.php");
    include_once("../db.config.inc.php");
    include_once("../db.ucenter.config.inc.php");
    include_once('../api/ucenter.config.inc.php');

    /*参数*/
    $mydata = array();
    $mydata['uid'] = intval(get_param('uid')); //用户id
    $mydata['mac'] = get_param('mac');//用户mac地址
    $mydata['imei'] = get_param('imei');//用户imei地址
    $mydata['encrypt'] = get_param('encrypt');
    $mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
    $mydata['key'] = get_param('key'); //验证key
    $request = $_SERVER['REQUEST_METHOD']; //请求方式
    $key_auth = kyx_authorize_key($mydata['key'],$request);

    //key判断
    if(empty($key_auth) || empty($mydata['key'])){
        exit('key error');
    }

    $mem_obj = new kyx_memcache();

    //定义回转的默认参数
    $returnArr = array(
        'rows' => array()
    );

    //用户条件限制
    $check_where = '';
    if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
        $check_where .= " AND `mac` = '".$mydata['mac']."'";
    }
    if(!empty($mydata['imei'])){
        $check_where .= " AND `imei` = '".$mydata['imei']."'";
    }

    //获取用户已订阅的游戏
    $sql = "SELECT `subid`,`subtype` FROM `video_user_sub_info` WHERE `status` = 1 AND (`subtype` = 3 OR `subtype` = 2) ".$check_where;
    $sub_arr = $conn->find($sql);
    $sub_game_arr = array();
    $sub_user_str = '';
    if(!empty($sub_arr)){
        $sub_user_arr = array();
        foreach($sub_arr as $val){
            if($val['subtype'] == 2){
                $sub_user_arr[] = intval($val['subid']);
            }elseif($val['subtype'] == 3){
                $sub_game_arr[] = intval($val['subid']);
            }
        }
        $sub_user_str = implode(',',$sub_user_arr);
    }

    //获取本机游戏信息
    $sql = "SELECT `nav_now_game`,`nav_json` FROM `video_user_nav_info` WHERE 1 ".$check_where;
    $user_nav = $conn->find($sql);
    $now_play = isset($user_nav[0]['nav_now_game']) ? $user_nav[0]['nav_now_game'] : array();
    $about_game = isset($user_nav[0]['nav_json']) ? $user_nav[0]['nav_json'] : array();
    $now_play_arr = json_decode(stripslashes($now_play),true); //正在玩
    $about_game_arr = json_decode(stripslashes($about_game),true); //相关

    //热门主播列表
    $user_data_key = md5("xl_sub_user_data_".$mydata['uid'].$mydata['mac'].$mydata['imei']);
    $user_data = $mem_obj->get($user_data_key);
    if($user_data === false){
        //获取本地游戏相关主播
        $user_data = array();
        if(!empty($now_play_arr)){
            $where_str = "";
            foreach($now_play_arr as $val){
                $where_str .= " FIND_IN_SET(".$val['id'].",`video_game`) > 0 OR";
            }
            $where_str = ' AND ('.rtrim($where_str,'OR').')';

            if(!empty($sub_user_str)){
                $where_str .= " AND `uid` NOT IN (".$sub_user_str.") ";
            }

            $sql = "SELECT `uid`,`nickname` FROM `uc_members` WHERE `video_num` > 0 ".$where_str." ORDER BY `video_num` DESC LIMIT 4";
            $user_data = $uconn->find($sql);
        }

        //没有获取到本地关联主播，则获取默认主播
        if(empty($user_data)){
            $sql = "SELECT `uid`,`nickname` FROM `uc_members` WHERE `video_num` > 0 AND `sub_recommend` = 1 LIMIT 4";
            $user_data = $uconn->find($sql);
        }

        $mem_obj->set($user_data_key,$user_data,300);
    }

    if(!empty($user_data)){
        $temp_arr = array();
        foreach($user_data as $val){

            $user_file_md5 = "user_file_md5_".$val['uid'];
            $md5file = $mem_obj->get($user_file_md5);
            if($md5file === false){
                //生产环境获取大头像md5的接口
                $get_img_url = UC_API . '/api/get_avatar_md5file.php';
                $arr_img = array('uid' => $val['uid']);

                //调用ucenter的头像处理接口
                $json = curl_post($get_img_url,$arr_img);
                $arr_return = json_decode($json,TRUE);

                $md5file = isset($arr_return['md5file']) ? $arr_return['md5file'] : '';
                $mem_obj->set($user_file_md5,$md5file,3600);
            }

            $temp_arr[] = array(
                'anchorid' => intval($val['uid']), //主播id
                'authorname' => $val['nickname'], //主播名称
                'subid' => intval($val['uid']), //订阅id
                'subtype' => 2, //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
                'subscribe' => 0, //是否订阅（1：已订阅 0：未订阅）
                'authorimg' => UC_API.'/avatar.php?uid='.intval($val['uid']).'&type=real&size=big&md5file='.$md5file, //用户大头像
                'md5file' => $md5file //获取ucenter中心的大图md5值
            );
        }

        $returnArr['rows'][] = array(
            'title' => '热门主播',
            'type' => 1, //列表类型（1：主播 2：游戏 3：标签）
            'row1' => $temp_arr
        );
    }

    //热门标签列表
    $game_tag_data_key = "xl_sub_game_tag_data";
    $tag_data = $mem_obj->get($game_tag_data_key);
    if($tag_data ===  false){
        $sql = "SELECT `vtc_id`,`vtc_name` FROM `video_game_tags`
                WHERE `vtc_status` = 1 AND `vtc_type` = 2 AND `vtc_start_recom` = 1 LIMIT 6";
        $tag_data = $conn->find($sql);
        $mem_obj->set($game_tag_data_key,$tag_data,300);
    }

    if(!empty($tag_data)){
        $temp_arr = array();
        foreach($tag_data as $val){
            $temp_arr[] = array(
                'id' => intval($val['vtc_id']), //关联id
                'type' => 1, //id类型（1：标签 2：主播 3：游戏 4：游戏分类）
                'title' => $val['vtc_name'] //标签名称
            );
        }

        $returnArr['rows'][] = array(
            'title' => '热门标签',
            'type' => 3, //列表类型（1：主播 2：游戏 3：标签）
            'row3' => $temp_arr
        );
    }

    //游戏列表
    $game_data_key = md5("xl_sub_game_data_".$mydata['uid'].$mydata['mac'].$mydata['imei']);
    $game_data = $mem_obj->get($game_data_key);
    if($game_data === false){
        //获取本地游戏
        $game_data = array();
        if(!empty($about_game_arr)){
            $ids_arr = array();
            foreach($about_game_arr as $val){
                if($val['type'] == 1 && !in_array($val,$sub_game_arr)){
                    $ids_arr[] = intval($val['id']);
                }
            }
            $ids_str = implode(',',$ids_arr);
            $where_str = '';
            if(!empty($ids_str)){
                $where_str .= " AND `id` IN (".$ids_str.") ";
                $sql = "SELECT `id`,`gi_name`,`gi_logo`,`gi_sub_recommend` FROM `video_game_info` WHERE `gi_isshow` = 1 AND `gi_video_num` > 2 ".$where_str.
                       " ORDER BY `gi_video_num` DESC LIMIT 5";
                $game_data = $conn->find($sql);
            }
        }

        //没有获取到本地游戏，则获取默认游戏
        if(empty($game_data)){
            $sql = "SELECT `id`,`gi_name`,`gi_logo`,`gi_sub_recommend` FROM `video_game_info` WHERE `gi_isshow` = 1 AND `gi_sub_recommend` > 1";
            $game_data = $conn->find($sql);
        }

        $mem_obj->set($game_data_key,$game_data,300);
    }

    if(!empty($game_data)){
        $temp_arr = array();
        foreach($game_data as $val){

            //游戏图片
            $game_ico = empty($val['gi_logo']) ? '' : (LOCAL_URL_DOWN_IMG.$val['gi_logo']);

            //获取游戏订阅数
            $game_sub_num_key = "game_sub_num_".$val['id'];
            $game_sub_num = $mem_obj->get($game_sub_num_key);
            if($game_sub_num === false){
                $sql = "SELECT COUNT(1) AS num FROM `video_user_sub_info` WHERE `subtype` = 3 AND `status` = 1 AND `subid` = ".intval($val['id']);
                $game_sub_num = $conn->count($sql);
                $mem_obj->set($game_sub_num_key,$game_sub_num,600);
            }

            //获取游戏视频数
            $game_video_num_key = "game_sub_video_num_".$val['id'];
            $game_video_num = $mem_obj->get($game_video_num_key);
            if($game_video_num === false){
                $sql = "SELECT COUNT(1) AS num FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_game_id` = ".intval($val['id']);
                $game_video_num = $conn->count($sql);
                $mem_obj->set($game_video_num_key,$game_video_num,600);
            }

            //获取游戏最热的两个视频
            $hot_video_arr = array();
            $video_data_key = "xl_sub_video_data_".$val['id'];
            $video_data = $mem_obj->get($video_data_key);
            if($video_data === false){
                $sql = "SELECT `id`,`vvl_imgurl_get`,`vvl_game_id`,`vvl_imgurl`,`vvl_time`,`vvl_sourcetype`,`vvl_title`,`in_date`,`vvl_sourcetype`,`vvl_video_id`,`vvl_uid`,`vvl_count`,`vvl_upload_time`
                        FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_game_id` = ".intval($val['id'])
                        ." ORDER BY `vvl_count` DESC LIMIT 2";
                $video_data = $conn->find($sql);
            }
            if(!empty($video_data)){
                foreach($video_data as $val){
                    //获取视频缓存播放次数
                    $a_play_key = 'video_play_num_'.intval($val['id']); //视频播放key
                    $a_old_play_val = $mem_obj->get($a_play_key); //获取视频原始播放数

                    //视频图片
                    $tmp_img_url = !empty($val['vvl_imgurl_get']) ? (LOCAL_URL_DOWN_IMG.$val['vvl_imgurl_get']) : $val['vvl_imgurl'];

                    //视频标签
                    $tag_data = array();
                    if(!empty($val['vvl_tags'])){ //有设置标签
                        $video_tag_name_key = 'video_tag_name_key_'.intval($val['vvl_tags']); //视频标签缓存key 'video_tag_name_key_' + 标签id
                        $tag_data = $mem_obj->get($video_tag_name_key); //视频标签数组
                        if($tag_data === false){
                            $tag_sql = "SELECT `tag_name_cn`,`tag_colour` FROM `mzw_video_tags` WHERE `tag_id` = ".intval($val['vvl_tags']);
                            $tag_data = $conn->get_one($tag_sql);
                            $mem_obj->set($video_tag_name_key,$tag_data,3600);
                        }
                    }
                    $tag_name = isset($tag_data['tag_name_cn']) ? $tag_data['tag_name_cn'] : '';
                    $tag_colour = isset($tag_data['tag_colour']) ? $tag_data['tag_colour'] : '';

                    //获取作者名称、头像
                    $author_data = array();
                    if(isset($val['vvl_uid']) && !empty($val['vvl_uid'])){
                        
                        $author_data_key = 'user_data_'.$val['vvl_uid'];
                        $author_data = $mem_obj->get($author_data_key);
                        if($author_data === false){
                            $sql = "SELECT `nickname`,`source` FROM `uc_members` WHERE `uid` = ".intval($val['vvl_uid']);
                            $author_data = $uconn->get_one($sql);
                            $mem_obj->set($author_data_key,$author_data,3600);
                        }
                        $author_name = isset($author_data['nickname']) ? $author_data['nickname'] : '网友';
                        $author_img = UC_API.'/avatar.php?uid='.intval($val['vvl_uid']).'&type=real&size=big';
                    }else{
                        if(!empty($val['vvl_author_id'])){
                            $author_data_key = 'author_data_'.$val['vvl_author_id'];
                            $author_data = $mem_obj->get($author_data_key);
                            if($author_data === false){
                                $sql = "SELECT `va_name`,`va_icon`,`va_icon_get` FROM `video_author_info` WHERE `va_isshow` = 1 AND `id` = ".intval($val['vvl_author_id']);
                                $author_data = $conn->get_one($sql);
                                $mem_obj->set($author_data_key,$author_data,3600);
                            }
                        }
                        $author_name = isset($author_data['va_name']) ? $author_data['va_name'] : '网友';
                        $author_img = (isset($author_data['va_icon_get']) && !empty($author_data['va_icon_get'])) ? (LOCAL_URL_DOWN_IMG.$author_data['va_icon_get']) : (isset($author_data['va_icon']) ? $author_data['va_icon'] : (UC_API.'/avatar.php?uid=&type=real&size=big'));
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
                    if(in_array($val['id'],$user_up_arr)){
                        $has_up = 1;
                    }elseif(in_array($val['id'],$user_down_arr)){
                        $has_down = 1;
                    }

                    //获取视频点赞点踩数
                    $video_up_down_key = 'video_up_num_data_key_'.$val['id'];
                    $video_up_down = $mem_obj->get($video_up_down_key);
                    if($video_up_down === false){
                        $sql = "SELECT vvl_up_num,vvl_down_num FROM `video_video_list` WHERE `id` = ".$val['id'];
                        $video_up_down = $conn->find($sql);
                        $mem_obj->set($video_up_down_key,$video_up_down,1800);
                    }
                    $up_num = intval($video_up_down[0]['vvl_up_num']);
                    $down_num = intval($video_up_down[0]['vvl_down_num']);                        

                    $hot_video_arr[] = array(
                        'appid' => intval($val['id']), //视频id
                        'title' => filter_search(delete_html($val['vvl_title'])), //视频标题
                        'source' => isset($val['vvl_sourcetype']) ? (isset($GLOBALS['SOURCE_ARR'][$val['vvl_sourcetype']]) ? $GLOBALS['SOURCE_ARR'][$val['vvl_sourcetype']] : '') : '',
                        'sourcetype' => isset($val['vvl_sourcetype']) ? intval($val['vvl_sourcetype']) : 0,
                        'videoid' => isset($val['vvl_video_id']) ? $val['vvl_video_id'] : '',
                        'imgurl' => $tmp_img_url, //视频图片
                        'gamename' => isset($game_arr[$val['vvl_game_id']]['gi_name']) ? $game_arr[$val['vvl_game_id']]['gi_name'] : '', //游戏名称
                        'gameid' => isset($val['vvl_game_id']) ? intval($val['vvl_game_id']) : 0, //游戏id
                        'id' => intval($val['vvl_game_id']), //导航关联id
                        'type' => 1, //导航类型（1：游戏 2：游戏分类）
                        'anchorid' => intval($val['vvl_uid']), //主播id
                        'authorname' => $author_name, //作者名称
                        'authorimg' => $author_img, //作者头像
                        'duration' => strstr($val['vvl_time'],':') ? $val['vvl_time'] : '',
                        'playnum' => intval($val['vvl_count'])  + intval($a_old_play_val), //视频播放总数 + 缓存播放次数
                        'tag' => $tag_name, //视频标签
                        'tagcolour' => $tag_colour, //视频标签颜色
                        'likenum' => $up_num, //视频点赞数
                        'unlikenum' => $down_num, //视频点踩数
                        'haslike' => $has_up, //是否点赞过该视频 1：是 0：否
                        'hasunlike' => $has_down, //是否点踩过该视频 1：是 0：否
                        'timestamp' => isset($val['vvl_upload_time']) ? $val['vvl_upload_time'] : 0, //时间戳
                        'time' => isset($val['vvl_upload_time']) ? (empty($val['vvl_upload_time']) ? '' : date('Y-m-d',$val['vvl_upload_time'])) : date('Y-m-d',$val['in_date']) //采集时间
                    );
                }
            }

            $json = array(
                'id' => intval($val['id']), //游戏id
                'type' => 1, //导航类型（1：游戏 2：游戏分类）
                'title' => $val['gi_name'], //游戏名称
                'imgurl' => $game_ico, //游戏图标
                'subnum' => intval($game_sub_num), //游戏订阅数
                'subid' => intval($val['id']), //订阅id
                'subtype' => 3, //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
                'subscribe' => 0, //是否订阅（1：已订阅 0：未订阅）
                'videonum' => intval($game_video_num), //游戏视频数
                'hotvideo' => $hot_video_arr //热门视频数组
            );

            $returnArr['rows'][] = array(
                'title' => '热门游戏',
                'type' => 2, //列表类型（1：主播 2：游戏 3：标签）
                'row2' => array(0 => $json)
            );
        }
    }

    $str_encode = responseJson($returnArr,$mydata['encrypt']);
    exit($str_encode);





