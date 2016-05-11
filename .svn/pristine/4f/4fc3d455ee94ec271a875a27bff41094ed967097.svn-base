<?php
//视频广告入库归档参数配置
$video_vert_param = array(
    1 => array( //首页广告展示点击跳过
        'dir_name' => 'titles_video_vert', //数据存放文件夹
        'db_name' => 'kyx_titles_video_vert_log', //数据表名称
        'field_pre' => 'tvvl', //字段前缀
        'db_comm' => '首页视频广告日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'otype', //操作类型（1：展示广告 2：点击广告 3：跳过广告）
                'param_name' => 'otype', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '操作类型（1：展示广告 2：点击广告 3：跳过广告）' //字段注释
            )
        )
    ),
    2 => array( //暂停广告展示点击跳过
        'dir_name' => 'pause_video_vert', //数据存放文件夹
        'db_name' => 'kyx_pause_video_vert_log', //数据表名称
        'field_pre' => 'pvvl', //字段前缀
        'db_comm' => '暂停视频广告日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'otype', //操作类型（1：展示广告 2：点击广告 3：跳过广告）
                'param_name' => 'otype', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '操作类型（1：展示广告 2：点击广告 3：跳过广告）' //字段注释
            )
        )
    ),
    3 => array( //右下角广告展示点击跳过
        'dir_name' => 'right_video_vert', //数据存放文件夹
        'db_name' => 'kyx_right_video_vert_log', //数据表名称
        'field_pre' => 'rvvl', //字段前缀
        'db_comm' => '右下角视频广告日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'otype', //操作类型（1：展示广告 2：点击广告 3：跳过广告）
                'param_name' => 'otype', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '操作类型（1：展示广告 2：点击广告 3：跳过广告）' //字段注释
            )
        )
    ),
    4 => array( //视频广告安装
        'dir_name' => 'video_vert_install', //数据存放文件夹
        'db_name' => 'kyx_video_vert_install_log', //数据表名称
        'field_pre' => 'vvil', //字段前缀
        'db_comm' => '视频广告安装日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array( //私有参数列表（不带前缀）
            0 => array(
                'type' => 0, //参数类型（1：非整数 0：整数）
                'name' => 'type', //安装位置（1：片头 2：暂停 3：右下角）
                'param_name' => 'type', //获取json值参数名
                'field_type' => 'tinyint', //字段类型
                'field_len' => 1, //字段长度
                'field_comm' => '安装位置（1：片头 2：暂停 3：右下角）' //字段注释
            )
        )
    ),
    5 => array( //视频广告下载
        'dir_name' => 'video_vert_down', //数据存放文件夹
        'db_name' => 'kyx_video_vert_down_log', //数据表名称
        'field_pre' => 'vvdl', //字段前缀
        'db_comm' => '视频广告下载日志表',
        'is_export' => 1, //是否需要归档
        'pri_field' => array()
    )
);

