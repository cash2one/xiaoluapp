<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频APP项目 首页数据列表,并JSON内容进行输出返回
 * @file: video_index_album.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-22  10:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['gameid'] = intval(get_param('gameid'));//游戏ID
$mydata['channel'] = get_param('channel');//渠道名称，字符串类型
$mydata['gpu'] = get_param('gpu');//字符串 gpu信息
$mydata['versioncode'] = intval(get_param('versioncode'));//客户端版本号
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//游戏id判断
if($mydata['gameid'] < 1){
    $mydata['gameid'] = 2;
}

//定义回转的默认参数
$returnArr = array(
    'rows' => array()
);

$where = ' WHERE va_isshow = 1 AND CHAR_LENGTH(vvl_title)>=5 AND `vvl_category_id` <> 3324 ';
if($mydata['gameid'] == 2){
    $where .= " AND (vvl_game_id = 12 OR vvl_game_id = 2) ";
}else{
    $where .= " AND vvl_game_id = ".$mydata['gameid'].' ';
}

$mem_obj = new kyx_memcache();

//首页中部广告
if($mydata['versioncode'] >= 17){

    $tmp_where = ' AND A.adp_id = 59 ';

    //游戏id
    if( !empty($mydata['gameid'])){
        $tmp_where .= ' AND (A.ad_game_id='.$mydata['gameid']." OR (A.ad_game_id is Null OR A.ad_game_id='0' OR A.ad_game_id='') ) ";
    }

    if(!is_empty($mydata['gpu'])){
        //查找适配的GPU
        $tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
        $tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');
        $tmp_find_gpu_id = " AND ( FIND_IN_SET(0,ad_gpu_id)>0 ";
        foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
            $tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",ad_gpu_id)>0 ";
        }
        $tmp_find_gpu_id .= " ) ";
        $tmp_where .= $tmp_find_gpu_id;
    }

    //如果有传渠道过来，则调渠道对应的
    if( !is_empty($mydata['channel'])){
        $tmp_where .= " AND (INSTR(A.ad_qudao,'".$mydata['channel']."')>0 OR A.ad_qudao is Null OR A.ad_qudao='0' OR A.ad_qudao='') ";
    }else{//如果没有传渠道过来，则调不限渠道的
        $tmp_where .= " AND (A.ad_qudao is Null OR A.ad_qudao='0' OR A.ad_qudao='') ";
    }

    //获取广告数据
    $sql = "SELECT A.ad_id,A.ad_title,A.ad_img_key,A.ad_a_href,A.ad_des,B.img_path FROM `mzw_ad` A
		    LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key
		    WHERE A.ad_status=1 ".$tmp_where."  AND (B.size_id=0 OR B.size_id is NULL) ORDER BY A.ad_dis_order DESC,A.ad_id DESC LIMIT 4";
    $data = $conn->find($sql);
    $count = count($data);
    if(!empty($data) && is_type($data,'Array') && $count == 4){
        $temp_arr = array();
        foreach($data as $row){

            preg_match('/type=(.*?),/', $row['ad_a_href'],$h_arr);
            if($mydata['versioncode'] <= 22 && (isset($h_arr[1]) && $h_arr[1] == 12)){
                $row['ad_a_href'] = str_replace('show_type=2','show_type=1',$row['ad_a_href']);
                $row['ad_a_href'] = str_replace('show_type=3','show_type=1',$row['ad_a_href']);
            }

            $temp_arr[] = array(
                'id' => intval($row['ad_id']), //广告id
                'title' => $row['ad_title'],//广告名称
                'img' => LOCAL_URL_DOWN_IMG.$row['img_path'], //广告图标
                'desc' => $row['ad_des'], //广告描述
                'identify' => 3, //跳转标识（1：跳专辑视频列表，要考虑专辑跳专辑的情况 2：跳视频详情 3:广告）
                'action' => $row['ad_a_href'] //广告链接地址
            );
        }
        $returnArr['rows'][] = array(
            'type_title' => '广告', //显示标题
            'category_title' => '广告', //分类显示标题
            'type' => 3, //显示样式
            'tid' => -2, //查看更多用到id
            'identify' => 4, //跳转标识（1：跳专辑列表 2：最新更多视频列表 3：热门更多 4：首页中部广告）
            'gameid' => $mydata['gameid'], //游戏id
            'row' => $temp_arr //游戏数据
        );
    }
}

