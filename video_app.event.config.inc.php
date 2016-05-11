<?php
//公共字段
$arr_public_fields = array(
        'in_date'=>array('type'=>'int','len'=>11,'comment'=>'服务器端的记录日期(Ymd)'),  
        'intime'=>array('type'=>'int','len'=>11,'comment'=>'服务器接收时间戳'),
        'pn'=>array('type'=>'varchar','len'=>200,'comment'=>'游戏包名'),
        'title'=>array('type'=>'varchar','len'=>100,'comment'=>'APP标题或游戏标题'),
        'md'=>array('type'=>'varchar','len'=>200,'comment'=>'型号'),
        'bd'=>array('type'=>'varchar','len'=>100,'comment'=>'品牌'),
        'sdkbv'=>array('type'=>'varchar','len'=>100,'comment'=>'系统版本号'),
        'vc'=>array('type'=>'int','len'=>10,'comment'=>'游戏版本号'),
        'vn'=>array('type'=>'varchar','len'=>100,'comment'=>'游戏版本名称'),
        'mac'=>array('type'=>'varchar','len'=>200,'comment'=>'设备MAC地址'),
        'chl'=>array('type'=>'varchar','len'=>100,'comment'=>'渠道号'),
        'eid'=>array('type'=>'varchar','len'=>50,'comment'=>'事件ID'),
        'imei'=>array('type'=>'varchar','len'=>100,'comment'=>'imei串号'),
        'ct'=>array('type'=>'bigint','len'=>20,'comment'=>'客户端时间戳(毫秒级别)'),
        'st'=>array('type'=>'bigint','len'=>20,'comment'=>'日志记录时间 long型数据，毫秒级'),
        'ip'=>array('type'=>'varchar','len'=>100,'comment'=>'获取客户端的IP 字符串'),
);