//视频广告统计参数配置（使用redis）
$video_vert_redis_param = array(
    1 => array(  //首页广告展示点击跳过
        'log_db_name' => 'kyx_titles_video_vert_log', //日志表名称
        'db_name' => 'kyx_video_vert_oper_time', //统计表名称
        'field_pre' => 'tvvl', //字段前缀
        'dis' => '视频广告展示点击跳过统计表', //描述
        'eid_key' => 'otype', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'tvvl_adid,tvvl_pn,tvvl_md,tvvl_chl', //统计维度字段
        'many_eid' => array( //多事件统计
            1 => 'snum', //展示广告
            2 => 'cnum', //点击广告
            3 => 'onum'  //跳过广告
        ),
        'today_num' => 0, //是否统计当天独立用户数
        'other_sql' => '1 as `wtype`', //其他维度sql
        'key_val' => array( //字段对应关系
            'aid' => array('type' => 0, 'name' => 'tvvl_adid'),//广告id,type=0正型 1：非整型
            'ad_title' => array('type' => 1, 'name' => 'tvvl_adtitle'),//广告id,type=0正型 1：非整型
            'type' => array('type' => 0, 'name' => 'wtype'),//广告位置（1：片头 2：暂停 3：右下角）
            'show_num' => array('type' => 0, 'name' => 'snum'),//广告展示次数
            "mac_show_num" => array('type' => 0, 'name' => 'isnum'), //广告独立展示次数
            "click_num" => array('type' => 0, 'name' => 'cnum'), //广告点击次数
            "mac_click_num" => array('type' => 0, 'name' => 'icnum'), //广告独立点击次数
            "over_num" => array('type' => 0, 'name' => 'onum'), //跳过次数
            "mac_over_num" => array('type' => 0, 'name' => 'ionum') //独立跳过次数
        )
    ),
    2 => array(  //暂停广告展示点击跳过
        'log_db_name' => 'kyx_pause_video_vert_log', //日志表名称
        'db_name' => 'kyx_video_vert_oper_time', //统计表名称
        'field_pre' => 'pvvl', //字段前缀
        'dis' => '视频广告展示点击跳过统计表', //描述
        'eid_key' => 'otype', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'pvvl_adid,pvvl_pn,pvvl_md,pvvl_chl', //统计维度字段
        'many_eid' => array( //多事件统计
            1 => 'snum', //展示广告
            2 => 'cnum', //点击广告
            3 => 'onum'  //跳过广告
        ),
        'today_num' => 0, //是否统计当天独立用户数
        'other_sql' => '2 as `wtype`', //其他维度sql
        'key_val' => array( //字段对应关系
            'aid' => array('type' => 0, 'name' => 'pvvl_adid'),//广告id,type=0正型 1：非整型
            'ad_title' => array('type' => 1, 'name' => 'pvvl_adtitle'),//广告id,type=0正型 1：非整型
            'type' => array('type' => 0, 'name' => 'wtype'),//广告位置（1：片头 2：暂停 3：右下角）
            'show_num' => array('type' => 0, 'name' => 'snum'),//广告展示次数
            "mac_show_num" => array('type' => 0, 'name' => 'isnum'), //广告独立展示次数
            "click_num" => array('type' => 0, 'name' => 'cnum'), //广告点击次数
            "mac_click_num" => array('type' => 0, 'name' => 'icnum'), //广告独立点击次数
            "over_num" => array('type' => 0, 'name' => 'onum'), //跳过次数
            "mac_over_num" => array('type' => 0, 'name' => 'ionum') //独立跳过次数
        )
    ),
    3 => array(  //右下角广告展示点击跳过
        'log_db_name' => 'kyx_right_video_vert_log', //日志表名称
        'db_name' => 'kyx_video_vert_oper_time', //统计表名称
        'field_pre' => 'rvvl', //字段前缀
        'dis' => '视频广告展示点击跳过统计表', //描述
        'eid_key' => 'otype', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'rvvl_adid,rvvl_pn,rvvl_md,rvvl_chl', //统计维度字段
        'many_eid' => array( //多事件统计
            1 => 'snum', //展示广告
            2 => 'cnum', //点击广告
            3 => 'onum'  //跳过广告
        ),
        'today_num' => 0, //是否统计当天独立用户数
        'other_sql' => '3 as `wtype`', //其他维度sql
        'key_val' => array( //字段对应关系
            'aid' => array('type' => 0, 'name' => 'rvvl_adid'),//广告id,type=0正型 1：非整型
            'ad_title' => array('type' => 1, 'name' => 'rvvl_adtitle'),//广告id,type=0正型 1：非整型
            'type' => array('type' => 0, 'name' => 'wtype'),//广告位置（1：片头 2：暂停 3：右下角）
            'show_num' => array('type' => 0, 'name' => 'snum'),//广告展示次数
            "mac_show_num" => array('type' => 0, 'name' => 'isnum'), //广告独立展示次数
            "click_num" => array('type' => 0, 'name' => 'cnum'), //广告点击次数
            "mac_click_num" => array('type' => 0, 'name' => 'icnum'), //广告独立点击次数
            "over_num" => array('type' => 0, 'name' => 'onum'), //跳过次数
            "mac_over_num" => array('type' => 0, 'name' => 'ionum') //独立跳过次数
        )
    ),
    4 => array(  //视频广告安装
        'log_db_name' => 'kyx_video_vert_install_log', //日志表名称
        'db_name' => 'kyx_video_vert_install_time', //统计表名称
        'field_pre' => 'vvil', //字段前缀
        'dis' => '视频广告安装统计表', //描述
        'eid_key' => '', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'vvil_adid,vvil_pn,vvil_md,vvil_chl,vvil_type', //统计维度字段
        'many_eid' => array(),
        'today_num' => 0, //是否统计当天独立用户数
        'other_sql' => 'vvil_type as wtype,count(1) as inum,count(DISTINCT `vvil_mac`,`vvil_imei`) as iinum', //其他维度sql
        'key_val' => array( //字段对应关系
            'aid' => array('type' => 0, 'name' => 'vvil_adid'),//广告id,type=0正型 1：非整型
            'ad_title' => array('type' => 1, 'name' => 'vvil_adtitle'),//广告id,type=0正型 1：非整型
            'type' => array('type' => 0, 'name' => 'wtype'),//广告位置（1：片头 2：暂停 3：右下角）
            'inst_succ_num' => array('type' => 0, 'name' => 'inum'),//安装成功次数
            "mac_inst_succ_num" => array('type' => 0, 'name' => 'iinum') //独立安装成功次数
        )
    ),
    5 => array(  //视频广告下载
        'log_db_name' => 'kyx_video_vert_down_log', //日志表名称
        'db_name' => 'kyx_video_vert_down_time', //统计表名称
        'field_pre' => 'vvdl', //字段前缀
        'dis' => '视频广告下载统计表', //描述
        'eid_key' => 'eid', //多事件统计字段名（不带字段前缀的）
        'dimensionality' => 'vvdl_adid,vvdl_pn,vvdl_md,vvdl_chl', //统计维度字段
        'many_eid' => array(
            10004 => 'nsnum', //不显示广告（已安装）
            10005 => 'sdnum', //开始下载
            10007 => 'dsnum'  //下载成功
        ),
        'today_num' => 0, //是否统计当天独立用户数
        'other_sql' => '(COUNT(if(`vvdl_eid` = 10005,true,null)) - COUNT(if(`vvdl_eid` = 10007,true,null))) as dfnum,if((COUNT(DISTINCT if(`vvdl_eid` = 10005,true,null)) - COUNT(DISTINCT if(`vvdl_eid` = 10007,true,null))) = 0,1,(COUNT(DISTINCT if(`vvdl_eid` = 10005,true,null)) - COUNT(DISTINCT if(`vvdl_eid` = 10007,true,null)))) as idfnum', //其他维度sql
        'key_val' => array( //字段对应关系
            "aid" => array('type' => 0, 'name' => 'vvdl_adid'),//广告id,type=0正型 1：非整型
            'ad_title' => array('type' => 1, 'name' => 'vvdl_adtitle'),//广告id,type=0正型 1：非整型
            "has_inst_num" => array('type' => 0, 'name' => 'nsnum'),//已安装次数
            "mac_has_inst_num" => array('type' => 0, 'name' => 'insnum'), //独立已安装次数
            "down_num" => array('type' => 0, 'name' => 'sdnum'),//开始下载次数
            "mac_down_num" => array('type' => 0, 'name' => 'isdnum'), //独立开始下载次数
            "down_succ_num" => array('type' => 0, 'name' => 'dsnum'),//下载成功次数
            "mac_down_succ_num" => array('type' => 0, 'name' => 'idsnum'), //独立下载成功次数
            "down_fail_num" => array('type' => 0, 'name' => 'dfnum'),//下载失败次数
            "mac_down_fail_num" => array('type' => 0, 'name' => 'idfnum') //独立下载失败次数
        )
    )
);