if($mydata['versioncode'] >= 20){

    //游戏专区映射数组   游戏id => 专区id
    $area_arr = array(2 => 2, 1 => 4, 3 => 5, 4 => 6, 9 => 7, 11 => 8,13 => 9,14 => 10,15 => 11,16293=>16);
    $area_id = isset($area_arr[$mydata['gameid']]) ? $area_arr[$mydata['gameid']] : 2; //默认我的世界视频专区

    //编辑推荐（专区）
    $index_album_recom_list_key = 'index_album_recom_list_data_'.$mydata['gameid'].'_'.$mydata['versioncode']; //首页编辑推荐视频列表数据缓存key 'index_album_recom_list_data_' + 游戏id + 版本号
    $index_album_recom_data = $mem_obj->get($index_album_recom_list_key);
    if($index_album_recom_data === false){
        $index_album_recom_data = array();
        $sql = "SELECT B.`id`,B.`vvl_title`,B.`vvl_imgurl`,B.`vvl_imgurl_get`,B.`vvl_category_id`,B.`vvl_type_id`,B.`vvl_author_id`,
                B.`in_date`,B.`vvl_tags`,B.`vvl_count`,B.`vvl_uid`
                FROM `video_area_video_info` AS A LEFT JOIN `video_video_list` AS B ON B.`id` = A.`vvl_id` WHERE B.`va_isshow` = 1 AND A.`va_id` = ".$area_id." LIMIT 6";
        $data = $conn->find($sql);
        $count = count($data);
        if(!empty($data) && is_type($data,'Array') && $count == 6){
            $temp_arr = array();
            foreach($data as $val){

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
                    if(!empty($val['vvl_uid'])){
                        $author_data_key = 'user_data_'.$val['vvl_uid'];
                        $author_data = $mem_obj->get($author_data_key);
                        if($author_data === false){
                            $sql = "SELECT `nickname`,`source` FROM `uc_members` WHERE `uid` = ".intval($val['vvl_uid']);
                            $author_data = $uconn->get_one($sql);
                            $mem_obj->set($author_data_key,$author_data,3600);
                        }
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
                    $author_img = (isset($author_data['va_icon_get']) && !empty($author_data['va_icon_get'])) ? (LOCAL_URL_DOWN_IMG.$author_data['va_icon_get']) : (isset($author_data['va_icon']) ? $author_data['va_icon'] : '');
                }

                $temp_arr[] = array(
                    'appid' => intval($val['id']), //视频id
                    'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
                    'title' => $val['vvl_title'], //视频标题
                    'imgurl' => $tmp_img_url, //视频图片
                    'authorname' => $author_name, //作者名称
                    'authorimg' => $author_img, //作者头像
                    'identify' => 2, //跳转标识（1：跳专辑视频列表，要考虑专辑跳专辑的情况 2：跳视频详情 3:广告）
                    'tag' => $tag_name,
                    'tagcolour' => $tag_colour,
                    'time' => intval($val['in_date']) //采集时间
                );
            }

            $index_album_recom_data = array(
                'type_title' => '编辑推荐', //显示标题
                'category_title' => '编辑推荐', //分类显示标题
                'type' => 1, //显示样式（1：10个5行两列）
                'tid' => -2, //查看更多用到id
                'icontype' => 1, //图标类型（1：钻石图标 2：最新图标 3：皇冠图标 4：标签图标）
                'url' => 'api_name=/moapi/video_area_video_list.php&areaid='.$area_id.'&show_type=1', //show_type 1:单列表 2：专辑
                'identify' => 5, //跳转标识（1：跳专辑列表 2：最新更多视频列表 3：热门更多）
                'gameid' => $mydata['gameid'], //游戏id
                'row' => $temp_arr //游戏数据
            );
            $mem_obj->set($index_album_recom_list_key,$index_album_recom_data,300); //设置5分钟的缓存
        }
    }
    if(!empty($index_album_recom_data)){
        $returnArr['rows'][] = $index_album_recom_data;
    }
}

