<?php
//SDK入库归档参数配置
$sdk_param = array(
    1 => array( //SDK连接服务启动跟失败
        'dir_name' => 'sdk_connect_server', //数据存放文件夹
        'db_name' => 'kyx_sdk_service_log', //数据表名称
        'field_pre' => 'ssl', //字段前缀
        'db_comm' => 'SDK连接服务启动跟失败统计日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'nip', //客户端传过来IP（内网）
                'param_name' => 'nip', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 30, //字段长度
                'field_comm' => '客户端传过来IP（内网）' //字段注释
            ),
            1 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'port', //视频标题
                'param_name' => 'port', //获取json值参数名
                'field_type' => 'int', //字段类型
                'field_len' => 11, //字段长度
                'field_comm' => '端口号' //字段注释
            ),
            2 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'wifiname', //网络环境（3G、wifi）
                'param_name' => 'wifiname', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 30, //字段长度
                'field_comm' => 'wifi名称' //字段注释
            ),
            3 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'msg', //网络环境（3G、wifi）
                'param_name' => 'msg', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 255, //字段长度
                'field_comm' => '错误信息' //字段注释
            )
        )
    ),
    2 => array( //一般异常统计
        'dir_name' => 'sdk_server_exception', //数据存放文件夹
        'db_name' => 'kyx_sdk_service_exception_log', //数据表名称
        'field_pre' => 'ssel', //字段前缀
        'db_comm' => '一般异常统计',
        'is_export' => 0, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'nip', //客户端传过来IP（内网）
                'param_name' => 'nip', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 30, //字段长度
                'field_comm' => '客户端传过来IP（内网）' //字段注释
            ),
            1 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'port', //视频标题
                'param_name' => 'port', //获取json值参数名
                'field_type' => 'int', //字段类型
                'field_len' => 11, //字段长度
                'field_comm' => '端口号' //字段注释
            ),
            2 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'wifiname', //网络环境（3G、wifi）
                'param_name' => 'wifiname', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 30, //字段长度
                'field_comm' => 'wifi名称' //字段注释
            ),
            3 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'msg', //网络环境（3G、wifi）
                'param_name' => 'msg', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 255, //字段长度
                'field_comm' => '错误信息' //字段注释
            )
        )
    ),
    3 => array( //SDK进入游戏跟退出游戏
        'dir_name' => 'sdk_into_out_game', //数据存放文件夹
        'db_name' => 'kyx_sdk_game_into_out_log', //数据表名称
        'field_pre' => 'sgiol', //字段前缀
        'db_comm' => '进入退出游戏统计日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'gt', //客户端传过来IP（内网）
                'param_name' => 'gt', //获取json值参数名
                'field_type' => 'bigint', //字段类型
                'field_len' => 20, //字段长度
                'field_comm' => '游戏时间' //字段注释
            ),
            1 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'sv', //系统版本号
                'param_name' => 'sv', //获取json值参数名
                'field_type' => 'int', //字段类型
                'field_len' => 11, //字段长度
                'field_comm' => '系统版本号' //字段注释
            ),
            2 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'gpu', //gpu信息
                'param_name' => 'gpu', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 100, //字段长度
                'field_comm' => 'gpu信息' //字段注释
            )
        )
    ),
    4 => array( //SDK游戏崩溃
        'dir_name' => 'sdk_game_crash', //数据存放文件夹
        'db_name' => 'kyx_sdk_game_crash_log', //数据表名称
        'field_pre' => 'sgcl', //字段前缀
        'db_comm' => 'SDK游戏崩溃统计日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'msg', //错误信息
                'param_name' => 'msg', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 255, //字段长度
                'field_comm' => '错误信息' //字段注释
            ),
            1 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'sv', //系统版本号
                'param_name' => 'sv', //获取json值参数名
                'field_type' => 'int', //字段类型
                'field_len' => 11, //字段长度
                'field_comm' => '系统版本号' //字段注释
            ),
            2 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'gpu', //gpu信息
                'param_name' => 'gpu', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 100, //字段长度
                'field_comm' => 'gpu信息' //字段注释
            )
        )
    ),
    5 => array( //SDK视频点击统计
        'dir_name' => 'sdk_video_play_click', //数据存放文件夹
        'db_name' => 'kyx_sdk_video_play_click_log', //数据表名称
        'field_pre' => 'svpcl', //字段前缀
        'db_comm' => 'SDK视频点击操作日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array() //私有参数列表（不带前缀）
    ),
    6 => array( //SDK视频点击广告位统计
        'dir_name' => 'sdk_video_ad_click', //数据存放文件夹
        'db_name' => 'kyx_sdk_video_ad_click_log', //数据表名称
        'field_pre' => 'svacl', //字段前缀
        'db_comm' => 'SDK视频点击广告位日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array(  //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'flag', //0代表未安装（将会是安装动作），1代表已安装（将会是打开app）
                'param_name' => 'flag', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '0代表未安装（将会是安装动作），1代表已安装（将会是打开app）' //字段注释
            ),
            1 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'adid', //广告位id
                'param_name' => 'adid', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '广告位id' //字段注释
            )
        )
    ),
    7 => array( //SDK视频点击专题
        'dir_name' => 'sdk_video_topic_click', //数据存放文件夹
        'db_name' => 'kyx_sdk_video_topic_click_log', //数据表名称
        'field_pre' => 'svtcl', //字段前缀
        'db_comm' => 'SDK视频点击专题日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array(  //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'tid', //专题id
                'param_name' => 'topicid', //获取json值参数名
                'field_type' => 'int', //字段类型
                'field_len' => 11, //字段长度
                'field_comm' => '专题id' //字段注释
            ),
            1 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'tname', //专题名称
                'param_name' => 'topicname', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 100, //字段长度
                'field_comm' => '专题名称' //字段注释
            )
        )
    ),
    8 => array( //SDK视频点击播放
        'dir_name' => 'sdk_video_click_play', //数据存放文件夹
        'db_name' => 'kyx_sdk_video_click_play_log', //数据表名称
        'field_pre' => 'svcpl', //字段前缀
        'db_comm' => 'SDK视频点击播放日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array(  //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'vid', //视频id
                'param_name' => 'videoid', //获取json值参数名
                'field_type' => 'int', //字段类型
                'field_len' => 11, //字段长度
                'field_comm' => '视频id' //字段注释
            ),
            1 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'vname', //视频名称
                'param_name' => 'videoname', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 150, //字段长度
                'field_comm' => '视频名称' //字段注释
            ),
            2 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'sid', //-1表示来自热门推荐，-2表示来自相关视频，1表示来自视频解说，2表示来自视频专辑
                'param_name' => 'sourceid', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '-1表示来自热门推荐，-2表示来自相关视频，1表示来自视频解说，2表示来自视频专辑' //字段注释
            )
        )
    ),
    9 => array( //SDK视频分享
        'dir_name' => 'sdk_video_share', //数据存放文件夹
        'db_name' => 'kyx_sdk_video_share_log', //数据表名称
        'field_pre' => 'svsl', //字段前缀
        'db_comm' => 'SDK视频分享日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array(  //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'stype', //分享类型，字符串，0：微博，1：QQ，2：QZONE，3：优酷，4：微信、5：更多
                'param_name' => 'stype', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '分享类型，字符串，0：微博，1：QQ，2：QZONE，3：优酷，4：微信、5：更多' //字段注释
            ),
            1 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'fsize', //文件大小
                'param_name' => 'fs', //获取json值参数名
                'field_type' => 'int', //字段类型
                'field_len' => 11, //字段长度
                'field_comm' => '文件大小' //字段注释
            ),
            2 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'url', //-1表示来自热门推荐，-2表示来自相关视频，1表示来自视频解说，2表示来自视频专辑
                'param_name' => 'url', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 255, //字段长度
                'field_comm' => '视频资源url，经过base64编码' //字段注释
            )
        )
    ),
    10 => array( //SDK视频上传
        'dir_name' => 'sdk_video_upload', //数据存放文件夹
        'db_name' => 'kyx_sdk_video_upload_log', //数据表名称
        'field_pre' => 'svul', //字段前缀
        'db_comm' => 'SDK视频上传日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array(  //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'utype', //分享类型，字符串，0：微博，1：QQ，2：QZONE，3：优酷，4：微信、5：更多
                'param_name' => 'utype', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '上传类型，整型，0为阿里百川，1为优酷' //字段注释
            ),
            1 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'fsize', //文件大小
                'param_name' => 'fs', //获取json值参数名
                'field_type' => 'int', //字段类型
                'field_len' => 11, //字段长度
                'field_comm' => '文件大小' //字段注释
            ),
            2 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'pt', //文件大小
                'param_name' => 'pt', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 200, //字段长度
                'field_comm' => '视频标题' //字段注释
            ),
            3 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'pu', //文件大小
                'param_name' => 'pu', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 500, //字段长度
                'field_comm' => '视频播放地址' //字段注释
            ),
            4 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'fmd5', //文件大小
                'param_name' => 'fm', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 32, //字段长度
                'field_comm' => '文件md5' //字段注释
            ),
            5 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'tags', //关联标签分类
                'param_name' => 'tags', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 255, //字段长度
                'field_comm' => '关联标签字符串' //字段注释
            ),
            6 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'uid', //关联用户id
                'param_name' => 'uid', //获取json值参数名
                'field_type' => 'int', //字段类型
                'field_len' => 11, //字段长度
                'field_comm' => '关联用户id' //字段注释
            ),
        )
    ),
    11 => array( //阿里百川服务启动失败
        'dir_name' => 'sdk_al_service', //数据存放文件夹
        'db_name' => 'kyx_sdk_al_service_log', //数据表名称
        'field_pre' => 'sasl', //字段前缀
        'db_comm' => '阿里百川服务启动失败日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array(  //私有参数列表（不带前缀）
            0 => array(
                'type' => 1, //参数类型（1：非整数 0：整数）
                'name' => 'rs', //失败原因
                'param_name' => 'reason', //获取json值参数名
                'field_type' => 'varchar', //字段类型
                'field_len' => 255, //字段长度
                'field_comm' => '失败原因' //字段注释
            )
        )
    ),
    12 => array( //SDK视频合成
        'dir_name' => 'sdk_video_syn', //数据存放文件夹
        'db_name' => 'kyx_sdk_video_syn_log', //数据表名称
        'field_pre' => 'svsl', //字段前缀
        'db_comm' => 'SDK视频合成统计日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'rt', //错误信息
                'param_name' => 'rt', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '合成结果，0为失败，1为成功，2为取消' //字段注释
            )
        )
    )
);