//模块配置
$arr_event_config = array(
    /**
     * @name:video_app_section
     * @description: 版块点击统计
     * @author: Xiong Jianbang
     * @create: 2015-10-12 上午10:28:12
     **/
	'video_app_section' => array(
	        'dir_name'     => 'video_app_section', //文件目录名称
	        'import_table_name' => 'kyx_video_app_section_log', //创建的导入数据表名称
	        'statistics_table_name' => 'kyx_video_app_section', //创建的统计数据表名称
	        'event_name' => '版块点击统计',//事件描述
	        'eid' => array(600000),//事件ID号
	        'log_files_count' => 10,  //随机读取写入的日志文件数量
	        'import_prefix' => 'vas_',  //字段的前缀
	        'import_fields' => array(//表字段定义
	                'module'=>array('type'=>'varchar','len'=>200,'comment'=>'banner，最近更新，推荐热门'),
	        ),
	        'statistics_fields' => array(//统计表字段定义
	                'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
	                'pn'=>array('type'=>'varchar','len'=>200,'comment'=>'游戏包名'),
	                'title'=>array('type'=>'varchar','len'=>100,'comment'=>'APP标题或游戏标题'),
	                'module'=>array('type'=>'varchar','len'=>200,'comment'=>'banner，最近更新，推荐热门'),
	                'chl'=>array('type'=>'varchar','len'=>100,'comment'=>'渠道号'),
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量')
	        ),
    ),
    /**
     * @name:video_app_home
     * @description: 首页点击统计
     * @author: Xiong Jianbang
     * @create: 2015-10-12 上午10:41:54
     **/
    'video_app_home' => array(
            'dir_name'     => 'video_app_home', //首页点击统计 
            'import_table_name' => 'kyx_video_app_home_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_video_app_home', //创建的统计数据表名称
            'event_name' => '首页点击统计 ',//事件描述
            'eid' => array(600001),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'vah_',
            'import_fields' => array(//表字段定义
                    'videoid'=>array('type'=>'int','len'=>10,'comment'=>'视频ID'),
                    'videotitle'=>array('type'=>'varchar','len'=>100,'comment'=>'视频标题'),
                    'videourl'=>array('type'=>'varchar','len'=>200,'comment'=>'视频播放URL'),
                    'module'=>array('type'=>'varchar','len'=>50,'comment'=>'banner，最近更新，推荐热门'),
                    'clickpos'=>array('type'=>'int','len'=>5,'comment'=>'点击位置，-1为更多'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'pn'=>array('type'=>'varchar','len'=>200,'comment'=>'游戏包名'),
                    'title'=>array('type'=>'varchar','len'=>100,'comment'=>'APP标题或游戏标题'),
	                'module'=>array('type'=>'varchar','len'=>50,'comment'=>'banner，最近更新，推荐热门'),
	                'clickpos'=>array('type'=>'int','len'=>5,'comment'=>'点击位置，-1为更多'),
	                'chl'=>array('type'=>'varchar','len'=>100,'comment'=>'渠道号'),
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    
    
    /**
     * @name:video_app_author
     * @description: 视频作者点击统计
     * @author: Xiong Jianbang
     * @create: 2015-10-12 上午10:52:00
     **/
    'video_app_author' => array(
            'dir_name'     => 'video_app_author', //文件目录名称
            'import_table_name' => 'kyx_video_app_author_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_video_app_author', //创建的统计数据表名称
            'event_name' => '视频作者点击统计',//事件描述
            'eid' => array(600002),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'vaa_',
            'import_fields' => array(//表字段定义
                    'authorid'=>array('type'=>'int','len'=>10,'comment'=>'作者ID'),
                    'author'=>array('type'=>'varchar','len'=>200,'comment'=>'作者名称'),
            ),
            'statistics_fields' => array(//统计表字段定义
                'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                'pn'=>array('type'=>'varchar','len'=>200,'comment'=>'游戏包名'),
                'title'=>array('type'=>'varchar','len'=>100,'comment'=>'APP标题或游戏标题'),
                'authorid'=>array('type'=>'int','len'=>10,'comment'=>'作者ID'),
                'author'=>array('type'=>'varchar','len'=>200,'comment'=>'作者名称'),
                'chl'=>array('type'=>'varchar','len'=>100,'comment'=>'渠道号'),
                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),//数量
            ),
    ),
    /**
     * @name:video_app_album
     * @description: 专辑点击
     * @author: Xiong Jianbang
     * @create: 2015-10-12 下午12:07:39
     **/
    'video_app_album' => array(
            'dir_name'     => 'video_app_album', //文件目录名称
            'import_table_name' => 'kyx_video_app_album_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_video_app_album', //创建的统计数据表名称
            'event_name' => '专辑点击',//事件描述
            'eid' => array(600003),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'vaa_',
            'import_fields' => array(//表字段定义
                    'topicid'=>array('type'=>'int','len'=>10,'comment'=>'专辑id'),
                    'topictitle'=>array('type'=>'varchar','len'=>20,'comment'=>'专辑名称'),
                    'authorid'=>array('type'=>'int','len'=>10,'comment'=>'作者ID'),
                    'author'=>array('type'=>'varchar','len'=>200,'comment'=>'作者名称'),
            ),
            'statistics_fields' => array(//统计表字段定义
                'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                'pn'=>array('type'=>'varchar','len'=>200,'comment'=>'游戏包名'),
                'title'=>array('type'=>'varchar','len'=>100,'comment'=>'APP标题或游戏标题'),
                'topicid'=>array('type'=>'int','len'=>10,'comment'=>'专辑id'),
                'topictitle'=>array('type'=>'varchar','len'=>20,'comment'=>'专辑名称'),
                'authorid'=>array('type'=>'int','len'=>10,'comment'=>'作者ID'),
                'author'=>array('type'=>'varchar','len'=>200,'comment'=>'作者名称'),
                'chl'=>array('type'=>'varchar','len'=>100,'comment'=>'渠道号'),
                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),//数量
            ),
    ),
    /**
     * @name:video_app_play
     * @description: 播放统计
     * @author: Xiong Jianbang
     * @create: 2015-10-12 下午12:11:00
     **/
    'video_app_play' => array(
            'dir_name'     => 'video_app_play', //文件目录名称
            'import_table_name' => 'kyx_video_app_play_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_video_app_play', //创建的统计数据表名称
            'event_name' => '播放统计',//事件描述
            'eid' => array(600004),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'ap_',
            'import_fields' => array(//表字段定义
                    'videoid'=>array('type'=>'int','len'=>10,'comment'=>'视频ID'),
                    'videotitle'=>array('type'=>'varchar','len'=>200,'comment'=>'视频标题'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'pn'=>array('type'=>'varchar','len'=>200,'comment'=>'游戏包名'),
                    'title'=>array('type'=>'varchar','len'=>100,'comment'=>'APP标题或游戏标题'),
                    'chl'=>array('type'=>'varchar','len'=>100,'comment'=>'渠道号'),
                    'videoid'=>array('type'=>'int','len'=>10,'comment'=>'视频ID'),
                    'videotitle'=>array('type'=>'varchar','len'=>200,'comment'=>'视频标题'),
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    /**
     * @name:video_app_rank
     * @description: 排行点击统计
     * @author: Xiong Jianbang
     * @create: 2015-10-12 下午12:13:39
     **/
    'video_app_rank' => array(
            'dir_name'     => 'video_app_rank', //文件目录名称
            'import_table_name' => 'kyx_video_app_rank_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_video_app_rank', //创建的统计数据表名称
            'event_name' => '排行点击统计',//事件描述
            'eid' => array(600005),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'var_',
            'import_fields' => array(//表字段定义
                      'videoid'=>array('type'=>'int','len'=>10,'comment'=>'视频ID'),
                      'videotitle'=>array('type'=>'varchar','len'=>200,'comment'=>'视频标题'),
                      'videourl'=>array('type'=>'varchar','len'=>200,'comment'=>'视频播放URL'),
                      'clickpos'=>array('type'=>'int','len'=>5,'comment'=>'点击位置，-1为更多'),
                      'ranktype'=>array('type'=>'int','len'=>5,'comment'=>'排行类型'),
            ),
             'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'pn'=>array('type'=>'varchar','len'=>200,'comment'=>'游戏包名'),
                    'title'=>array('type'=>'varchar','len'=>100,'comment'=>'APP标题或游戏标题'),
                    'chl'=>array('type'=>'varchar','len'=>100,'comment'=>'渠道号'),
	                'clickpos'=>array('type'=>'int','len'=>5,'comment'=>'点击位置，-1为更多'),
	                'ranktype'=>array('type'=>'int','len'=>5,'comment'=>'排行类型'),
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    /**
     * @name:video_app_related
     * @description: 相关推荐点击统计
     * @author: Xiong Jianbang
     * @create: 2015-10-12 上午10:06:57
     **/
    'video_app_related' => array(
            'dir_name'     => 'video_app_related', //文件目录名称
            'import_table_name' => 'kyx_video_app_related_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_video_app_related', //创建的统计数据表名称
            'event_name' => '相关推荐点击统计',//事件描述
            'eid' => array(600006),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'var_',
            'import_fields' => array(//表字段定义
                    'videoid'=>array('type'=>'int','len'=>10,'comment'=>'视频ID'),
                    'videotitle'=>array('type'=>'varchar','len'=>200,'comment'=>'视频标题'),
                    'videourl'=>array('type'=>'varchar','len'=>200,'comment'=>'视频播放URL'),
                    'clickpos'=>array('type'=>'int','len'=>5,'comment'=>'点击位置，-1为更多'),
                    'relatedvideoid'=>array('type'=>'int','len'=>5,'comment'=>'相关推荐ID'),
                    'relatedvideotitle'=>array('type'=>'varchar','len'=>200,'comment'=>'相关推荐标题'),
                    'relatedvideourl'=>array('type'=>'varchar','len'=>200,'comment'=>'相关推荐URL'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'pn'=>array('type'=>'varchar','len'=>200,'comment'=>'游戏包名'),
                    'title'=>array('type'=>'varchar','len'=>100,'comment'=>'APP标题或游戏标题'),
                    'chl'=>array('type'=>'varchar','len'=>100,'comment'=>'渠道号'),
                    'videoid'=>array('type'=>'int','len'=>10,'comment'=>'视频ID'),
	                'clickpos'=>array('type'=>'int','len'=>5,'comment'=>'点击位置，-1为更多'),
	                'relatedvideoid'=>array('type'=>'int','len'=>5,'comment'=>'相关推荐ID'),
	                'relatedvideotitle'=>array('type'=>'varchar','len'=>200,'comment'=>'相关推荐标题'),
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    
    /**
     * @name:video_app_set
     * @description: 设置点击统计
     * @author: Xiong Jianbang
     * @create: 2015-10-12 上午10:06:57
     **/
    'video_app_set' => array(
            'dir_name'     => 'video_app_set', //文件目录名称
            'import_table_name' => 'kyx_video_app_set_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_video_app_set', //创建的统计数据表名称
            'event_name' => '设置点击统计',//事件描述
            'eid' => array(600007),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'vas_',
            'import_fields' => array(//表字段定义
                    'type'=>array('type'=>'int','len'=>5,'comment'=>'类别'),
                    'state'=>array('type'=>'int','len'=>2,'comment'=>'开关'),
             ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'pn'=>array('type'=>'varchar','len'=>200,'comment'=>'游戏包名'),
                    'title'=>array('type'=>'varchar','len'=>100,'comment'=>'APP标题或游戏标题'),
                    'chl'=>array('type'=>'varchar','len'=>100,'comment'=>'渠道号'),
                    'type'=>array('type'=>'int','len'=>5,'comment'=>'类别'),
                    'state'=>array('type'=>'int','len'=>2,'comment'=>'开关'),
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
);