//最新更新（根据采集时间降序）
$index_album_new_list_key = 'index_album_new_list_data_'.$mydata['gameid'].'_'.$mydata['versioncode']; //首页最新视频列表数据缓存key 'index_album_new_list_data_' + 游戏id + 版本号
$index_album_new_data = $mem_obj->get($index_album_new_list_key);
if($index_album_new_data === false){
    $sql = "SELECT id,vvl_title,vvl_imgurl,vvl_imgurl_get,vvl_category_id,vvl_type_id,vvl_author_id,vvl_tags,in_date,vvl_uid
            FROM `video_video_list` ".$where." ORDER BY in_date DESC LIMIT 6";
    $data = $conn->find($sql);
    $count = count($data);
    if(!empty($data) && is_type($data,'Array') && $count == 6){
        $temp_arr = array();
        foreach($data as $val){

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
                if(!empty($val['vvl_uid'])){
                    $author_data_key = 'user_data_'.$val['vvl_uid'];
                    $author_data = $mem_obj->get($author_data_key);
                    if($author_data === false){
                        $sql = "SELECT `nickname`,`source` FROM `uc_members` WHERE `uid` = ".intval($val['vvl_uid']);
                        $author_data = $uconn->get_one($sql);
                        $mem_obj->set($author_data_key,$author_data,3600);
                    }
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
                $author_img = (isset($author_data['va_icon_get']) && !empty($author_data['va_icon_get'])) ? (LOCAL_URL_DOWN_IMG.$author_data['va_icon_get']) : (isset($author_data['va_icon']) ? $author_data['va_icon'] : '');
            }

            $temp_arr[] = array(
                'appid' => intval($val['id']), //视频id
                'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
                'title' => filter_search(delete_html($val['vvl_title'])), //视频标题
                'imgurl' => $tmp_img_url, //视频图片
                'authorname' => $author_name, //作者名称
                'authorimg' => $author_img, //作者头像
                'identify' => 2, //跳转标识（1：跳专辑视频列表，要考虑专辑跳专辑的情况 2：跳视频详情 3:广告）
                'tag' => $tag_name,
                'tagcolour' => $tag_colour,
                'time' => intval($val['in_date']) //采集时间
            );
        }

        $index_album_new_data = array(
            'type_title' => '最新更新', //显示标题
            'category_title' => '最新更新', //分类显示标题
            'type' => 1, //显示样式（1：10个5行两列）
            'tid' => -1, //查看更多用到id
            'icontype' => 2, //图标类型（1：钻石图标 2：最新图标 3：皇冠图标 4：标签图标）
            'identify' => 2, //跳转标识（1：跳专辑列表 2：最新更多视频列表 3：热门更多）
            'gameid' => $mydata['gameid'], //游戏id
            'row' => $temp_arr //游戏数据
        );
        $mem_obj->set($index_album_new_list_key,$index_album_new_data,300); //设置5分钟的缓存
    }
}
$returnArr['rows'][] = $index_album_new_data;

if($mydata['versioncode'] >= 17){
    //首页专辑推荐
    $index_album_category_list_key = 'index_album_category_list_data_'.$mydata['gameid'].'_'.$mydata['versioncode']; //首页专辑列表数据缓存key 'index_album_category_list_data_' + 游戏id + 版本号
    $index_album_category_data = $mem_obj->get($index_album_category_list_key);

    if($index_album_category_data === false){
        $index_album_category_data = array();
        $category_where = " WHERE `vc_game_id` = ".$mydata['gameid']." AND `vc_isshow` = 1 AND `vc_index_recom` = 1";
        $sql = "SELECT `id`,`vc_name`,`vc_bicon`,`vc_index_icon`,`vc_intro`,`in_date`,`vc_p_id` FROM `video_category_info` ".$category_where." ORDER BY vc_order DESC LIMIT 1";
        $data = $conn->find($sql);
        $count = count($data);
        if(!empty($data) && is_type($data,'Array') && $count == 1){
            $temp_arr = array();
            foreach($data as $val){

                //专辑图片
                $tmp_img_url = !empty($val['vc_index_icon']) ? (LOCAL_URL_DOWN_IMG.$val['vc_index_icon']) : $val['vc_bicon'];

                //判断是否有子级分类
                $rec_sql = "SELECT `id` FROM `video_category_info` WHERE `vc_isshow` = 1 AND `vc_p_id` = ".intval($val['id']).' LIMIT 1';
                $rec_data = $conn->get_one($rec_sql);
                $reclassify = isset($rec_data['id']) ? 1 : 0;

                $temp_arr[] = array(
                    'categoryid' => intval($val['id']), //分类id
                    'typeid' => 4, //视频类型（1任务，2解说，3赛事战况，4集锦）
                    'gameid' => $mydata['gameid'], //游戏id
                    'title' => filter_search(delete_html($val['vc_name'])), //专辑标题
                    'pid' => intval($val['id']), //父级id
                    'reclassify' => $reclassify, //是否存在子级分类（1：存在 0：不存在）
                    'identify' => 1, //跳转标识（1：跳专辑视频列表，要考虑专辑跳专辑的情况 2：跳视频详情 3:广告）
                    'desc' => filter_search(delete_html($val['vc_intro'])), //专辑描述
                    'imgurl' => $tmp_img_url, //视频图片
                    'time' => intval($val['in_date']) //采集时间
                );
            }

            //专辑列表数据
            $index_album_category_data = array(
                'type_title' => '专辑推荐', //显示标题
                'category_title' => '专辑推荐', //分类显示标题
                'typeid' => 4, //视频类型（1任务，2解说，3赛事战况，4集锦）
                'type' => 2, //显示样式（1：4个2行2列）
                'tid' => -4, //查看更多用到id
                'identify' => 1, //跳转标识（1：跳专辑列表 2：最新更多视频列表 3：热门更多）
                'gameid' => $mydata['gameid'], //游戏id
                'row' => $temp_arr //游戏数据
            );
            $mem_obj->set($index_album_category_list_key,$index_album_category_data,300); //设置5分钟的缓存
        }
    }

    if(!empty($index_album_category_data)){
        $returnArr['rows'][] = $index_album_category_data;
    }
}