//SDK统计参数配置（使用redis）
$sdk_stati_redis_param = array(
    1 => array(  //SDK连接服务启动跟失败
        'log_db_name' => 'kyx_sdk_service_log', //日志表名称
        'db_name' => 'kyx_sdk_service_time', //统计表名称
        'field_pre' => 'ssl', //字段前缀
        'dis' => 'SDK连接服务启动跟失败统计', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'ssl_pn,ssl_chl', //统计维度字段
        'many_eid' => array( //多事件统计
            200004 => 'fnum', //连接服务启动失败
            200008 => 'cnum' //连接服务关闭
        ),
        'today_num' => 1, //是否统计当天独立用户数
        'other_sql' => 'count(DISTINCT `ssl_mac`,`ssl_imei`) as chl_game_num', //其他维度sql
        'key_val' => array( //字段对应关系
            'fail_num' => array('type' => 0, 'name' => 'fnum'),//连接服务启动失败次数,type=0正型 1：非整型
            'mac_fail_num' => array('type' => 0, 'name' => 'ifnum'),//连接服务独立启动失败次数
            'close_num' => array('type' => 0, 'name' => 'cnum'),//连接服务关闭次数
            "mac_close_num" => array('type' => 0, 'name' => 'icnum'), //连接服务独立关闭次数
            "mac_chl_game_num" => array('type' => 0, 'name' => 'chl_game_num'), //游戏渠道独立用户数
            "user_num" => array('type' => 0, 'name' => 'user_num') //当天独立MAC用户数（固定用user_num名称）
        )
    ),
    2 => array( //SDK进入游戏跟退出游戏
        'log_db_name' => 'kyx_sdk_game_into_out_log', //日志表名称
        'db_name' => 'kyx_sdk_game_into_out_time', //统计表名称
        'field_pre' => 'sgiol', //字段前缀
        'dis' => 'SDK进入退出游戏统计', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'sgiol_pn,sgiol_vc,sgiol_chl', //统计维度字段
        'many_eid' => array( //多事件统计
            100020 => 'inum', //进入游戏
            100021 => 'onum' //退出游戏
        ),
        'today_num' => 1, //是否统计当天独立用户数
        'other_sql' => 'count(DISTINCT `sgiol_mac`,`sgiol_imei`) as mac_chl_game_ve_num,(sum(if(sgiol_gt < 14400000,sgiol_gt,0))/COUNT(if(`sgiol_eid` = 100021 and sgiol_gt < 14400000,true,null))) as avg_time', //其他维度sql
        'key_val' => array( //字段对应关系
            'game_vc' => array('type' => 0, 'name' => 'sgiol_vc'),//游戏版本号,type=0整型 1：非整型
            'into_num' => array('type' => 0, 'name' => 'inum'),//进入游戏次数,type=0整型 1：非整型
            'mac_into_num' => array('type' => 0, 'name' => 'iinum'),//独立进入游戏次数
            'out_num' => array('type' => 0, 'name' => 'onum'),//退出游戏次数
            "game_chl_vc_num" => array('type' => 0, 'name' => 'mac_chl_game_ve_num'), //游戏渠道版本独立MAC用户数
            "mac_out_num" => array('type' => 0, 'name' => 'ionum'), //独立退出游戏次数
            "avg_time" => array('type' => 0, 'name' => 'avg_time'), //游戏平均时长
            "user_num" => array('type' => 0, 'name' => 'user_num') //当天独立MAC用户数（固定用user_num名称）
        )
    ),
    3 => array( //SDK游戏崩溃
        'log_db_name' => 'kyx_sdk_game_crash_log', //日志表名称
        'db_name' => 'kyx_sdk_game_crash_time', //统计表名称
        'field_pre' => 'sgcl', //字段前缀
        'dis' => 'SDK游戏崩溃统计表', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'sgcl_pn,sgcl_vc,sgcl_chl', //统计维度字段
        'many_eid' => array(
            100003 => 'cnum' //游戏崩溃
        ),
        'today_num' => 0, //是否统计当天独立用户数
        'other_sql' => 'count(DISTINCT `sgcl_mac`,`sgcl_imei`) as mac_chl_game_ve_num,sgcl_msg as msg,sgcl_md as model', //其他维度sql
        'key_val' => array( //字段对应关系
            'game_vc' => array('type' => 0, 'name' => 'sgcl_vc'),//游戏版本号,type=0整型 1：非整型
            'model' => array('type' => 1, 'name' => 'model'),//游戏版本号,type=0整型 1：非整型
            'crash_num' => array('type' => 0, 'name' => 'cnum'),//游戏崩溃次数
            'mac_crash_num' => array('type' => 0, 'name' => 'icnum'),//独立游戏崩溃次数
            "game_chl_vc_num" => array('type' => 0, 'name' => 'mac_chl_game_ve_num'), //游戏渠道版本独立MAC用户数
            "message" => array('type' => 1, 'name' => 'msg') //崩溃信息
        )
    ),
    4 => array( //SDK视频点击统计
        'log_db_name' => 'kyx_sdk_video_play_click_log', //日志表名称
        'db_name' => 'kyx_sdk_video_play_click_time', //统计表名称
        'field_pre' => 'svpcl', //字段前缀
        'dis' => 'SDK视频点击操作统计表', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'svpcl_pn,svpcl_chl,svpcl_vc', //统计维度字段
        'many_eid' => array(
            310001 => 'pnum', //视频点击观看按钮
            310005 => 'fnum' //视频点击播放全屏
        ),
        'today_num' => 1, //是否统计当天独立用户数
        'other_sql' => 'count(DISTINCT `svpcl_mac`,`svpcl_imei`) as mac_chl_game_ve_num', //其他维度sql
        'key_val' => array( //字段对应关系
            'game_vc' => array('type' => 0, 'name' => 'svpcl_vc'),//游戏版本号,type=0整型 1：非整型
            'play_num' => array('type' => 0, 'name' => 'pnum'),//点击观看按钮次数
            'mac_play_num' => array('type' => 0, 'name' => 'ipnum'),//独立点击观看按钮次数
            'full_num' => array('type' => 0, 'name' => 'fnum'),//点击全屏播放次数
            'mac_full_num' => array('type' => 0, 'name' => 'ifnum'), //独立点击全屏播放次数
            "game_chl_vc_num" => array('type' => 0, 'name' => 'mac_chl_game_ve_num'), //游戏渠道版本独立MAC用户数
            "user_num" => array('type' => 0, 'name' => 'user_num') //当天独立MAC用户数（固定用user_num名称）
        )
    ),
    5 => array( //SDK视频点击广告位统计
        'log_db_name' => 'kyx_sdk_video_ad_click_log', //日志表名称
        'db_name' => 'kyx_sdk_video_ad_click_time', //统计表名称
        'field_pre' => 'svacl', //字段前缀
        'dis' => 'SDK视频点击广告位统计表', //描述
        'eid_key' => 'flag', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'svacl_pn,svacl_chl,svacl_vc,svacl_adid', //统计维度字段
        'many_eid' => array(
            0 => 'ninum', //未安装次数
            1 => 'inum' //已安装次数
        ),
        'today_num' => 1, //是否统计当天独立用户数
        'other_sql' => 'svacl_adid,count(DISTINCT `svacl_mac`,`svacl_imei`) as mac_chl_game_ve_num', //其他维度sql
        'key_val' => array( //字段对应关系
            'game_vc' => array('type' => 0, 'name' => 'svacl_vc'),//游戏版本号,type=0整型 1：非整型
            'install_num' => array('type' => 0, 'name' => 'inum'),//已安装次数
            'aid' => array('type' => 0, 'name' => 'svacl_adid'),//广告位ID
            'mac_install_num' => array('type' => 0, 'name' => 'iinum'),//独立已安装次数
            'ninstall_num' => array('type' => 0, 'name' => 'ninum'),//未安装次数
            'mac_ninstall_num' => array('type' => 0, 'name' => 'ininum'), //独立未安装次数
            "game_chl_vc_num" => array('type' => 0, 'name' => 'mac_chl_game_ve_num'), //游戏渠道版本独立MAC用户数
            "user_num" => array('type' => 0, 'name' => 'user_num') //当天独立MAC用户数（固定用user_num名称）
        )
    ),
    6 => array( //SDK视频点击专题统计
        'log_db_name' => 'kyx_sdk_video_topic_click_log', //日志表名称
        'db_name' => 'kyx_sdk_video_topic_click_time', //统计表名称
        'field_pre' => 'svtcl', //字段前缀
        'dis' => 'SDK视频点击专题统计表', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'svtcl_pn,svtcl_chl,svtcl_vc,svtcl_tid', //统计维度字段
        'many_eid' => array(
            310003 => 'cnum', //点击次数
        ),
        'today_num' => 1, //是否统计当天独立用户数
        'other_sql' => 'svtcl_tid,svtcl_tname', //其他维度sql
        'key_val' => array( //字段对应关系
            'game_vc' => array('type' => 0, 'name' => 'svtcl_vc'),//游戏版本号,type=0整型 1：非整型
            't_id' => array('type' => 0, 'name' => 'svtcl_tid'),//专题id,type=0整型 1：非整型
            't_name' => array('type' => 1, 'name' => 'svtcl_tname'),//专题id,type=0整型 1：非整型
            'click_num' => array('type' => 0, 'name' => 'cnum'),//点击次数
            'mac_click_num' => array('type' => 0, 'name' => 'icnum'),//独立点击次数
            "user_num" => array('type' => 0, 'name' => 'user_num') //当天独立MAC用户数（固定用user_num名称）
        )
    ),
    7 => array( //SDK视频播放统计
        'log_db_name' => 'kyx_sdk_video_click_play_log', //日志表名称
        'db_name' => 'kyx_sdk_video_click_play_time', //统计表名称
        'field_pre' => 'svcpl', //字段前缀
        'dis' => 'SDK视频点击播放统计表', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'svcpl_pn,svcpl_chl,svcpl_vid,svcpl_sid', //统计维度字段
        'many_eid' => array(
            310004 => 'pnum', //播放次数
        ),
        'today_num' => 1, //是否统计当天独立用户数
        'other_sql' => 'svcpl_vid,svcpl_vname,svcpl_sid', //其他维度sql
        'key_val' => array( //字段对应关系
            'v_id' => array('type' => 0, 'name' => 'svcpl_vid'),//视频id
            's_id' => array('type' => 0, 'name' => 'svcpl_sid'),//-1表示来自热门推荐，-2表示来自相关视频，1表示来自视频解说，2表示来自视频专辑
            'v_name' => array('type' => 1, 'name' => 'svcpl_vname'),//视频名称
            'play_num' => array('type' => 0, 'name' => 'pnum'),//播放次数
            'mac_play_num' => array('type' => 0, 'name' => 'ipnum'),//独立播放次数
            "user_num" => array('type' => 0, 'name' => 'user_num') //当天独立MAC用户数（固定用user_num名称）
        )
    ),
    8 => array( //SDK视频分享统计
        'log_db_name' => 'kyx_sdk_video_share_log', //日志表名称
        'db_name' => 'kyx_sdk_video_share_time', //统计表名称
        'field_pre' => 'svsl', //字段前缀
        'dis' => 'SDK视频分享统计表', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'svsl_pn,svsl_chl,svsl_stype', //统计维度字段
        'many_eid' => array(
            310006 => 'bnum', //开始分享次数
            310007 => 'snum', //分享成功次数
            310008 => 'fnum', //分享失败次数
            310009 => 'qnum' //取消分享次数
        ),
        'today_num' => 1, //是否统计当天独立用户数
        'other_sql' => 'svsl_stype', //其他维度sql
        'key_val' => array( //字段对应关系
            's_type' => array('type' => 0, 'name' => 'svsl_stype'),//分享类型，字符串，0：微博，1：QQ，2：QZONE，3：优酷，4：微信、5：更多
            'bshare_num' => array('type' => 0, 'name' => 'bnum'),//开始分享次数
            'mac_bshare_num' => array('type' => 0, 'name' => 'ibnum'),//独立开始分享次数
            'sshare_num' => array('type' => 0, 'name' => 'snum'),//分享成功次数
            'mac_sshare_num' => array('type' => 0, 'name' => 'isnum'),//独立分享成功次数
            'fshare_num' => array('type' => 0, 'name' => 'fnum'),//分享失败次数
            'mac_fshare_num' => array('type' => 0, 'name' => 'ifnum'),//独立分享失败次数
            'qshare_num' => array('type' => 0, 'name' => 'qnum'),//取消分享次数
            'mac_qshare_num' => array('type' => 0, 'name' => 'iqnum'),//独立取消分享次数
            "user_num" => array('type' => 0, 'name' => 'user_num') //当天独立MAC用户数（固定用user_num名称）
        )
    ),
    9 => array( //SDK视频上传统计
        'log_db_name' => 'kyx_sdk_video_upload_log', //日志表名称
        'db_name' => 'kyx_sdk_video_upload_time', //统计表名称
        'field_pre' => 'svul', //字段前缀
        'dis' => 'SDK视频上传统计表', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'svul_pn,svul_chl,svul_utype', //统计维度字段
        'many_eid' => array(
            310010 => 'bnum', //开始上传次数
            310011 => 'snum', //成功上传次数
            310012 => 'fnum', //上传失败次数
            310013 => 'qnum' //取消上传次数
        ),
        'today_num' => 1, //是否统计当天独立用户数
        'other_sql' => 'svul_utype,SUM(if(`svul_eid`=310011,`svul_ut`,0))/COUNT(if(`svul_eid`=310011,true,null)) AS avg_time', //其他维度sql
        'key_val' => array( //字段对应关系
            'u_type' => array('type' => 0, 'name' => 'svul_utype'),//上传类型，整型，0为阿里百川，1为优酷
            'bupload_num' => array('type' => 0, 'name' => 'bnum'),//开始上传次数
            'mac_bupload_num' => array('type' => 0, 'name' => 'ibnum'),//独立开始上传次数
            'supload_num' => array('type' => 0, 'name' => 'snum'),//成功上传次数
            'mac_supload_num' => array('type' => 0, 'name' => 'isnum'),//独立成功上传次数
            'fupload_num' => array('type' => 0, 'name' => 'fnum'),//分上传失败次数
            'mac_fupload_num' => array('type' => 0, 'name' => 'ifnum'),//独立上传失败次数
            'qupload_num' => array('type' => 0, 'name' => 'qnum'),//取消上传次数
            'mac_qupload_num' => array('type' => 0, 'name' => 'iqnum'),//独立取消上传次数
            "user_num" => array('type' => 0, 'name' => 'user_num'), //当天独立MAC用户数（固定用user_num名称）
            "avg_time" => array('type' => 0, 'name' => 'avg_time') //上传平均时长
        )
    ),
    10 => array( //SDK视频合成
        'log_db_name' => 'kyx_sdk_video_syn_log', //日志表名称
        'db_name' => 'kyx_sdk_video_syn_time', //统计表名称
        'field_pre' => 'svsl', //字段前缀
        'dis' => 'SDK视频合成统计表', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'svsl_pn,svsl_chl', //统计维度字段
        'many_eid' => array(
            310015 => 'snum' //开始合成
        ),
        'today_num' => 0, //是否统计当天独立用户数
        'other_sql' => 'COUNT(if(`svsl_eid`=310016 and `svsl_rt`=0,true,null)) AS fnum,COUNT(DISTINCT if(`svsl_eid`=310016 and `svsl_rt`=0,CONCAT(`svsl_mac`,`svsl_imei`),null)) AS ifnum,
                        COUNT(if(`svsl_eid`=310016 and `svsl_rt`=1,true,null)) AS sunum,COUNT(DISTINCT if(`svsl_eid`=310016 and `svsl_rt`=1,CONCAT(`svsl_mac`,`svsl_imei`),null)) AS isunum,
                        COUNT(if(`svsl_eid`=310016 and `svsl_rt`=2,true,null)) AS cnum,COUNT(DISTINCT if(`svsl_eid`=310016 and `svsl_rt`=2,CONCAT(`svsl_mac`,`svsl_imei`),null)) AS icnum,
                        SUM(if(`svsl_eid`=310016,svsl_ut,0))/COUNT(if(`svsl_eid`=310016,true,null)) AS avg_time,COUNT(if(`svsl_eid`=310016,true,null)) AS finnum', //其他维度sql
        'key_val' => array( //字段对应关系
            'syn_num' => array('type' => 0, 'name' => 'snum'),//开始合成次数
            'mac_syn_num' => array('type' => 0, 'name' => 'isnum'),//独立开始合成次数
            'fail_num' => array('type' => 0, 'name' => 'fnum'),//合成失败次数
            'mac_fail_num' => array('type' => 0, 'name' => 'ifnum'),//独立合成失败次数
            'suc_num' => array('type' => 0, 'name' => 'sunum'),//合成成功次数
            'mac_suc_num' => array('type' => 0, 'name' => 'isunum'),//独立合成成功次数
            'cancel_num' => array('type' => 0, 'name' => 'cnum'),//合成取消次数
            'mac_cancel_num' => array('type' => 0, 'name' => 'icnum'),//独立合成取消次数
            'finish_num' => array('type' => 0, 'name' => 'finnum'),//合成完成次数
            'avg_time' => array('type' => 0, 'name' => 'avg_time') //独立平均时长
        )
    )
);