//推荐热门（根据播放数降序）
$index_album_hot_list_key = 'index_album_hot_list_data_'.$mydata['gameid'].'_'.$mydata['versioncode']; //首页最热视频列表数据缓存key 'index_album_new_list_data_' + 游戏id + 版本号
$index_album_hot_data = $mem_obj->get($index_album_hot_list_key);
if($index_album_hot_data === false){
    $sql = "SELECT id,vvl_title,vvl_imgurl,vvl_imgurl_get,vvl_category_id,vvl_type_id,vvl_author_id,vvl_tags,in_date,vvl_uid
            FROM `video_video_list` ".$where." ORDER BY vvl_last_week_plays DESC LIMIT 10";
    $data = $conn->find($sql);
    $count = count($data);
    if(!empty($data) && is_type($data,'Array') && $count == 10){
        $temp_arr = array();
        foreach($data as $val){

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
                if(!empty($val['vvl_uid'])){
                    $author_data_key = 'user_data_'.$val['vvl_uid'];
                    $author_data = $mem_obj->get($author_data_key);
                    if($author_data === false){
                        $sql = "SELECT `nickname`,`source` FROM `uc_members` WHERE `uid` = ".intval($val['vvl_uid']);
                        $author_data = $uconn->get_one($sql);
                        $mem_obj->set($author_data_key,$author_data,3600);
                    }
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
                $author_img = (isset($author_data['va_icon_get']) && !empty($author_data['va_icon_get'])) ? (LOCAL_URL_DOWN_IMG.$author_data['va_icon_get']) : (isset($author_data['va_icon']) ? $author_data['va_icon'] : '');
            }

            $temp_arr[] = array(
                'appid' => intval($val['id']), //视频id
                'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
                'title' => $val['vvl_title'], //视频标题
                'imgurl' => $tmp_img_url, //视频图片
                'authorname' => $author_name, //作者名称
                'authorimg' => $author_img, //作者头像
                'identify' => 2, //跳转标识（1：跳专辑视频列表，要考虑专辑跳专辑的情况 2：跳视频详情 3:广告）
                'tag' => $tag_name,
                'tagcolour' => $tag_colour,
                'time' => intval($val['in_date']) //采集时间
            );
        }

        $index_album_hot_data = array(
            'type_title' => '推荐热门', //显示标题
            'category_title' => '推荐热门', //分类显示标题
            'type' => 1, //显示样式（1：10个5行两列）
            'tid' => -2, //查看更多用到id
            'icontype' => 3, //图标类型（1：钻石图标 2：最新图标 3：皇冠图标 4：标签图标）
            'identify' => 3, //跳转标识（1：跳专辑列表 2：最新更多视频列表 3：热门更多）
            'gameid' => $mydata['gameid'], //游戏id
            'row' => $temp_arr //游戏数据
        );
        $mem_obj->set($index_album_hot_list_key,$index_album_hot_data,300); //设置5分钟的缓存
    }
}
$returnArr['rows'][] = $index_album_hot_data;