//APP模拟手柄入库归档参数配置
$app_param = array(
    1 => array( //用户玩游戏的时长
        'dir_name' => 'play_game_time', //数据存放文件夹
        'db_name' => 'kyx_app_play_game_log', //数据表名称
        'field_pre' => 'apgl', //字段前缀
        'db_comm' => '用户玩游戏的时长统计日志表',
        'is_game' => 1, //是否是游戏相关统计（带游戏统计公共参数）
        'is_export' => 1, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'gtime', //客户端传过来IP（内网）
                'param_name' => 'gtime', //获取json值参数名
                'field_type' => 'decimal', //字段类型
                'field_len' => '10,2', //字段长度
                'field_comm' => '游戏时间（分钟）' //字段注释
            )
        )
    )
);

//APP统计参数配置（使用redis）
$app_stati_redis_param = array(
    1 => array(
        'log_db_name' => 'kyx_app_play_game_log', //日志表名称
        'db_name' => 'kyx_app_play_game_time', //统计表名称
        'field_pre' => 'apgl', //字段前缀
        'is_game' => 1, //是否是游戏相关统计
        'dis' => 'app用户玩游戏的时长统计', //描述
        'eid_key' => '', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'apgl_pn,apgl_gvc,apgl_gchl', //统计维度字段
        'many_eid' => '',
        'today_num' => 1, //是否统计当天独立用户数
        'other_sql' => 'count(`apgl_mac`) as chl_game_ve_num,count(DISTINCT `apgl_mac`) as mac_chl_game_ve_num,
                        sum(`apgl_gtime`) as stime,avg(`apgl_gtime`) as atime', //其他维度sql
        'key_val' => array( //字段对应关系
            "chl_game_vc_num" => array('type' => 0, 'name' => 'chl_game_ve_num'), //游戏版本渠道用户数
            "mac_chl_game_vc_num" => array('type' => 0, 'name' => 'mac_chl_game_ve_num'), //游戏版本渠道独立用户数
            "game_avg_time" => array('type' => 1, 'name' => 'atime'), //游戏平均时间
            "game_sum_time" => array('type' => 0, 'name' => 'stime'), //游戏总时间
            "user_num" => array('type' => 0, 'name' => 'user_num') //当天独立MAC用户数（固定用user_num名称）
        )
    )
);