//分类视频推荐
if($mydata['versioncode'] >= 20){

    //所有分类缓存key
    $index_album_all_category_key = 'index_album_all_category_'.$mydata['gameid'].'_'.$mydata['versioncode']; //首页分类标签所有分类列表数据缓存key 'index_album_all_category_' + 游戏id + 版本号
    $all_category = $mem_obj->get($index_album_all_category_key);
    if($all_category === false){
        //获取所有分类
        $sql = "SELECT `vtc_id`,`vtc_name` FROM `mzw_video_tags_category` WHERE `vtc_type` = 2 AND `vtc_status` = 1 AND `vtc_game_id` = ".$mydata['gameid'];
        $all_category = $conn->find($sql);
        $mem_obj->set($index_album_all_category_key,$all_category,1800); //设置30分钟的缓存
    }

    if(!empty($all_category)){
        foreach($all_category as $aval){
            //缓存key
            $index_album_chal_gory_list_key = 'index_album_chal_gory_list_data_'.$mydata['gameid'].'_'.$mydata['versioncode'].'_'.$aval['vtc_id']; //首页分类标签视频列表数据缓存key 'index_album_chal_gory_list_data_' + 游戏id + 版本号 + 分类标签id
            $index_album_chal_gory_data = $mem_obj->get($index_album_chal_gory_list_key);
            if($index_album_chal_gory_data === false){
                //获取分类下特定数量视频
                $sql = "SELECT B.`id`,B.`vvl_title`,B.`vvl_imgurl`,B.`vvl_imgurl_get`,B.`vvl_category_id`,B.`vvl_type_id`,B.`vvl_author_id`,
                        B.`in_date`,B.`vvl_tags`,B.`vvl_count`,B.`vvl_uid`
                        FROM `mzw_video_tag_mapping` AS A LEFT JOIN `video_video_list` AS B ON A.`v_id` = B.`id`
                        WHERE A.`game_id` = ".$mydata['gameid']." AND A.`vtc_category_id` = ".intval($aval['vtc_id'])." AND B.`va_isshow` = 1 AND B.`vvl_category_id` <> 3324 GROUP BY A.`v_id` ORDER BY `in_date` DESC LIMIT 4";
                $data = $conn->find($sql);
                $count = count($data);
                if(!empty($data) && is_type($data,'Array') && $count == 4){
                    $temp_arr = array();
                    foreach($data as $val){

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
                            if(!empty($val['vvl_uid'])){
                                $author_data_key = 'user_data_'.$val['vvl_uid'];
                                $author_data = $mem_obj->get($author_data_key);
                                if($author_data === false){
                                    $sql = "SELECT `nickname`,`source` FROM `uc_members` WHERE `uid` = ".intval($val['vvl_uid']);
                                    $author_data = $uconn->get_one($sql);
                                    $mem_obj->set($author_data_key,$author_data,3600);
                                }
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
                            $author_img = (isset($author_data['va_icon_get']) && !empty($author_data['va_icon_get'])) ? (LOCAL_URL_DOWN_IMG.$author_data['va_icon_get']) : (isset($author_data['va_icon']) ? $author_data['va_icon'] : '');
                        }

                        $temp_arr[] = array(
                            'appid' => intval($val['id']), //视频id
                            'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
                            'title' => $val['vvl_title'], //视频标题
                            'imgurl' => $tmp_img_url, //视频图片
                            'authorname' => $author_name, //作者名称
                            'authorimg' => $author_img, //作者头像
                            'identify' => 2, //跳转标识（1：跳专辑视频列表，要考虑专辑跳专辑的情况 2：跳视频详情 3:广告）
                            'tag' => $tag_name,
                            'tagcolour' => $tag_colour,
                            'time' => intval($val['in_date']) //采集时间
                        );
                    }

                    $index_album_chal_gory_data = array(
                        'type_title' => $aval['vtc_name'], //显示标题
                        'category_title' => $aval['vtc_name'], //分类显示标题
                        'type' => 4, //显示样式（1：4个2行2列）
                        'icontype' => 4, //图标类型（1：钻石图标 2：最新图标 3：皇冠图标 4：标签图标）
                        'tid' => intval($aval['vtc_id']), //查看更多用到id
                        'url' => 'api_name=/moapi/video_chal_gory_video_list.php&categoryid='.intval($aval['vtc_id']).'&show_type=1', //show_type 1:单列表 2：专辑
                        'identify' => 5, //跳转标识（1：跳专辑列表 2：最新更多视频列表 3：热门更多）
                        'gameid' => $mydata['gameid'], //游戏id
                        'row' => $temp_arr //游戏数据
                    );
                    $mem_obj->set($index_album_chal_gory_list_key,$index_album_chal_gory_data,1800); //设置30分钟的缓存
                }
            }

            if(!empty($index_album_chal_gory_data)){
                $returnArr['rows'][] = $index_album_chal_gory_data;
            }
        }
    }
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);





